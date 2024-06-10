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

// Handle category deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];

    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);

    if ($stmt->execute()) {
        echo "Category deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Handle category update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];

    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->bind_param("si", $category_name, $category_id);

    if ($stmt->execute()) {
        echo "Category updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch and display PDFs
$result_pdfs = $conn->query("SELECT pdfs.*, categories.name AS category_name FROM pdfs JOIN categories ON pdfs.category_id = categories.id");

// Fetch and display categories
$result_categories = $conn->query("SELECT * FROM categories");



// Handle user deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $deleteQuery = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch users from the database
$query = "SELECT id, username, email FROM users";
$result = $conn->query($query)
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Admin Panel</h1>
    
    <h2>Upload PDF</h2>
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
    <?php if ($result_pdfs->num_rows > 0) { ?>
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
                <?php while ($pdf = $result_pdfs->fetch_assoc()) { ?>
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

    <h2>Manage Categories</h2>
    <form method="post" action="">
        <input type="text" name="category_name" placeholder="Category Name" required>
        <button type="submit" name="add_category">Add Category</button>
    </form>

    <h2>Existing Categories</h2>
    <?php if ($result_categories->num_rows > 0) { ?>
        <table>
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($category = $result_categories->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $category['name']; ?></td>
                        <td>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="delete_category" value="1">
                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                <button type="submit">Delete</button>
                            </form>
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="edit_category" value="1">
                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                <input type="text" name="category_name" value="<?php echo $category['name']; ?>" required>
                                <button type="submit">Edit</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No categories available.</p>
    <?php } ?>


    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['username'] . "</td>";
                echo "<td>" . $row['email'] . "</td>";
                echo "<td><a href='admin.php?delete=" . $row['id'] . "' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    <?php include 'display_likes.php'; ?>
    <br>
    <?php include 'display_comments.php'; ?>
</body>
</html>

<?php
$conn->close();
?>
