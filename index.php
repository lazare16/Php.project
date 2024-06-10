<?php
include 'connection.php';
session_start();

// Fetch categories
$categories_query = "SELECT * FROM categories";
$categories_result = mysqli_query($conn, $categories_query);

// Fetch PDFs based on selected category
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$category_condition = $category_filter ? "WHERE pdfs.category_id = '$category_filter'" : '';

$query = "SELECT pdfs.*, categories.name AS category_name, users.username FROM pdfs
          JOIN categories ON pdfs.category_id = categories.id
          JOIN users ON pdfs.user_id = users.id
          $category_condition";
$result = mysqli_query($conn, $query);

// Handle comment submission
if (isset($_POST['submit_comment'])) {
    $pdf_id = $_POST['pdf_id'];
    $user_id = $_SESSION['user_id']; // Assumes the user is logged in and user_id is stored in session
    $comment_text = mysqli_real_escape_string($conn, $_POST['comment_text']);
    
    $insert_comment_query = "INSERT INTO pdf_comments (pdf_id, user_id, comment_text, comment_date) VALUES ('$pdf_id', '$user_id', '$comment_text', NOW())";
    if (mysqli_query($conn, $insert_comment_query)) {
        echo "Comment added successfully.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PDF List</title>
</head>
<body>
    <h1>PDF List</h1>

    <!-- Category Filter -->
    <form method="GET" action="">
        <label for="category">Filter by Category:</label>
        <select name="category" id="category">
            <option value="">All</option>
            <?php while ($category = mysqli_fetch_assoc($categories_result)) : ?>
                <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                    <?php echo $category['name']; ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Filter</button>
    </form>

    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <div>
            <h2><?php echo $row['title']; ?></h2>
            <p>Category: <?php echo $row['category_name']; ?></p>
            <p>Uploaded by: <?php echo $row['username']; ?></p>
            <p><a href="<?php echo $row['file_path']; ?>">Download PDF</a></p>

            <!-- Likes functionality -->
            <p>
                <?php
                $pdf_id = $row['id'];
                $likes_query = "SELECT COUNT(*) AS like_count FROM pdf_likes WHERE pdf_id = '$pdf_id'";
                $likes_result = mysqli_query($conn, $likes_query);
                $likes_row = mysqli_fetch_assoc($likes_result);
                $like_count = $likes_row['like_count'];

                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $user_like_query = "SELECT * FROM pdf_likes WHERE pdf_id = '$pdf_id' AND user_id = '$user_id'";
                    $user_like_result = mysqli_query($conn, $user_like_query);
                    $user_has_liked = mysqli_num_rows($user_like_result) > 0;

                    if ($user_has_liked) {
                        echo "<a href='unlike.php?pdf_id=$pdf_id'>Unlike</a>";
                    } else {
                        echo "<a href='like.php?pdf_id=$pdf_id'>Like</a>";
                    }
                }

                echo " ($like_count likes)";
                ?>
            </p>

            <!-- Comments section -->
            <h3>Comments</h3>
            <?php
            $comments_query = "SELECT pdf_comments.*, users.username FROM pdf_comments
                               JOIN users ON pdf_comments.user_id = users.id
                               WHERE pdf_comments.pdf_id = '$pdf_id'
                               ORDER BY pdf_comments.comment_date DESC";
            $comments_result = mysqli_query($conn, $comments_query);
            ?>
            <ul>
                <?php while ($comment = mysqli_fetch_assoc($comments_result)) : ?>
                    <li>
                        <strong><?php echo $comment['username']; ?>:</strong>
                        <?php echo $comment['comment_text']; ?>
                        <em>(<?php echo $comment['comment_date']; ?>)</em>
                    </li>
                <?php endwhile; ?>
            </ul>

            <!-- Comment form -->
            <?php if (isset($_SESSION['user_id'])) : ?>
                <form method="post" action="">
                    <textarea name="comment_text" required></textarea>
                    <input type="hidden" name="pdf_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="submit_comment">Submit Comment</button>
                    <a href="logout.php">Logout</a>
                </form>
            <?php else : ?>
                <p>Please <a href="login.php">login</a> to leave a comment.</p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</body>
</html>
