<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']); // Trim whitespace
    $email = trim($_POST['email']); // New email field
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Input validation
    if (empty($username) || empty($email) || empty($_POST['password'])) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        // Check if the username or email already exists
        $checkSql = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$username, $email]);
        $userExists = $checkStmt->fetchColumn();

        if ($userExists) {
            $error = "Username or email already exists. Please choose different credentials.";
        } else {
            // Insert new user
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            try {
                $stmt->execute([$username, $email, $password]);
                header("Location: login.php");
                exit;
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage()); // Log error for debugging
                $error = "An error occurred. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Signup</h1>
    <form method="POST" action="">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Signup</button>
        <a href="login.php" class="btn btn-link">Login</a>
    </form>
</div>
</body>
</html>
