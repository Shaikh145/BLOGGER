<?php
// delete-post.php
require_once 'config.php';

// Start session for authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if post ID is provided
if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
    $_SESSION['error'] = 'Invalid post ID';
    header('Location: dashboard.php');
    exit();
}

$post_id = (int)$_POST['post_id'];
$user_id = $_SESSION['user_id'];

try {
    // First check if the post belongs to the logged-in user
    $check_stmt = $pdo->prepare("
        SELECT user_id 
        FROM posts 
        WHERE id = ? 
        LIMIT 1
    ");
    $check_stmt->execute([$post_id]);
    $post = $check_stmt->fetch();

    if (!$post || $post['user_id'] !== $user_id) {
        $_SESSION['error'] = 'You do not have permission to delete this post';
        header('Location: dashboard.php');
        exit();
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Delete comments first (if you have a comments table)
    $delete_comments = $pdo->prepare("
        DELETE FROM comments 
        WHERE post_id = ?
    ");
    $delete_comments->execute([$post_id]);

    // Delete the post
    $delete_post = $pdo->prepare("
        DELETE FROM posts 
        WHERE id = ? 
        AND user_id = ?
    ");
    $delete_post->execute([$post_id, $user_id]);

    // Commit transaction
    $pdo->commit();

    // Set success message
    $_SESSION['success'] = 'Post deleted successfully';

} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = 'Failed to delete post';
    
    // Log the error (in a production environment)
    error_log("Delete post error: " . $e->getMessage());
}

// Redirect back to dashboard
header('Location: dashboard.php');
exit();
