<?php
// test_khalti.php - Test your Khalti configuration
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'config.php';
require_once 'khalti_config.php';

// Only allow access in development
if (!defined('BASE_URL') || strpos(BASE_URL, 'localhost') === false) {
    die('This test page is only accessible on localhost.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khalti Configuration Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 40px;
        }
        h1 {
            color: #5C2D91;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #6c757d;
            margin-bottom: 30px;
        }
        .test-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #5C2D91;
        }
        .test-section h2 {
            font-size: 1.2rem;
            color: #1a1a1a;
            margin-bottom: 15px;
        }
        .test-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .test-item:last-child {
            border-bottom: none;
        }
        .test-label {
            font-weight: 600;
            color: #495057;
        }
        .test-value {
            color: #1a1a1a;
            font-family: 'Courier New', monospace;
        }
        .status {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .status-success {
            background: #d1fae5;
            color: #065f46;
        }
        .status-error {
            background: #fee2e2;
            color: #991b1b;
        }
        .status-warning {
            background: #fef3c7;
            color: #92400e;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #5C2D91;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #4a2470;
            transform: translateY(-2px);
        }
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            margin-top: 10px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Khalti Configuration Test</h1>
        <p class="subtitle">Verify your Khalti payment gateway setup</p>

        <?php
        // Test 1: Check if khalti_config.php exists
        $config_exists = file_exists(__DIR__ . '/khalti_config.php');
        ?>

        <div class="test-section">
            <h2>1. Configuration File</h2>
            <div class="test-item">
                <span class="test-label">khalti_config.php exists:</span>
                <span class="status <?php echo $config_exists ? 'status-success' : 'status-error'; ?>">
                    <?php echo $config_exists ? '✓ Found' : '✗ Missing'; ?>
                </span>
            </div>
        </div>

        <?php if ($config_exists): ?>
            <div class="test-section">
                <h2>2. Configuration Values</h2>
                <div class="test-item">
                    <span class="test-label">Mode:</span>
                    <span class="test-value"><?php echo KHALTI_MODE; ?></span>
                </div>
                <div class="test-item">
                    <span class="test-label">API URL:</span>
                    <span class="test-value"><?php echo get_khalti_api_url(); ?></span>
                </div>
                <div class="test-item">
                    <span class="test-label">Secret Key:</span>
                    <span class="test-value">
                        <?php 
                        $key = get_khalti_secret_key();
                        echo substr($key, 0, 20) . '...';
                        ?>
                        <span class="status <?php echo strlen($key) > 10 ? 'status-success' : 'status-error'; ?>">
                            <?php echo strlen($key) > 10 ? '✓ Set' : '✗ Not Set'; ?>
                        </span>
                    </span>
                </div>
                <div class="test-item">
                    <span class="test-label">Public Key:</span>
                    <span class="test-value">
                        <?php 
                        $pub_key = get_khalti_public_key();
                        echo substr($pub_key, 0, 20) . '...';
                        ?>
                        <span class="status <?php echo strlen($pub_key) > 10 ? 'status-success' : 'status-error'; ?>">
                            <?php echo strlen($pub_key) > 10 ? '✓ Set' : '✗ Not Set'; ?>
                        </span>
                    </span>
                </div>
            </div>

            <div class="test-section">
                <h2>3. CURL Extension</h2>
                <div class="test-item">
                    <span class="test-label">CURL Available:</span>
                    <span class="status <?php echo function_exists('curl_init') ? 'status-success' : 'status-error'; ?>">
                        <?php echo function_exists('curl_init') ? '✓ Yes' : '✗ No'; ?>
                    </span>
                </div>
            </div>

            <div class="test-section">
                <h2>4. Database Columns</h2>
                <?php
                $conn = db_connect();
                $columns_check = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'khalti_pidx'");
                $pidx_exists = mysqli_num_rows($columns_check) > 0;
                
                $columns_check2 = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'khalti_transaction_id'");
                $txn_exists = mysqli_num_rows($columns_check2) > 0;
                ?>
                <div class="test-item">
                    <span class="test-label">khalti_pidx column:</span>
                    <span class="status <?php echo $pidx_exists ? 'status-success' : 'status-error'; ?>">
                        <?php echo $pidx_exists ? '✓ Exists' : '✗ Missing'; ?>
                    </span>
                </div>
                <div class="test-item">
                    <span class="test-label">khalti_transaction_id column:</span>
                    <span class="status <?php echo $txn_exists ? 'status-success' : 'status-error'; ?>">
                        <?php echo $txn_exists ? '✓ Exists' : '✗ Missing'; ?>
                    </span>
                </div>
            </div>

            <div class="test-section">
                <h2>5. Test API Connection</h2>
                <?php
                // Try a simple test request
                $test_payload = [
                    'return_url' => BASE_URL . 'test_callback.php',
                    'website_url' => BASE_URL,
                    'amount' => 1000, // Rs. 10
                    'purchase_order_id' => 'TEST_' . time(),
                    'purchase_order_name' => 'Configuration Test',
                    'customer_info' => [
                        'name' => 'Test User',
                        'phone' => '9800000000',
                        'email' => 'test@example.com'
                    ]
                ];

                $test_response = khalti_api_request('epayment/initiate/', $test_payload, 'POST');
                
                $api_success = $test_response['success'] && isset($test_response['data']['pidx']);
                ?>
                <div class="test-item">
                    <span class="test-label">API Connection:</span>
                    <span class="status <?php echo $api_success ? 'status-success' : 'status-error'; ?>">
                        <?php echo $api_success ? '✓ Working' : '✗ Failed'; ?>
                    </span>
                </div>
                <div class="test-item">
                    <span class="test-label">HTTP Status Code:</span>
                    <span class="test-value"><?php echo $test_response['http_code']; ?></span>
                </div>

                <?php if (!$api_success): ?>
                    <div class="alert alert-info" style="margin-top: 15px;">
                        <strong>Error Details:</strong>
                        <div class="code-block">
                            <?php echo htmlspecialchars(print_r($test_response['data'], true)); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="test-item">
                        <span class="test-label">Test PIDX Generated:</span>
                        <span class="test-value"><?php echo $test_response['data']['pidx']; ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="alert alert-info">
                <strong>📝 Test Credentials (Sandbox):</strong><br>
                <strong>Khalti ID:</strong> 9800000000, 9800000001, 9800000002<br>
                <strong>MPIN:</strong> 1111<br>
                <strong>OTP:</strong> 987654
            </div>

            <?php if (!$pidx_exists || !$txn_exists): ?>
                <div class="alert alert-info">
                    <strong>⚠️ Missing Database Columns</strong><br>
                    Run this SQL to add required columns:
                    <div class="code-block">
ALTER TABLE `orders` 
ADD COLUMN `email` VARCHAR(255) DEFAULT NULL AFTER `phone`,
ADD COLUMN `khalti_pidx` VARCHAR(100) DEFAULT NULL AFTER `status`,
ADD COLUMN `khalti_transaction_id` VARCHAR(100) DEFAULT NULL AFTER `khalti_pidx`,
ADD COLUMN `paid_at` DATETIME DEFAULT NULL AFTER `khalti_transaction_id`;
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-info">
                <strong>⚠️ Configuration Missing</strong><br>
                Create <code>khalti_config.php</code> in your project root with your API keys.
            </div>
        <?php endif; ?>

        <a href="<?php echo BASE_URL; ?>index.php" class="btn">← Back to Home</a>
    </div>
</body>
</html>