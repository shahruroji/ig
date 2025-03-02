<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "instagramm"; // Pastikan nama database sudah benar

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
