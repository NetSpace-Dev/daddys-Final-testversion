<?php
// vfd_update.php - API Endpoint to update VFD/LED8 display in real-time
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$amount = isset($_GET['amount']) ? trim($_GET['amount']) : '0.00';
$light = isset($_GET['light']) ? trim($_GET['light']) : 'none'; // none, price, total, collect, change

// Clean and validate amount
$clean_amount = preg_replace('/[^0-9\.\-]/', '', $amount);
$clean_amount = substr($clean_amount, 0, 8); // Max 8 digits

$port = 'COM2';
$baud = 2400;

if (empty($clean_amount)) {
    $clean_amount = '0.00';
}

try {
    // Configure port
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        @exec("mode " . $port . " BAUD=" . $baud . " PARITY=N DATA=8 STOP=1");
    }

    // Open connection with retries to prevent port locks from fast successive clicks
    $fp = false;
    for ($i = 0; $i < 5; $i++) {
        $fp = @fopen($port, "wb+");
        if ($fp !== false) {
            break;
        }
        usleep(200000); // Wait 200ms before retrying
    }

    if ($fp === false) {
        throw new Exception("Cannot open serial port $port (port busy or locked).");
    }

    // 1. Initialize Display: ESC @ (Hex: 1B 40)
    fwrite($fp, chr(27) . chr(64));

    // 2. Set Status Light (Price, Total, Collect, Change)
    $esc_s_val = 0;
    $stx_l_bytes = [0, 0, 0, 0];
    switch ($light) {
        case 'price': $esc_s_val = 1; $stx_l_bytes = [1, 0, 0, 0]; break;
        case 'total': $esc_s_val = 2; $stx_l_bytes = [0, 1, 0, 0]; break;
        case 'collect': $esc_s_val = 3; $stx_l_bytes = [0, 0, 1, 0]; break;
        case 'change': $esc_s_val = 4; $stx_l_bytes = [0, 0, 0, 1]; break;
    }
    // Send STX L command
    fwrite($fp, chr(2) . chr(76) . chr($stx_l_bytes[0]) . chr($stx_l_bytes[1]) . chr($stx_l_bytes[2]) . chr($stx_l_bytes[3]));
    // Send ESC s command
    fwrite($fp, chr(27) . chr(115) . chr($esc_s_val));

    // 3. Send Display Data: ESC Q A <amount> CR
    fwrite($fp, chr(27) . chr(81) . chr(65) . $clean_amount . chr(13));

    fclose($fp);

    echo json_encode([
        "status" => "success",
        "message" => "Successfully updated VFD display.",
        "data" => [
            "port" => $port,
            "baud" => $baud,
            "amount" => $clean_amount,
            "light" => $light
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
