<?php
require_once 'config.php';
require_once 'functions.php';

$conn = db_connect();

function check_and_add_column($conn, $table, $column, $definition) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    if (mysqli_num_rows($check) == 0) {
        echo "Adding column $column to $table...\n";
        $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        if (mysqli_query($conn, $sql)) {
            echo "Success.\n";
        } else {
            echo "Error: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "Column $column already exists in $table.\n";
    }
}

echo "Checking database schema...\n";

// Add khalti_pidx
check_and_add_column($conn, 'orders', 'khalti_pidx', "VARCHAR(255) DEFAULT NULL AFTER status");

// Add khalti_transaction_id
check_and_add_column($conn, 'orders', 'khalti_transaction_id', "VARCHAR(255) DEFAULT NULL AFTER khalti_pidx");

// Add paid_at if not exists
check_and_add_column($conn, 'orders', 'paid_at', "DATETIME DEFAULT NULL AFTER khalti_transaction_id");

echo "Done.\n";
?>
