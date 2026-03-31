<?php

// ── CORS ──
header('Access-Control-Allow-Origin: https://www.theartisan.my');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

/**
 * =====================================================
 * THE ARTISAN PARFUM — checkout.php (v2 + Supabase)
 * =====================================================
 */

define('TP_SECRET_KEY',    getenv('TP_SECRET_KEY'));
define('TP_CATEGORY_CODE', getenv('TP_CATEGORY_CODE'));
define('TP_SANDBOX',       false);
define('STORE_NAME',       'The Artisan Parfum');
define('STORE_EMAIL',      'info@theartisanparfum.my');
define('WA_NUMBER',        '601159003985');
define('BASE_URL',         'https://www.theartisan.my');
define('BACKEND_URL',      'https://perfume-backend-9653.onrender.com');
define('MIN_ORDER_RM',     1);

// Supabase
define('SB_URL', 'https://oyhtkqfmlwbkjbcfgqxm.supabase.co');
define('SB_KEY', getenv('SB_SERVICE_KEY')); // Use service role key for backend writes

define('TP_API_URL', TP_SANDBOX
    ? 'https://dev.toyyibpay.com/index.php/api/createBill'
    : 'https://toyyibpay.com/index.php/api/createBill'
);
define('TP_PAY_BASE', TP_SANDBOX
    ? 'https://dev.toyyibpay.com/'
    : 'https://toyyibpay.com/'
);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

/* ── HELPERS ── */
function clean(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)));
}

function isValidPhone(string $phone): bool {
    $clean = preg_replace('/[\s\-\(\)]/', '', $phone);
    return (bool) preg_match('/^(\+?60|0)[0-9]{8,10}$/', $clean);
}

function logOrder(string $message): void {
    $logFile = __DIR__ . '/orders.log';
    file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n", FILE_APPEND | LOCK_EX);
}

function generateOrderRef(string $phone): string {
    return 'TAP-' . strtoupper(substr(md5(time() . $phone . rand(1000, 9999)), 0, 8));
}

function saveOrderToSupabase(array $order): void {
    $sbKey = SB_KEY;
    if (!$sbKey) return; // Skip if no service key set

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => SB_URL . '/rest/v1/orders',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($order),
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

function updateOrderBillCode(string $orderRef, string $billCode): void {
    $sbKey = SB_KEY;
    if (!$sbKey) return;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => SB_URL . '/rest/v1/orders?order_ref=eq.' . urlencode($orderRef),
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode(['bill_code' => $billCode]),
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

function fallbackToWhatsApp(string $name, string $phone, string $address, string $items, int $total, string $note): void {
    $msg = "*Pesanan Baru — The Artisan Parfum*\n\n"
         . "👤 *Nama:* {$name}\n📞 *Tel:* {$phone}\n📍 *Alamat:* {$address}\n\n"
         . "*Item:*\n{$items}\n\n💰 *Jumlah: RM {$total}*\n\n"
         . "📝 *Nota:* " . ($note ?: '-') . "\n\n_[Auto-fallback dari website]_";
    header('Location: https://wa.me/' . WA_NUMBER . '?text=' . urlencode($msg));
    exit;
}

/* ── READ & SANITIZE ── */
$name     = clean($_POST['name']             ?? '');
$phone    = clean($_POST['phone']            ?? '');
$email    = clean($_POST['email']            ?? '');
$address  = clean($_POST['address']          ?? '');
$note     = clean($_POST['note']             ?? '');
$items    = clean($_POST['items']            ?? '');
$itemsFmt = clean($_POST['itemsFormatted']   ?? $items);
$total    = (int) filter_var($_POST['total'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

/* ── VALIDATE ── */
$errors = [];
if (strlen($name) < 3)     $errors[] = 'Nama tidak sah';
if (!isValidPhone($phone)) $errors[] = 'No. telefon tidak sah';
if ($total < MIN_ORDER_RM) $errors[] = 'Jumlah tidak sah';
if (empty($address))       $errors[] = 'Alamat diperlukan';
if (empty($items))         $errors[] = 'Item tidak sah';

if (!empty($errors)) {
    http_response_code(400);
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'errors' => $errors]));
}

/* ── GENERATE ORDER ── */
$orderId  = generateOrderRef($phone);
$totalSen = $total * 100;

logOrder("NEW ORDER | {$orderId} | {$name} | {$phone} | RM {$total} | {$items}");

// Save to Supabase
saveOrderToSupabase([
    'order_ref'  => $orderId,
    'name'       => $name,
    'phone'      => $phone,
    'email'      => $email,
    'address'    => $address,
    'note'       => $note,
    'items'      => $items,
    'total'      => $total,
    'pay_status' => 'pending'
]);

/* ── CALL TOYYIBPAY ── */
$postFields = [
    'userSecretKey'           => TP_SECRET_KEY,
    'categoryCode'            => TP_CATEGORY_CODE,
    'billName'                => 'Artisan — ' . $orderId,
    'billDescription'         => "Pesanan #{$orderId}: " . substr($items, 0, 150) . ($note ? " | Nota: " . substr($note, 0, 40) : ''),
    'billPriceSetting'        => 1,
    'billPayorInfo'           => 1,
    'billAmount'              => $totalSen,
    'billReturnUrl'           => BACKEND_URL . '/thankyou.php?ref=' . $orderId,
    'billCallbackUrl'         => BACKEND_URL . '/callback.php',
    'billExternalReferenceNo' => $orderId,
    'billTo'                  => $name,
    'billEmail'               => !empty($email) ? $email : STORE_EMAIL,
    'billPhone'               => preg_replace('/[\s\-]/', '', $phone),
    'billSplitPayment'        => 0,
    'billSplitPaymentArgs'    => '',
    'billPaymentChannel'      => 0,
    'billContentEmail'        =>
        "Terima kasih kerana membeli di " . STORE_NAME . "!\n\n"
        . "Pesanan: #{$orderId}\nJumlah: RM {$total}\n\nItem:\n{$itemsFmt}\n\n"
        . "Kami akan menghubungi anda dalam 24 jam.\n\nWhatsApp: +60 " . substr(WA_NUMBER, 2),
    'billChargeToCustomer'    => 1,
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => TP_API_URL,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($postFields),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => !TP_SANDBOX,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_FOLLOWLOCATION => false,
]);

$apiResponse = curl_exec($ch);
$httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError   = curl_error($ch);
curl_close($ch);

file_put_contents(__DIR__ . '/debug.log',
    date('H:i:s') . " | HTTP:{$httpCode} | cURL:{$curlError} | Response:{$apiResponse}\n",
    FILE_APPEND
);

if ($curlError || $httpCode !== 200) {
    logOrder("API ERROR | {$orderId} | HTTP:{$httpCode} | {$curlError}");
    fallbackToWhatsApp($name, $phone, $address, $itemsFmt, $total, $note);
}

$result = json_decode($apiResponse, true);
if (empty($result) || !isset($result[0]['BillCode'])) {
    logOrder("API INVALID RESPONSE | {$orderId} | " . substr($apiResponse, 0, 200));
    fallbackToWhatsApp($name, $phone, $address, $itemsFmt, $total, $note);
}

$billCode   = $result[0]['BillCode'];
$paymentUrl = TP_PAY_BASE . $billCode;

logOrder("BILL CREATED | {$orderId} | BillCode:{$billCode} | RM {$total}");
updateOrderBillCode($orderId, $billCode);

header('Location: ' . $paymentUrl);
exit;
