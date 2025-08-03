<?php
require_once 'config/database.php';

echo "<h2>Database Status Check</h2>";

// Check if tables exist
echo "<h3>Table Status:</h3>";
$tables = ['users', 'books', 'categories', 'book_borrows', 'book_favorites', 'book_reservations', 'borrows', 'reservations'];

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<p style='color: green;'>✅ Table '$table' exists with $count records</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Table '$table' does not exist or has error: " . $e->getMessage() . "</p>";
    }
}

// Check users
echo "<hr><h3>Users:</h3>";
try {
    $stmt = $pdo->query("SELECT id, username, email, role FROM users");
    $users = $stmt->fetchAll();
    if (count($users) > 0) {
        foreach ($users as $user) {
            echo "<p>ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}, Role: {$user['role']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No users found</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error checking users: " . $e->getMessage() . "</p>";
}

// Check books
echo "<hr><h3>Books:</h3>";
try {
    $stmt = $pdo->query("SELECT id, title, author, cover_image FROM books");
    $books = $stmt->fetchAll();
    if (count($books) > 0) {
        foreach ($books as $book) {
            echo "<p>ID: {$book['id']}, Title: {$book['title']}, Author: {$book['author']}, Image: {$book['cover_image']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No books found</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error checking books: " . $e->getMessage() . "</p>";
}

// Check categories
echo "<hr><h3>Categories:</h3>";
try {
    $stmt = $pdo->query("SELECT id, name, description FROM categories");
    $categories = $stmt->fetchAll();
    if (count($categories) > 0) {
        foreach ($categories as $category) {
            echo "<p>ID: {$category['id']}, Name: {$category['name']}, Description: {$category['description']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No categories found</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error checking categories: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<p><a href='complete_database_setup.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🚀 Run Complete Database Setup</a></p>";
echo "<p><a href='index.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>📚 View Library</a></p>";
?> 