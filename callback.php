<?php
/**
 * THE ARTISAN PARFUM — callback.php (v2 + Supabase)
 * ToyyibPay posts here after payment
 */
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = ['https://www.theartisan.my', 'https://theartisan.my'];
if (in_array($origin, $allowed)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: https://www.theartisan.my');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

define('LOG_FILE',  __DIR__ . '/payments.log');
define('WA_NUMBER', '601159003985');
define('SB_URL',    'https://oyhtkqfmlwbkjbcfgqxm.supabase.co');
define('SB_KEY',    getenv('SB_SERVICE_KEY'));

function logPayment(string $msg): void {
    file_put_contents(LOG_FILE, '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n", FILE_APPEND | LOCK_EX);
}

function updateOrderInSupabase(string $orderRef, string $status, string $payRef): void {
    $sbKey = SB_KEY;
    if (!$sbKey || !$orderRef) return;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => SB_URL . '/rest/v1/orders?order_ref=eq.' . urlencode($orderRef),
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode(['pay_status' => $status, 'pay_ref' => $payRef]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'apikey: ' . $sbKey,
            'Authorization: Bearer ' . $sbKey,
            'Content-Type: application/json',
            'Prefer: return=minimal'
        ]
    ]);
    curl_exec($ch);
    curl_close($ch);
}

$billCode = $_POST['billcode'] ?? $_GET['billcode'] ?? '';
$refNo    = $_POST['refno']    ?? $_GET['refno']    ?? '';
$status   = $_POST['status']   ?? $_GET['status']   ?? '';
$reason   = $_POST['reason']   ?? $_GET['reason']   ?? '';
$orderRef = $_POST['order_id'] ?? $_GET['order_id'] ?? '';
$amount   = $_POST['amount']   ?? $_GET['amount']   ?? 0;
$amountRM = number_format((int)$amount / 100, 2);

logPayment("CALLBACK | BillCode:{$billCode} | Ref:{$refNo} | Status:{$status} | Amount:RM{$amountRM}");

if ($status == '1') {
    logPayment("SUCCESS | {$billCode} | Ref:{$refNo} | RM{$amountRM}");
    // Use billExternalReferenceNo which ToyyibPay sends back
$orderRef = $_POST['billExternalReferenceNo'] ?? $_GET['billExternalReferenceNo'] ?? $billCode;
updateOrderInSupabase($orderRef, 'paid', $refNo);
    http_response_code(200);
    echo 'OK';
} elseif ($status == '2') {
    logPayment("PENDING | {$billCode} | Ref:{$refNo}");
    updateOrderInSupabase($billCode, 'pending', $refNo);
    http_response_code(200);
    echo 'PENDING_NOTED';
} else {
    logPayment("FAILED | {$billCode} | Reason:{$reason}");
    updateOrderInSupabase($billCode, 'failed', $refNo);
    http_response_code(200);
    echo 'FAILED_NOTED';
}
