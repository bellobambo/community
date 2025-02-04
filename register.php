<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = $_POST['role'];

    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit();
    }

    $conn = new mysqli('127.0.0.1', 'root', '', 'community');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $password, $role);

    if ($stmt->execute()) {
        header("Refresh: 2; url=sign-in.html");
        echo "Registration successful! Redirecting to the Login page in.";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>