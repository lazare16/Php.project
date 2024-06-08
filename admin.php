<?php
// admin.php

include 'connection.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Debug: Print session data
// echo '<pre>';
// print_r($_SESSION);
// echo '</pre>';

// Handle PDF upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_pdf'])) {
    $title = $_POST['title'];
    $file_path = $_POST['file_path']; // We are using URLs for PDFs
    $category_id = $_POST['category_id'];

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO pdfs (title, file_path, category_id, user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $title, $file_path, $category_id, $user_id);

        if ($stmt->execute()) {
            echo "PDF uploaded successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: User ID is not set in the session.";
    }
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

// Fetch and display PDFs
$result = $conn->query("SELECT pdfs.*, categories.name AS category_name FROM pdfs JOIN categories ON pdfs.category_id = categories.id");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
</head>
<body>
    <h1>Admin Panel</h1>
    <form method="post" action="">
        <input type="text" name="title" placeholder="PDF Title" required>
        <input type="url" name="file_path" placeholder="PDF URL" required>
        <select name="category_id" required>
            <?php
            $categories_result = $conn->query("SELECT * FROM categories");
            while ($category = $categories_result->fetch_assoc()) {
                echo "<option value=\"{$category['id']}\">{$category['name']}</option>";
            }
            ?>
        </select>
        <button type="submit" name="upload_pdf">Upload PDF</button>
    </form>
    
    <h2>Existing PDFs</h2>
    <?php if ($result->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>File Path</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($pdf = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $pdf['title']; ?></td>
                        <td><?php echo $pdf['category_name']; ?></td>
                        <td><a href="<?php echo $pdf['file_path']; ?>">View PDF</a></td>
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
