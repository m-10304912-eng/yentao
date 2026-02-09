<?php
// Wrapper to test user's db_connect.php
try {
    require_once 'c:/xampp1/htdocs/Yentao/db_connect.php';
    echo "SUCCESS: Included db_connect.php without error.\n";
    if (isset($conn)) {
        echo "SUCCESS: \$conn is set.\n";
        if ($conn instanceof mysqli) {
            echo "SUCCESS: \$conn is a mysqli object.\n";
            echo "Host info: " . $conn->host_info . "\n";
        } else {
            echo "FAILURE: \$conn is not a mysqli object.\n";
        }
    } else {
        echo "FAILURE: \$conn is not set.\n";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
