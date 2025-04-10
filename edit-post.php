<?php
// edit-post.php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';
$post = null;

// Fetch post data
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $post = $stmt->fetch();

    if (!$post) {
        header('Location: dashboard.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // Handle file upload
    $image_path = $post['image_url']; // Keep existing image by default
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
                // Delete old image if exists
                if (!empty($post['image_url']) && file_exists($post['image_url'])) {
                    unlink($post['image_url']);
                }
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
                UPDATE posts 
                SET title = ?, content = ?, image_url = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$title, $content, $image_path, $post['id'], $_SESSION['user_id']]);
            $success = 'Post updated successfully!';
            
            // Refresh post data
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
            $stmt->execute([$post['id'], $_SESSION['user_id']]);
            $post = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Failed to update post. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - MyBlogger</title>
    <style>
        /* Reuse styles from new-post.php */
        /* Add additional styles for edit page */
        .current-image {
            max-width: 300px;
            margin: 1rem 0;
            border-radius: 4px;
        }

        .remove-image {
            color: #e53e3e;
            text-decoration: none;
            margin-left: 1rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav style="display: flex; justify-content: space-between; align-items: center;">
                <h1>Edit Post</h1>
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
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" required><?= htmlspecialchars($post['content']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Featured Image</label>
                <?php if (!empty($post['image_url'])): ?>
                    <div>
                        <img src="<?= htmlspecialchars($post['image_url']) ?>" class="current-image" alt="Current featured image">
                        <span class="remove-image" onclick="removeImage()">Remove Image</span>
                    </div>
                <?php endif; ?>
                <div class="file-input-wrapper">
                    <button type="button" class="file-input-button">Choose New Image</button>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                <span class="file-name"></span>
                <img id="imagePreview" class="image-preview">
            </div>

            <div class="button-group">
                <a href="dashboard.php" class="btn cancel-btn">Cancel</a>
                <button type="submit" class="btn submit-btn">Update Post</button>
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

        function removeImage() {
            if (confirm('Are you sure you want to remove the current image?')) {
                // You can implement AJAX here to remove the image
                // For now, we'll just hide it
                document.querySelector('.current-image').style.display = 'none';
                document.querySelector('.remove-image').style.display = 'none';
            }
        }
    </script>
</body>
</html>
