<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Connect to DB
$conn = db_connect();
$page_title = 'Register';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : '';

    // Validate username (cannot start with number/symbol)
    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{2,49}$/', $username)) {
        flash_set('error', 'Username must start with a letter and be 3-50 characters.');
        redirect('register.php');
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('error', 'Invalid email address.');
        redirect('register.php');
    }

    // Validate password
    if (strlen($password) < 6) {
        flash_set('error', 'Password must be at least 6 characters.');
        redirect('register.php');
    }

    if ($password !== $confirm) {
        flash_set('error', 'Passwords do not match.');
        redirect('register.php');
    }

    // Check if email already exists
    $email_safe = mysqli_real_escape_string($conn, $email);
    $res = mysqli_query($conn, "SELECT id FROM users WHERE email='{$email_safe}'");
    if (mysqli_num_rows($res) > 0) {
        flash_set('error', 'Email already registered.');
        redirect('register.php');
    }

    // Insert user
    $username_safe = mysqli_real_escape_string($conn, $username);
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $sql = "INSERT INTO users (username, email, password) VALUES ('$username_safe', '$email_safe', '$password_hash')";
    if (mysqli_query($conn, $sql)) {
        flash_set('success', 'Registration successful! Please login.');
        redirect('login.php');
    } else {
        flash_set('error', 'Registration failed. Try again.');
        redirect('register.php');
    }
}

include 'includes/header.php';
?>

<style>
/* Register Page Specific Styles */
.register-wrapper {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    position: relative;
    overflow: hidden;
}

.register-wrapper::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: backgroundMove 20s linear infinite;
}

@keyframes backgroundMove {
    0% { transform: translate(0, 0); }
    100% { transform: translate(50px, 50px); }
}

.register-container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 480px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 50px 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.register-header {
    text-align: center;
    margin-bottom: 40px;
}

.register-header h2 {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
}

.register-header p {
    color: #6b7280;
    font-size: 0.95rem;
}

.register-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    color: #374151;
    font-size: 0.95rem;
    margin-left: 4px;
}

.form-group input {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #fff;
    color: #1f2937;
}

.form-group input:focus {
    outline: none;
    border-color: #f093fb;
    box-shadow: 0 0 0 4px rgba(240, 147, 251, 0.1);
    transform: translateY(-2px);
}

.form-group input::placeholder {
    color: #9ca3af;
}

.register-button {
    margin-top: 10px;
    padding: 16px;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 1.05rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(240, 147, 251, 0.4);
    position: relative;
    overflow: hidden;
}

.register-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
}

.register-button:hover::before {
    left: 100%;
}

.register-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(240, 147, 251, 0.5);
}

.register-button:active {
    transform: translateY(0);
}

.register-footer {
    margin-top: 30px;
    text-align: center;
    color: #6b7280;
    font-size: 0.9rem;
}

.register-footer a {
    color: #f5576c;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.register-footer a:hover {
    color: #f093fb;
    text-decoration: underline;
}

.password-hint {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: -4px;
    margin-left: 4px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .register-container {
        padding: 40px 30px;
        max-width: 90%;
    }
    
    .register-header h2 {
        font-size: 1.75rem;
    }
}

@media (max-width: 480px) {
    .register-wrapper {
        padding: 20px 15px;
    }
    
    .register-container {
        padding: 30px 20px;
    }
    
    .register-header h2 {
        font-size: 1.5rem;
    }
}
</style>

<div class="register-wrapper">
    <div class="register-container">
        <div class="register-header">
            <h2>Create Account</h2>
            <p>Join us today and start shopping</p>
        </div>
        
        <form method="post" action="register.php" class="register-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required>
                <span class="password-hint">Must start with a letter, 3-50 characters</span>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
                <span class="password-hint">At least 6 characters</span>
            </div>
            
            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" placeholder="Re-enter your password" required>
            </div>
            
            <button type="submit" class="register-button">Create Account</button>
        </form>
        
        <div class="register-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
