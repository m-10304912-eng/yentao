<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sistem_undian_pustakawan";

echo "Attempting connection to '$db' on '$host' with user '$user'...\n";

// Explicitly disable error reporting to avoid clutter, we want the connection result
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @mysqli_connect($host, $user, $pass, $db);

if ($conn) {
    echo "SUCCESS: Connected successfully to $db\n";
    $result = mysqli_query($conn, "SELECT count(*) as c FROM calon");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "Table 'calon' has " . $row['c'] . " rows.\n";
    } else {
        echo "WARNING: Could not query 'calon' table.\n";
    }
} else {
    echo "FAILURE: Connection failed: " . mysqli_connect_error() . "\n";
    echo "Error No: " . mysqli_connect_errno() . "\n";
}
?>
