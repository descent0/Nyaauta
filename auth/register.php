<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . 'dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['otp'])) {
        // Step 4: Verify OTP
        $inputOtp = $_POST['otp'];
        $pending = $_SESSION['pending_registration'] ?? null;

        if ($pending && $inputOtp === $pending['otp']) {
            // Register the user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $pending['username'], $pending['email'], $pending['password'], $pending['role']);
            if ($stmt->execute()) {
                unset($_SESSION['pending_registration']);
                $success = 'Registration successful! You can now <a href="login.php">login</a>.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        } else {
            $error = 'Invalid OTP entered.';
        }
    } else {
        // Step 1: Handle registration form submit
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = $_POST['role'] ?? 'user';

        if ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            // Step 2: Generate OTP & store in session
            $otp = generateVerificationCode();
            $_SESSION['pending_registration'] = [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'otp' => $otp
            ];

            if (sendVerificationEmail($email, $otp)) {
                $success = 'OTP sent to your email. Please enter it below to complete registration.';
            } else {
                $error = 'Failed to send verification email.';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Register</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if (!isset($_SESSION['pending_registration'])): ?>
                        <!-- Step 1: Registration form -->
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-control" id="role" name="role">
                                    <option value="user">User</option>
                                    <option value="employee">Employee</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Send OTP</button>
                        </form>
                    <?php elseif (isset($_SESSION['pending_registration'])): ?>
                        <!-- Step 3: OTP verification form -->
                        <form method="POST">
                            <div class="mb-3">
                                <label for="otp" class="form-label">Enter OTP sent to your email</label>
                                <input type="text" class="form-control" id="otp" name="otp" value="<?php echo htmlspecialchars($_POST['otp'] ?? ''); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Verify OTP</button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
