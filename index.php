<?php
include 'connection.php';

session_start();

// Check if user is logged in
if (isset($_SESSION['username'])) {
    echo "Welcome, " . $_SESSION['username'] . "!<br>";

    // Fetch and display PDFs
    $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : 0;
    $sql = "SELECT * FROM pdfs";
    if ($category_id) {
        $sql .= " WHERE category_id = " . $category_id;
    }
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li><a href='" . $row['file_path'] . "'>" . $row['title'] . "</a></li>";
        }
        echo "</ul>";
    } else {
        echo "No PDFs available.";
    }

    echo '<a href="logout.php">Logout</a>';
} else {
    echo '<a href="register.php">Register</a> | <a href="login.php">Login</a>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF</title>
</head>

<body>
    
</body>

</html>