<?php
/**
 * THE ARTISAN PARFUM — callback.php (v2 + Supabase)
 * ToyyibPay posts here after payment
 */


define('LOG_FILE',  __DIR__ . '/payments.log');
define('WA_NUMBER', '601159003985');
define('SB_URL',    'https://oyhtkqfmlwbkjbcfgqxm.supabase.co');
define('SB_KEY',    getenv('SB_SERVICE_KEY'));
define('BREVO_API_KEY', getenv('BREVO_API_KEY')); // or paste your key directly as a string
define('YOUR_EMAIL',    'meowersthe65@gmail.com');

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

function sendBrevoEmail(string $orderRef, string $refNo, string $amountRM): void {
    $payload = [
        'sender' => ['name' => 'The Artisan Parfum', 'email' => 'meowersthe65@gmail.com'],
        'to'         => [['email' => YOUR_EMAIL, 'name' => 'Admin']],
        'subject'    => "💰 Bayaran Berjaya — {$orderRef} (RM{$amountRM})",
        'htmlContent' => "
            <h2>Bayaran Berjaya ✓</h2>
            <p><strong>Order Ref:</strong> {$orderRef}</p>
            <p><strong>Payment Ref:</strong> {$refNo}</p>
            <p><strong>Jumlah:</strong> RM{$amountRM}</p>
            <p>Log masuk ke <a href='https://www.theartisan.my/admin.php'>Admin Panel</a> untuk lihat butiran pesanan.</p>
        "
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'api-key: ' . BREVO_API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ]
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    logPayment("BREVO_EMAIL | OrderRef:{$orderRef} | Response:" . substr($res, 0, 100));
}

if ($status == '1') {
    logPayment("SUCCESS | {$billCode} | Ref:{$refNo} | RM{$amountRM}");
    // Use billExternalReferenceNo which ToyyibPay sends back
updateOrderInSupabase($billCode, 'paid', $refNo);
    sendBrevoEmail($billCode, $refNo, $amountRM);
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
