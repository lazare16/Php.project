<?php


include 'connection.php';

session_start();

if (isset($_SESSION['username']) && isset($_POST['pdf_id'])) {
    $user_id = $_SESSION['user_id'];
    $pdf_id = intval($_POST['pdf_id']);


    $check_like = $conn->prepare("SELECT * FROM pdf_likes WHERE user_id = ? AND pdf_id = ?");
    $check_like->bind_param("ii", $user_id, $pdf_id);
    $check_like->execute();
    $result = $check_like->get_result();

    if ($result->num_rows == 0) {

        $insert_like = $conn->prepare("INSERT INTO pdf_likes (pdf_id, user_id, like_date) VALUES (?, ?, NOW())");
        $insert_like->bind_param("ii", $pdf_id, $user_id);
        $insert_like->execute();
        echo "PDF liked successfully!";
    } else {
        echo "You have already liked this PDF.";
    }
} else {
    echo "Error: User not logged in or invalid PDF ID.";
}


header('Location: index.php');
exit;
?>
