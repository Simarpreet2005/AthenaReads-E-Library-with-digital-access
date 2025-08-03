<?php
require_once 'config/database.php';

echo "<h2>Fixing Upload Directories</h2>";

// Step 1: Create upload directories if they don't exist
echo "<h3>Step 1: Creating Upload Directories</h3>";

$directories = [
    'uploads',
    'uploads/covers',
    'uploads/books'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p style='color: green;'>✅ Created directory: $dir</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create directory: $dir</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Directory already exists: $dir</p>";
    }
}

// Step 2: Set proper permissions
echo "<hr><h3>Step 2: Setting Permissions</h3>";

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (chmod($dir, 0755)) {
            echo "<p style='color: green;'>✅ Set permissions for: $dir</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to set permissions for: $dir</p>";
        }
    }
}

// Step 3: Copy sample images to uploads directory
echo "<hr><h3>Step 3: Copying Sample Images</h3>";

$sampleImages = [
    'assets/images/books/67fba7f66bd9e.jpg' => 'uploads/covers/great-gatsby.jpg',
    'assets/images/books/67fba85c69b96.jpg' => 'uploads/covers/mockingbird.jpg',
    'assets/images/books/67fba8f05576c.jpg' => 'uploads/covers/1984.jpg',
    'assets/images/books/67fbaf5806fe3.jpg' => 'uploads/covers/pride-prejudice.jpg',
    'assets/images/books/67fbb0e93e302.jpg' => 'uploads/covers/catcher-rye.jpg'
];

$copiedCount = 0;
foreach ($sampleImages as $source => $destination) {
    if (file_exists($source)) {
        if (copy($source, $destination)) {
            echo "<p style='color: green;'>✅ Copied: $source → $destination</p>";
            $copiedCount++;
        } else {
            echo "<p style='color: red;'>❌ Failed to copy: $source</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Source file not found: $source</p>";
    }
}

echo "<p style='color: green;'>✅ Copied $copiedCount sample images</p>";

// Step 4: Update database with correct image paths
echo "<hr><h3>Step 4: Updating Database Image Paths</h3>";

$imageUpdates = [
    'assets/images/books/67fba7f66bd9e.jpg' => 'uploads/covers/great-gatsby.jpg',
    'assets/images/books/67fba85c69b96.jpg' => 'uploads/covers/mockingbird.jpg',
    'assets/images/books/67fba8f05576c.jpg' => 'uploads/covers/1984.jpg',
    'assets/images/books/67fbaf5806fe3.jpg' => 'uploads/covers/pride-prejudice.jpg',
    'assets/images/books/67fbb0e93e302.jpg' => 'uploads/covers/catcher-rye.jpg'
];

$updatedCount = 0;
foreach ($imageUpdates as $oldPath => $newPath) {
    try {
        $stmt = $pdo->prepare("UPDATE books SET cover_image = ? WHERE cover_image = ?");
        $stmt->execute([$newPath, $oldPath]);
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Updated image path: $oldPath → $newPath</p>";
            $updatedCount++;
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error updating image path: " . $e->getMessage() . "</p>";
    }
}

echo "<p style='color: green;'>✅ Updated $updatedCount image paths</p>";

// Step 5: Show current books with their images
echo "<hr><h3>Step 5: Current Books and Images</h3>";

try {
    $stmt = $pdo->query("SELECT id, title, author, cover_image FROM books");
    $books = $stmt->fetchAll();
    
    if (count($books) > 0) {
        foreach ($books as $book) {
            echo "<div style='border: 1px solid #ccc; padding: 15px; margin: 15px 0; background: #f9f9f9; border-radius: 8px;'>";
            echo "<h4 style='margin: 0 0 10px 0; color: #333;'><strong>{$book['title']}</strong> by {$book['author']}</h4>";
            echo "<p style='margin: 5px 0; color: #666;'><small>Image path: {$book['cover_image']}</small></p>";
            
            if (file_exists($book['cover_image'])) {
                echo "<img src='{$book['cover_image']}' style='max-width: 120px; max-height: 180px; border: 2px solid #ddd; border-radius: 5px; margin: 10px 0;'><br>";
                echo "<span style='color: green; font-weight: bold;'>✅ Image displays correctly</span>";
            } else {
                echo "<div style='width: 120px; height: 180px; background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; margin: 10px 0;'>";
                echo "<span style='color: #999;'>No Image</span>";
                echo "</div>";
                echo "<span style='color: red; font-weight: bold;'>❌ Image file not found</span>";
            }
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>❌ No books found in database</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error fetching books: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🎉 Upload System Ready!</h3>";
echo "<p style='color: green;'>✅ Upload directories created</p>";
echo "<p style='color: green;'>✅ Sample images copied</p>";
echo "<p style='color: green;'>✅ Database paths updated</p>";

echo "<hr>";
echo "<h3>How to Add Your Own Book Covers:</h3>";
echo "<ol style='text-align: left; max-width: 600px; margin: 0 auto;'>";
echo "<li><strong>Login as Admin:</strong> admin@elibrary.com / admin123</li>";
echo "<li><strong>Go to Admin Dashboard:</strong> <a href='admin/dashboard.php' style='color: #007bff;'>Admin Dashboard</a></li>";
echo "<li><strong>Click 'Add Book':</strong> <a href='admin/add-book.php' style='color: #007bff;'>Add New Book</a></li>";
echo "<li><strong>Fill in book details:</strong> Title, Author, Category, etc.</li>";
echo "<li><strong>Upload Cover Image:</strong> Click 'Choose File' and select your image</li>";
echo "<li><strong>Upload PDF (optional):</strong> If you have the book PDF</li>";
echo "<li><strong>Click 'Add Book':</strong> Your book will be added with your cover image</li>";
echo "</ol>";

echo "<hr>";
echo "<h3>Quick Links:</h3>";
echo "<p><a href='admin/login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🔐 Admin Login</a></p>";
echo "<p><a href='admin/add-book.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📚 Add New Book</a></p>";
echo "<p><a href='index.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🏠 View Library</a></p>";
?> 