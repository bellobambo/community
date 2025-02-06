<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

$email = $_SESSION['email'];

$conn = new mysqli('127.0.0.1', 'root', '', 'community');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle fetching all posts
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $sql = "SELECT posts.id, users.email, posts.post_content, posts.created_at, posts.image_url 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            ORDER BY posts.created_at DESC";

    $result = $conn->query($sql);

    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }

    echo json_encode(["status" => "success", "posts" => $posts]);
    exit();
}

// Handle creating a new post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
    } else {
        echo json_encode(["status" => "error", "message" => "User not found."]);
        exit();
    }

    $stmt->close();

    $post_content = trim($_POST['post_content']);
    $image_url = isset($_POST['image_url']) ? trim($_POST['image_url']) : NULL;
    $created_at = date("Y-m-d H:i:s");

    if (!empty($post_content)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, post_content, created_at, image_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $post_content, $created_at, $image_url);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Post created successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Post content cannot be empty."]);
    }
}

$conn->close();
?>