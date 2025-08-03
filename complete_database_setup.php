<?php
require_once 'config/database.php';

echo "<h2>Complete Database Setup</h2>";

// Step 1: Create missing tables
echo "<h3>Step 1: Creating Missing Tables</h3>";

$tables = [
    // Categories table
    "CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Users table (if not exists)
    "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Books table (if not exists)
    "CREATE TABLE IF NOT EXISTS books (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(100) NOT NULL,
        isbn VARCHAR(20) UNIQUE,
        description TEXT,
        cover_image VARCHAR(255),
        file_path VARCHAR(255),
        category VARCHAR(50),
        category_id INT,
        added_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total_quantity INT NOT NULL DEFAULT 1,
        available_quantity INT NOT NULL DEFAULT 1,
        status ENUM('available', 'borrowed', 'reserved') DEFAULT 'available',
        FOREIGN KEY (added_by) REFERENCES users(id),
        FOREIGN KEY (category_id) REFERENCES categories(id),
        CHECK (available_quantity >= 0 AND available_quantity <= total_quantity)
    )",
    
    // Book borrows table
    "CREATE TABLE IF NOT EXISTS book_borrows (
        id INT PRIMARY KEY AUTO_INCREMENT,
        book_id INT,
        user_id INT,
        borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        due_date TIMESTAMP NULL,
        return_date TIMESTAMP NULL,
        status ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
        FOREIGN KEY (book_id) REFERENCES books(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    
    // Book favorites table
    "CREATE TABLE IF NOT EXISTS book_favorites (
        id INT PRIMARY KEY AUTO_INCREMENT,
        book_id INT,
        user_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (book_id) REFERENCES books(id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        UNIQUE KEY unique_favorite (book_id, user_id)
    )",
    
    // Book reservations table
    "CREATE TABLE IF NOT EXISTS book_reservations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        book_id INT,
        user_id INT,
        reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expiry_date TIMESTAMP NULL,
        status ENUM('pending', 'approved', 'cancelled', 'expired') DEFAULT 'pending',
        FOREIGN KEY (book_id) REFERENCES books(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    
    // Additional borrows table
    "CREATE TABLE IF NOT EXISTS borrows (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        borrowed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        returned_at DATETIME NULL,
        status ENUM('borrowed', 'returned') DEFAULT 'borrowed',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
    )",
    
    // Additional reservations table
    "CREATE TABLE IF NOT EXISTS reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
    )"
];

$createdTables = 0;
foreach ($tables as $sql) {
    try {
        $pdo->exec($sql);
        $createdTables++;
        echo "<p style='color: green;'>✅ Table created/verified successfully</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠️ Table already exists or error: " . $e->getMessage() . "</p>";
    }
}

echo "<p style='color: green;'>✅ Created/verified $createdTables tables</p>";

// Step 2: Add sample categories
echo "<hr><h3>Step 2: Adding Sample Categories</h3>";

$categories = [
    ['name' => 'Classic Literature', 'description' => 'Timeless literary works'],
    ['name' => 'Science Fiction', 'description' => 'Futuristic and speculative fiction'],
    ['name' => 'Romance', 'description' => 'Love stories and romantic fiction'],
    ['name' => 'Coming of Age', 'description' => 'Stories about growing up and self-discovery'],
    ['name' => 'Mystery', 'description' => 'Detective and crime fiction'],
    ['name' => 'Fantasy', 'description' => 'Imaginative and magical fiction'],
    ['name' => 'Non-Fiction', 'description' => 'Factual and educational books'],
    ['name' => 'Poetry', 'description' => 'Verse and poetic works']
];

$addedCategories = 0;
foreach ($categories as $category) {
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$category['name'], $category['description']]);
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Added category: {$category['name']}</p>";
            $addedCategories++;
        } else {
            echo "<p style='color: orange;'>⚠️ Category already exists: {$category['name']}</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error adding category {$category['name']}: " . $e->getMessage() . "</p>";
    }
}

echo "<p style='color: green;'>✅ Added $addedCategories new categories</p>";

// Step 3: Create admin user
echo "<hr><h3>Step 3: Creating Admin User</h3>";

// Delete existing admin users
try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE role = 'admin'");
    $stmt->execute();
    echo "<p style='color: orange;'>⚠️ Removed existing admin users</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error removing admin users: " . $e->getMessage() . "</p>";
}

// Create new admin user
$adminUsername = 'admin';
$adminEmail = 'admin@elibrary.com';
$adminPassword = 'admin123';
$hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute([$adminUsername, $adminEmail, $hashedPassword]);
    $adminId = $pdo->lastInsertId();
    
    echo "<p style='color: green;'>✅ Admin user created successfully!</p>";
    echo "<p><strong>Admin Login Details:</strong></p>";
    echo "<p>Email: <strong>$adminEmail</strong></p>";
    echo "<p>Password: <strong>$adminPassword</strong></p>";
    echo "<p>User ID: <strong>$adminId</strong></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error creating admin user: " . $e->getMessage() . "</p>";
}

// Step 4: Add sample books
echo "<hr><h3>Step 4: Adding Sample Books</h3>";

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
$stmt = $pdo->query("SELECT id, name FROM categories");
$categories = $stmt->fetchAll();
foreach ($categories as $cat) {
    $categoryMap[$cat['name']] = $cat['id'];
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
            INSERT INTO books (title, author, description, cover_image, category, category_id, isbn, total_quantity, available_quantity, added_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $adminId ?? 1 // added_by
        ]);
        
        $bookId = $pdo->lastInsertId();
        echo "<p style='color: green;'>✅ Added: '{$book['title']}' by {$book['author']} (ID: $bookId)</p>";
        $addedBooks++;
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error adding '{$book['title']}': " . $e->getMessage() . "</p>";
    }
}

echo "<p style='color: green;'>✅ Successfully added: $addedBooks books</p>";

// Step 5: Verify everything
echo "<hr><h3>Step 5: System Verification</h3>";

// Check tables
$tableCount = $pdo->query("SHOW TABLES")->rowCount();
echo "<p style='color: green;'>✅ Database has $tableCount tables</p>";

// Check categories
$categoryCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
echo "<p style='color: green;'>✅ Database has $categoryCount categories</p>";

// Check books
$bookCount = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
echo "<p style='color: green;'>✅ Database has $bookCount books</p>";

// Check users
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
echo "<p style='color: green;'>✅ Database has $userCount users</p>";

echo "<hr>";
echo "<h3>🎉 Setup Complete!</h3>";
echo "<p style='color: green;'>✅ All tables created</p>";
echo "<p style='color: green;'>✅ Sample categories added</p>";
echo "<p style='color: green;'>✅ Admin user created</p>";
echo "<p style='color: green;'>✅ Sample books added</p>";

echo "<hr>";
echo "<h3>Quick Links:</h3>";
echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🔐 Login (admin@elibrary.com / admin123)</a></p>";
echo "<p><a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📚 View Library</a></p>";
echo "<p><a href='categories.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📂 View Categories</a></p>";
echo "<p><a href='admin/dashboard.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>⚙️ Admin Dashboard</a></p>";
?> 