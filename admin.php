<?php
// admin.php

include 'connection.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle PDF upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_pdf'])) {
    $title = $_POST['title'];
    $file_path = $_POST['file_path']; // We are using URLs for PDFs
    $category_id = $_POST['category_id'];

    $stmt = $conn->prepare("INSERT INTO pdfs (title, file_path, category_id, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $title, $file_path, $category_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        echo "PDF uploaded successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle new category addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];

    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $category_name);

    if ($stmt->execute()) {
        echo "Category added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle PDF deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_pdf'])) {
    $pdf_id = $_POST['pdf_id'];

    $stmt = $conn->prepare("DELETE FROM pdfs WHERE id = ?");
    $stmt->bind_param("i", $pdf_id);

    if ($stmt->execute()) {
        echo "PDF deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle PDF update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_pdf'])) {
    $pdf_id = $_POST['pdf_id'];
    $title = $_POST['title'];
    $file_path = $_POST['file_path'];
    $category_id = $_POST['category_id'];

    $stmt = $conn->prepare("UPDATE pdfs SET title = ?, file_path = ?, category_id = ? WHERE id = ?");
    $stmt->bind_param("ssii", $title, $file_path, $category_id, $pdf_id);

    if ($stmt->execute()) {
        echo "PDF updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch categories
$categories_result = $conn->query("SELECT * FROM categories");

// Fetch PDFs
$pdfs_result = $conn->query("SELECT * FROM pdfs");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>
<body>
    <h1>Admin Panel</h1>
    <p>Welcome, <?php echo $_SESSION['username']; ?>! (<a href="logout.php">Logout</a>)</p>

    <h2>Upload New PDF</h2>
    <form method="post" action="">
        <input type="hidden" name="upload_pdf" value="1">
        <label for="title">PDF Title:</label>
        <input type="text" name="title" id="title" required><br>
        <label for="file_path">PDF URL:</label>
        <input type="url" name="file_path" id="file_path" required><br>
        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" required>
            <?php while ($category = $categories_result->fetch_assoc()) { ?>
                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
            <?php } ?>
        </select><br>
        <button type="submit">Upload PDF</button>
    </form>
     
    <h2>Add New Category</h2>
    <form method="post" action="">
        <input type="hidden" name="add_category" value="1">
        <label for="category_name">Category Name:</label>
        <input type="text" name="category_name" id="category_name" required><br>
        <button type="submit">Add Category</button>
    </form>

    <h2>Manage PDFs</h2>
    <?php if ($pdfs_result->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>URL</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($pdf = $pdfs_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $pdf['title']; ?></td>
                        <td><a href="<?php echo $pdf['file_path']; ?>" target="_blank">View PDF</a></td>
                        <td><?php echo $pdf['category_id']; ?></td>
                        <td>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="delete_pdf" value="1">
                                <input type="hidden" name="pdf_id" value="<?php echo $pdf['id']; ?>">
                                <button type="submit">Delete</button>
                            </form>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="edit_pdf" value="1">
                                <input type="hidden" name="pdf_id" value="<?php echo $pdf['id']; ?>">
                                <input type="text" name="title" value="<?php echo $pdf['title']; ?>" required>
                                <input type="url" name="file_path" value="<?php echo $pdf['file_path']; ?>" required>
                                <select name="category_id" required>
                                    <?php 
                                    // Fetch categories again for each row
                                    $categories_result = $conn->query("SELECT * FROM categories"); 
                                    while ($category = $categories_result->fetch_assoc()) { 
                                        $selected = $pdf['category_id'] == $category['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $selected; ?>><?php echo $category['name']; ?></option>
                                    <?php } ?>
                                </select>
                                <button type="submit">Edit</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No PDFs available.</p>
    <?php } ?>

</body>
</html>

<?php
$conn->close();
?>
