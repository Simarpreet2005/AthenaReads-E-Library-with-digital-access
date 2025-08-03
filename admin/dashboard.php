<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle book deletion
if (isset($_POST['delete_book'])) {
    $book_id = $_POST['book_id'];
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$user_id]);
}

// Get all books
$stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC");
$books = $stmt->fetchAll();

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - E-Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dark-academia.css">
    <style>
        body {
            background-image: url('https://tse4.mm.bing.net/th?id=OIP.h4i-GV4on1w2UmRJSerAbgHaFj&pid=Api&P=0&h=180');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            color: var(--text-color);
        }
        
        .card {
            background: rgba(40, 44, 52, 0.95);
            border: 1px solid var(--accent-color);
            backdrop-filter: blur(10px);
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border-color: var(--light-accent);
        }
        
        .card-header {
            background: rgba(61, 90, 254, 0.2);
            border-bottom: 1px solid var(--accent-color);
            color: var(--text-color);
        }
        
        .card-title {
            color: var(--text-color);
        }
        
        .table {
            color: var(--text-color);
        }
        
        .table th {
            background: rgba(61, 90, 254, 0.1);
            border-color: var(--accent-color);
            color: var(--light-accent);
        }
        
        .table td {
            border-color: var(--accent-color);
        }
        
        .table tbody tr:hover {
            background: rgba(61, 90, 254, 0.1);
        }
        
        .bg-primary {
            background: linear-gradient(135deg, #3d5afe, #304ffe) !important;
        }
        
        .bg-success {
            background: linear-gradient(135deg, #00c853, #00e676) !important;
        }
        
        .bg-info {
            background: linear-gradient(135deg, #00bcd4, #00e5ff) !important;
        }
        
        .bg-warning {
            background: linear-gradient(135deg, #ff9800, #ffc107) !important;
        }
        
        .display-4 {
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .text-white {
            color: white !important;
        }
        
        h1 {
            color: var(--text-color);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            font-weight: bold;
        }
        
        .container {
            background: rgba(40, 44, 52, 0.1);
            border-radius: 15px;
            padding: 20px;
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body>
    <?php include '../includes/admin-navbar.php'; ?>

    <div class="container py-5">
        <h1 class="text-center mb-5">
            <i class="fas fa-crown me-3"></i>Admin Dashboard
        </h1>
        
        <div class="row">
            <!-- Quick Stats -->
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-book fa-3x mb-3"></i>
                        <h5 class="card-title">Total Books</h5>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM books");
                        $total_books = $stmt->fetchColumn();
                        ?>
                        <h2 class="display-4"><?php echo $total_books; ?></h2>
                        <a href="manage-books.php" class="text-white text-decoration-none">
                            <i class="fas fa-arrow-right"></i> Manage Books
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5 class="card-title">Total Users</h5>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                        $total_users = $stmt->fetchColumn();
                        ?>
                        <h2 class="display-4"><?php echo $total_users; ?></h2>
                        <a href="manage-users.php" class="text-white text-decoration-none">
                            <i class="fas fa-arrow-right"></i> Manage Users
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-exchange-alt fa-3x mb-3"></i>
                        <h5 class="card-title">Active Borrows</h5>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM book_borrows WHERE return_date IS NULL");
                        $active_borrows = $stmt->fetchColumn();
                        ?>
                        <h2 class="display-4"><?php echo $active_borrows; ?></h2>
                        <a href="manage-borrows.php" class="text-white text-decoration-none">
                            <i class="fas fa-arrow-right"></i> View Borrows
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-check fa-3x mb-3"></i>
                        <h5 class="card-title">Pending Reservations</h5>
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM book_reservations WHERE status = 'pending'");
                        $pending_reservations = $stmt->fetchColumn();
                        ?>
                        <h2 class="display-4"><?php echo $pending_reservations; ?></h2>
                        <a href="manage-reservations.php" class="text-white text-decoration-none">
                            <i class="fas fa-arrow-right"></i> View Reservations
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-book-open me-2"></i>Recent Book Additions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-book me-1"></i>Title</th>
                                        <th><i class="fas fa-user me-1"></i>Author</th>
                                        <th><i class="fas fa-calendar me-1"></i>Added Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC LIMIT 5");
                                    while($book = $stmt->fetch(PDO::FETCH_ASSOC)):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($book['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-plus me-2"></i>Recent User Registrations
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-user me-1"></i>Username</th>
                                        <th><i class="fas fa-envelope me-1"></i>Email</th>
                                        <th><i class="fas fa-calendar me-1"></i>Joined Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
                                    while($user = $stmt->fetch(PDO::FETCH_ASSOC)):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="add-book.php" class="btn btn-primary w-100">
                                    <i class="fas fa-plus me-2"></i>Add New Book
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="manage-categories.php" class="btn btn-success w-100">
                                    <i class="fas fa-tags me-2"></i>Manage Categories
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="profile.php" class="btn btn-info w-100">
                                    <i class="fas fa-user-cog me-2"></i>Admin Profile
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="../index.php" class="btn btn-warning w-100">
                                    <i class="fas fa-home me-2"></i>View Library
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 