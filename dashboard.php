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

$stmt = $conn->prepare("SELECT role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  $user = $result->fetch_assoc();
  $role = $user['role'];
} else {
  $role = 'guest';
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="https://bootswatch.com/5/quartz/bootstrap.min.css" />
    <style>
    body {
        background-color: #f4f7fc;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .container {
        background-color: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 600px;
        display: flex;
        flex-direction: column;
    }

    .chat-container {
        display: flex;
        flex-direction: column;
        max-height: 400px;
        overflow-y: auto;
        padding: 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
        background: white;
        margin-bottom: 20px;
    }

    .message {
        display: flex;
        align-items: start;
        margin-bottom: 10px;
        position: relative;
    }

    .message .content {
        padding: 10px;
        border-radius: 10px;
        max-width: 70%;
        display: inline-block;
        color: black;
        background-color: #e1f5fe;
        word-wrap: break-word;
        margin-left: 10px;
    }

    .message.admin .content {
        background-color: #ffd700;
    }

    .message.admin {
        flex-direction: row-reverse;
    }

    .message.user {
        flex-direction: row;
    }

    /* New class to align the user's post to the right */
    .message.user-right {
        flex-direction: row-reverse;
    }

    .delete-btn {
        cursor: pointer;
        color: red;
        font-size: 1.2em;
        font-weight: bold;
    }

    textarea {
        width: 100%;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #ccc;
        font-size: 1em;
        resize: vertical;
        color: black;
    }

    button {
        padding: 10px 20px;
        font-size: 1.1em;
        width: 100%;
        cursor: pointer;
        background-color: #007bff;
        color: white;
        border-radius: 4px;
        border: none;
    }

    .email-color {
        color: #007bff;
        font-weight: bold;
    }

    .message .content small {
        display: block;
        margin-top: 5px;
        color: #888;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center" style="margin-bottom: 20px; color: black;">Community Chat</h1>
        <a class="text-center" style="margin-bottom: 20px; color: black;" href="index.html">Home Page</a>
        <p class="text-center" style="font-size: 1.1em; color: black;">
            Logged in as: <?php echo htmlspecialchars($email); ?> | Role: <?php echo htmlspecialchars($role); ?>
        </p>

        <hr />

        <div id="posts" class="chat-container"></div>

        <form id="postForm" action="make_post.php" method="POST" style="display: flex; flex-direction: column;">
            <textarea name="post_content" rows="4" placeholder="Write something..."></textarea>
            <button type="submit">Post</button>
        </form>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Pass the logged-in email to JavaScript
        var loggedInEmail = "<?php echo htmlspecialchars($email); ?>";
        fetchPosts(loggedInEmail);

        document.getElementById("postForm").addEventListener("submit", function(e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch("make_post.php", {
                    method: "POST",
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        fetchPosts(loggedInEmail); // Refresh posts
                        document.querySelector("textarea[name='post_content']").value = "";
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    });

    function fetchPosts(loggedInEmail) {
        fetch("make_post.php")
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    let postsContainer = document.getElementById("posts");
                    postsContainer.innerHTML = "";

                    data.posts.forEach(post => {
                        let postElement = document.createElement("div");

                        // Apply the 'user-right' class if the logged-in email matches the post's email
                        let isCurrentUser = post.email === loggedInEmail;
                        postElement.classList.add("message", isCurrentUser ? "user-right" : (post.role ===
                            "admin" ? "admin" : "user"));

                        let checkMark = post.role === "admin" ? "✔️" : "";
                        let uniqueColor = getUniqueColor(post
                            .email); // Generate a unique color for the email

                        postElement.innerHTML = `
                            <div class="content" style='width : 250px;'>
                                <span class="email-color" style="  color: ${uniqueColor};">${post.email} ${checkMark}:</span>
                                <p>${post.post_content}</p>
                                <small>${post.created_at}</small>
                            </div>
                            ${"<?php echo ($role === 'admin') ? '<span class=\"delete-btn\" onclick=\"deletePost(" + post.id + ")\">&times;</span>' : ''; ?>"}
                        `;
                        postsContainer.appendChild(postElement);
                    });
                }
            })
            .catch(error => console.error("Error fetching posts:", error));
    }

    function deletePost(postId) {
        // Display confirmation alert before deleting the post
        if (confirm("Are you sure you want to delete this post?")) {
            fetch(`delete_post.php?id=${postId}`, {
                    method: "GET"
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        // Find and remove the post element from the DOM
                        let postElement = document.getElementById("post-" + postId);
                        if (postElement) {
                            postElement.remove();
                        }
                        window.location.reload()
                    } else {
                        alert("Failed to delete post");
                    }
                })
                .catch(error => console.error("Error deleting post:", error));
        }
    }


    // Function to generate a unique color for each email
    function getUniqueColor(email) {
        const hash = Array.from(email).reduce((acc, char) => acc + char.charCodeAt(0), 0);
        const hue = hash % 360; // Generate hue value
        return `hsl(${hue}, 70%, 50%)`; // Use HSL to generate a color
    }
    </script>
</body>

</html>