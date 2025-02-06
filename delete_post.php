<?php
session_start();

if (!isset($_SESSION['email'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

$email = $_SESSION['email'];

$conn = new mysqli('127.0.0.1', 'root', '', 'community');

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed"]));
}

// Check if user is an admin
$stmt = $conn->prepare("SELECT role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $role = $user['role'];
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit();
}

$stmt->close();

if ($role !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Access denied"]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "Missing post ID"]);
    exit();
}

$post_id = intval($_GET['id']);

$delete_stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
$delete_stmt->bind_param("i", $post_id);

if ($delete_stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Post deleted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete post"]);
}

$delete_stmt->close();
$conn->close();
?>