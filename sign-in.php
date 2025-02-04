<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];


    $conn = new mysqli('127.0.0.1', 'root', '', 'community');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {

        $_SESSION['email'] = $email;
        echo "Login successful! Welcome, " . $email;

        header("Location: dashboard.html");
        exit();
    } else {
        echo "Invalid email or password!";
    }

    $stmt->close();
    $conn->close();
}
?>