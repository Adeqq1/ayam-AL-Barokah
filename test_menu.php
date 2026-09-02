<?php
require_once 'config/database.php';
/** @var mysqli $conn */
$res = mysqli_query($conn, "SELECT * FROM menu");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        echo $row['id'] . ": " . $row['nama_menu'] . " (" . $row['kategori'] . ")\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}
?>
