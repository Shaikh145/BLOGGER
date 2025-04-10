<?php
// view-post.php
require_once 'config.php';
session_start();

// Initialize variables
$post = null;
$comments = [];
$error = '';
$success = '';

// Check if post ID exists and is numeric
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$post_id = (int)$_GET['id'];

// Fetch post with author details
try {
    // First check if post exists
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            u.username,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
        FROM posts p
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = ?
        LIMIT 1
    ");
    
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        // Post not found, display error
        $error = 'Post not found';
    } else {
        // If post exists, fetch comments
        $comment_stmt = $pdo->prepare("
            SELECT 
                c.*,
                u.username
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at DESC
        ");
        $comment_stmt->execute([$post_id]);
        $comments = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error = 'Database error: Unable to fetch post details';
    error_log("Database error in view-post.php: " . $e->getMessage());
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    if (!isset($_POST['comment']) || trim($_POST['comment']) === '') {
        $error = 'Comment cannot be empty';
    } else {
        try {
            $comment_content = trim($_POST['comment']);
            
            $comment_insert = $pdo->prepare("
                INSERT INTO comments (post_id, user_id, content)
                VALUES (?, ?, ?)
            ");
            
            $comment_insert->execute([
                $post_id,
                $_SESSION['user_id'],
                $comment_content
            ]);

            // Redirect to avoid form resubmission
            header("Location: view-post.php?id=$post_id&success=1");
            exit();
            
        } catch (PDOException $e) {
            $error = 'Failed to add comment';
            error_log("Comment insertion error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $post ? htmlspecialchars($post['title']) : 'Post Not Found' ?> - MyBlogger</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f7fafc;
            color: #2d3748;
        }

        .header {
            background: linear-gradient(to right, #2c3e50, #3498db);
            color: white;
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .post {
            background-color: white;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .post-title {
            font-size: 2.5rem;
            color: #2d3748;
            margin-bottom: 1rem;
        }

        .post-meta {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .post-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #4a5568;
            margin-bottom: 2rem;
        }

        .comments-section {
            margin-top: 3rem;
        }

        .comment {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .comment-meta {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .comment-form {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .error {
            background-color: #fed7d7;
            color: #c53030;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .success {
            background-color: #c6f6d5;
            color: #2f855a;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 2rem;
            color: #3182ce;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .not-found {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 0.5rem;
            }

            .post {
                padding: 1.5rem;
            }

            .post-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>MyBlogger</h1>
        </div>
    </header>

    <main class="container">
        <a href="index.php" class="back-link">← Back to Posts</a>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php if ($error === 'Post not found'): ?>
                <div class="not-found">
                    <h2>Post Not Found</h2>
                    <p>The post you're looking for doesn't exist or has been removed.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="success">Comment added successfully!</div>
        <?php endif; ?>

        <?php if ($post): ?>
            <article class="post">
                <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
                <div class="post-meta">
                    Posted by <?= htmlspecialchars($post['username']) ?>
                    on <?= date('F j, Y', strtotime($post['created_at'])) ?>
                </div>
                <div class="post-content">
                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                </div>
            </article>

            <section class="comments-section">
                <h2>Comments (<?= count($comments) ?>)</h2>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form class="comment-form" method="POST">
                        <div class="form-group">
                            <label for="comment">Add a comment:</label>
                            <textarea name="comment" id="comment" required></textarea>
                        </div>
                        <button type="submit" class="btn">Post Comment</button>
                    </form>
                <?php else: ?>
                    <p style="margin-bottom: 2rem;">
                        Please <a href="login.php">login</a> to leave a comment.
                    </p>
                <?php endif; ?>

                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-meta">
                            <strong><?= htmlspecialchars($comment['username']) ?></strong>
                            commented on
                            <?= date('F j, Y g:i A', strtotime($comment['created_at'])) ?>
                        </div>
                        <div class="comment-content">
                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($comments)): ?>
                    <p>No comments yet. Be the first to comment!</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
