<?php
session_start(); // start session
require_once 'config.php';
require_once 'functions.php';

// Connect to the database
$conn = db_connect();

$page_title = 'Login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        flash_set('error', 'Email and password are required.');
        redirect('login.php');
    }

    // Check if user exists
    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='{$email}'");
    if ($user = mysqli_fetch_assoc($res)) {
        if (password_verify($password, $user['password'])) {
            // login success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Restore cart from database
            load_cart_from_db($conn, $user['id']);
            
            flash_set('success', 'Logged in successfully!');
            
            // Check if there's a return URL
            if(isset($_SESSION['return_url'])){
                $return_url = $_SESSION['return_url'];
                unset($_SESSION['return_url']);
                redirect($return_url);
            }
            // Redirect based on role
            elseif($user['is_admin'] == 1){
                redirect('admin/dashboard.php');
            } else {
                redirect('index.php');
            }
        } else {
            flash_set('error', 'Invalid credentials.');
            redirect('login.php');
        }
    } else {
        flash_set('error', 'User not found.');
        redirect('login.php');
    }
}

include 'includes/header.php';
?>

<style>
/* Login Page Specific Styles */
.login-wrapper {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.login-wrapper::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
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

.login-container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 450px;
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

.login-header {
    text-align: center;
    margin-bottom: 40px;
}

.login-header h2 {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
}

.login-header p {
    color: #6b7280;
    font-size: 0.95rem;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
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
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
}

.form-group input::placeholder {
    color: #9ca3af;
}

.login-button {
    margin-top: 10px;
    padding: 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 1.05rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    position: relative;
    overflow: hidden;
}

.login-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
}

.login-button:hover::before {
    left: 100%;
}

.login-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

.login-button:active {
    transform: translateY(0);
}

.login-footer {
    margin-top: 30px;
    text-align: center;
    color: #6b7280;
    font-size: 0.9rem;
}

.login-footer a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.login-footer a:hover {
    color: #764ba2;
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .login-container {
        padding: 40px 30px;
        max-width: 90%;
    }
    
    .login-header h2 {
        font-size: 1.75rem;
    }
}

@media (max-width: 480px) {
    .login-wrapper {
        padding: 20px 15px;
    }
    
    .login-container {
        padding: 30px 20px;
    }
    
    .login-header h2 {
        font-size: 1.5rem;
    }
}
</style>

<div class="login-wrapper">
    <div class="login-container">
        <div class="login-header">
            <h2>Welcome Back</h2>
            <p>Please login to your account</p>
        </div>
        
        <form method="post" action="login.php" class="login-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="login-button">Login</button>
        </form>
        
        <div class="login-footer">
            Don't have an account? <a href="register.php">Sign up here</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
