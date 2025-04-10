<?php
// new-post.php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // Handle file upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_extension, $allowed_extensions)) {
            $error = 'Only JPG, JPEG, PNG & GIF files are allowed.';
        } else {
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                $error = 'Failed to upload image.';
            }
        }
    }

    if (empty($title) || empty($content)) {
        $error = 'Title and content are required';
    } elseif (empty($error)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO posts (user_id, title, content, image_url) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $title, $content, $image_path]);
            $success = 'Post created successfully!';
            
            // Redirect to dashboard after short delay
            header("refresh:2;url=dashboard.php");
        } catch (PDOException $e) {
            $error = 'Failed to create post. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Post - MyBlogger</title>
    <style>
        /* Previous styles remain the same */
        .image-preview {
            max-width: 300px;
            margin-top: 1rem;
            border-radius: 4px;
            display: none;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-input-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-input-button {
            background-color: #4299e1;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }

        .file-name {
            margin-left: 1rem;
            color: #4a5568;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav style="display: flex; justify-content: space-between; align-items: center;">
                <h1>Create New Post</h1>
                <a href="dashboard.php" style="color: white; text-decoration: none;">Back to Dashboard</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <form class="post-form" method="POST" enctype="multipart/form-data">
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" required></textarea>
            </div>

            <div class="form-group">
                <label for="image">Featured Image</label>
                <div class="file-input-wrapper">
                    <button type="button" class="file-input-button">Choose Image</button>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                <span class="file-name"></span>
                <img id="imagePreview" class="image-preview">
            </div>

            <div class="button-group">
                <a href="dashboard.php" class="btn cancel-btn">Cancel</a>
                <button type="submit" class="btn submit-btn">Create Post</button>
            </div>
        </form>
    </main>

    <script>
        // Image preview functionality
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const fileName = document.querySelector('.file-name');

        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                fileName.textContent = file.name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
