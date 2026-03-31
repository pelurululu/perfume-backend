<?php
/**
 * =====================================================
 * THE ARTISAN PARFUM — callback.php
 * ToyyibPay Payment Callback Handler
 *
 * ToyyibPay akan POST ke URL ini setiap kali ada
 * pembayaran (berjaya, gagal, atau pending).
 *
 * INTERN NOTE:
 * - Fail ini berfungsi di background (pelanggan tak nampak)
 * - Semua data bayaran akan dilog ke payments.log
 * - Untuk production: tambah code update database di sini
 * =====================================================
 */

define('LOG_FILE',  __DIR__ . '/payments.log');
define('WA_NUMBER', '601159003985');
define('STORE_NAME','The Artisan Parfum');

/* ─── HELPER ─── */
function logPayment(string $msg): void {
    file_put_contents(LOG_FILE, '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n", FILE_APPEND | LOCK_EX);
}

/* ─── READ CALLBACK DATA ─── */
// ToyyibPay sends both POST and GET
$billCode = $_POST['billcode']   ?? $_GET['billcode']   ?? '';
$refNo    = $_POST['refno']      ?? $_GET['refno']      ?? '';
$status   = $_POST['status']     ?? $_GET['status']     ?? '';
$reason   = $_POST['reason']     ?? $_GET['reason']     ?? '';
$orderId  = $_POST['order_id']   ?? $_GET['order_id']   ?? '';
$amount   = $_POST['amount']     ?? $_GET['amount']     ?? 0;
$amountRM = number_format((int)$amount / 100, 2);

logPayment("CALLBACK | BillCode:{$billCode} | Ref:{$refNo} | Status:{$status} | Amount:RM{$amountRM} | Reason:{$reason}");

/* ─── PROCESS BY STATUS ─── */
// Status codes: 1 = success, 2 = pending, 3 = failed
if ($status == '1') {
    // ✅ PAYMENT SUCCESS
    logPayment("SUCCESS | {$billCode} | Ref:{$refNo} | RM{$amountRM}");

    /*
     * TODO (INTERN): Add your database update here when ready
     * Example:
     *   $pdo = new PDO(...);
     *   $pdo->prepare("UPDATE orders SET status='paid', payment_ref=? WHERE order_ref=?")
     *       ->execute([$refNo, $billCode]);
     *
     * TODO: Send email confirmation to customer here
     * TODO: Notify admin via WhatsApp API (optional)
     */

    http_response_code(200);
    echo 'OK';

} elseif ($status == '2') {
    // ⏳ PAYMENT PENDING
    logPayment("PENDING | {$billCode} | Ref:{$refNo}");
    http_response_code(200);
    echo 'PENDING_NOTED';

} else {
    // ❌ PAYMENT FAILED
    logPayment("FAILED | {$billCode} | Reason:{$reason}");
    http_response_code(200);
    echo 'FAILED_NOTED';
}
