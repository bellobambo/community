<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
  // If not logged in, redirect to login page
  header("Location: login.html");
  exit();
}

$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
</head>

<body>
    <h1>Welcome to your Dashboard</h1>
    <p>
        Logged in as:
        <?php echo htmlspecialchars($email); ?>
    </p>

    <!-- You can also add more user details or profile info here -->

    <h2>Make a Post</h2>
    <form action="make_post.php" method="POST">
        <textarea name="post_content" rows="4" cols="50" placeholder="Write something..."></textarea><br />
        <button type="submit">Post</button>
    </form>
</body>

</html>