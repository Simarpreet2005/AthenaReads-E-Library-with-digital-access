<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $total_quantity = intval($_POST['total_quantity'] ?? 0);

    // Validate required fields
    if (empty($title) || empty($author) || $total_quantity <= 0) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            // Handle file uploads
            $cover_image = '';
            $file_path = '';

            // Handle cover image upload
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $cover_image = 'uploads/covers/' . basename($_FILES['cover_image']['name']);
                move_uploaded_file($_FILES['cover_image']['tmp_name'], $cover_image);
            }

            // Handle PDF file upload
            if (isset($_FILES['file_path']) && $_FILES['file_path']['error'] === UPLOAD_ERR_OK) {
                $file_path = 'uploads/books/' . basename($_FILES['file_path']['name']);
                move_uploaded_file($_FILES['file_path']['tmp_name'], $file_path);
            }

            // Insert book into database
            $stmt = $pdo->prepare("
                INSERT INTO books (title, author, isbn, category, description, total_quantity, available_quantity, cover_image, file_path)
                VALUES (:title, :author, :isbn, :category, :description, :total_quantity, :available_quantity, :cover_image, :file_path)
            ");

            $stmt->execute([
                'title' => $title,
                'author' => $author,
                'isbn' => $isbn,
                'category' => $category,
                'description' => $description,
                'total_quantity' => $total_quantity,
                'available_quantity' => $total_quantity,
                'cover_image' => $cover_image,
                'file_path' => $file_path
            ]);

            $success = 'Book added successfully!';
            
            // Clear form
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add New Book - E-Library</title>
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
        
        .form-control {
            background: rgba(40, 44, 52, 0.8);
            border: 1px solid var(--accent-color);
            color: var(--text-color);
            backdrop-filter: blur(5px);
        }
        
        .form-control:focus {
            background: rgba(40, 44, 52, 0.9);
            border-color: var(--light-accent);
            color: var(--text-color);
            box-shadow: 0 0 0 0.25rem rgba(139, 115, 85, 0.25);
        }
        
        .form-label {
            color: var(--light-accent);
            font-weight: 600;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3d5afe, #304ffe);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #304ffe, #1a237e);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(61, 90, 254, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #495057, #343a40);
            transform: translateY(-2px);
        }
        
        .alert {
            border: 1px solid var(--accent-color);
            backdrop-filter: blur(10px);
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
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
        
        .file-upload-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        
        .file-upload-label {
            display: block;
            padding: 12px;
            background: rgba(40, 44, 52, 0.8);
            border: 2px dashed var(--accent-color);
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text-color);
        }
        
        .file-upload-label:hover {
            border-color: var(--light-accent);
            background: rgba(40, 44, 52, 0.9);
        }
    </style>
</head>
<body>
    <?php include '../includes/admin-navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h1 class="mb-0">
                            <i class="fas fa-plus-circle me-3"></i>Add New Book
                        </h1>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label">
                                        <i class="fas fa-book me-2"></i>Title *
                                    </label>
                                    <input type="text" class="form-control" id="title" name="title" required
                                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="author" class="form-label">
                                        <i class="fas fa-user me-2"></i>Author *
                                    </label>
                                    <input type="text" class="form-control" id="author" name="author" required
                                           value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : ''; ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="isbn" class="form-label">
                                        <i class="fas fa-barcode me-2"></i>ISBN
                                    </label>
                                    <input type="text" class="form-control" id="isbn" name="isbn"
                                           value="<?php echo isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : ''; ?>" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">
                                        <i class="fas fa-tags me-2"></i>Category
                                    </label>
                                    <input type="text" class="form-control" id="category" name="category"
                                           value="<?php echo isset($_POST['category']) ? htmlspecialchars($_POST['category']) : ''; ?>" />
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left me-2"></i>Description
                                </label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="total_quantity" class="form-label">
                                        <i class="fas fa-layer-group me-2"></i>Total Quantity *
                                    </label>
                                    <input type="number" class="form-control" id="total_quantity" name="total_quantity" min="1" required
                                           value="<?php echo isset($_POST['total_quantity']) ? htmlspecialchars($_POST['total_quantity']) : '1'; ?>" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-image me-2"></i>Cover Image
                                    </label>
                                    <div class="file-upload-wrapper">
                                        <input type="file" id="cover_image" name="cover_image" accept="image/*" />
                                        <label for="cover_image" class="file-upload-label">
                                            <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                            Choose Cover Image<br>
                                            <small>JPG, PNG, GIF (Max 5MB)</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-file-pdf me-2"></i>Book File (PDF)
                                    </label>
                                    <div class="file-upload-wrapper">
                                        <input type="file" id="file_path" name="file_path" accept=".pdf" />
                                        <label for="file_path" class="file-upload-label">
                                            <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                            Choose PDF File<br>
                                            <small>PDF files only (Max 10MB)</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Book
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload preview
        document.getElementById('cover_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const label = e.target.nextElementSibling;
                label.innerHTML = `<i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>${file.name}<br><small>Selected</small>`;
            }
        });
        
        document.getElementById('file_path').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const label = e.target.nextElementSibling;
                label.innerHTML = `<i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>${file.name}<br><small>Selected</small>`;
            }
        });
    </script>
</body>
</html> 