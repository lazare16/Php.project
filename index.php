<?php
// index.php

include 'connection.php';

session_start();

// Check if user is logged in
if (isset($_SESSION['username'])) {
    echo "Welcome, " . $_SESSION['username'] . "!<br>";

    // Show admin panel link for admin users
    if ($_SESSION['role'] === 'admin') {
        echo '<a href="admin.php">Admin Panel</a><br>';
    }

    // Fetch categories
    $categories_result = $conn->query("SELECT * FROM categories");
    if ($categories_result->num_rows > 0) {
        echo "<form method='get' action=''>";
        echo "<select name='category_id'>";
        echo "<option value='0'>All Categories</option>";
        while ($category = $categories_result->fetch_assoc()) {
            $selected = isset($_GET['category_id']) && $_GET['category_id'] == $category['id'] ? 'selected' : '';
            echo "<option value='{$category['id']}' $selected>{$category['name']}</option>";
        }
        echo "</select>";
        echo "<button type='submit'>Filter</button>";
        echo "</form>";
    }

    // Fetch and display PDFs
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
    $sql = "SELECT * FROM pdfs";
    if ($category_id) {
        $sql .= " WHERE category_id = " . $category_id;
    }
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li><a href='" . $row['file_path'] . "' target='_blank'>" . $row['title'] . "</a></li>";
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