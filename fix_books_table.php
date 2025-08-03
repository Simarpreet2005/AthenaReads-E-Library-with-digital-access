<?php
require_once 'config/database.php';

echo "<h2>Fixing Books Table Structure</h2>";

// Step 1: Check current books table structure
echo "<h3>Step 1: Checking Current Books Table Structure</h3>";
try {
    $stmt = $pdo->query("DESCRIBE books");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Current columns in books table:</p>";
    foreach ($columns as $column) {
        echo "<p>- $column</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error checking table structure: " . $e->getMessage() . "</p>";
}

// Step 2: Add missing columns to books table
echo "<hr><h3>Step 2: Adding Missing Columns</h3>";

$alterQueries = [
    "ALTER TABLE books ADD COLUMN IF NOT EXISTS category_id INT",
    "ALTER TABLE books ADD COLUMN IF NOT EXISTS file_path VARCHAR(255)",
    "ALTER TABLE books ADD COLUMN IF NOT EXISTS isbn VARCHAR(20)",
    "ALTER TABLE books ADD COLUMN IF NOT EXISTS total_quantity INT NOT NULL DEFAULT 1",
    "ALTER TABLE books ADD COLUMN IF NOT EXISTS available_quantity INT NOT NULL DEFAULT 1",
    "ALTER TABLE books ADD COLUMN IF NOT EXISTS status ENUM('available', 'borrowed', 'reserved') DEFAULT 'available'",
    "ALTER TABLE books ADD COLUMN IF NOT EXISTS added_by INT",
    "ALTER TABLE books ADD FOREIGN KEY IF NOT EXISTS (category_id) REFERENCES categories(id)",
    "ALTER TABLE books ADD FOREIGN KEY IF NOT EXISTS (added_by) REFERENCES users(id)"
];

foreach ($alterQueries as $query) {
    try {
        $pdo->exec($query);
        echo "<p style='color: green;'>✅ Column added/modified successfully</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Column already exists or error: " . $e->getMessage() . "</p>";
    }
}

// Step 3: Clear existing books and add them properly
echo "<hr><h3>Step 3: Adding Sample Books</h3>";

// Clear existing books
try {
    $stmt = $pdo->prepare("DELETE FROM books");
    $stmt->execute();
    echo "<p style='color: orange;'>⚠️ Removed existing books</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error removing books: " . $e->getMessage() . "</p>";
}

// Get category IDs
$categoryMap = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM categories");
    $categories = $stmt->fetchAll();
    foreach ($categories as $cat) {
        $categoryMap[$cat['name']] = $cat['id'];
    }
    echo "<p style='color: green;'>✅ Found " . count($categoryMap) . " categories</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error getting categories: " . $e->getMessage() . "</p>";
}

// Get admin user ID
$adminId = 1; // Default fallback
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();
    if ($admin) {
        $adminId = $admin['id'];
        echo "<p style='color: green;'>✅ Found admin user ID: $adminId</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error getting admin user: " . $e->getMessage() . "</p>";
}

$sampleBooks = [
    [
        'title' => 'The Great Gatsby',
        'author' => 'F. Scott Fitzgerald',
        'description' => 'A story of the fabulously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan.',
        'cover_image' => 'assets/images/books/67fba7f66bd9e.jpg',
        'category' => 'Classic Literature',
        'isbn' => '978-0743273565'
    ],
    [
        'title' => 'To Kill a Mockingbird',
        'author' => 'Harper Lee',
        'description' => 'The story of racial injustice and the loss of innocence in the American South.',
        'cover_image' => 'assets/images/books/67fba85c69b96.jpg',
        'category' => 'Classic Literature',
        'isbn' => '978-0446310789'
    ],
    [
        'title' => '1984',
        'author' => 'George Orwell',
        'description' => 'A dystopian social science fiction novel and cautionary tale.',
        'cover_image' => 'assets/images/books/67fba8f05576c.jpg',
        'category' => 'Science Fiction',
        'isbn' => '978-0451524935'
    ],
    [
        'title' => 'Pride and Prejudice',
        'author' => 'Jane Austen',
        'description' => 'A romantic novel of manners that follows the emotional development of Elizabeth Bennet.',
        'cover_image' => 'assets/images/books/67fbaf5806fe3.jpg',
        'category' => 'Romance',
        'isbn' => '978-0141439518'
    ],
    [
        'title' => 'The Catcher in the Rye',
        'author' => 'J.D. Salinger',
        'description' => 'The story of teenage alienation and loss of innocence.',
        'cover_image' => 'assets/images/books/67fbb0e93e302.jpg',
        'category' => 'Coming of Age',
        'isbn' => '978-0316769488'
    ]
];

$addedBooks = 0;
foreach ($sampleBooks as $book) {
    try {
        $categoryId = $categoryMap[$book['category']] ?? null;
        
        $stmt = $pdo->prepare("
            INSERT INTO books (title, author, description, cover_image, category, category_id, isbn, total_quantity, available_quantity, added_by, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $book['title'],
            $book['author'],
            $book['description'],
            $book['cover_image'],
            $book['category'],
            $categoryId,
            $book['isbn'],
            3, // total_quantity
            3, // available_quantity
            $adminId
        ]);
        
        $bookId = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Added: '{$book['title']}' by {$book['author']} (ID: $bookId)</p>";
        $addedBooks++;
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error adding '{$book['title']}': " . $e->getMessage() . "</p>";
    }
}

echo "<p style='color: green;'>✅ Successfully added: $addedBooks books</p>";

// Step 4: Verify books
echo "<hr><h3>Step 4: Verifying Books</h3>";
try {
    $stmt = $pdo->query("SELECT id, title, author, cover_image, category FROM books");
    $books = $stmt->fetchAll();
    echo "<p style='color: green;'>✅ Found " . count($books) . " books in database</p>";
    
    foreach ($books as $book) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0; background: #f9f9f9;'>";
        echo "<strong>{$book['title']}</strong> by {$book['author']}<br>";
        echo "<small>Category: {$book['category']}</small><br>";
        echo "<small>Image: {$book['cover_image']}</small><br>";
        
        if (file_exists($book['cover_image'])) {
            echo "<img src='{$book['cover_image']}' style='max-width: 100px; max-height: 150px; border: 1px solid #ddd; margin: 5px;'><br>";
            echo "<span style='color: green;'>✅ Image file exists</span>";
        } else {
            echo "<span style='color: red;'>❌ Image file not found</span>";
        }
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error verifying books: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🎉 Books Table Fixed!</h3>";
echo "<p><a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📚 View Library</a></p>";
echo "<p><a href='books.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📖 View All Books</a></p>";
echo "<p><a href='categories.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📂 View Categories</a></p>";
?> 