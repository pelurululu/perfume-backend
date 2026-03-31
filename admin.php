<?php
/**
 * =====================================================
 * THE ARTISAN PARFUM — admin.php
 * Seller Order Dashboard
 * =====================================================
 */

// ── CHANGE THIS PASSWORD ──
define('ADMIN_PASSWORD', 'artisan2024');
define('ORDERS_LOG',    __DIR__ . '/orders.log');
define('PAYMENTS_LOG',  __DIR__ . '/payments.log');

// ── SESSION AUTH ──
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
    } else {
        $error = 'Kata laluan salah.';
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}
$loggedIn = !empty($_SESSION['admin']);

// ── PARSE LOGS (only when logged in) ──
$orders   = [];  // keyed by orderId
$payments = [];  // keyed by orderId/billCode

if ($loggedIn) {

    // --- Parse orders.log ---
    // Format: [2025-01-01 12:00:00] NEW ORDER | TAP-XXXXXXXX | Name | Phone | RM 79 | items...
    // Format: [2025-01-01 12:00:01] BILL CREATED | TAP-XXXXXXXX | BillCode:abc123 | RM 79 | Redirect:...
    if (file_exists(ORDERS_LOG)) {
        foreach (file(ORDERS_LOG, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (!preg_match('/^\[(.+?)\]\s+(.+)$/', $line, $m)) continue;
            $ts   = $m[1];
            $body = $m[2];

            if (str_starts_with($body, 'NEW ORDER')) {
                $parts = explode(' | ', $body);
                // NEW ORDER | orderId | name | phone | RM total | items
                $orderId = trim($parts[1] ?? '');
                if (!$orderId) continue;
                $orders[$orderId] = [
                    'id'        => $orderId,
                    'time'      => $ts,
                    'name'      => trim($parts[2] ?? ''),
                    'phone'     => trim($parts[3] ?? ''),
                    'total'     => trim($parts[4] ?? ''),
                    'items'     => trim($parts[5] ?? ''),
                    'billCode'  => '',
                    'payStatus' => 'pending',
                    'payRef'    => '',
                ];
            } elseif (str_starts_with($body, 'BILL CREATED')) {
                $parts = explode(' | ', $body);
                $orderId = trim($parts[1] ?? '');
                if (!$orderId || !isset($orders[$orderId])) continue;
                // Extract BillCode:xxx
                foreach ($parts as $p) {
                    if (str_starts_with($p, 'BillCode:')) {
                        $orders[$orderId]['billCode'] = substr($p, 9);
                    }
                }
            }
        }
    }

    // --- Parse payments.log ---
    // SUCCESS | billCode | Ref:xxx | RMxx.xx
    // PENDING | billCode | Ref:xxx
    // FAILED  | billCode | Reason:xxx
    if (file_exists(PAYMENTS_LOG)) {
        foreach (file(PAYMENTS_LOG, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (!preg_match('/^\[(.+?)\]\s+(.+)$/', $line, $m)) continue;
            $body = $m[2];

            $status = null;
            if (str_starts_with($body, 'SUCCESS')) $status = 'paid';
            elseif (str_starts_with($body, 'PENDING')) $status = 'pending';
            elseif (str_starts_with($body, 'FAILED'))  $status = 'failed';
            if (!$status) continue;

            $parts    = explode(' | ', $body);
            $billCode = trim($parts[1] ?? '');
            $ref      = '';
            foreach ($parts as $p) {
                if (str_starts_with($p, 'Ref:')) $ref = substr($p, 4);
            }

            // Match billCode back to order
            foreach ($orders as $oid => &$o) {
                if ($o['billCode'] === $billCode) {
                    $o['payStatus'] = $status;
                    if ($ref) $o['payRef'] = $ref;
                    break;
                }
            }
            unset($o);
        }
    }

    // Sort newest first
    usort($orders, fn($a, $b) => strcmp($b['time'], $a['time']));

    // ── STATS ──
    $total_orders  = count($orders);
    $total_paid    = count(array_filter($orders, fn($o) => $o['payStatus'] === 'paid'));
    $total_pending = count(array_filter($orders, fn($o) => $o['payStatus'] === 'pending'));
    $total_failed  = count(array_filter($orders, fn($o) => $o['payStatus'] === 'failed'));
    $total_revenue = array_sum(array_map(function($o) {
        preg_match('/[\d.]+/', $o['total'], $m);
        return $o['payStatus'] === 'paid' ? (float)($m[0] ?? 0) : 0;
    }, $orders));
}

// ── FILTER ──
$filterStatus = $_GET['status'] ?? '';
$filtered = $loggedIn ? array_filter($orders, function($o) use ($filterStatus) {
    return !$filterStatus || $o['payStatus'] === $filterStatus;
}) : [];
?>
<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — The Artisan Parfum</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<style>
:root {
  --k:#0A0908; --k2:#111010; --k3:#181614;
  --c:#F4EFE6; --m:#8A8278; --m2:#6A6058;
  --g:#BF9B5F; --g2:#D4AF74;
  --border:rgba(255,255,255,.07);
  --paid:#2ecc71; --pending:#f39c12; --failed:#e74c3c;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{background:var(--k);color:var(--c);font-family:'DM Sans',sans-serif;font-weight:300;min-height:100vh;}

/* LOGIN */
.login-wrap{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;}
.login-box{width:100%;max-width:360px;border:1px solid var(--border);padding:40px 36px;background:var(--k2);}
.login-logo{font-family:'Cormorant Garamond',serif;font-size:16px;font-style:italic;color:var(--g);letter-spacing:.06em;margin-bottom:28px;display:block;}
.login-box h1{font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:300;margin-bottom:6px;}
.login-box p{font-size:11px;color:var(--m);margin-bottom:28px;letter-spacing:.02em;}
.field{margin-bottom:16px;}
.field label{display:block;font-size:9px;letter-spacing:.2em;text-transform:uppercase;color:var(--m);margin-bottom:7px;}
.field input{width:100%;padding:10px 14px;background:var(--k3);border:1px solid var(--border);color:var(--c);font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .2s;}
.field input:focus{border-color:var(--g);}
.btn-login{width:100%;padding:12px;background:var(--g);color:var(--k);font-family:'DM Sans',sans-serif;font-size:9px;letter-spacing:.22em;text-transform:uppercase;border:none;cursor:pointer;transition:background .2s;margin-top:4px;}
.btn-login:hover{background:var(--g2);}
.error{background:rgba(231,76,60,.1);border:1px solid rgba(231,76,60,.3);color:#e74c3c;padding:10px 14px;font-size:11px;margin-bottom:16px;}

/* DASHBOARD */
.dash-nav{background:var(--k2);border-bottom:1px solid var(--border);padding:14px 32px;display:flex;align-items:center;justify-content:space-between;}
.dash-brand{font-family:'Cormorant Garamond',serif;font-size:16px;font-style:italic;color:var(--g);letter-spacing:.05em;}
.dash-nav-right{display:flex;align-items:center;gap:20px;}
.dash-time{font-size:10px;color:var(--m);letter-spacing:.06em;}
.btn-logout{font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:var(--m);background:none;border:1px solid var(--border);padding:6px 14px;cursor:pointer;transition:.2s;font-family:'DM Sans',sans-serif;}
.btn-logout:hover{border-color:var(--g);color:var(--g);}

.dash-body{padding:32px;}
.dash-title{font-family:'Cormorant Garamond',serif;font-size:28px;font-weight:300;font-style:italic;margin-bottom:4px;}
.dash-sub{font-size:10px;color:var(--m);letter-spacing:.08em;margin-bottom:28px;}

/* STATS CARDS */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--border);margin-bottom:28px;}
.stat-card{background:var(--k2);padding:20px 24px;}
.stat-label{font-size:8.5px;letter-spacing:.22em;text-transform:uppercase;color:var(--m);margin-bottom:8px;}
.stat-val{font-family:'Cormorant Garamond',serif;font-size:34px;font-weight:300;line-height:1;}
.stat-val.green{color:var(--paid);}
.stat-val.gold{color:var(--g2);}
.stat-val.orange{color:var(--pending);}
.stat-val.red{color:var(--failed);}

/* FILTER TABS */
.filter-row{display:flex;gap:0;border:1px solid var(--border);width:fit-content;margin-bottom:20px;}
.filter-tab{padding:8px 22px;font-size:9px;letter-spacing:.18em;text-transform:uppercase;background:transparent;border:none;cursor:pointer;color:var(--m2);transition:.2s;font-family:'DM Sans',sans-serif;text-decoration:none;display:block;}
.filter-tab:hover{color:var(--g);}
.filter-tab.active{background:rgba(191,155,95,.1);color:var(--g2);}

/* TABLE */
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;font-size:12px;}
thead tr{border-bottom:1px solid var(--border);}
th{font-size:8px;letter-spacing:.2em;text-transform:uppercase;color:var(--m);font-weight:400;padding:10px 14px;text-align:left;white-space:nowrap;}
td{padding:13px 14px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:top;}
tr:hover td{background:rgba(255,255,255,.015);}
.td-ref{font-family:'Cormorant Garamond',serif;font-size:13px;letter-spacing:.04em;color:var(--g);white-space:nowrap;}
.td-name{font-weight:400;}
.td-phone{color:var(--m);font-size:11px;}
.td-items{font-size:10.5px;color:rgba(194,186,174,.65);max-width:280px;line-height:1.6;}
.td-total{font-weight:400;white-space:nowrap;color:var(--g2);}
.td-time{font-size:10px;color:var(--m2);white-space:nowrap;}
.badge{display:inline-block;padding:3px 10px;font-size:8px;letter-spacing:.15em;text-transform:uppercase;font-weight:400;}
.badge-paid{background:rgba(46,204,113,.1);color:var(--paid);border:1px solid rgba(46,204,113,.25);}
.badge-pending{background:rgba(243,156,18,.1);color:var(--pending);border:1px solid rgba(243,156,18,.25);}
.badge-failed{background:rgba(231,76,60,.1);color:var(--failed);border:1px solid rgba(231,76,60,.25);}
.wa-link{font-size:10px;color:var(--g);text-decoration:none;opacity:.7;}
.wa-link:hover{opacity:1;}
.empty-state{text-align:center;padding:60px 0;color:var(--m2);}
.empty-state p{font-family:'Cormorant Garamond',serif;font-size:20px;font-style:italic;margin-bottom:6px;}
@media(max-width:900px){
  .stats-grid{grid-template-columns:repeat(2,1fr);}
  .dash-body{padding:20px 16px;}
  .dash-nav{padding:14px 16px;}
}
</style>
</head>
<body>

<?php if (!$loggedIn): ?>
<!-- ── LOGIN SCREEN ── -->
<div class="login-wrap">
  <div class="login-box">
    <span class="login-logo">the artisan<span style="color:var(--g2)">.</span></span>
    <h1>Dashboard Admin</h1>
    <p>Log masuk untuk lihat pesanan</p>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="field">
        <label>Kata Laluan</label>
        <input type="password" name="password" autofocus placeholder="••••••••">
      </div>
      <button type="submit" class="btn-login">Log Masuk</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ── DASHBOARD ── -->
<nav class="dash-nav">
  <span class="dash-brand">the artisan<span style="color:var(--g2)">.</span> admin</span>
  <div class="dash-nav-right">
    <span class="dash-time"><?= date('d M Y, H:i') ?></span>
    <a href="?logout=1" class="btn-logout">Log Keluar</a>
  </div>
</nav>

<div class="dash-body">
  <h1 class="dash-title">Semua Pesanan</h1>
  <p class="dash-sub"><?= $total_orders ?> pesanan diterima · dikemas kini setiap muat semula</p>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Jumlah Pesanan</div>
      <div class="stat-val"><?= $total_orders ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Bayaran Berjaya</div>
      <div class="stat-val green"><?= $total_paid ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Belum Bayar</div>
      <div class="stat-val orange"><?= $total_pending ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Jumlah Hasil (RM)</div>
      <div class="stat-val gold"><?= number_format($total_revenue, 0) ?></div>
    </div>
  </div>

  <!-- Filter Tabs -->
  <div class="filter-row">
    <a href="admin.php" class="filter-tab <?= !$filterStatus ? 'active':'' ?>">Semua (<?= $total_orders ?>)</a>
    <a href="?status=paid"    class="filter-tab <?= $filterStatus==='paid'    ? 'active':'' ?>">✓ Dibayar (<?= $total_paid ?>)</a>
    <a href="?status=pending" class="filter-tab <?= $filterStatus==='pending' ? 'active':'' ?>">⏳ Pending (<?= $total_pending ?>)</a>
    <a href="?status=failed"  class="filter-tab <?= $filterStatus==='failed'  ? 'active':'' ?>">✕ Gagal (<?= $total_failed ?>)</a>
  </div>

  <!-- Table -->
  <div class="table-wrap">
    <?php if (empty($filtered)): ?>
      <div class="empty-state">
        <p>Tiada pesanan dijumpai</p>
        <span style="font-size:11px">Log kosong atau tiada pesanan dalam kategori ini</span>
      </div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Ref Pesanan</th>
          <th>Status</th>
          <th>Pelanggan</th>
          <th>Item</th>
          <th>Jumlah</th>
          <th>Masa</th>
          <th>Tindakan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filtered as $o): ?>
        <tr>
          <td>
            <div class="td-ref"><?= htmlspecialchars($o['id']) ?></div>
            <?php if ($o['billCode']): ?>
              <div style="font-size:9px;color:var(--m);margin-top:2px;">Bill: <?= htmlspecialchars($o['billCode']) ?></div>
            <?php endif; ?>
            <?php if ($o['payRef']): ?>
              <div style="font-size:9px;color:var(--m);margin-top:1px;">Ref: <?= htmlspecialchars($o['payRef']) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <?php
              $badgeClass = match($o['payStatus']) {
                'paid'    => 'badge-paid',
                'failed'  => 'badge-failed',
                default   => 'badge-pending',
              };
              $badgeLabel = match($o['payStatus']) {
                'paid'    => '✓ Dibayar',
                'failed'  => '✕ Gagal',
                default   => '⏳ Pending',
              };
            ?>
            <span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
          </td>
          <td>
            <div class="td-name"><?= htmlspecialchars($o['name']) ?></div>
            <div class="td-phone"><?= htmlspecialchars($o['phone']) ?></div>
          </td>
          <td>
            <div class="td-items"><?= nl2br(htmlspecialchars(str_replace(' | ', "\n", $o['items']))) ?></div>
          </td>
          <td><div class="td-total"><?= htmlspecialchars($o['total']) ?></div></td>
          <td><div class="td-time"><?= htmlspecialchars($o['time']) ?></div></td>
          <td>
            <?php if ($o['phone']): ?>
              <?php
                $waPhone = preg_replace('/[^0-9]/', '', $o['phone']);
                if (str_starts_with($waPhone, '0')) $waPhone = '6' . $waPhone;
                $waMsg = urlencode("Salam {$o['name']}, terima kasih kerana membeli di The Artisan Parfum! Pesanan anda ({$o['id']}) sedang kami proses. Kami akan maklumkan status penghantaran tidak lama lagi.");
              ?>
              <a href="https://wa.me/<?= $waPhone ?>?text=<?= $waMsg ?>" target="_blank" class="wa-link">💬 WhatsApp</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php endif; ?>
</body>
</html>
