<?php
$host = 'localhost'; // Ganti jika menggunakan host berbeda
$username = 'root'; // Ganti sesuai username database Anda
$password = ''; // Ganti sesuai password database Anda
$database = 'instagramm'; // Nama database yang sudah dibuat

// Buat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>
