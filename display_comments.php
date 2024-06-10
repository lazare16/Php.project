<?php
include 'connection.php';

$sql = "SELECT * FROM pdf_comments";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Comments</h2>";
    echo "<table border='1'><tr><th>Comment ID</th><th>PDF ID</th><th>User ID</th><th>Comment</th><th>Comment Date</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id"]. "</td><td>" . $row["pdf_id"]. "</td><td>" . $row["user_id"]. "</td><td>" . $row["comment_text"]. "</td><td>" . $row["comment_date"]. "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 comments";
}
?>
