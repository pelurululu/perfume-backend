<?php
// ── SIMPLE AUTH ──
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'artisan2024');
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
?>
<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — The Artisan Parfum</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
<style>
:root {
  /* Off-white light theme */
  --bg:    #F7F4EF;
  --bg2:   #EDE9E1;
  --bg3:   #E4DFD5;
  --bg4:   #D9D2C5;
  --ink:   #1C1A16;
  --ink2:  #2E2B24;
  --ink3:  #4A4640;
  --muted: #7A756C;
  --muted2:#A09890;
  --g:     #8A6A3A;
  --g2:    #A07F4A;
  --g3:    #C4A06A;
  --border: rgba(138,106,58,.14);
  --shadow: 0 1px 3px rgba(28,26,22,.08), 0 4px 16px rgba(28,26,22,.06);
  --red:   #B04040;
  --grn:   #3A7A52;
  --amb:   #B07030;
  --t: .22s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--ink);font-family:'DM Sans',sans-serif;font-weight:300;min-height:100vh;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}
button,input,select,textarea{font-family:inherit}
img{display:block;max-width:100%}

/* ── LOGIN PAGE ── */
.login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:var(--bg);position:relative;overflow:hidden}
.login-wrap::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 55% 55% at 50% 40%,rgba(138,106,58,.07) 0%,transparent 70%)}
.login-box{width:100%;max-width:360px;position:relative;z-index:1}
.login-logo{font-family:'Cormorant Garamond',serif;font-size:24px;font-style:italic;color:var(--ink);text-align:center;margin-bottom:6px;letter-spacing:.04em}
.login-logo span{color:var(--g)}
.login-sub{font-size:8px;letter-spacing:.32em;text-transform:uppercase;color:var(--muted);text-align:center;margin-bottom:40px}
.login-box label{display:block;font-size:7.5px;letter-spacing:.22em;text-transform:uppercase;color:var(--muted);margin-bottom:6px}
.login-box input{width:100%;padding:11px 14px;background:#fff;border:1px solid var(--border);color:var(--ink);font-size:13px;outline:none;transition:border-color var(--t);margin-bottom:14px;box-shadow:var(--shadow)}
.login-box input:focus{border-color:var(--g)}
.login-error{font-size:9px;color:var(--red);margin-bottom:10px;letter-spacing:.04em;display:flex;align-items:center;gap:6px}
.btn-login{width:100%;padding:12px;background:var(--g);color:#fff;font-size:9px;letter-spacing:.22em;text-transform:uppercase;border:none;cursor:pointer;transition:background var(--t)}
.btn-login:hover{background:var(--g2)}

/* ── LAYOUT ── */
.admin-wrap{display:grid;grid-template-columns:220px 1fr;min-height:100vh}

/* ── SIDEBAR ── */
.sidebar{background:var(--ink2);border-right:1px solid rgba(255,255,255,.06);padding:28px 0;position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column}
.sidebar-logo{font-family:'Cormorant Garamond',serif;font-size:17px;font-style:italic;color:#F4EFE6;padding:0 22px 24px;border-bottom:1px solid rgba(255,255,255,.07);letter-spacing:.04em}
.sidebar-logo span{color:var(--g3)}
.sidebar-logo small{display:block;font-family:'DM Sans',sans-serif;font-size:7px;letter-spacing:.28em;text-transform:uppercase;color:rgba(244,239,230,.28);margin-top:3px;font-style:normal}
.nav-group{padding:20px 0 0}
.nav-label{font-size:6.5px;letter-spacing:.32em;text-transform:uppercase;color:rgba(244,239,230,.2);padding:0 22px 8px}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 22px;font-size:11px;color:rgba(244,239,230,.45);cursor:pointer;transition:var(--t);border-left:2px solid transparent;letter-spacing:.02em}
.nav-item:hover{color:rgba(244,239,230,.9);background:rgba(255,255,255,.04)}
.nav-item.active{color:var(--g3);border-left-color:var(--g3);background:rgba(196,160,106,.08)}
.nav-item svg{width:14px;height:14px;flex-shrink:0;opacity:.6}
.nav-item.active svg{opacity:1}
.sidebar-footer{margin-top:auto;padding:18px 22px;border-top:1px solid rgba(255,255,255,.06)}
.btn-logout{width:100%;padding:8px;background:transparent;border:1px solid rgba(255,255,255,.1);color:rgba(244,239,230,.38);font-size:8px;letter-spacing:.18em;text-transform:uppercase;cursor:pointer;transition:var(--t)}
.btn-logout:hover{border-color:rgba(176,64,64,.5);color:#D07070}

/* ── MAIN CONTENT ── */
.main{padding:36px 40px;overflow-y:auto;background:var(--bg)}
.page{display:none}
.page.active{display:block}
.page-header{margin-bottom:32px}
.page-eyebrow{font-size:7px;letter-spacing:.38em;text-transform:uppercase;color:var(--g);margin-bottom:6px}
.page-title{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:300;color:var(--ink)}
.page-title em{font-style:italic;color:var(--g)}

/* ── STAT CARDS ── */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:28px}
.stat-card{background:#fff;padding:22px 20px;border:1px solid var(--border);box-shadow:var(--shadow)}
.stat-label{font-size:7px;letter-spacing:.25em;text-transform:uppercase;color:var(--muted);margin-bottom:8px}
.stat-value{font-family:'Cormorant Garamond',serif;font-size:36px;font-weight:300;color:var(--g);line-height:1}
.stat-sub{font-size:9px;color:var(--muted2);margin-top:4px}

/* ── TABLES ── */
.table-wrap{background:#fff;border:1px solid var(--border);overflow:hidden;margin-bottom:24px;box-shadow:var(--shadow)}
.table-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
.table-title{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:400;color:var(--ink)}
table{width:100%;border-collapse:collapse}
th{font-size:7px;letter-spacing:.22em;text-transform:uppercase;color:var(--muted);padding:10px 16px;text-align:left;border-bottom:1px solid var(--border);white-space:nowrap;background:var(--bg)}
td{padding:12px 16px;font-size:11px;color:var(--muted);border-bottom:1px solid rgba(138,106,58,.06);vertical-align:middle}
tr:last-child td{border:none}
tr:hover td{background:var(--bg);color:var(--ink)}
.badge-paid{background:rgba(58,122,82,.1);color:var(--grn);padding:3px 9px;font-size:7px;letter-spacing:.12em;text-transform:uppercase;border:1px solid rgba(58,122,82,.2)}
.badge-pending{background:rgba(176,112,48,.1);color:var(--amb);padding:3px 9px;font-size:7px;letter-spacing:.12em;text-transform:uppercase;border:1px solid rgba(176,112,48,.2)}
.badge-failed{background:rgba(176,64,64,.08);color:var(--red);padding:3px 9px;font-size:7px;letter-spacing:.12em;text-transform:uppercase;border:1px solid rgba(176,64,64,.2)}

/* ── SEARCH / FILTER BAR ── */
.filter-bar{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.filter-bar input,.filter-bar select{padding:7px 12px;background:#fff;border:1px solid var(--border);color:var(--ink);font-size:11px;outline:none;transition:border-color var(--t)}
.filter-bar input:focus,.filter-bar select:focus{border-color:var(--g)}
.filter-bar input::placeholder{color:var(--muted2)}

/* ── BUTTONS ── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;font-size:8.5px;letter-spacing:.18em;text-transform:uppercase;border:none;cursor:pointer;transition:var(--t);font-family:'DM Sans',sans-serif;font-weight:400}
.btn-primary{background:var(--g);color:#fff}
.btn-primary:hover{background:var(--g2)}
.btn-ghost{background:transparent;border:1px solid var(--border);color:var(--muted)}
.btn-ghost:hover{border-color:var(--g);color:var(--g)}
.btn-danger{background:transparent;border:1px solid rgba(176,64,64,.25);color:var(--red)}
.btn-danger:hover{background:rgba(176,64,64,.08)}
.btn-sm{padding:5px 12px;font-size:7.5px}
.btn-view{background:transparent;border:1px solid var(--border);color:var(--muted);padding:4px 10px;font-size:7px;letter-spacing:.14em;text-transform:uppercase;cursor:pointer;transition:var(--t);font-family:'DM Sans',sans-serif}
.btn-view:hover{border-color:var(--g);color:var(--g);background:rgba(138,106,58,.04)}

/* ── PRODUCT GRID (admin) ── */
.product-admin-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.product-admin-card{background:#fff;padding:18px;display:flex;gap:14px;align-items:flex-start;border:1px solid var(--border);box-shadow:var(--shadow);transition:box-shadow var(--t)}
.product-admin-card:hover{box-shadow:0 4px 20px rgba(28,26,22,.1)}
.pac-img{width:50px;height:70px;flex-shrink:0;object-fit:cover;background:var(--bg2);display:flex;align-items:center;justify-content:center;overflow:hidden}
.pac-info{flex:1;min-width:0}
.pac-name{font-family:'Cormorant Garamond',serif;font-size:14px;color:var(--ink);margin-bottom:2px;line-height:1.2}
.pac-insp{font-size:8px;color:var(--muted);letter-spacing:.06em;margin-bottom:6px}
.pac-meta{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}
.pac-tag{font-size:7px;letter-spacing:.12em;text-transform:uppercase;padding:2px 7px;background:rgba(138,106,58,.08);color:var(--g);border:1px solid rgba(138,106,58,.15)}
.pac-actions{display:flex;gap:5px}

/* ── MODAL ── */
.modal-overlay{position:fixed;inset:0;background:rgba(28,26,22,.55);z-index:900;display:flex;align-items:flex-start;justify-content:center;padding:48px 20px 20px;opacity:0;pointer-events:none;transition:opacity var(--t);backdrop-filter:blur(6px);overflow-y:auto}
.modal-overlay.open{opacity:1;pointer-events:all}
.modal-box{background:#fff;width:100%;max-width:560px;padding:36px;position:relative;border:1px solid var(--border);box-shadow:0 20px 60px rgba(28,26,22,.18);transform:translateY(16px);transition:transform .3s}
.modal-overlay.open .modal-box{transform:translateY(0)}
.modal-box-lg{max-width:680px}
.modal-close{position:absolute;top:14px;right:14px;background:none;border:none;color:var(--muted);cursor:pointer;line-height:1;padding:5px;transition:color var(--t);display:flex;align-items:center;justify-content:center}
.modal-close:hover{color:var(--ink)}
.modal-close svg{width:16px;height:16px}
.modal-eyebrow{font-size:7px;letter-spacing:.38em;text-transform:uppercase;color:var(--g);margin-bottom:4px}
.modal-title{font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:400;color:var(--ink);margin-bottom:20px}

/* ── ORDER DETAIL MODAL ── */
.order-detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
.order-detail-section{background:var(--bg);border:1px solid var(--border);padding:16px}
.order-detail-section h4{font-size:7px;letter-spacing:.3em;text-transform:uppercase;color:var(--g);margin-bottom:12px}
.order-detail-row{display:flex;flex-direction:column;gap:2px;margin-bottom:10px}
.order-detail-row:last-child{margin-bottom:0}
.order-detail-label{font-size:7.5px;letter-spacing:.14em;text-transform:uppercase;color:var(--muted)}
.order-detail-value{font-size:12px;color:var(--ink);line-height:1.5}
.order-items-list{background:var(--bg);border:1px solid var(--border);padding:14px;margin-bottom:16px}
.order-items-list h4{font-size:7px;letter-spacing:.3em;text-transform:uppercase;color:var(--g);margin-bottom:10px}
.order-item-row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--border);font-size:11px;color:var(--ink2)}
.order-item-row:last-child{border:none}
.order-item-row .price{font-family:'Cormorant Garamond',serif;font-size:14px;color:var(--g)}
.order-total-row{display:flex;justify-content:space-between;align-items:center;padding:12px 0 0;border-top:2px solid var(--border)}
.order-total-row .label{font-size:8px;letter-spacing:.2em;text-transform:uppercase;color:var(--muted)}
.order-total-row .value{font-family:'Cormorant Garamond',serif;font-size:24px;color:var(--g)}
.order-ref-display{font-family:'Cormorant Garamond',serif;font-size:28px;font-weight:300;color:var(--g);margin-bottom:4px}
.order-status-row{display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--border)}
.detail-status-select{padding:6px 12px;background:#fff;border:1px solid var(--border);color:var(--ink);font-size:11px;outline:none;transition:border-color var(--t)}
.detail-status-select:focus{border-color:var(--g)}
.detail-actions{display:flex;gap:8px;margin-top:16px;padding-top:14px;border-top:1px solid var(--border)}

/* ── FORM ── */
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:7.5px;letter-spacing:.22em;text-transform:uppercase;color:var(--muted);margin-bottom:5px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:9px 12px;background:var(--bg);border:1px solid var(--border);color:var(--ink);font-size:12px;font-weight:300;outline:none;transition:border-color var(--t);-webkit-appearance:none;border-radius:0}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--g)}
.form-group textarea{resize:vertical;min-height:68px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}

/* ── IMAGE UPLOAD ── */
.img-upload-area{border:1px dashed rgba(138,106,58,.3);padding:24px;text-align:center;cursor:pointer;transition:var(--t);position:relative;background:var(--bg)}
.img-upload-area:hover{border-color:var(--g);background:rgba(138,106,58,.03)}
.img-upload-area input{position:absolute;inset:0;opacity:0;cursor:pointer}
.img-upload-text{font-size:9px;color:var(--muted);letter-spacing:.06em}
.img-preview{width:100%;height:140px;object-fit:contain;margin-bottom:8px;background:var(--bg2)}

/* ── PRICING PAGE ── */
.pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px}
.pricing-card{background:#fff;padding:28px 24px;text-align:center;border:1px solid var(--border);box-shadow:var(--shadow)}
.pricing-size{font-size:8px;letter-spacing:.32em;text-transform:uppercase;color:var(--g);margin-bottom:12px}
.pricing-inputs{display:flex;flex-direction:column;gap:10px}
.pricing-input-wrap label{font-size:7px;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);display:block;margin-bottom:4px;text-align:left}
.pricing-input-wrap input{width:100%;padding:9px 12px;background:var(--bg);border:1px solid var(--border);color:var(--ink);font-size:18px;font-family:'Cormorant Garamond',serif;font-weight:300;outline:none;transition:border-color var(--t);text-align:center}
.pricing-input-wrap input:focus{border-color:var(--g)}
.save-bar{background:rgba(138,106,58,.05);border:1px solid rgba(138,106,58,.18);padding:14px 20px;display:flex;justify-content:space-between;align-items:center}
.save-bar-text{font-size:10px;color:var(--muted)}

/* ── TOAST ── */
.toast{position:fixed;bottom:24px;right:24px;background:#fff;border:1px solid var(--border);padding:12px 18px;font-size:10px;color:var(--ink);z-index:9999;transform:translateY(80px);opacity:0;transition:var(--t);letter-spacing:.04em;box-shadow:var(--shadow)}
.toast.show{transform:translateY(0);opacity:1}
.toast.success{border-color:rgba(58,122,82,.35);color:var(--grn)}
.toast.error{border-color:rgba(176,64,64,.3);color:var(--red)}

/* ── EMPTY STATE ── */
.empty-state{text-align:center;padding:60px 20px}
.empty-state p{font-family:'Cormorant Garamond',serif;font-size:20px;font-style:italic;color:var(--muted)}
.empty-state span{font-size:9px;color:var(--muted2);display:block;margin-top:4px}

/* ── LOADING ── */
.loading{text-align:center;padding:40px;color:var(--muted);font-size:10px;letter-spacing:.1em}
.spinner{width:24px;height:24px;border:1.5px solid var(--border);border-top-color:var(--g);border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 12px}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── CLICKABLE ROW ── */
tr.clickable{cursor:pointer}
tr.clickable:hover td{background:rgba(138,106,58,.04)}

/* ── RESPONSIVE ── */
@media(max-width:900px){
  .admin-wrap{grid-template-columns:1fr}
  .sidebar{position:fixed;left:-220px;z-index:800;height:100%;transition:left var(--t)}
  .sidebar.open{left:0}
  .main{padding:20px}
  .stats-grid{grid-template-columns:1fr 1fr}
  .product-admin-grid{grid-template-columns:1fr}
  .pricing-grid{grid-template-columns:1fr}
  .order-detail-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>

<?php if (!$loggedIn): ?>
<!-- ── LOGIN SCREEN ── -->
<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo">the artisan<span>.</span></div>
    <div class="login-sub">Admin Dashboard</div>
    <?php if ($error): ?>
      <div class="login-error">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="POST">
      <label for="pw">Kata Laluan</label>
      <input type="password" id="pw" name="password" placeholder="••••••••" autofocus>
      <button type="submit" class="btn-login">Masuk</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ── ADMIN DASHBOARD ── -->
<div class="admin-wrap">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      the artisan<span>.</span>
      <small>Admin Panel</small>
    </div>

    <div class="nav-group">
      <div class="nav-label">Utama</div>
      <div class="nav-item active" onclick="showPage('dashboard')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Dashboard
      </div>
      <div class="nav-item" onclick="showPage('orders')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Pesanan
      </div>
    </div>

    <div class="nav-group">
      <div class="nav-label">Produk</div>
      <div class="nav-item" onclick="showPage('products')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        Semua Produk
      </div>
      <div class="nav-item" onclick="showPage('pricing')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Harga
      </div>
    </div>

    <div class="sidebar-footer">
      <a href="?logout=1"><button class="btn-logout">Log Keluar</button></a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <!-- ══ DASHBOARD PAGE ══ -->
    <div class="page active" id="page-dashboard">
      <div class="page-header">
        <div class="page-eyebrow">Ringkasan</div>
        <h1 class="page-title">Dashboard <em>Hari Ini</em></h1>
      </div>

      <div class="stats-grid" id="stats-grid">
        <div class="stat-card"><div class="stat-label">Jumlah Pesanan</div><div class="stat-value" id="stat-orders">—</div><div class="stat-sub">Semua masa</div></div>
        <div class="stat-card"><div class="stat-label">Bayaran Berjaya</div><div class="stat-value" id="stat-paid">—</div><div class="stat-sub">Confirmed</div></div>
        <div class="stat-card"><div class="stat-label">Jumlah Hasil</div><div class="stat-value" id="stat-revenue">—</div><div class="stat-sub">RM</div></div>
        <div class="stat-card"><div class="stat-label">Produk Aktif</div><div class="stat-value" id="stat-products">—</div><div class="stat-sub">Dalam koleksi</div></div>
      </div>

      <div class="table-wrap">
        <div class="table-header">
          <div class="table-title">Pesanan Terkini</div>
        </div>
        <div id="recent-orders-wrap"><div class="loading"><div class="spinner"></div>Memuatkan...</div></div>
      </div>
    </div>

    <!-- ══ ORDERS PAGE ══ -->
    <div class="page" id="page-orders">
      <div class="page-header">
        <div class="page-eyebrow">Pengurusan</div>
        <h1 class="page-title">Semua <em>Pesanan</em></h1>
      </div>

      <div class="table-wrap">
        <div class="table-header">
          <div class="table-title">Senarai Pesanan</div>
          <div class="filter-bar">
            <input type="text" id="order-search" placeholder="Cari nama / ref..." oninput="filterOrders()">
            <select id="order-status-filter" onchange="filterOrders()">
              <option value="">Semua Status</option>
              <option value="paid">Berjaya</option>
              <option value="pending">Pending</option>
              <option value="failed">Gagal</option>
            </select>
          </div>
        </div>
        <div id="orders-table-wrap"><div class="loading"><div class="spinner"></div>Memuatkan...</div></div>
      </div>
    </div>

    <!-- ══ PRODUCTS PAGE ══ -->
    <div class="page" id="page-products">
      <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-end">
        <div>
          <div class="page-eyebrow">Pengurusan</div>
          <h1 class="page-title">Semua <em>Produk</em></h1>
        </div>
        <button class="btn btn-primary" onclick="openProductModal()">+ Tambah Produk</button>
      </div>

      <div style="margin-bottom:16px">
        <div class="filter-bar">
          <input type="text" id="prod-search" placeholder="Cari produk..." oninput="filterProducts()">
          <select id="prod-gender-filter" onchange="filterProducts()">
            <option value="">Semua Jantina</option>
            <option value="m">Lelaki</option>
            <option value="w">Wanita</option>
            <option value="u">Unisex</option>
          </select>
          <select id="prod-status-filter" onchange="filterProducts()">
            <option value="">Semua Status</option>
            <option value="true">Aktif</option>
            <option value="false">Tidak Aktif</option>
          </select>
        </div>
      </div>

      <div id="products-grid-wrap"><div class="loading"><div class="spinner"></div>Memuatkan...</div></div>
    </div>

    <!-- ══ PRICING PAGE ══ -->
    <div class="page" id="page-pricing">
      <div class="page-header">
        <div class="page-eyebrow">Pengurusan</div>
        <h1 class="page-title">Tetapan <em>Harga</em></h1>
      </div>

      <div class="pricing-grid" id="pricing-grid">
        <div class="loading"><div class="spinner"></div>Memuatkan...</div>
      </div>

      <div class="save-bar">
        <span class="save-bar-text">Perubahan harga akan dikemas kini di kedai dalam masa nyata.</span>
        <button class="btn btn-primary" onclick="savePricing()">Simpan Harga</button>
      </div>
    </div>

  </main>
</div>

<!-- ══ ORDER DETAIL MODAL ══ -->
<div class="modal-overlay" id="order-detail-modal">
  <div class="modal-box modal-box-lg">
    <button class="modal-close" onclick="closeOrderDetail()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-eyebrow">Butiran Pesanan</div>
    <div class="order-ref-display" id="od-ref">—</div>
    <div class="order-status-row">
      <span id="od-badge"></span>
      <select class="detail-status-select" id="od-status-select" onchange="updateOrderStatusFromDetail()">
        <option value="pending">Pending</option>
        <option value="paid">Berjaya (Paid)</option>
        <option value="failed">Gagal (Failed)</option>
      </select>
      <span style="font-size:9px;color:var(--muted)" id="od-date"></span>
    </div>

    <div class="order-detail-grid">
      <div class="order-detail-section">
        <h4>Maklumat Pelanggan</h4>
        <div class="order-detail-row"><span class="order-detail-label">Nama</span><span class="order-detail-value" id="od-name">—</span></div>
        <div class="order-detail-row"><span class="order-detail-label">Telefon</span><span class="order-detail-value" id="od-phone">—</span></div>
        <div class="order-detail-row"><span class="order-detail-label">Emel</span><span class="order-detail-value" id="od-email">—</span></div>
      </div>
      <div class="order-detail-section">
        <h4>Alamat Penghantaran</h4>
        <div class="order-detail-row"><span class="order-detail-value" id="od-address">—</span></div>
      </div>
    </div>

    <div class="order-items-list">
      <h4>Item Ditempah</h4>
      <div id="od-items">—</div>
      <div class="order-total-row">
        <span class="label">Jumlah Bayaran</span>
        <span class="value" id="od-total">—</span>
      </div>
    </div>

    <div id="od-note-wrap" style="display:none;margin-bottom:14px">
      <div class="order-detail-section">
        <h4>Nota Pelanggan</h4>
        <div class="order-detail-value" id="od-note">—</div>
      </div>
    </div>

    <div class="detail-actions">
      <a id="od-wa-link" href="#" target="_blank" class="btn btn-primary">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        WhatsApp
      </a>
      <button class="btn btn-ghost" onclick="closeOrderDetail()">Tutup</button>
    </div>
  </div>
</div>

<!-- ══ PRODUCT MODAL ══ -->
<div class="modal-overlay" id="product-modal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeProductModal()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="modal-eyebrow" id="pm-eyebrow">Produk Baru</div>
    <div class="modal-title" id="pm-title">Tambah Produk</div>

    <form id="product-form" onsubmit="saveProduct(event)">
      <input type="hidden" id="pm-id">

      <div class="form-group">
        <label>Gambar Produk</label>
        <div class="img-upload-area" id="upload-area">
          <img id="img-preview" class="img-preview" style="display:none">
          <div id="upload-placeholder">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin:0 auto 8px;opacity:.3"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <div class="img-upload-text">Klik untuk muat naik gambar</div>
            <div class="img-upload-text" style="margin-top:3px;opacity:.5">PNG, JPG — max 2MB</div>
          </div>
          <input type="file" id="pm-image-file" accept="image/*" onchange="previewImage(event)">
        </div>
        <input type="hidden" id="pm-image-url">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>ID Produk *</label>
          <input type="text" id="pm-id-field" placeholder="cth: m56, w99, u13" required>
        </div>
        <div class="form-group">
          <label>Jantina *</label>
          <select id="pm-gender" required>
            <option value="">Pilih</option>
            <option value="m">Lelaki</option>
            <option value="w">Wanita</option>
            <option value="u">Unisex</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Nama Produk *</label>
        <input type="text" id="pm-name" placeholder="cth: Chanel No 5" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Terinspirasi Oleh *</label>
          <input type="text" id="pm-insp" placeholder="cth: Chanel" required>
        </div>
        <div class="form-group">
          <label>Famili Wangian *</label>
          <input type="text" id="pm-family" placeholder="cth: Floral Woody" required>
        </div>
      </div>

      <div class="form-group">
        <label>Notes Wangian *</label>
        <input type="text" id="pm-notes" placeholder="cth: Rose · Jasmine · Sandalwood" required>
      </div>

      <div class="form-group">
        <label>Deskripsi (Mood)</label>
        <textarea id="pm-mood" placeholder="Gambaran suasana wangian ini..."></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Vibe (pendek)</label>
          <input type="text" id="pm-vibe" placeholder="cth: Romantic · Soft">
        </div>
        <div class="form-group">
          <label>Badge</label>
          <select id="pm-badge">
            <option value="">Tiada</option>
            <option value="Hot">Hot</option>
            <option value="New">New</option>
            <option value="Trending">Trending</option>
            <option value="Exclusive">Exclusive</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Warna Cap (hex)</label>
          <input type="text" id="pm-cap" placeholder="#3A1828">
        </div>
        <div class="form-group">
          <label>Warna Botol (r,g,b)</label>
          <input type="text" id="pm-rgb" placeholder="155,85,110">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Stok Awal</label>
          <input type="number" id="pm-stock" value="50" min="0">
        </div>
        <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:1px">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:0">
            <input type="checkbox" id="pm-active" checked style="width:auto;padding:0;border:none;background:none;accent-color:var(--g)">
            <span style="font-size:10px;color:var(--muted)">Produk Aktif</span>
          </label>
        </div>
      </div>

      <div style="display:flex;gap:10px;margin-top:8px">
        <button type="submit" class="btn btn-primary" id="pm-submit-btn">Simpan Produk</button>
        <button type="button" class="btn btn-ghost" onclick="closeProductModal()">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ TOAST ══ -->
<div class="toast" id="toast"></div>

<?php endif; ?>

<script>
'use strict';

const SB_URL  = 'https://oyhtkqfmlwbkjbcfgqxm.supabase.co';
const SB_KEY  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im95aHRrcWZtbHdia2piY2ZncXhtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzQ5MzM0NzcsImV4cCI6MjA5MDUwOTQ3N30.ZtWi9M7biYA47TcELySXXT-8KdhEne5Iag6uSA7bhrQ';

async function sbFetch(path, options = {}) {
  const res = await fetch(SB_URL + '/rest/v1/' + path, {
    ...options,
    headers: {
      'apikey': SB_KEY,
      'Authorization': 'Bearer ' + SB_KEY,
      'Content-Type': 'application/json',
      'Prefer': options.prefer || 'return=representation',
      ...(options.headers || {})
    }
  });
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.message || 'Supabase error');
  }
  return res.status === 204 ? null : res.json();
}

const sbGet    = (table, params = '')  => sbFetch(table + '?' + params);
const sbPost   = (table, data)         => sbFetch(table, { method: 'POST', body: JSON.stringify(data) });
const sbPatch  = (table, filter, data) => sbFetch(table + '?' + filter, { method: 'PATCH', body: JSON.stringify(data), prefer: 'return=representation' });
const sbDelete = (table, filter)       => sbFetch(table + '?' + filter, { method: 'DELETE', prefer: 'return=minimal' });

async function uploadImage(file) {
  const ext  = file.name.split('.').pop();
  const name = `product_${Date.now()}.${ext}`;
  const res  = await fetch(`${SB_URL}/storage/v1/object/product-images/${name}`, {
    method: 'POST',
    headers: { 'apikey': SB_KEY, 'Authorization': 'Bearer ' + SB_KEY, 'Content-Type': file.type },
    body: file
  });
  if (!res.ok) throw new Error('Upload gagal');
  return `${SB_URL}/storage/v1/object/public/product-images/${name}`;
}

function toast(msg, type = 'success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = 'toast ' + type + ' show';
  setTimeout(() => el.classList.remove('show'), 3000);
}

function showPage(page) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('page-' + page).classList.add('active');
  document.querySelector(`[onclick="showPage('${page}')"]`).classList.add('active');
  if (page === 'dashboard') loadDashboard();
  if (page === 'orders')    loadOrders();
  if (page === 'products')  loadProducts();
  if (page === 'pricing')   loadPricing();
}

// ══ DASHBOARD ══
let allOrders = [], allProducts = [];

async function loadDashboard() {
  try {
    const [orders, products] = await Promise.all([
      sbGet('orders', 'order=created_at.desc'),
      sbGet('products', 'select=id,active')
    ]);
    allOrders   = orders   || [];
    allProducts = products || [];

    const paid    = allOrders.filter(o => o.pay_status === 'paid');
    const revenue = paid.reduce((s, o) => s + (o.total || 0), 0);
    const active  = allProducts.filter(p => p.active).length;

    document.getElementById('stat-orders').textContent   = allOrders.length;
    document.getElementById('stat-paid').textContent     = paid.length;
    document.getElementById('stat-revenue').textContent  = 'RM ' + revenue;
    document.getElementById('stat-products').textContent = active;

    renderRecentOrders(allOrders.slice(0, 8));
  } catch(e) {
    toast('Gagal muatkan dashboard', 'error');
  }
}

function renderRecentOrders(orders) {
  const wrap = document.getElementById('recent-orders-wrap');
  if (!orders.length) { wrap.innerHTML = '<div class="empty-state"><p>Tiada pesanan lagi</p></div>'; return; }
  wrap.innerHTML = `<table>
    <thead><tr>
      <th>Ref</th><th>Nama</th><th>Tel</th><th>Item</th><th>Jumlah</th><th>Status</th><th>Tarikh</th><th></th>
    </tr></thead>
    <tbody>${orders.map(o => `<tr class="clickable" onclick="openOrderDetail('${o.id}')">
      <td style="color:var(--g);font-family:'Cormorant Garamond',serif;font-weight:400">${o.order_ref}</td>
      <td style="color:var(--ink);font-weight:400">${o.name}</td>
      <td>${o.phone}</td>
      <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${o.items}</td>
      <td style="color:var(--g);font-family:'Cormorant Garamond',serif;font-size:14px">RM ${o.total}</td>
      <td><span class="badge-${o.pay_status === 'paid' ? 'paid' : o.pay_status === 'failed' ? 'failed' : 'pending'}">${o.pay_status === 'paid' ? 'Berjaya' : o.pay_status === 'failed' ? 'Gagal' : 'Pending'}</span></td>
      <td>${new Date(o.created_at).toLocaleDateString('ms-MY')}</td>
      <td><button class="btn-view" onclick="event.stopPropagation();openOrderDetail('${o.id}')">Lihat</button></td>
    </tr>`).join('')}</tbody>
  </table>`;
}

// ══ ORDERS ══
let ordersData = [];
let currentDetailOrderId = null;

async function loadOrders() {
  const wrap = document.getElementById('orders-table-wrap');
  wrap.innerHTML = '<div class="loading"><div class="spinner"></div>Memuatkan...</div>';
  try {
    ordersData = await sbGet('orders', 'order=created_at.desc') || [];
    renderOrdersTable(ordersData);
  } catch(e) {
    toast('Gagal muatkan pesanan', 'error');
  }
}

function renderOrdersTable(orders) {
  const wrap = document.getElementById('orders-table-wrap');
  if (!orders.length) { wrap.innerHTML = '<div class="empty-state"><p>Tiada pesanan</p></div>'; return; }
  wrap.innerHTML = `<table>
    <thead><tr>
      <th>Ref</th><th>Nama</th><th>Tel</th><th>Alamat</th><th>Item</th><th>Jumlah</th><th>Status</th><th>Tarikh</th><th>Tindakan</th>
    </tr></thead>
    <tbody>${orders.map(o => `<tr class="clickable" onclick="openOrderDetail('${o.id}')">
      <td style="color:var(--g);font-family:'Cormorant Garamond',serif;font-weight:400;white-space:nowrap">${o.order_ref}</td>
      <td style="color:var(--ink);font-weight:400;white-space:nowrap">${o.name}</td>
      <td style="white-space:nowrap">${o.phone}</td>
      <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${o.address}</td>
      <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${o.items}</td>
      <td style="color:var(--g);font-family:'Cormorant Garamond',serif;font-size:14px;white-space:nowrap">RM ${o.total}</td>
      <td onclick="event.stopPropagation()">
        <select onchange="updateOrderStatus('${o.id}', this.value)" style="background:#fff;border:1px solid var(--border);color:var(--ink);padding:4px 8px;font-size:10px;outline:none">
          <option value="pending"  ${o.pay_status==='pending'  ? 'selected':''}>Pending</option>
          <option value="paid"     ${o.pay_status==='paid'     ? 'selected':''}>Berjaya</option>
          <option value="failed"   ${o.pay_status==='failed'   ? 'selected':''}>Gagal</option>
        </select>
      </td>
      <td style="white-space:nowrap">${new Date(o.created_at).toLocaleDateString('ms-MY')}</td>
      <td onclick="event.stopPropagation()">
        <button class="btn-view" onclick="openOrderDetail('${o.id}')">Lihat Detail</button>
      </td>
    </tr>`).join('')}</tbody>
  </table>`;
}

function filterOrders() {
  const q      = document.getElementById('order-search').value.toLowerCase();
  const status = document.getElementById('order-status-filter').value;
  const filtered = ordersData.filter(o =>
    (!q      || o.name.toLowerCase().includes(q) || o.order_ref.toLowerCase().includes(q)) &&
    (!status || o.pay_status === status)
  );
  renderOrdersTable(filtered);
}

async function updateOrderStatus(id, status) {
  try {
    await sbPatch('orders', 'id=eq.' + id, { pay_status: status });
    toast('Status dikemas kini');
    const o = ordersData.find(x => x.id == id);
    if (o) o.pay_status = status;
    // refresh detail modal if open for same order
    if (currentDetailOrderId === id) {
      document.getElementById('od-status-select').value = status;
      renderOrderDetailBadge(status);
    }
  } catch(e) {
    toast('Gagal kemas kini status', 'error');
  }
}

// ── ORDER DETAIL MODAL ──
function openOrderDetail(id) {
  const o = ordersData.find(x => x.id === id) || allOrders.find(x => x.id === id);
  if (!o) return;
  currentDetailOrderId = id;

  document.getElementById('od-ref').textContent   = o.order_ref || '—';
  document.getElementById('od-name').textContent  = o.name || '—';
  document.getElementById('od-phone').textContent = o.phone || '—';
  document.getElementById('od-email').textContent = o.email || '—';
  document.getElementById('od-address').textContent = o.address || '—';
  document.getElementById('od-total').textContent  = 'RM ' + (o.total || 0);
  document.getElementById('od-date').textContent   = new Date(o.created_at).toLocaleString('ms-MY');
  document.getElementById('od-status-select').value = o.pay_status || 'pending';
  renderOrderDetailBadge(o.pay_status);

  // Parse items string into rows
  const itemsStr = o.items || '';
  const itemsEl  = document.getElementById('od-items');
  // Items are stored like "Name (30ml) x2 | Name2 (60ml) x1"
  const parts = itemsStr.split('|').map(s => s.trim()).filter(Boolean);
  if (parts.length) {
    itemsEl.innerHTML = parts.map(item => `<div class="order-item-row"><span>${item}</span></div>`).join('');
  } else {
    itemsEl.innerHTML = `<div class="order-item-row"><span>${itemsStr}</span></div>`;
  }

  // Note
  const noteWrap = document.getElementById('od-note-wrap');
  const noteEl   = document.getElementById('od-note');
  if (o.note && o.note.trim()) {
    noteEl.textContent = o.note;
    noteWrap.style.display = 'block';
  } else {
    noteWrap.style.display = 'none';
  }

  // WhatsApp link
  const phone = (o.phone || '').replace(/\D/g, '');
  document.getElementById('od-wa-link').href = `https://wa.me/${phone}`;

  document.getElementById('order-detail-modal').classList.add('open');
}

function renderOrderDetailBadge(status) {
  const badge = document.getElementById('od-badge');
  const cls   = status === 'paid' ? 'badge-paid' : status === 'failed' ? 'badge-failed' : 'badge-pending';
  const label = status === 'paid' ? 'Berjaya' : status === 'failed' ? 'Gagal' : 'Pending';
  badge.innerHTML = `<span class="${cls}">${label}</span>`;
}

function closeOrderDetail() {
  document.getElementById('order-detail-modal').classList.remove('open');
  currentDetailOrderId = null;
}

async function updateOrderStatusFromDetail() {
  if (!currentDetailOrderId) return;
  const status = document.getElementById('od-status-select').value;
  await updateOrderStatus(currentDetailOrderId, status);
  // Refresh table
  loadOrders();
}

// ══ PRODUCTS ══
let productsData = [];

async function loadProducts() {
  const wrap = document.getElementById('products-grid-wrap');
  wrap.innerHTML = '<div class="loading"><div class="spinner"></div>Memuatkan...</div>';
  try {
    productsData = await sbGet('products', 'order=id.asc') || [];
    renderProductsGrid(productsData);
  } catch(e) {
    toast('Gagal muatkan produk', 'error');
  }
}

function renderProductsGrid(products) {
  const wrap = document.getElementById('products-grid-wrap');
  if (!products.length) {
    wrap.innerHTML = '<div class="empty-state"><p>Tiada produk</p><span>Tambah produk pertama anda</span></div>';
    return;
  }
  wrap.innerHTML = `<div class="product-admin-grid">${products.map(p => `
    <div class="product-admin-card" data-id="${p.id}" data-gender="${p.gender}" data-active="${p.active}">
      <div class="pac-img">
        ${p.image_url
          ? `<img src="${p.image_url}" alt="${p.name}" style="width:100%;height:100%;object-fit:cover">`
          : createMiniBottle(p.cap_color, p.rgb)
        }
      </div>
      <div class="pac-info">
        <div class="pac-name">${p.name}</div>
        <div class="pac-insp">Terinspirasi: ${p.inspired_by}</div>
        <div class="pac-meta">
          <span class="pac-tag">${p.gender === 'm' ? 'Lelaki' : p.gender === 'w' ? 'Wanita' : 'Unisex'}</span>
          <span class="pac-tag">${p.family}</span>
          ${p.badge ? `<span class="pac-tag" style="background:rgba(138,106,58,.15)">${p.badge}</span>` : ''}
          ${!p.active ? `<span class="pac-tag" style="color:var(--red);background:rgba(176,64,64,.07);border-color:rgba(176,64,64,.2)">Tidak Aktif</span>` : ''}
        </div>
        <div style="font-size:9px;color:var(--muted);margin-bottom:8px">Stok: ${p.stock ?? 50}</div>
        <div class="pac-actions">
          <button class="btn btn-ghost btn-sm" onclick="editProduct('${p.id}')">Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deleteProduct('${p.id}', '${p.name.replace(/'/g,"\\'")}')">Padam</button>
        </div>
      </div>
    </div>
  `).join('')}</div>`;
}

function filterProducts() {
  const q      = document.getElementById('prod-search').value.toLowerCase();
  const gender = document.getElementById('prod-gender-filter').value;
  const status = document.getElementById('prod-status-filter').value;
  const filtered = productsData.filter(p =>
    (!q      || p.name.toLowerCase().includes(q) || p.inspired_by.toLowerCase().includes(q)) &&
    (!gender || p.gender === gender) &&
    (!status || String(p.active) === status)
  );
  renderProductsGrid(filtered);
}

function createMiniBottle(cap, rgbStr) {
  const rgb = (rgbStr || '155,85,110').split(',').map(Number);
  const [r,g,b] = rgb;
  return `<svg width="36" height="70" viewBox="0 0 36 70" fill="none">
    <rect x="12" y="0" width="12" height="8" rx="2" fill="${cap || '#3A1828'}"/>
    <rect x="11" y="8" width="14" height="5" rx="1" fill="${cap || '#3A1828'}" opacity=".6"/>
    <rect x="5" y="13" width="26" height="52" rx="2.5" fill="rgba(${r},${g},${b},.75)"/>
  </svg>`;
}

// ── PRODUCT MODAL ──
let editingProductId = null;

function openProductModal(product = null) {
  editingProductId = product ? product.id : null;
  document.getElementById('pm-eyebrow').textContent    = product ? 'Edit Produk' : 'Produk Baru';
  document.getElementById('pm-title').textContent      = product ? 'Kemaskini Produk' : 'Tambah Produk';
  document.getElementById('pm-submit-btn').textContent = product ? 'Kemaskini' : 'Simpan Produk';

  document.getElementById('pm-id').value         = product?.id ?? '';
  document.getElementById('pm-id-field').value   = product?.id ?? '';
  document.getElementById('pm-name').value        = product?.name ?? '';
  document.getElementById('pm-insp').value        = product?.inspired_by ?? '';
  document.getElementById('pm-gender').value      = product?.gender ?? '';
  document.getElementById('pm-family').value      = product?.family ?? '';
  document.getElementById('pm-notes').value       = product?.notes ?? '';
  document.getElementById('pm-mood').value        = product?.mood ?? '';
  document.getElementById('pm-vibe').value        = product?.vibe ?? '';
  document.getElementById('pm-badge').value       = product?.badge ?? '';
  document.getElementById('pm-cap').value         = product?.cap_color ?? '';
  document.getElementById('pm-rgb').value         = product?.rgb ?? '';
  document.getElementById('pm-stock').value       = product?.stock ?? 50;
  document.getElementById('pm-active').checked   = product?.active ?? true;
  document.getElementById('pm-image-url').value  = product?.image_url ?? '';
  document.getElementById('pm-id-field').disabled = !!product;

  const preview = document.getElementById('img-preview'), placeholder = document.getElementById('upload-placeholder');
  if (product?.image_url) { preview.src = product.image_url; preview.style.display = 'block'; placeholder.style.display = 'none'; }
  else { preview.style.display = 'none'; placeholder.style.display = 'block'; }

  document.getElementById('product-modal').classList.add('open');
}

function closeProductModal() {
  document.getElementById('product-modal').classList.remove('open');
  document.getElementById('product-form').reset();
  editingProductId = null;
}

function editProduct(id) { const p = productsData.find(x => x.id === id); if (p) openProductModal(p); }

async function deleteProduct(id, name) {
  if (!confirm(`Padam "${name}"? Tindakan ini tidak boleh dibatalkan.`)) return;
  try { await sbDelete('products', 'id=eq.' + id); toast('Produk dipadam'); loadProducts(); }
  catch(e) { toast('Gagal padam produk', 'error'); }
}

function previewImage(event) {
  const file = event.target.files[0]; if (!file) return;
  const reader = new FileReader();
  reader.onload = e => { const p = document.getElementById('img-preview'); p.src = e.target.result; p.style.display = 'block'; document.getElementById('upload-placeholder').style.display = 'none'; };
  reader.readAsDataURL(file);
}

async function saveProduct(event) {
  event.preventDefault();
  const btn = document.getElementById('pm-submit-btn');
  btn.textContent = 'Menyimpan...'; btn.disabled = true;
  try {
    let imageUrl = document.getElementById('pm-image-url').value;
    const fileInput = document.getElementById('pm-image-file');
    if (fileInput.files[0]) imageUrl = await uploadImage(fileInput.files[0]);

    const data = {
      id:          document.getElementById('pm-id-field').value.trim(),
      name:        document.getElementById('pm-name').value.trim(),
      inspired_by: document.getElementById('pm-insp').value.trim(),
      gender:      document.getElementById('pm-gender').value,
      family:      document.getElementById('pm-family').value.trim(),
      notes:       document.getElementById('pm-notes').value.trim(),
      mood:        document.getElementById('pm-mood').value.trim(),
      vibe:        document.getElementById('pm-vibe').value.trim(),
      badge:       document.getElementById('pm-badge').value,
      cap_color:   document.getElementById('pm-cap').value.trim() || '#3A1828',
      rgb:         document.getElementById('pm-rgb').value.trim() || '155,85,110',
      stock:       parseInt(document.getElementById('pm-stock').value) || 50,
      active:      document.getElementById('pm-active').checked,
      image_url:   imageUrl
    };

    if (editingProductId) { await sbPatch('products', 'id=eq.' + editingProductId, data); toast('Produk dikemas kini'); }
    else { await sbPost('products', data); toast('Produk ditambah'); }
    closeProductModal(); loadProducts();
  } catch(e) {
    toast('Gagal: ' + e.message, 'error');
  } finally {
    btn.textContent = editingProductId ? 'Kemaskini' : 'Simpan Produk'; btn.disabled = false;
  }
}

// ══ PRICING ══
let pricingData = {};

async function loadPricing() {
  const grid = document.getElementById('pricing-grid');
  grid.innerHTML = '<div class="loading"><div class="spinner"></div>Memuatkan...</div>';
  try {
    const rows = await sbGet('pricing') || [];
    rows.forEach(r => pricingData[r.size] = r);
    renderPricingGrid();
  } catch(e) { toast('Gagal muatkan harga', 'error'); }
}

function renderPricingGrid() {
  const sizes = ['10ml', '30ml', '60ml'];
  document.getElementById('pricing-grid').innerHTML = sizes.map(size => {
    const p = pricingData[size] || { normal_price: 0, promo_price: 0 };
    return `<div class="pricing-card">
      <div class="pricing-size">${size}</div>
      <div class="pricing-inputs">
        <div class="pricing-input-wrap"><label>Harga Asal (RM)</label><input type="number" id="price-normal-${size}" value="${p.normal_price}" min="0"></div>
        <div class="pricing-input-wrap"><label>Harga Promo (RM)</label><input type="number" id="price-promo-${size}" value="${p.promo_price}" min="0"></div>
        <div style="font-size:9px;color:var(--grn);text-align:center;margin-top:4px">Jimat RM <span id="price-save-${size}">${p.normal_price - p.promo_price}</span></div>
      </div>
    </div>`;
  }).join('');

  sizes.forEach(size => {
    ['normal', 'promo'].forEach(type => {
      document.getElementById(`price-${type}-${size}`).addEventListener('input', () => {
        const n = parseInt(document.getElementById(`price-normal-${size}`).value) || 0;
        const p = parseInt(document.getElementById(`price-promo-${size}`).value)  || 0;
        document.getElementById(`price-save-${size}`).textContent = Math.max(0, n - p);
      });
    });
  });
}

async function savePricing() {
  const sizes = ['10ml', '30ml', '60ml'];
  try {
    for (const size of sizes) {
      const normal = parseInt(document.getElementById(`price-normal-${size}`).value) || 0;
      const promo  = parseInt(document.getElementById(`price-promo-${size}`).value)  || 0;
      await sbPatch('pricing', 'size=eq.' + size, { normal_price: normal, promo_price: promo });
      pricingData[size] = { size, normal_price: normal, promo_price: promo };
    }
    toast('Harga disimpan');
  } catch(e) { toast('Gagal simpan harga', 'error'); }
}

// ── CLOSE MODALS ON ESC ──
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeOrderDetail(); closeProductModal(); }
});

<?php if ($loggedIn): ?>
loadDashboard();
<?php endif; ?>
</script>
</body>
</html>
