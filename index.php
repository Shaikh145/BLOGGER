<?php
// index.php
require_once 'config.php';

// Fetch all blog posts
$stmt = $pdo->query("
    SELECT posts.*, users.username 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY created_at DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyBlogger - Home</title>
    <style>
        /* Reset and base styles */
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

        /* Header styles */
        .header {
            background: linear-gradient(to right, #2c3e50, #3498db);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .nav a:hover {
            background-color: rgba(255,255,255,0.1);
        }

        /* Main content styles */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .post-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .post-card:hover {
            transform: translateY(-5px);
        }

        .post-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .post-content {
            padding: 1.5rem;
        }

        .post-title {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .post-meta {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .post-excerpt {
            color: #666;
            margin-bottom: 1rem;
        }

        .read-more {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .read-more:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <h1>MyBlogger</h1>
            <div>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <?php if ($post['image_url']): ?>
                        <img src="<?= htmlspecialchars($post['image_url']) ?>" alt="" class="post-image">
                    <?php endif; ?>
                    <div class="post-content">
                        <h2 class="post-title"><?= htmlspecialchars($post['title']) ?></h2>
                        <div class="post-meta">
                            By <?= htmlspecialchars($post['username']) ?> on 
                            <?= date('F j, Y', strtotime($post['created_at'])) ?>
                        </div>
                        <p class="post-excerpt">
                            <?= htmlspecialchars(substr($post['content'], 0, 150)) ?>...
                        </p>
                        <a href="post.php?id=<?= $post['id'] ?>" class="read-more">Read More</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        // Add smooth scrolling
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.post-card');
            
            // Animate cards on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s, transform 0.5s';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
