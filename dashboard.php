<?php
// dashboard.php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Fetch user's posts
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle post deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['post_id'], $_SESSION['user_id']]);
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MyBlogger</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
        }

        .header {
            background: linear-gradient(to right, #2c3e50, #3498db);
            color: white;
            padding: 1rem 2rem;
            margin-bottom: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Dashboard specific styles */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .new-post-btn {
            padding: 0.75rem 1.5rem;
            background-color: #2ecc71;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .new-post-btn:hover {
            background-color: #27ae60;
        }

        .posts-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .posts-table th,
        .posts-table td {
            padding: 1rem;
            text-align: left;
        }

        .posts-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .posts-table tr {
            border-bottom: 1px solid #eee;
        }

        .posts-table tr:last-child {
            border-bottom: none;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            margin-right: 0.5rem;
        }

        .edit-btn {
            background-color: #3498db;
        }

        .delete-btn {
            background-color: #e74c3c;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .action-btn:hover {
            opacity: 0.9;
        }

        .no-posts {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .nav-link {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .username-display {
            color: #fff;
            margin-right: 1rem;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: white;
            margin: 15% auto;
            padding: 2rem;
            width: 90%;
            max-width: 500px;
            border-radius: 8px;
            text-align: center;
        }

        .modal-buttons {
            margin-top: 1.5rem;
        }

        .modal-btn {
            padding: 0.5rem 1.5rem;
            margin: 0 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .confirm-delete {
            background-color: #e74c3c;
            color: white;
        }

        .cancel-delete {
            background-color: #95a5a6;
            color: white;
        }

        .post-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav style="display: flex; justify-content: space-between; align-items: center;">
                <h1>Dashboard</h1>
                <div>
                    <span class="username-display">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>!</span>
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="profile.php" class="nav-link">Profile</a>
                    <a href="logout.php" class="nav-link">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Stats Section -->
        <div class="post-stats">
            <div class="stat-card">
                <div class="stat-number"><?= count($posts) ?></div>
                <div class="stat-label">Total Posts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php
                    $recent = array_filter($posts, function($post) {
                        return strtotime($post['created_at']) > strtotime('-7 days');
                    });
                    echo count($recent);
                    ?>
                </div>
                <div class="stat-label">Posts This Week</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php
                    if (!empty($posts)) {
                        $latest = max(array_map(function($post) {
                            return strtotime($post['created_at']);
                        }, $posts));
                        echo date('M j', $latest);
                    } else {
                        echo '-';
                    }
                    ?>
                </div>
                <div class="stat-label">Last Post Date</div>
            </div>
        </div>

        <div class="dashboard-header">
            <h2>Your Posts</h2>
            <a href="new-post.php" class="new-post-btn">New Post</a>
        </div>

        <?php if (empty($posts)): ?>
            <div class="no-posts">
                <h3>You haven't created any posts yet.</h3>
                <p>Click the "New Post" button to get started!</p>
            </div>
        <?php else: ?>
            <table class="posts-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Created</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= htmlspecialchars($post['title']) ?></td>
                            <td><?= date('M j, Y', strtotime($post['created_at'])) ?></td>
                            <td><?= date('M j, Y', strtotime($post['updated_at'])) ?></td>
                            <td>
                                <a href="edit-post.php?id=<?= $post['id'] ?>" class="action-btn edit-btn">Edit</a>
                                <button 
                                    onclick="confirmDelete(<?= $post['id'] ?>, '<?= htmlspecialchars($post['title']) ?>')" 
                                    class="action-btn delete-btn">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete "<span id="postTitle"></span>"?</p>
            <p>This action cannot be undone.</p>
            <div class="modal-buttons">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="post_id" id="postIdInput">
                    <input type="hidden" name="delete_post" value="1">
                    <button type="button" class="modal-btn cancel-delete" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="modal-btn confirm-delete">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(postId, postTitle) {
            document.getElementById('deleteModal').style.display = 'block';
            document.getElementById('postTitle').textContent = postTitle;
            document.getElementById('postIdInput').value = postId;
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
