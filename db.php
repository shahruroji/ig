<?php
$servername = "localhost";
$username = "root"; // Username database Anda (default: root)
$password = ""; // Password database Anda (default: kosong)
$dbname = "instagramm"; // Ganti dengan nama database Anda

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
