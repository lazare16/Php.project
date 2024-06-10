<?php
include 'connection.php';

$sql = "SELECT * FROM pdf_likes";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Likes</h2>";
    echo "<table border='1'><tr><th>Like ID</th><th>PDF ID</th><th>User ID</th><th>Like Date</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["like_id"]. "</td><td>" . $row["pdf_id"]. "</td><td>" . $row["user_id"]. "</td><td>" . $row["like_date"]. "</td></tr>";
    }
    echo "</table>";
} else {
    echo "0 likes";
}
?>
