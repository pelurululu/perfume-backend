<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Terima Kasih — The Artisan Parfum</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400&display=swap" rel="stylesheet">
<style>
  :root { --k:#0A0908; --c:#F4EFE6; --g:#BF9B5F; --g2:#D4AF74; --m2:#A89E92; }
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    background: var(--k); color: var(--c);
    font-family: 'DM Sans', sans-serif; font-weight: 300;
    display: flex; align-items: center; justify-content: center;
    min-height: 100vh; text-align: center; padding: 24px;
  }
  .box { max-width: 480px; width: 100%; }
  .logo { font-family: 'Cormorant Garamond', serif; font-size: 18px; font-style: italic; color: var(--c); margin-bottom: 48px; letter-spacing: .04em; display: block; opacity: .6; }
  .logo span { color: var(--g); }
  .icon { font-size: 52px; margin-bottom: 20px; }
  h1 { font-family: 'Cormorant Garamond', serif; font-size: 42px; font-weight: 300; font-style: italic; color: var(--g); margin-bottom: 14px; }
  .subtitle { font-size: 12px; color: var(--m2); line-height: 2; margin-bottom: 32px; letter-spacing: .025em; }
  .ref-box { background: rgba(191,155,95,.08); border: 1px solid rgba(191,155,95,.2); padding: 14px 20px; margin-bottom: 28px; }
  .ref-lbl { font-size: 7.5px; letter-spacing: .3em; text-transform: uppercase; color: var(--g); margin-bottom: 4px; }
  .ref-val { font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 400; color: var(--c); letter-spacing: .08em; }
  .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
  .btn-primary {
    display: inline-flex; align-items: center; gap: 8px;
    background: #25D366; color: #fff; padding: 12px 28px;
    font-family: 'DM Sans', sans-serif; font-size: 9.5px;
    letter-spacing: .2em; text-transform: uppercase; text-decoration: none;
    transition: background .2s;
  }
  .btn-primary:hover { background: #1db956; }
  .btn-secondary {
    display: inline-flex; align-items: center; gap: 8px;
    background: transparent; color: var(--g); padding: 12px 28px;
    font-family: 'DM Sans', sans-serif; font-size: 9.5px;
    letter-spacing: .2em; text-transform: uppercase; text-decoration: none;
    border: 1px solid rgba(191,155,95,.3); transition: border-color .2s;
  }
  .btn-secondary:hover { border-color: var(--g); }
  .tagline { margin-top: 44px; font-family: 'Cormorant Garamond', serif; font-size: 11px; font-style: italic; color: rgba(191,155,95,.45); letter-spacing: .1em; }
</style>
</head>
<body>
<?php
// ─── Get data from URL params sent by ToyyibPay ─── //
$orderRef  = htmlspecialchars($_GET['ref']       ?? '');
$statusId  = htmlspecialchars($_GET['status_id'] ?? '1');
$billCode  = htmlspecialchars($_GET['billcode']  ?? '');
$refNo     = htmlspecialchars($_GET['refno']     ?? '');

// Determine success/fail
$isSuccess = ($statusId === '1' || $statusId === '');

// Log visit
$logFile = __DIR__ . '/payments.log';
file_put_contents($logFile,
    '[' . date('Y-m-d H:i:s') . "] THANKYOU_PAGE | OrderRef:{$orderRef} | Status:{$statusId} | BillCode:{$billCode}\n",
    FILE_APPEND | LOCK_EX
);

$waUrl = 'https://wa.me/601159003985?text=' . urlencode(
    "Salam, saya baru sahaja membuat bayaran untuk pesanan #{$orderRef}. Boleh sahkan status? Terima kasih!"
);
?>

<div class="box">
  <a href="index.html" class="logo">the artisan<span>.</span></a>

  <?php if ($isSuccess): ?>
    <div class="icon">✓</div>
    <h1>Terima Kasih!</h1>
    <p class="subtitle">
      Pembayaran anda telah berjaya. Pasukan kami akan menghubungi anda melalui
      <strong style="color:var(--g)">WhatsApp</strong> dalam masa 1–2 jam untuk
      pengesahan dan penghantaran.
    </p>
  <?php else: ?>
    <div class="icon" style="font-size:42px">✕</div>
    <h1 style="color:var(--m2)">Bayaran Tidak Berjaya</h1>
    <p class="subtitle">
      Maaf, pembayaran anda tidak berjaya diproses. Sila cuba sekali lagi atau
      hubungi kami terus via WhatsApp untuk bantuan.
    </p>
  <?php endif; ?>

  <?php if ($orderRef): ?>
  <div class="ref-box">
    <div class="ref-lbl">Nombor Rujukan Pesanan</div>
    <div class="ref-val"><?= $orderRef ?></div>
  </div>
  <?php endif; ?>

  <div class="actions">
    <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="btn-primary">
      💬 Hubungi via WhatsApp
    </a>
    <a href="index.html" class="btn-secondary">
      ← Kembali ke Kedai
    </a>
  </div>

  <p class="tagline">the artisan parfum — wangian yang bercerita.</p>
</div>
</body>
</html>
