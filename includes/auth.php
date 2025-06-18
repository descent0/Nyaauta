<?php
session_start();
define("SITE_URL", "http://localhost/invitation-platform/");

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . 'auth/login.php');
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        header('Location: ' . SITE_URL . 'dashboard.php');
        exit();
    }
}

function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    global $conn;
    $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    return $result->fetch_assoc();
}

function getAllUsers() {
    global $conn; 
    $result = $conn->query("SELECT id, email, role FROM users");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

function generateVerificationCode(): string {
    // TODO: Implement this function
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Send a verification code to an email.
 */
function sendVerificationEmail(string $email, string $code): bool {
    // TODO: Implement this function
    $subject = "Your Verification Code";
    $body = "<p>Your verification code is: <strong>$code</strong></p>";
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($email, $subject, $body, $headers);
    
}

function login($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
    }
    return false;
}

function register($username, $email, $password, $role = 'user') {
    global $conn;
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
   if ($stmt->execute()) {
        header('Location: ' . SITE_URL . 'auth/login.php');
        exit();
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: ' . SITE_URL . 'index.php');
    exit();
}
?>
