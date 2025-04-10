<?php
// blog.php
require_once 'config.php';

$post_id = $_GET['id'] ?? 0;
$error = '';
$post = null;

try {
    $stmt = $pdo->prepare("
        SELECT posts.*, users.username 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE posts.id = ?
    ");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    $error = 'Failed to fetch post details';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - MyBlogger</title>
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
        }

        .error {
            background-color: #fed7d7;
            color: #c53030;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: #3182ce;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .post-title {
                font-size: 2rem;
            }

            .container {
                padding: 0 0.5rem;
            }

            .post {
                padding: 1.5rem;
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
        <?php if ($error): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
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
        <?php endif; ?>

        <a href="index.php" class="back-link">← Back to Posts</a>
    </main>
</body>
</html>
