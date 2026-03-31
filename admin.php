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
  --k:  #0A0908;
  --k2: #111009;
  --k3: #1A1814;
  --k4: #242019;
  --c:  #F4EFE6;
  --c2: #EDE6D8;
  --g:  #BF9B5F;
  --g2: #D4AF74;
  --g3: #E8CC98;
  --m:  #7A736A;
  --m2: #A89E92;
  --red:   #C85A5A;
  --grn:   #5AB878;
  --amb:   #D4924A;
  --border: rgba(191,155,95,.12);
  --t: .25s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{background:var(--k);color:var(--c);font-family:'DM Sans',sans-serif;font-weight:300;min-height:100vh;-webkit-font-smoothing:antialiased}
a{text-decoration:none;color:inherit}
button,input,select,textarea{font-family:inherit}
img{display:block;max-width:100%}

/* ── LOGIN PAGE ── */
.login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:radial-gradient(ellipse 60% 60% at 50% 40%,rgba(191,155,95,.07) 0%,transparent 70%)}
.login-box{width:100%;max-width:380px}
.login-logo{font-family:'Cormorant Garamond',serif;font-size:22px;font-style:italic;color:var(--c);text-align:center;margin-bottom:8px;letter-spacing:.04em}
.login-logo span{color:var(--g)}
.login-sub{font-size:9px;letter-spacing:.3em;text-transform:uppercase;color:var(--m);text-align:center;margin-bottom:40px}
.login-box label{display:block;font-size:7.5px;letter-spacing:.22em;text-transform:uppercase;color:var(--m);margin-bottom:6px}
.login-box input{width:100%;padding:11px 14px;background:var(--k2);border:1px solid var(--border);color:var(--c);font-size:13px;outline:none;transition:border-color var(--t);margin-bottom:14px}
.login-box input:focus{border-color:var(--g)}
.login-error{font-size:9px;color:var(--red);margin-bottom:10px;letter-spacing:.04em}
.btn-login{width:100%;padding:12px;background:var(--g);color:var(--k);font-size:9.5px;letter-spacing:.22em;text-transform:uppercase;border:none;cursor:pointer;transition:background var(--t)}
.btn-login:hover{background:var(--g2)}

/* ── LAYOUT ── */
.admin-wrap{display:grid;grid-template-columns:220px 1fr;min-height:100vh}

/* ── SIDEBAR ── */
.sidebar{background:var(--k2);border-right:1px solid var(--border);padding:28px 0;position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column}
.sidebar-logo{font-family:'Cormorant Garamond',serif;font-size:17px;font-style:italic;color:var(--c);padding:0 22px 28px;border-bottom:1px solid var(--border);letter-spacing:.04em}
.sidebar-logo span{color:var(--g)}
.sidebar-logo small{display:block;font-family:'DM Sans',sans-serif;font-size:7px;letter-spacing:.28em;text-transform:uppercase;color:var(--m);margin-top:3px;font-style:normal}
.nav-group{padding:22px 0 0}
.nav-label{font-size:6.5px;letter-spacing:.32em;text-transform:uppercase;color:var(--m);padding:0 22px 8px}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 22px;font-size:11px;color:var(--m2);cursor:pointer;transition:var(--t);border-left:2px solid transparent;letter-spacing:.02em}
.nav-item:hover{color:var(--c);background:rgba(191,155,95,.04)}
.nav-item.active{color:var(--g);border-left-color:var(--g);background:rgba(191,155,95,.06)}
.nav-item svg{width:14px;height:14px;flex-shrink:0;opacity:.7}
.nav-item.active svg{opacity:1}
.sidebar-footer{margin-top:auto;padding:18px 22px;border-top:1px solid var(--border)}
.btn-logout{width:100%;padding:8px;background:transparent;border:1px solid var(--border);color:var(--m);font-size:8px;letter-spacing:.18em;text-transform:uppercase;cursor:pointer;transition:var(--t)}
.btn-logout:hover{border-color:var(--red);color:var(--red)}

/* ── MAIN CONTENT ── */
.main{padding:36px 40px;overflow-y:auto}
.page{display:none}
.page.active{display:block}
.page-header{margin-bottom:32px}
.page-eyebrow{font-size:7px;letter-spacing:.38em;text-transform:uppercase;color:var(--g);margin-bottom:6px}
.page-title{font-family:'Cormorant Garamond',serif;font-size:32px;font-weight:300;color:var(--c)}
.page-title em{font-style:italic;color:var(--g)}

/* ── STAT CARDS ── */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--border);margin-bottom:32px}
.stat-card{background:var(--k2);padding:22px 20px}
.stat-label{font-size:7px;letter-spacing:.25em;text-transform:uppercase;color:var(--m);margin-bottom:8px}
.stat-value{font-family:'Cormorant Garamond',serif;font-size:36px;font-weight:300;color:var(--g);line-height:1}
.stat-sub{font-size:9px;color:var(--m2);margin-top:4px}

/* ── TABLES ── */
.table-wrap{background:var(--k2);border:1px solid var(--border);overflow:hidden;margin-bottom:24px}
.table-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
.table-title{font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:400;color:var(--c)}
table{width:100%;border-collapse:collapse}
th{font-size:7px;letter-spacing:.22em;text-transform:uppercase;color:var(--m);padding:10px 16px;text-align:left;border-bottom:1px solid var(--border);white-space:nowrap}
td{padding:12px 16px;font-size:11px;color:var(--m2);border-bottom:1px solid rgba(191,155,95,.05);vertical-align:middle}
tr:last-child td{border:none}
tr:hover td{background:rgba(191,155,95,.03);color:var(--c)}
.badge-paid{background:rgba(90,184,120,.12);color:var(--grn);padding:3px 9px;font-size:7px;letter-spacing:.12em;text-transform:uppercase}
.badge-pending{background:rgba(212,146,74,.12);color:var(--amb);padding:3px 9px;font-size:7px;letter-spacing:.12em;text-transform:uppercase}
.badge-failed{background:rgba(200,90,90,.12);color:var(--red);padding:3px 9px;font-size:7px;letter-spacing:.12em;text-transform:uppercase}

/* ── SEARCH / FILTER BAR ── */
.filter-bar{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.filter-bar input,.filter-bar select{padding:7px 12px;background:var(--k3);border:1px solid var(--border);color:var(--c);font-size:11px;outline:none;transition:border-color var(--t)}
.filter-bar input:focus,.filter-bar select:focus{border-color:var(--g)}
.filter-bar input::placeholder{color:var(--m)}

/* ── BUTTONS ── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;font-size:8.5px;letter-spacing:.18em;text-transform:uppercase;border:none;cursor:pointer;transition:var(--t);font-family:'DM Sans',sans-serif;font-weight:400}
.btn-primary{background:var(--g);color:var(--k)}
.btn-primary:hover{background:var(--g2)}
.btn-ghost{background:transparent;border:1px solid var(--border);color:var(--m2)}
.btn-ghost:hover{border-color:var(--g);color:var(--g)}
.btn-danger{background:transparent;border:1px solid rgba(200,90,90,.3);color:var(--red)}
.btn-danger:hover{background:rgba(200,90,90,.1)}
.btn-sm{padding:5px 12px;font-size:7.5px}

/* ── PRODUCT GRID (admin) ── */
.product-admin-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--border)}
.product-admin-card{background:var(--k2);padding:18px;display:flex;gap:14px;align-items:flex-start}
.product-admin-card:hover{background:var(--k3)}
.pac-img{width:50px;height:70px;flex-shrink:0;object-fit:cover;background:var(--k3);display:flex;align-items:center;justify-content:center;overflow:hidden}
.pac-img svg{opacity:.4}
.pac-info{flex:1;min-width:0}
.pac-name{font-family:'Cormorant Garamond',serif;font-size:14px;color:var(--c);margin-bottom:2px;line-height:1.2}
.pac-insp{font-size:8px;color:var(--m);letter-spacing:.06em;margin-bottom:6px}
.pac-meta{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}
.pac-tag{font-size:7px;letter-spacing:.12em;text-transform:uppercase;padding:2px 7px;background:rgba(191,155,95,.08);color:var(--g2)}
.pac-actions{display:flex;gap:5px}

/* ── MODAL ── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:900;display:flex;align-items:flex-start;justify-content:center;padding:48px 20px 20px;opacity:0;pointer-events:none;transition:opacity var(--t);backdrop-filter:blur(8px);overflow-y:auto}
.modal-overlay.open{opacity:1;pointer-events:all}
.modal-box{background:var(--k2);width:100%;max-width:560px;padding:36px;position:relative;border:1px solid var(--border);transform:translateY(16px);transition:transform .3s}
.modal-overlay.open .modal-box{transform:translateY(0)}
.modal-close{position:absolute;top:12px;right:14px;background:none;border:none;color:var(--m);font-size:18px;cursor:pointer;line-height:1;padding:4px;transition:color var(--t)}
.modal-close:hover{color:var(--c)}
.modal-eyebrow{font-size:7px;letter-spacing:.38em;text-transform:uppercase;color:var(--g);margin-bottom:4px}
.modal-title{font-family:'Cormorant Garamond',serif;font-size:24px;font-weight:400;color:var(--c);margin-bottom:20px}

/* ── FORM ── */
.form-group{margin-bottom:14px}
.form-group label{display:block;font-size:7.5px;letter-spacing:.22em;text-transform:uppercase;color:var(--m);margin-bottom:5px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:9px 12px;background:var(--k3);border:1px solid var(--border);color:var(--c);font-size:12px;font-weight:300;outline:none;transition:border-color var(--t);-webkit-appearance:none;border-radius:0}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--g)}
.form-group textarea{resize:vertical;min-height:68px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}

/* ── IMAGE UPLOAD ── */
.img-upload-area{border:1px dashed rgba(191,155,95,.3);padding:24px;text-align:center;cursor:pointer;transition:var(--t);position:relative}
.img-upload-area:hover{border-color:var(--g);background:rgba(191,155,95,.03)}
.img-upload-area input{position:absolute;inset:0;opacity:0;cursor:pointer}
.img-upload-icon{font-size:28px;margin-bottom:8px;opacity:.5}
.img-upload-text{font-size:9px;color:var(--m);letter-spacing:.06em}
.img-preview{width:100%;height:140px;object-fit:contain;margin-bottom:8px;background:var(--k3)}

/* ── PRICING PAGE ── */
.pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--border);margin-bottom:28px}
.pricing-card{background:var(--k2);padding:28px 24px;text-align:center}
.pricing-size{font-size:8px;letter-spacing:.32em;text-transform:uppercase;color:var(--g);margin-bottom:12px}
.pricing-inputs{display:flex;flex-direction:column;gap:10px}
.pricing-input-wrap label{font-size:7px;letter-spacing:.18em;text-transform:uppercase;color:var(--m);display:block;margin-bottom:4px;text-align:left}
.pricing-input-wrap input{width:100%;padding:9px 12px;background:var(--k3);border:1px solid var(--border);color:var(--c);font-size:18px;font-family:'Cormorant Garamond',serif;font-weight:300;outline:none;transition:border-color var(--t);text-align:center}
.pricing-input-wrap input:focus{border-color:var(--g)}
.save-bar{background:rgba(191,155,95,.07);border:1px solid rgba(191,155,95,.2);padding:14px 20px;display:flex;justify-content:space-between;align-items:center}
.save-bar-text{font-size:10px;color:var(--m2)}

/* ── TOAST ── */
.toast{position:fixed;bottom:24px;right:24px;background:var(--k3);border:1px solid var(--border);padding:12px 18px;font-size:10px;color:var(--c);z-index:999;transform:translateY(80px);opacity:0;transition:var(--t);letter-spacing:.04em}
.toast.show{transform:translateY(0);opacity:1}
.toast.success{border-color:rgba(90,184,120,.4);color:var(--grn)}
.toast.error{border-color:rgba(200,90,90,.4);color:var(--red)}

/* ── EMPTY STATE ── */
.empty-state{text-align:center;padding:60px 20px}
.empty-state p{font-family:'Cormorant Garamond',serif;font-size:20px;font-style:italic;color:var(--m)}
.empty-state span{font-size:9px;color:var(--m2);display:block;margin-top:4px}

/* ── LOADING ── */
.loading{text-align:center;padding:40px;color:var(--m);font-size:10px;letter-spacing:.1em}
.spinner{width:24px;height:24px;border:1.5px solid var(--border);border-top-color:var(--g);border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 12px}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── RESPONSIVE ── */
@media(max-width:900px){
  .admin-wrap{grid-template-columns:1fr}
  .sidebar{position:fixed;left:-220px;z-index:800;height:100%;transition:left var(--t)}
  .sidebar.open{left:0}
  .main{padding:20px}
  .stats-grid{grid-template-columns:1fr 1fr}
  .product-admin-grid{grid-template-columns:1fr}
  .pricing-grid{grid-template-columns:1fr}
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
      <div class="login-error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <label for="pw">Kata Laluan</label>
      <input type="password" id="pw" name="password" placeholder="••••••••" autofocus>
      <button type="submit" class="btn-login">Masuk →</button>
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

      <div style="margin-bottom:16px;display:flex;gap:10px;flex-wrap:wrap">
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
        <button class="btn btn-primary" onclick="savePricing()">Simpan Harga →</button>
      </div>
    </div>

  </main>
</div>

<!-- ══ PRODUCT MODAL ══ -->
<div class="modal-overlay" id="product-modal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeProductModal()">✕</button>
    <div class="modal-eyebrow" id="pm-eyebrow">Produk Baru</div>
    <div class="modal-title" id="pm-title">Tambah Produk</div>

    <form id="product-form" onsubmit="saveProduct(event)">
      <input type="hidden" id="pm-id">

      <!-- Image Upload -->
      <div class="form-group">
        <label>Gambar Produk</label>
        <div class="img-upload-area" id="upload-area">
          <img id="img-preview" class="img-preview" style="display:none">
          <div id="upload-placeholder">
            <div class="img-upload-icon">📷</div>
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
            <span style="font-size:10px;color:var(--m2)">Produk Aktif</span>
          </label>
        </div>
      </div>

      <div style="display:flex;gap:10px;margin-top:8px">
        <button type="submit" class="btn btn-primary" id="pm-submit-btn">Simpan Produk →</button>
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

// ── SUPABASE HELPERS ──
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

const sbGet    = (table, params = '')       => sbFetch(table + '?' + params);
const sbPost   = (table, data)              => sbFetch(table, { method: 'POST', body: JSON.stringify(data) });
const sbPatch  = (table, filter, data)      => sbFetch(table + '?' + filter, { method: 'PATCH', body: JSON.stringify(data), prefer: 'return=representation' });
const sbDelete = (table, filter)            => sbFetch(table + '?' + filter, { method: 'DELETE', prefer: 'return=minimal' });

// ── STORAGE UPLOAD ──
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

// ── TOAST ──
function toast(msg, type = 'success') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.className = 'toast ' + type + ' show';
  setTimeout(() => el.classList.remove('show'), 3000);
}

// ── NAVIGATION ──
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
    console.error(e);
    toast('Gagal muatkan dashboard', 'error');
  }
}

function renderRecentOrders(orders) {
  const wrap = document.getElementById('recent-orders-wrap');
  if (!orders.length) { wrap.innerHTML = '<div class="empty-state"><p>Tiada pesanan lagi</p></div>'; return; }
  wrap.innerHTML = `<table>
    <thead><tr>
      <th>Ref</th><th>Nama</th><th>Tel</th><th>Item</th><th>Jumlah</th><th>Status</th><th>Tarikh</th>
    </tr></thead>
    <tbody>${orders.map(o => `<tr>
      <td style="color:var(--g);font-family:'Cormorant Garamond',serif">${o.order_ref}</td>
      <td style="color:var(--c)">${o.name}</td>
      <td>${o.phone}</td>
      <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${o.items}">${o.items}</td>
      <td style="color:var(--g);font-family:'Cormorant Garamond',serif">RM ${o.total}</td>
      <td><span class="badge-${o.pay_status === 'paid' ? 'paid' : o.pay_status === 'failed' ? 'failed' : 'pending'}">${o.pay_status === 'paid' ? 'Berjaya' : o.pay_status === 'failed' ? 'Gagal' : 'Pending'}</span></td>
      <td>${new Date(o.created_at).toLocaleDateString('ms-MY')}</td>
    </tr>`).join('')}</tbody>
  </table>`;
}

// ══ ORDERS ══
let ordersData = [];

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
    <tbody>${orders.map(o => `<tr>
      <td style="color:var(--g);font-family:'Cormorant Garamond',serif;white-space:nowrap">${o.order_ref}</td>
      <td style="color:var(--c);white-space:nowrap">${o.name}</td>
      <td style="white-space:nowrap">${o.phone}</td>
      <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${o.address}">${o.address}</td>
      <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${o.items}">${o.items}</td>
      <td style="color:var(--g);font-family:'Cormorant Garamond',serif;white-space:nowrap">RM ${o.total}</td>
      <td>
        <select onchange="updateOrderStatus('${o.id}', this.value)" style="background:var(--k3);border:1px solid var(--border);color:var(--c);padding:4px 8px;font-size:10px;outline:none">
          <option value="pending"  ${o.pay_status==='pending'  ? 'selected':''}>Pending</option>
          <option value="paid"     ${o.pay_status==='paid'     ? 'selected':''}>Berjaya</option>
          <option value="failed"   ${o.pay_status==='failed'   ? 'selected':''}>Gagal</option>
        </select>
      </td>
      <td style="white-space:nowrap">${new Date(o.created_at).toLocaleDateString('ms-MY')}</td>
      <td>
        <a href="https://wa.me/${o.phone.replace(/\D/g,'')}" target="_blank" class="btn btn-ghost btn-sm">WA</a>
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
  } catch(e) {
    toast('Gagal kemas kini status', 'error');
  }
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
          ${p.badge ? `<span class="pac-tag" style="background:rgba(191,155,95,.18)">${p.badge}</span>` : ''}
          ${!p.active ? `<span class="pac-tag" style="color:var(--red);background:rgba(200,90,90,.08)">Tidak Aktif</span>` : ''}
        </div>
        <div style="font-size:9px;color:var(--m);margin-bottom:8px">Stok: ${p.stock ?? 50}</div>
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
  document.getElementById('pm-eyebrow').textContent = product ? 'Edit Produk' : 'Produk Baru';
  document.getElementById('pm-title').textContent   = product ? 'Kemaskini Produk' : 'Tambah Produk';
  document.getElementById('pm-submit-btn').textContent = product ? 'Kemaskini →' : 'Simpan Produk →';

  // Fill fields
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
  document.getElementById('pm-active').checked    = product?.active ?? true;
  document.getElementById('pm-image-url').value   = product?.image_url ?? '';
  document.getElementById('pm-id-field').disabled = !!product; // can't change ID on edit

  // Image preview
  const preview = document.getElementById('img-preview');
  const placeholder = document.getElementById('upload-placeholder');
  if (product?.image_url) {
    preview.src = product.image_url;
    preview.style.display = 'block';
    placeholder.style.display = 'none';
  } else {
    preview.style.display = 'none';
    placeholder.style.display = 'block';
  }

  document.getElementById('product-modal').classList.add('open');
}

function closeProductModal() {
  document.getElementById('product-modal').classList.remove('open');
  document.getElementById('product-form').reset();
  editingProductId = null;
}

function editProduct(id) {
  const p = productsData.find(x => x.id === id);
  if (p) openProductModal(p);
}

async function deleteProduct(id, name) {
  if (!confirm(`Padam "${name}"? Tindakan ini tidak boleh dibatalkan.`)) return;
  try {
    await sbDelete('products', 'id=eq.' + id);
    toast('Produk dipadam');
    loadProducts();
  } catch(e) {
    toast('Gagal padam produk', 'error');
  }
}

function previewImage(event) {
  const file = event.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const preview = document.getElementById('img-preview');
    preview.src = e.target.result;
    preview.style.display = 'block';
    document.getElementById('upload-placeholder').style.display = 'none';
  };
  reader.readAsDataURL(file);
}

async function saveProduct(event) {
  event.preventDefault();
  const btn = document.getElementById('pm-submit-btn');
  btn.textContent = 'Menyimpan...';
  btn.disabled = true;

  try {
    // Upload image if new file selected
    let imageUrl = document.getElementById('pm-image-url').value;
    const fileInput = document.getElementById('pm-image-file');
    if (fileInput.files[0]) {
      imageUrl = await uploadImage(fileInput.files[0]);
    }

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

    if (editingProductId) {
      await sbPatch('products', 'id=eq.' + editingProductId, data);
      toast('Produk dikemas kini ✓');
    } else {
      await sbPost('products', data);
      toast('Produk ditambah ✓');
    }

    closeProductModal();
    loadProducts();
  } catch(e) {
    toast('Gagal: ' + e.message, 'error');
  } finally {
    btn.textContent = editingProductId ? 'Kemaskini →' : 'Simpan Produk →';
    btn.disabled = false;
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
  } catch(e) {
    toast('Gagal muatkan harga', 'error');
  }
}

function renderPricingGrid() {
  const sizes = ['10ml', '30ml', '60ml'];
  document.getElementById('pricing-grid').innerHTML = sizes.map(size => {
    const p = pricingData[size] || { normal_price: 0, promo_price: 0 };
    return `<div class="pricing-card">
      <div class="pricing-size">${size}</div>
      <div class="pricing-inputs">
        <div class="pricing-input-wrap">
          <label>Harga Asal (RM)</label>
          <input type="number" id="price-normal-${size}" value="${p.normal_price}" min="0">
        </div>
        <div class="pricing-input-wrap">
          <label>Harga Promo (RM)</label>
          <input type="number" id="price-promo-${size}" value="${p.promo_price}" min="0">
        </div>
        <div style="font-size:9px;color:var(--grn);text-align:center;margin-top:4px">
          Jimat RM <span id="price-save-${size}">${p.normal_price - p.promo_price}</span>
        </div>
      </div>
    </div>`;
  }).join('');

  // Live savings preview
  sizes.forEach(size => {
    ['normal', 'promo'].forEach(type => {
      document.getElementById(`price-${type}-${size}`).addEventListener('input', () => {
        const n = parseInt(document.getElementById(`price-normal-${size}`).value) || 0;
        const p = parseInt(document.getElementById(`price-promo-${size}`).value) || 0;
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
    toast('Harga disimpan ✓');
  } catch(e) {
    toast('Gagal simpan harga', 'error');
  }
}

// ── INIT ──
<?php if ($loggedIn): ?>
loadDashboard();
<?php endif; ?>
</script>
</body>
</html>
