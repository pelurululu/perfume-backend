<?php
// ── CORS — allow Vercel frontend to call this backend ──
header('Access-Control-Allow-Origin: https://www.theartisan.my'); // ← change this
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
// ────────────────────────────────────────────────────────
/**
 * =====================================================
 * THE ARTISAN PARFUM — checkout.php
 * ToyyibPay Payment Gateway Integration
 *
 * CARA GUNA (INTERN):
 * 1. Daftar akaun merchant di https://toyyibpay.com
 * 2. Login dashboard → API → copy userSecretKey
 * 3. Buat satu kategori → copy categoryCode
 * 4. Isi kedua-dua value di bahagian CONFIG di bawah
 * 5. Tukar BASE_URL kepada domain website anda
 * 6. Set TP_SANDBOX = false apabila ready untuk live
 * 7. Dalam index.html tukar: USE_TOYYIBPAY: false → true
 * 8. Upload semua fail ke server (index.html, checkout.php, callback.php, thankyou.php)
 *
 * FLOW:
 * Customer submit order form
 *   → index.html POST ke checkout.php
 *   → checkout.php buat API call ke ToyyibPay
 *   → Redirect customer ke halaman bayaran ToyyibPay
 *   → Selepas bayar, ToyyibPay redirect ke thankyou.php
 *   → ToyyibPay juga callback ke callback.php
 * =====================================================
 */

/* =====================================================
   CONFIG — INTERN KENA UBAH BAHAGIAN INI
===================================================== */
define('TP_SECRET_KEY',    'YOUR_TOYYIBPAY_SECRET_KEY');  // Dari dashboard ToyyibPay
define('TP_CATEGORY_CODE', 'YOUR_CATEGORY_CODE');          // Dari dashboard ToyyibPay
define('TP_SANDBOX',       true);    // true = test mode | false = live production
define('STORE_NAME',       'The Artisan Parfum');
define('STORE_EMAIL',      'info@theartisanparfum.my');    // Emel tuan kedai
define('WA_NUMBER',        '601159003985');                  // Nombor WA untuk fallback
define('BASE_URL',         'https://theartisanparfum.my'); // ← TUKAR kepada domain anda (tiada slash di hujung)
define('MIN_ORDER_RM',     1);                               // Minimum order dalam RM

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

// Basic security header
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

/* =====================================================
   HELPER FUNCTIONS
===================================================== */

/**
 * Sanitize input — remove HTML tags, trim whitespace
 */
function clean(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)));
}

/**
 * Validate Malaysian phone number
 */
function isValidPhone(string $phone): bool {
    $clean = preg_replace('/[\s\-\(\)]/', '', $phone);
    return (bool) preg_match('/^(\+?60|0)[0-9]{8,10}$/', $clean);
}

/**
 * Log order to file (backup record)
 */
function logOrder(string $message): void {
    $logFile = __DIR__ . '/orders.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
}

/**
 * Generate unique order reference
 */
function generateOrderRef(string $phone): string {
    $hash = strtoupper(substr(md5(time() . $phone . rand(1000, 9999)), 0, 8));
    return 'TAP-' . $hash;
}

/**
 * Redirect to WhatsApp as fallback when payment gateway fails
 */
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
$items    = clean($_POST['items']    ?? '');         // "Aventus Noir (60ml) x1 | ..."
$itemsFmt = clean($_POST['itemsFormatted'] ?? $items); // "• Aventus Noir (60ml) ×1 = RM79\n..."
$total    = (int) filter_var($_POST['total'] ?? 0, FILTER_SANITIZE_NUMBER_INT);

/* =====================================================
   STEP 2 — SERVER-SIDE VALIDATION
===================================================== */
$errors = [];

if (strlen($name) < 3)         $errors[] = 'Nama tidak sah (minimum 3 huruf)';
if (!isValidPhone($phone))     $errors[] = 'No. telefon tidak sah';
if ($total < MIN_ORDER_RM)     $errors[] = 'Jumlah pesanan tidak sah';
if (empty($address))           $errors[] = 'Alamat diperlukan';
if (empty($items))             $errors[] = 'Item pesanan tidak sah';

if (!empty($errors)) {
    // Return error JSON (frontend can handle this if needed)
    http_response_code(400);
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'errors' => $errors]));
}

/* =====================================================
   STEP 3 — GENERATE ORDER ID & LOG
===================================================== */
$orderId   = generateOrderRef($phone);
$totalSen  = $total * 100; // ToyyibPay uses sen (100 sen = RM1)
$billLabel = STORE_NAME . ' — ' . $orderId;
$billDesc  = "Pesanan #{$orderId}: " . substr($items, 0, 150);
if ($note) $billDesc .= " | Nota: " . substr($note, 0, 40);

// Log the order attempt
logOrder("NEW ORDER | {$orderId} | {$name} | {$phone} | RM {$total} | {$items}");

/* =====================================================
   STEP 4 — CALL TOYYIBPAY API
===================================================== */
$postFields = [
    'userSecretKey'          => TP_SECRET_KEY,
    'categoryCode'           => TP_CATEGORY_CODE,
    'billName'               => $billLabel,
    'billDescription'        => $billDesc,
    'billPriceSetting'       => 1,          // 1 = Jumlah tetap
    'billPayorInfo'          => 1,          // 1 = Kumpul maklumat payer
    'billAmount'             => $totalSen,
    'billReturnUrl'          => BASE_URL . '/thankyou.php?ref=' . $orderId,
    'billCallbackUrl'        => BASE_URL . '/callback.php',
    'billExternalReferenceNo' => $orderId,
    'billTo'                 => $name,
    'billEmail'              => !empty($email) ? $email : STORE_EMAIL,
    'billPhone'              => preg_replace('/[\s\-]/', '', $phone),
    'billSplitPayment'       => 0,
    'billSplitPaymentArgs'   => '',
    'billPaymentChannel'     => 0,          // 0 = Semua kaedah (FPX + Kad + eWallet)
    'billContentEmail'       =>
        "Terima kasih kerana membeli di " . STORE_NAME . "!\n\n"
        . "Pesanan: #{$orderId}\n"
        . "Jumlah: RM {$total}\n\n"
        . "Item:\n{$itemsFmt}\n\n"
        . "Kami akan menghubungi anda dalam 24 jam untuk pengesahan penghantaran.\n\n"
        . "WhatsApp: +60 " . substr(WA_NUMBER, 2), // Format: +60 11-5900 3985
    'billChargeToCustomer'   => 1,          // 1 = Pelanggan bayar processing fee
];

// Make API call via cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => TP_API_URL,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($postFields),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => !TP_SANDBOX,  // Disable SSL verify in sandbox
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
    // cURL failed
    logOrder("CURL ERROR | {$orderId} | {$curlError}");
    fallbackToWhatsApp($name, $phone, $address, $itemsFmt, $total, $note);
}

if ($httpCode !== 200) {
    // API returned non-200
    logOrder("API HTTP ERROR | {$orderId} | HTTP:{$httpCode}");
    fallbackToWhatsApp($name, $phone, $address, $itemsFmt, $total, $note);
}

$result = json_decode($apiResponse, true);

if (empty($result) || !isset($result[0]['BillCode'])) {
    // API returned unexpected response
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
