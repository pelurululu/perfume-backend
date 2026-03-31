<?php

// TEMPORARY DEBUG — remove after fixing
file_put_contents(__DIR__ . '/debug.log',
    date('H:i:s') . " | HTTP: {$httpCode} | cURL err: {$curlError} | Response: {$apiResponse}\n",
    FILE_APPEND
);
// ── CORS — allow Vercel frontend to call this backend ──
header('Access-Control-Allow-Origin: https://perfume-backend-9653.onrender.com');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
// ────────────────────────────────────────────────────────
/**
 * =====================================================
 * THE ARTISAN PARFUM — checkout.php
 * ToyyibPay Payment Gateway Integration
 * =====================================================
 */

/* =====================================================
   CONFIG — INTERN KENA UBAH BAHAGIAN INI
===================================================== */
define('TP_SECRET_KEY',    getenv('TP_SECRET_KEY'));
define('TP_CATEGORY_CODE', getenv('TP_CATEGORY_CODE'));
define('TP_SANDBOX',       false);    // true = test mode | false = live production
define('STORE_NAME',       'The Artisan Parfum');
define('STORE_EMAIL',      'info@theartisanparfum.my');
define('WA_NUMBER',        '601159003985');
define('BASE_URL',         'https://perfume-backend-9653.onrender.com'); // ✅ FIXED
define('MIN_ORDER_RM',     1);

// ToyyibPay API endpoint
define('TP_API_URL', TP_SANDBOX
    ? 'https://dev.toyyibpay.com/index.php/api/createBill'
    : 'https://toyyibpay.com/index.php/api/createBill'
);
define('TP_PAY_BASE', TP_SANDBOX
    ? 'https://dev.toyyibpay.com/'
    : 'https://toyyibpay.com/'
);

/* =====================================================
   SECURITY — Hanya terima POST request
===================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

// Basic security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

/* =====================================================
   HELPER FUNCTIONS
===================================================== */
function clean(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)));
}

function isValidPhone(string $phone): bool {
    $clean = preg_replace('/[\s\-\(\)]/', '', $phone);
    return (bool) preg_match('/^(\+?60|0)[0-9]{8,10}$/', $clean);
}

function logOrder(string $message): void {
    $logFile = __DIR__ . '/orders.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
}

function generateOrderRef(string $phone): string {
    $hash = strtoupper(substr(md5(time() . $phone . rand(1000, 9999)), 0, 8));
    return 'TAP-' . $hash;
}

function fallbackToWhatsApp(string $name, string $phone, string $address, string $items, int $total, string $note): void {
    $msg = "*Pesanan Baru — The Artisan Parfum*\n\n"
         . "👤 *Nama:* {$name}\n"
         . "📞 *Tel:* {$phone}\n"
         . "📍 *Alamat:* {$address}\n\n"
         . "*Item:*\n{$items}\n\n"
         . "💰 *Jumlah: RM {$total}*\n\n"
         . "📝 *Nota:* " . ($note ?: '-') . "\n\n"
         . "_[Auto-fallback dari website]_";
    $waUrl = 'https://wa.me/' . WA_NUMBER . '?text=' . urlencode($msg);
    header('Location: ' . $waUrl);
    exit;
}

/* =====================================================
   STEP 1 — READ & SANITIZE POST DATA
===================================================== */
$name     = clean($_POST['name']     ?? '');
$phone    = clean($_POST['phone']    ?? '');
$email    = clean($_POST['email']    ?? '');
$address  = clean($_POST['address']  ?? '');
$note     = clean($_POST['note']     ?? '');
$items    = clean($_POST['items']    ?? '');
$itemsFmt = clean($_POST['itemsFormatted'] ?? $items);
$total    = (int) filter_var($_POST['total'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

/* =====================================================
   STEP 2 — SERVER-SIDE VALIDATION
===================================================== */
$errors = [];

if (strlen($name) < 3)     $errors[] = 'Nama tidak sah (minimum 3 huruf)';
if (!isValidPhone($phone)) $errors[] = 'No. telefon tidak sah';
if ($total < MIN_ORDER_RM) $errors[] = 'Jumlah pesanan tidak sah';
if (empty($address))       $errors[] = 'Alamat diperlukan';
if (empty($items))         $errors[] = 'Item pesanan tidak sah';

if (!empty($errors)) {
    http_response_code(400);
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'errors' => $errors]));
}

/* =====================================================
   STEP 3 — GENERATE ORDER ID & LOG
===================================================== */
$orderId   = generateOrderRef($phone);
$totalSen  = $total * 100;
$billLabel = STORE_NAME . ' — ' . $orderId;
$billDesc  = "Pesanan #{$orderId}: " . substr($items, 0, 150);
if ($note) $billDesc .= " | Nota: " . substr($note, 0, 40);

logOrder("NEW ORDER | {$orderId} | {$name} | {$phone} | RM {$total} | {$items}");

/* =====================================================
   STEP 4 — CALL TOYYIBPAY API
===================================================== */
$postFields = [
    'userSecretKey'           => TP_SECRET_KEY,
    'categoryCode'            => TP_CATEGORY_CODE,
    'billName'                => $billLabel,
    'billDescription'         => $billDesc,
    'billPriceSetting'        => 1,
    'billPayorInfo'           => 1,
    'billAmount'              => $totalSen,
    'billReturnUrl'           => BASE_URL . '/thankyou.php?ref=' . $orderId,
    'billCallbackUrl'         => BASE_URL . '/callback.php',
    'billExternalReferenceNo' => $orderId,
    'billTo'                  => $name,
    'billEmail'               => !empty($email) ? $email : STORE_EMAIL,
    'billPhone'               => preg_replace('/[\s\-]/', '', $phone),
    'billSplitPayment'        => 0,
    'billSplitPaymentArgs'    => '',
    'billPaymentChannel'      => 0,
    'billContentEmail'        =>
        "Terima kasih kerana membeli di " . STORE_NAME . "!\n\n"
        . "Pesanan: #{$orderId}\n"
        . "Jumlah: RM {$total}\n\n"
        . "Item:\n{$itemsFmt}\n\n"
        . "Kami akan menghubungi anda dalam 24 jam untuk pengesahan penghantaran.\n\n"
        . "WhatsApp: +60 " . substr(WA_NUMBER, 2),
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

/* =====================================================
   STEP 5 — PROCESS API RESPONSE
===================================================== */
if ($curlError) {
    logOrder("CURL ERROR | {$orderId} | {$curlError}");
    fallbackToWhatsApp($name, $phone, $address, $itemsFmt, $total, $note);
}

if ($httpCode !== 200) {
    logOrder("API HTTP ERROR | {$orderId} | HTTP:{$httpCode}");
    fallbackToWhatsApp($name, $phone, $address, $itemsFmt, $total, $note);
}

$result = json_decode($apiResponse, true);

if (empty($result) || !isset($result[0]['BillCode'])) {
    logOrder("API INVALID RESPONSE | {$orderId} | Response: " . substr($apiResponse, 0, 200));
    fallbackToWhatsApp($name, $phone, $address, $itemsFmt, $total, $note);
}

/* =====================================================
   STEP 6 — REDIRECT TO TOYYIBPAY PAYMENT PAGE
===================================================== */
$billCode   = $result[0]['BillCode'];
$paymentUrl = TP_PAY_BASE . $billCode;

logOrder("BILL CREATED | {$orderId} | BillCode:{$billCode} | RM {$total} | Redirect: {$paymentUrl}");

header('Location: ' . $paymentUrl);
exit;
