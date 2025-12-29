<?php
require_once '../config.php';
require_once '../functions.php';
$conn = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='" . mysqli_real_escape_string($conn, $email) . "' AND is_admin=1");
    if ($user = mysqli_fetch_assoc($res)) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = 1;
            flash_set('success', 'Admin logged in.');
            redirect('admin/products.php');

        }
    }
    $error = 'Invalid admin credentials.';
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css">
</head>

<body>
    <div class="container">
        <h2>Admin Login</h2>
        <?php if (isset($error))
            echo '<div class="flash error">' . e($error) . '</div>'; ?>
        <form method="post">
            <label>Email</label><input class="input" name="email">
            <label>Password</label><input class="input" type="password" name="password">
            <button class="input" type="submit">Login</button>
        </form>
    </div>
</body>

</html>