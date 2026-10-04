<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';

$error = '';
$pesan = '';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

if (isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $cek = mysqli_prepare($koneksi, "SELECT id FROM admin WHERE username=? LIMIT 1");
        mysqli_stmt_bind_param($cek, "s", $username);
        mysqli_stmt_execute($cek);
        $hasil = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil) > 0) {
            $error = 'Username sudah digunakan.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, "INSERT INTO admin(username,password) VALUES(?,?)");
            mysqli_stmt_bind_param($stmt, "ss", $username, $hash);

            if (mysqli_stmt_execute($stmt)) {
                $pesan = 'Registrasi berhasil. Silakan login.';
            } else {
                $error = 'Registrasi gagal: ' . mysqli_error($koneksi);
            }
        }
    }
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM admin WHERE username=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($hasil);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_login'] = true;
        $_SESSION['admin_username'] = $user['username'];
        header("Location: admin.php");
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}

$login = isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true;

$total_tiket = 0;
$total_pendapatan = 0;
$sisa_kuota = 0;
$total_transaksi = 0;

if ($login) {
    $q = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pendaftar");
    if ($q) {
        $d = mysqli_fetch_assoc($q);
        $total_transaksi = (int)$d['total'];
    }

    $q = mysqli_query($koneksi, "SELECT COALESCE(SUM(jumlah_tiket),0) AS total,COALESCE(SUM(total_harga),0) AS pendapatan FROM pendaftar");
    if ($q) {
        $d = mysqli_fetch_assoc($q);
        $total_tiket = (int)$d['total'];
        $total_pendapatan = (int)$d['pendapatan'];
    }

    $kuota = 1000;
    $sisa_kuota = max(0, $kuota - $total_tiket);
}

function rupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

$transaksi = [];
if ($login) {
    $q = mysqli_query($koneksi, "SELECT * FROM pendaftar ORDER BY id DESC");
    if ($q) {
        while ($row = mysqli_fetch_assoc($q)) {
            $transaksi[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Admin - Senja Lo-Fi Festival 2026</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        :root {
            --primary: #6c55d9;
            --primary2: #8b78e8;
            --orange: #ff9a76;
            --purple: #b8a4ff;
            --blue: #9ccbff;
            --cream: #fff8f0;
            --dark: #30264d;
            --muted: #77718a;
            --white: #fff;
            --border: #eeeaf5;
            --bg: #f7f5fb
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--dark)
        }

        button,
        input,
        select {
            font-family: inherit
        }

        a {
            text-decoration: none
        }

        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
            background: linear-gradient(135deg, #fff8f0, #f0ecff, #edf7ff)
        }

        .auth-box {
            width: 420px;
            background: #fff;
            border: 1px solid rgba(108, 85, 217, .12);
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 20px 60px rgba(48, 38, 77, .12)
        }

        .logo {
            text-align: center;
            margin-bottom: 25px
        }

        .logo-icon {
            width: 58px;
            height: 58px;
            margin: auto;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--orange), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 25px;
            font-weight: 800
        }

        .logo h1 {
            font-family: 'Plus Jakarta Sans';
            font-size: 22px;
            margin-top: 12px
        }

        .logo p {
            font-size: 13px;
            color: var(--muted);
            margin-top: 5px
        }

        .auth-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #f6f4fa;
            padding: 4px;
            border-radius: 12px;
            margin-bottom: 22px
        }

        .auth-tabs button {
            border: 0;
            background: transparent;
            padding: 11px;
            border-radius: 9px;
            cursor: pointer;
            color: var(--muted);
            font-weight: 600
        }

        .auth-tabs button.active {
            background: #fff;
            color: var(--primary);
            box-shadow: 0 3px 10px rgba(0, 0, 0, .05)
        }

        .form-group {
            margin-bottom: 17px
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px
        }

        .form-group input,
        .form-control {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid var(--border);
            border-radius: 11px;
            outline: none;
            background: #fff;
            font-size: 14px;
            transition: .2s
        }

        .form-group input:focus,
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 85, 217, .1)
        }

        .btn-primary {
            width: 100%;
            border: 0;
            padding: 13px;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--primary), var(--primary2));
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            transition: .2s
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(108, 85, 217, .25)
        }

        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 16px
        }

        .alert-error {
            background: #fff0f0;
            color: #c0392b
        }

        .alert-success {
            background: #edfff5;
            color: #16834a
        }

        .back-web {
            text-align: center;
            margin-top: 18px
        }

        .back-web a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--primary);
            font-size: 13px;
            font-weight: 600
        }

        .back-web a:hover {
            text-decoration: underline
        }

        #form-register {
            display: none
        }

        .app {
            display: none;
            min-height: 100vh
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 250px;
            background: #fff;
            border-right: 1px solid var(--border);
            padding: 24px 16px;
            z-index: 20
        }

        .side-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 5px 10px 28px
        }

        .side-logo {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--orange), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800
        }

        .side-brand strong {
            font-family: 'Plus Jakarta Sans';
            font-size: 15px
        }

        .side-brand span {
            display: block;
            color: #9993a7;
            font-size: 10px;
            margin-top: 2px
        }

        .menu-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #aaa4b5;
            font-weight: 700;
            padding: 0 12px;
            margin: 8px 0
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 12px;
            border-radius: 11px;
            color: #77718a;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 3px;
            cursor: pointer
        }

        .menu a:hover {
            background: #f5f2fc;
            color: var(--primary)
        }

        .menu a.active {
            background: #eeeaff;
            color: var(--primary)
        }

        .menu-icon {
            width: 18px;
            text-align: center;
            font-size: 16px
        }

        .logout {
            position: absolute;
            bottom: 20px;
            left: 16px;
            right: 16px
        }

        .logout a {
            color: #d65d5d !important;
            background: #fff4f4 !important
        }

        .main {
            margin-left: 250px;
            min-height: 100vh
        }

        .topbar {
            height: 75px;
            background: #fff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 10
        }

        .page-title h2 {
            font-family: 'Plus Jakarta Sans';
            font-size: 19px
        }

        .page-title p {
            font-size: 11px;
            color: #9690a2;
            margin-top: 3px
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--purple), var(--blue));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #fff;
            font-size: 13px
        }

        .admin-user small {
            display: block;
            color: #9993a7;
            font-size: 10px
        }

        .admin-user strong {
            font-size: 12px
        }

        .content {
            padding: 28px 30px
        }

        .page {
            display: none
        }

        .page.active {
            display: block
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 22px
        }

        .stat {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 17px;
            padding: 20px
        }

        .stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .stat-icon {
            width: 39px;
            height: 39px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1edff;
            color: var(--primary);
            font-weight: 800
        }

        .stat:nth-child(2) .stat-icon {
            background: #fff1eb;
            color: #e86f46
        }

        .stat:nth-child(3) .stat-icon {
            background: #edf6ff;
            color: #528dcc
        }

        .stat:nth-child(4) .stat-icon {
            background: #edfff5;
            color: #299a64
        }

        .stat h3 {
            font-size: 25px;
            margin-top: 15px;
            font-family: 'Plus Jakarta Sans'
        }

        .stat p {
            font-size: 11px;
            color: #9993a7;
            margin-top: 3px
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 18px
        }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 17px;
            padding: 22px;
            margin-bottom: 18px
        }

        .card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px
        }

        .card-head h3 {
            font-family: 'Plus Jakarta Sans';
            font-size: 15px
        }

        .card-head span {
            font-size: 11px;
            color: #9993a7
        }

        .info-list {
            display: grid;
            gap: 12px
        }

        .info-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f2eff6;
            padding-bottom: 11px;
            font-size: 12px
        }

        .info-row:last-child {
            border: 0
        }

        .info-row span {
            color: #9993a7
        }

        .badge {
            display: inline-flex;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            background: #edfff5;
            color: #24925e
        }

        .lineup-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px
        }

        .artist {
            padding: 20px;
            border-radius: 15px;
            background: linear-gradient(145deg, #fff8f0, #f5f0ff);
            border: 1px solid var(--border)
        }

        .artist-avatar {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--orange), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 18px;
            margin-bottom: 15px
        }

        .artist h4 {
            font-size: 14px
        }

        .artist p {
            font-size: 11px;
            color: #8f899c;
            margin-top: 5px
        }

        .schedule-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px
        }

        .schedule-tabs button {
            border: 1px solid var(--border);
            background: #fff;
            padding: 9px 17px;
            border-radius: 9px;
            color: #77718a;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer
        }

        .schedule-tabs button.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary)
        }

        .schedule-list {
            display: grid;
            gap: 10px
        }

        .schedule-item {
            display: flex;
            gap: 15px;
            align-items: center;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 12px
        }

        .schedule-time {
            font-weight: 800;
            font-size: 13px;
            width: 55px;
            color: var(--primary)
        }

        .schedule-item h4 {
            font-size: 12px
        }

        .schedule-item p {
            font-size: 10px;
            color: #9993a7;
            margin-top: 3px
        }

        .table-wrap {
            overflow-x: auto
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px
        }

        th {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #9993a7;
            text-align: left;
            background: #faf9fc;
            padding: 12px
        }

        td {
            font-size: 12px;
            padding: 13px 12px;
            border-bottom: 1px solid #f0edf4
        }

        td strong {
            font-size: 12px
        }

        .ticket-type {
            display: inline-block;
            padding: 5px 8px;
            border-radius: 7px;
            font-size: 10px;
            font-weight: 700
        }

        .ticket-regular {
            background: #fff1eb;
            color: #d96843
        }

        .ticket-vip {
            background: #eeeaff;
            color: #6751c7
        }

        .btn-add {
            border: 0;
            background: var(--primary);
            color: #fff;
            padding: 9px 13px;
            border-radius: 9px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer
        }

        .settings {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px
        }

        .setting-full {
            grid-column: 1/-1
        }

        .mobile-menu {
            display: none;
            border: 0;
            background: #f4f1fa;
            border-radius: 9px;
            padding: 9px;
            cursor: pointer
        }

        @media(max-width:1000px) {
            .sidebar {
                transform: translateX(-100%);
                transition: .25s
            }

            .sidebar.open {
                transform: translateX(0)
            }

            .main {
                margin-left: 0
            }

            .mobile-menu {
                display: block
            }

            .stats {
                grid-template-columns: repeat(2, 1fr)
            }

            .grid-2 {
                grid-template-columns: 1fr
            }

            .lineup-grid {
                grid-template-columns: 1fr 1fr
            }
        }

        @media(max-width:600px) {
            .auth-box {
                width: 100%;
                padding: 25px
            }

            .topbar {
                padding: 0 16px
            }

            .content {
                padding: 20px 16px
            }

            .stats {
                grid-template-columns: 1fr
            }

            .lineup-grid {
                grid-template-columns: 1fr
            }

            .settings {
                grid-template-columns: 1fr
            }

            .setting-full {
                grid-column: auto
            }

            .admin-user div:last-child {
                display: none
            }
        }
    </style>
</head>

<body>

    <?php if (!$login): ?>
        <div class="auth-page">
            <div class="auth-box">
                <div class="logo">
                    <div class="logo-icon">S</div>
                    <h1>Senja Lo-Fi</h1>
                    <p>Admin Festival Musik 2026</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($pesan): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($pesan) ?></div>
                <?php endif; ?>

                <div class="auth-tabs">
                    <button id="tab-login" class="active" onclick="switchAuthTab('login')">Login</button>
                    <button id="tab-register" onclick="switchAuthTab('register')">Register</button>
                </div>

                <form id="form-login" method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="Masukkan username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Masukkan password" required>
                    </div>
                    <button type="submit" name="login" class="btn-primary">Masuk ke Dashboard</button>
                </form>

                <form id="form-register" method="POST">
                    <div class="form-group">
                        <label>Username Baru</label>
                        <input type="text" name="username" placeholder="Buat username" required>
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password" placeholder="Minimal 6 karakter" required>
                    </div>
                    <button type="submit" name="register" class="btn-primary">Daftarkan Admin</button>
                </form>

                <div class="back-web">
                    <a href="index.php">← Kembali ke Halaman Web</a>
                </div>
            </div>
        </div>

    <?php else: ?>

        <div class="app" style="display:block">
            <aside class="sidebar" id="sidebar">
                <div class="side-brand">
                    <div class="side-logo">S</div>
                    <div><strong>Senja Lo-Fi</strong><span>Festival Musik 2026</span></div>
                </div>

                <div class="menu-title">Menu Utama</div>
                <nav class="menu">
                    <a class="active" onclick="showPage('dashboard',this)"><span class="menu-icon">⌂</span>Dashboard</a>
                    <a onclick="showPage('lineup',this)"><span class="menu-icon">♪</span>Lineup Musisi</a>
                    <a onclick="showPage('jadwal',this)"><span class="menu-icon">◷</span>Jadwal Festival</a>
                    <a onclick="showPage('tiket',this)"><span class="menu-icon">▣</span>Tiket & Transaksi</a>
                </nav>

                <div class="menu-title">Pengaturan</div>
                <nav class="menu">
                    <a onclick="showPage('pengaturan',this)"><span class="menu-icon">⚙</span>Pengaturan</a>
                    <a href="index.php"><span class="menu-icon">↗</span>Lihat Website</a>
                </nav>

                <div class="logout">
                    <a href="admin.php?logout=1"><span class="menu-icon">↪</span>Logout</a>
                </div>
            </aside>

            <main class="main">
                <header class="topbar">
                    <div style="display:flex;align-items:center;gap:12px">
                        <button class="mobile-menu" onclick="toggleSidebar()">☰</button>
                        <div class="page-title">
                            <h2 id="pageTitle">Dashboard</h2>
                            <p>Kelola Senja Lo-Fi Festival 2026</p>
                        </div>
                    </div>
                    <div class="admin-user">
                        <div>
                            <small>Admin</small>
                            <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong>
                        </div>
                        <div class="avatar"><?= strtoupper(substr($_SESSION['admin_username'], 0, 1)) ?></div>
                    </div>
                </header>

                <div class="content">

                    <section class="page active" id="dashboard">
                        <div class="stats">
                            <div class="stat">
                                <div class="stat-top"><span>Total Tiket Terjual</span>
                                    <div class="stat-icon">T</div>
                                </div>
                                <h3><?= $total_tiket ?></h3>
                                <p>tiket berhasil dipesan</p>
                            </div>
                            <div class="stat">
                                <div class="stat-top"><span>Total Transaksi</span>
                                    <div class="stat-icon">↗</div>
                                </div>
                                <h3><?= $total_transaksi ?></h3>
                                <p>data pembelian</p>
                            </div>
                            <div class="stat">
                                <div class="stat-top"><span>Total Pendapatan</span>
                                    <div class="stat-icon">Rp</div>
                                </div>
                                <h3 style="font-size:19px"><?= rupiah($total_pendapatan) ?></h3>
                                <p>total pembayaran</p>
                            </div>
                            <div class="stat">
                                <div class="stat-top"><span>Sisa Kuota</span>
                                    <div class="stat-icon">✓</div>
                                </div>
                                <h3><?= $sisa_kuota ?></h3>
                                <p>dari kuota 1000 tiket</p>
                            </div>
                        </div>

                        <div class="grid-2">
                            <div class="card">
                                <div class="card-head">
                                    <h3>Informasi Festival</h3>
                                    <span>2026</span>
                                </div>
                                <div class="info-list">
                                    <div class="info-row"><span>Tanggal</span><strong>28 Oktober 2026</strong></div>
                                    <div class="info-row"><span>Lokasi</span><strong>Taman Senja Kota</strong></div>
                                    <div class="info-row"><span>Kota</span><strong>Jakarta</strong></div>
                                    <div class="info-row"><span>Status Sistem</span><span class="badge">Terhubung Secure</span></div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-head">
                                    <h3>Penjualan Tiket</h3>
                                </div>
                                <div style="text-align:center;padding:12px 0">
                                    <div style="font-size:36px;font-weight:800;color:var(--primary)"><?= $total_tiket ?></div>
                                    <p style="font-size:11px;color:#9993a7;margin-top:5px">dari 1000 tiket tersedia</p>
                                    <div style="height:8px;background:#eeeaf5;border-radius:20px;margin-top:16px;overflow:hidden">
                                        <div style="height:100%;width:<?= min(100, ($total_tiket / 1000) * 100) ?>%;background:linear-gradient(90deg,var(--orange),var(--purple));border-radius:20px"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-head">
                                <h3>Transaksi Terbaru</h3>
                                <span><?= count($transaksi) ?> transaksi</span>
                            </div>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>WhatsApp</th>
                                            <th>Tiket</th>
                                            <th>Jumlah</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($transaksi)): ?>
                                            <tr>
                                                <td colspan="5" style="text-align:center;color:#999">Belum ada transaksi.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach (array_slice($transaksi, 0, 5) as $row): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($row['nama_pembeli']) ?></strong></td>
                                                    <td><?= htmlspecialchars($row['no_wa']) ?></td>
                                                    <td><span class="ticket-type <?= $row['jenis_tiket'] == 'vip' ? 'ticket-vip' : 'ticket-regular' ?>"><?= strtoupper(htmlspecialchars($row['jenis_tiket'])) ?></span></td>
                                                    <td><?= $row['jumlah_tiket'] ?></td>
                                                    <td><?= rupiah($row['total_harga']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="page" id="lineup">
                        <div class="card">
                            <div class="card-head">
                                <div>
                                    <h3>Lineup Musisi</h3><span>Daftar pengisi acara Senja Lo-Fi</span>
                                </div>
                                <button class="btn-add" onclick="tambahMusisi()">+ Tambah Musisi</button>
                            </div>
                            <div class="lineup-grid">
                                <div class="artist">
                                    <div class="artist-avatar">NS</div>
                                    <h4>Nara Senja</h4>
                                    <p>Acoustic · Indie Folk</p>
                                </div>
                                <div class="artist">
                                    <div class="artist-avatar">RT</div>
                                    <h4>Ruang Teduh</h4>
                                    <p>Lo-Fi · Acoustic Pop</p>
                                </div>
                                <div class="artist">
                                    <div class="artist-avatar">MM</div>
                                    <h4>Malam Minggu</h4>
                                    <p>Chill · Alternative</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="page" id="jadwal">
                        <div class="card">
                            <div class="card-head">
                                <div>
                                    <h3>Jadwal Festival</h3><span>28 Oktober 2026</span>
                                </div>
                            </div>
                            <div class="schedule-tabs">
                                <button class="active" onclick="showSchedule('siang',this)">Siang</button>
                                <button onclick="showSchedule('sore',this)">Sore</button>
                                <button onclick="showSchedule('malam',this)">Malam</button>
                            </div>
                            <div id="scheduleList" class="schedule-list"></div>
                        </div>
                    </section>

                    <section class="page" id="tiket">
                        <div class="card">
                            <div class="card-head">
                                <div>
                                    <h3>Data Tiket & Transaksi</h3><span>Semua pembelian tiket</span>
                                </div>
                            </div>
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pembeli</th>
                                            <th>No. WhatsApp</th>
                                            <th>Jenis</th>
                                            <th>Jumlah</th>
                                            <th>Total</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($transaksi)): ?>
                                            <tr>
                                                <td colspan="7" style="text-align:center;color:#999">Belum ada data transaksi.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1;
                                            foreach ($transaksi as $row): ?>
                                                <tr>
                                                    <td><?= $no++ ?></td>
                                                    <td><strong><?= htmlspecialchars($row['nama_pembeli']) ?></strong></td>
                                                    <td><?= htmlspecialchars($row['no_wa']) ?></td>
                                                    <td><span class="ticket-type <?= $row['jenis_tiket'] == 'vip' ? 'ticket-vip' : 'ticket-regular' ?>"><?= strtoupper(htmlspecialchars($row['jenis_tiket'])) ?></span></td>
                                                    <td><?= $row['jumlah_tiket'] ?></td>
                                                    <td><?= rupiah($row['total_harga']) ?></td>
                                                    <td><?= isset($row['created_at']) ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="page" id="pengaturan">
                        <div class="card">
                            <div class="card-head">
                                <div>
                                    <h3>Pengaturan Festival</h3><span>Informasi dasar festival</span>
                                </div>
                            </div>
                            <div class="settings">
                                <div class="form-group">
                                    <label>Nama Festival</label>
                                    <input class="form-control" value="Senja Lo-Fi Festival Musik 2026">
                                </div>
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input class="form-control" value="28 Oktober 2026">
                                </div>
                                <div class="form-group">
                                    <label>Lokasi</label>
                                    <input class="form-control" value="Taman Senja Kota, Jakarta">
                                </div>
                                <div class="form-group">
                                    <label>Kuota Tiket</label>
                                    <input class="form-control" value="1000">
                                </div>
                                <div class="form-group setting-full">
                                    <label>Deskripsi</label>
                                    <textarea class="form-control" rows="4">Festival musik lo-fi acoustic dengan suasana santai dan hangat di bawah langit senja.</textarea>
                                </div>
                            </div>
                            <button class="btn-add" onclick="alert('Pengaturan festival siap disimpan setelah tabel pengaturan dibuat.')">Simpan Pengaturan</button>
                        </div>
                    </section>

                </div>
            </main>
        </div>

    <?php endif; ?>

    <script>
        function switchAuthTab(tab) {
            const login = document.getElementById('form-login'),
                register = document.getElementById('form-register'),
                tl = document.getElementById('tab-login'),
                tr = document.getElementById('tab-register');
            if (tab === 'login') {
                login.style.display = 'block';
                register.style.display = 'none';
                tl.classList.add('active');
                tr.classList.remove('active');
            } else {
                login.style.display = 'none';
                register.style.display = 'block';
                tl.classList.remove('active');
                tr.classList.add('active');
            }
        }

        function showPage(page, el) {
            document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
            document.getElementById(page).classList.add('active');
            document.querySelectorAll('.menu a').forEach(a => a.classList.remove('active'));
            if (el) el.classList.add('active');
            const titles = {
                dashboard: 'Dashboard',
                lineup: 'Lineup Musisi',
                jadwal: 'Jadwal Festival',
                tiket: 'Tiket & Transaksi',
                pengaturan: 'Pengaturan'
            };
            document.getElementById('pageTitle').textContent = titles[page] || 'Dashboard';
            document.getElementById('sidebar').classList.remove('open');
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }

        const scheduleData = {
            siang: [{
                    time: '13.00',
                    title: 'Gate Open',
                    desc: 'Registrasi dan masuk area festival'
                },
                {
                    time: '14.00',
                    title: 'Nara Senja',
                    desc: 'Acoustic Indie Folk Session'
                },
                {
                    time: '15.30',
                    title: 'Chill Afternoon',
                    desc: 'Santai bersama musik lo-fi'
                }
            ],
            sore: [{
                    time: '16.30',
                    title: 'Ruang Teduh',
                    desc: 'Lo-Fi Acoustic Pop Session'
                },
                {
                    time: '17.30',
                    title: 'Sunset Session',
                    desc: 'Menikmati musik bersama langit senja'
                },
                {
                    time: '18.15',
                    title: 'Golden Hour',
                    desc: 'Special acoustic performance'
                }
            ],
            malam: [{
                    time: '19.30',
                    title: 'Malam Minggu',
                    desc: 'Chill Alternative Performance'
                },
                {
                    time: '21.00',
                    title: 'Lo-Fi Night',
                    desc: 'Musik santai untuk menutup malam'
                },
                {
                    time: '22.00',
                    title: 'Closing',
                    desc: 'Penutupan Senja Lo-Fi Festival 2026'
                }
            ]
        };

        function showSchedule(session, btn) {
            document.querySelectorAll('.schedule-tabs button').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            const list = document.getElementById('scheduleList');
            list.innerHTML = '';
            scheduleData[session].forEach(item => {
                list.innerHTML += `<div class="schedule-item"><div class="schedule-time">${item.time}</div><div><h4>${item.title}</h4><p>${item.desc}</p></div></div>`;
            });
        }

        function tambahMusisi() {
            alert('Form tambah musisi dapat dihubungkan ke database tabel musisi.');
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('scheduleList')) {
                showSchedule('siang', document.querySelector('.schedule-tabs button'));
            }
        });
    </script>
</body>

</html>