<?php
// admin/users.php - User Management
require_once '../config.php';
require_once '../functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Require admin login
if (!is_logged_in() || !is_admin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$conn = db_connect();
$page_title = 'User Management';

// Get all users
$users_query = "SELECT id, username, email, is_admin, created_at FROM users ORDER BY created_at DESC";
$users = mysqli_query($conn, $users_query);

include '../includes/header.php';
?>

<style>
.users-management {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 2rem;
    color: #1a1a1a;
}

.back-btn {
    padding: 10px 20px;
    background: #6c757d;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: background 0.3s ease;
}

.back-btn:hover {
    background: #5a6268;
}

.users-table-container {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table thead {
    background: #f8f9fa;
}

.users-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.users-table td {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
}

.users-table tr:hover {
    background: #f8f9fa;
}

.role-badge {
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.role-admin {
    background: #d1fae5;
    color: #065f46;
}

.role-user {
    background: #e0e7ff;
    color: #3730a3;
}

.user-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

.stat-box h3 {
    font-size: 2rem;
    margin-bottom: 5px;
}

.stat-box p {
    font-size: 0.9rem;
    opacity: 0.9;
}
</style>

<div class="users-management">
    <div class="page-header">
        <h1>👥 User Management</h1>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <?php
    // Calculate user stats
    $total_users = mysqli_num_rows($users);
    $admin_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE is_admin=1"))['count'];
    $regular_users = $total_users - $admin_count;
    ?>

    <div class="user-stats">
        <div class="stat-box">
            <h3><?php echo $total_users; ?></h3>
            <p>Total Users</p>
        </div>
        <div class="stat-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <h3><?php echo $admin_count; ?></h3>
            <p>Administrators</p>
        </div>
        <div class="stat-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <h3><?php echo $regular_users; ?></h3>
            <p>Regular Users</p>
        </div>
    </div>

    <div class="users-table-container">
        <h2 style="margin-bottom: 20px;">All Users</h2>
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($users) > 0): ?>
                    <?php 
                    mysqli_data_seek($users, 0); // Reset pointer
                    while($user = mysqli_fetch_assoc($users)): 
                    ?>
                        <?php $is_admin_user = (isset($user['is_admin']) && $user['is_admin'] == 1); ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td><strong><?php echo e($user['username']); ?></strong></td>
                            <td><?php echo e($user['email']); ?></td>
                            <td>
                                <span class="role-badge <?php echo $is_admin_user ? 'role-admin' : 'role-user'; ?>">
                                    <?php echo $is_admin_user ? 'Admin' : 'User'; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #6c757d; padding: 40px;">
                            No users found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
