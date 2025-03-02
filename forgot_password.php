<?php
session_start();
include 'config.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');

    if (!empty($email)) {
        // Cek apakah email terdaftar
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Buat kode OTP 6 digit
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['email_reset'] = $email;
            $_SESSION['otp_expire'] = time() + 300; // Expire dalam 5 menit

            // Simpan OTP di database (opsional jika ingin mengecek lebih lanjut)
            $updateOtp = $conn->prepare("UPDATE users SET otp_code = ? WHERE email = ?");
            $updateOtp->bind_param("ss", $otp, $email);
            $updateOtp->execute();

            // Kirim email (gunakan fungsi mail atau PHPMailer)
            mail($email, "Reset Password OTP", "Kode OTP reset password Anda adalah: $otp");

            header("Location: verify_otp.php");
            exit();
        } else {
            $error = "Email tidak ditemukan!";
        }
    } else {
        $error = "Harap isi email Anda.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lupa Password</title>
</head>
<body>
    <h2>Lupa Password</h2>
    <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Masukkan Email" required>
        <button type="submit">Kirim OTP</button>
    </form>
</body>
</html>
