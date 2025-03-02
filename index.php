<?php
session_start();
include 'config.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Handle login
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($email) && !empty($password)) {
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username']; // Simpan username di sesi
                    header('Location: pages/home.php');
                    exit();
                } else {
                    $error = "Password salah.";
                }
            } else {
                $error = "Email tidak ditemukan.";
            }
        } else {
            $error = "Harap isi semua bidang.";
        }
    } elseif (isset($_POST['register'])) {
        // Handle registration
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['reg_password'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $birthdate = $_POST['birthdate'] ?? '';

        if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($password) && !empty($gender) && !empty($birthdate)) {
            // Cek apakah email sudah terdaftar
            $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            $resultEmail = $checkEmail->get_result();

            if ($resultEmail->num_rows > 0) {
                $error = "Email sudah digunakan.";
            } else {
                // Generate username unik dari first_name
                $base_username = strtolower(preg_replace('/\s+/', '', $first_name)); // Hilangkan spasi & kecilkan huruf
                $username = $base_username;
                $count = 1;

                // Cek apakah username sudah ada di database
                $checkUsername = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $checkUsername->bind_param("s", $username);
                $checkUsername->execute();
                $resultUsername = $checkUsername->get_result();

                while ($resultUsername->num_rows > 0) {
                    $username = $base_username . $count; // Tambahkan angka jika sudah ada
                    $count++;
                    $checkUsername->bind_param("s", $username);
                    $checkUsername->execute();
                    $resultUsername = $checkUsername->get_result();
                }

                // Simpan ke database
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO users (first_name, last_name, username, email, password, gender, birthdate) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssssss", $first_name, $last_name, $username, $email, $hashed_password, $gender, $birthdate);

                if ($stmt->execute()) {
                    $_SESSION['user_id'] = $stmt->insert_id; // Auto-login setelah daftar
                    $_SESSION['username'] = $username; // Simpan username di sesi
                    header('Location: pages/home.php');
                    exit();
                } else {
                    $error = "Gagal membuat akun.";
                }
            }
        } else {
            $error = "Harap isi semua bidang.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagramm - Login/Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fafafa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            width: 320px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }
        .form-container {
            display: flex;
            width: 200%;
            transition: transform 0.5s ease-in-out;
        }
        .form {
            width: 50%;
            padding: 20px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #0095f6;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }
        button:hover {
            background-color: #007ace;
        }
        .toggle-link {
            margin-top: 15px;
            cursor: pointer;
            color: #0095f6;
            text-decoration: none;
        }
        .toggle-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($error): ?><p style="color: red;"> <?= $error; ?> </p><?php endif; ?>
        <?php if ($success): ?><p style="color: green;"> <?= $success; ?> </p><?php endif; ?>
        <div class="form-container" id="formContainer">
            <form action="" method="POST" class="form login-form">
                <h1>Instagramm</h1>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
                <a class="toggle-link" onclick="toggleForms()">Belum punya akun? Daftar</a>
                <br>
    <a href="forgot_password.php" class="toggle-link" style="display: block; margin-top: 10px;">Lupa akun?</a>
            </form>
            <form action="" method="POST" class="form register-form">
                <h1>Daftar</h1>
                <input type="text" name="first_name" placeholder="Nama Depan" required>
                <input type="text" name="last_name" placeholder="Nama Belakang" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="reg_password" placeholder="Password" required>
                <select name="gender" required>
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
                <input type="date" name="birthdate" required>
                <button type="submit" name="register">Daftar</button>
                <a class="toggle-link" onclick="toggleForms()">Sudah punya akun? Login</a>
            </form>
        </div>
    </div>
    <script>
        function toggleForms() {
            document.getElementById('formContainer').style.transform = 
                document.getElementById('formContainer').style.transform === 'translateX(-50%)' ? 'translateX(0)' : 'translateX(-50%)';
        }
    </script>
</body>
</html>
