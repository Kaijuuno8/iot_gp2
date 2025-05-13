<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        // Check if username or email already exists
        $check_query = "SELECT 1 FROM users WHERE username = $1 OR email = $2";
        $result = pg_query_params($conn, $check_query, [$username, $email]);

        if (pg_num_rows($result) > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $insert_query = "INSERT INTO users (username, email, password) VALUES ($1, $2, $3)";
            $insert_result = pg_query_params($conn, $insert_query, [$username, $email, $hashed_password]);

            if ($insert_result) {
                $success = 'Registration successful. You can now <a href="login.php" class="underline text-blue-600">log in</a>.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md bg-white p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-6 text-center">Create an Account</h2>
        <?php if ($error): ?>
            <div class="mb-4 text-red-600 text-sm text-center"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="mb-4 text-green-600 text-sm text-center"><?= $success ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 mb-1">Username</label>
                <input type="text" id="username" name="username" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300">
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition">Sign Up</button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">
            Already have an account? <a href="login.php" class="text-blue-600 underline">Log in</a>
        </p>
    </div>
</body>
</html>
