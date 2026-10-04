<?php
require_once __DIR__ . '/config/koneksi.php';

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['beli_tiket'])) {
    $nama = trim($_POST['nama_pembeli'] ?? '');
    $wa = trim($_POST['no_wa'] ?? '');
    $jenis = $_POST['jenis_tiket'] ?? '';
    $jumlah = (int)($_POST['jumlah_tiket'] ?? 0);

    $harga = [
        'regular' => 75000,
        'vip' => 150000
    ];

    if ($nama === '' || $wa === '' || !isset($harga[$jenis]) || $jumlah < 1 || $jumlah > 10) {
        $pesan = '<div class="alert error">Data pembelian belum valid.</div>';
    } else {
        $total = $harga[$jenis] * $jumlah;

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO pendaftar(nama_pembeli,no_wa,jenis_tiket,jumlah_tiket,total_harga) VALUES(?,?,?,?,?)"
        );

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "sssii",
                $nama,
                $wa,
                $jenis,
                $jumlah,
                $total
            );

            if (mysqli_stmt_execute($stmt)) {
                $pesan = '<div class="alert success">Pembelian berhasil dicatat. Silakan simpan informasi pesanan kamu.</div>';
            } else {
                $pesan = '<div class="alert error">Pembelian gagal. Silakan coba lagi.</div>';
            }

            mysqli_stmt_close($stmt);
        } else {
            $pesan = '<div class="alert error">Terjadi kesalahan pada database.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Senja Lo-Fi Festival Musik 2026</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --orange: #ff9a76;
            --pink: #ffb199;
            --purple: #b8a4ff;
            --blue: #9ccbff;
            --cream: #fff8f0;
            --dark: #30264d;
            --muted: #77718a;
            --white: #fff;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: Inter, sans-serif;
            color: var(--dark);
            background: var(--cream);
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button,
        input,
        select {
            font-family: inherit;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            padding: 15px 6%;
            transition: .3s;
        }

        .navbar.scrolled {
            background: rgba(255, 248, 240, .92);
            backdrop-filter: blur(14px);
            box-shadow: 0 5px 25px rgba(48, 38, 77, .07);
        }

        .nav-inner {
            max-width: 1200px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Plus Jakarta Sans';
            font-weight: 800;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 13px;
            background: linear-gradient(135deg, var(--orange), var(--purple));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-links {
            display: flex;
            gap: 28px;
            font-size: 13px;
            font-weight: 600;
            color: #615a70;
        }

        .nav-links a:hover {
            color: var(--purple);
        }

        .nav-btn {
            padding: 10px 17px;
            border-radius: 10px;
            background: var(--dark);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
        }

        .hero {
            min-height: 100vh;
            padding: 150px 6% 80px;
            display: flex;
            align-items: center;
            overflow: hidden;
            background:
                radial-gradient(circle at 85% 20%, #cfc4ff 0, transparent 28%),
                radial-gradient(circle at 10% 80%, #ffd2c0 0, transparent 30%),
                linear-gradient(135deg, #fff8f0, #f4efff, #edf7ff);
        }

        .hero-inner {
            max-width: 1200px;
            width: 100%;
            margin: auto;
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 50px;
            align-items: center;
        }

        .eyebrow {
            display: inline-block;
            background: rgba(255, 255, 255, .75);
            padding: 8px 13px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 700;
            color: #705f9d;
            margin-bottom: 18px;
        }

        .hero h1 {
            font-family: 'Plus Jakarta Sans';
            font-size: clamp(42px, 6vw, 76px);
            line-height: 1.03;
            letter-spacing: -3px;
            margin-bottom: 20px;
        }

        .hero h1 span {
            background: linear-gradient(90deg, #ff815e, #866ee5, #5798d8);
            -webkit-background-clip: text;
            color: transparent;
        }

        .hero p {
            max-width: 560px;
            color: #716b7e;
            line-height: 1.8;
            font-size: 15px;
        }

        .hero-info {
            display: flex;
            gap: 25px;
            margin: 28px 0;
        }

        .info-item strong {
            display: block;
            font-size: 15px;
        }

        .info-item span {
            font-size: 11px;
            color: #928b9e;
        }

        .hero-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-main {
            padding: 14px 22px;
            background: var(--dark);
            color: #fff;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            border: 0;
            cursor: pointer;
        }

        .btn-main:hover {
            opacity: .9;
        }

        .btn-soft {
            padding: 14px 22px;
            background: #fff;
            color: var(--dark);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
        }

        .hero-art {
            height: 450px;
            border-radius: 35px;
            background: linear-gradient(145deg, #ffad8d, #c1aefc 55%, #91c7f5);
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(78, 61, 115, .18);
        }

        .sun {
            position: absolute;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background: #ffe5a8;
            top: 70px;
            right: 80px;
            box-shadow: 0 0 60px rgba(255, 230, 168, .8);
        }

        .hill {
            position: absolute;
            bottom: -60px;
            width: 120%;
            height: 210px;
            left: -10%;
            background: #75659b;
            border-radius: 50% 50% 0 0;
        }

        .hill.two {
            bottom: -100px;
            background: #4d416e;
            left: -20%;
            height: 190px;
        }

        .moon-text {
            position: absolute;
            top: 40px;
            left: 35px;
            color: #fff;
            font-family: 'Plus Jakarta Sans';
            font-size: 12px;
            letter-spacing: 2px;
        }

        .music-note {
            position: absolute;
            color: rgba(255, 255, 255, .7);
            font-size: 25px;
            animation: float 3s infinite ease-in-out;
        }

        .n1 {
            left: 25%;
            top: 28%;
        }

        .n2 {
            right: 20%;
            top: 42%;
            animation-delay: 1s;
        }

        .n3 {
            left: 50%;
            top: 18%;
            animation-delay: 1.5s;
        }

        @keyframes float {
            50% {
                transform: translateY(-12px);
            }
        }

        section {
            padding: 95px 6%;
        }

        .section-inner {
            max-width: 1200px;
            margin: auto;
        }

        .section-head {
            max-width: 650px;
            margin-bottom: 38px;
        }

        .section-head .small {
            color: #8a78d0;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .section-head h2 {
            font-family: 'Plus Jakarta Sans';
            font-size: 34px;
            margin: 8px 0;
        }

        .section-head p {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
        }

        .lineup {
            background: #fff;
        }

        .lineup-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .artist {
            padding: 27px;
            border-radius: 20px;
            background: linear-gradient(145deg, #fff8f0, #f4efff);
            border: 1px solid #eee8f5;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: .3s;
        }

        .artist:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 35px rgba(78, 61, 115, .12);
            border-color: #cfc4ff;
        }

        .artist:active {
            transform: translateY(-2px);
        }

        .artist::after {
            content: 'Beli Tiket →';
            position: absolute;
            right: 20px;
            bottom: 18px;
            font-size: 10px;
            font-weight: 700;
            color: #7a64d9;
            opacity: 0;
            transform: translateX(8px);
            transition: .3s;
        }

        .artist:hover::after {
            opacity: 1;
            transform: translateX(0);
        }

        .artist-top {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .artist-avatar {
            width: 58px;
            height: 58px;
            border-radius: 17px;
            background: linear-gradient(135deg, var(--orange), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 18px;
        }

        .artist h3 {
            font-size: 16px;
        }

        .artist p {
            font-size: 11px;
            color: #898294;
            margin-top: 4px;
        }

        .artist .num {
            position: absolute;
            right: 20px;
            top: 20px;
            color: #ddd6eb;
            font-size: 25px;
            font-weight: 800;
        }

        .schedule {
            background: #f7f3ff;
        }

        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .tabs button {
            border: 0;
            background: #fff;
            padding: 11px 19px;
            border-radius: 10px;
            color: #77718a;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .tabs button.active {
            background: var(--dark);
            color: #fff;
        }

        .schedule-list {
            display: grid;
            gap: 10px;
            max-width: 800px;
        }

        .schedule-item {
            display: flex;
            align-items: center;
            gap: 22px;
            background: #fff;
            padding: 18px 20px;
            border-radius: 14px;
            border: 1px solid #eee9f5;
        }

        .time {
            width: 55px;
            font-weight: 800;
            color: #8a75db;
            font-size: 13px;
        }

        .schedule-item h4 {
            font-size: 13px;
        }

        .schedule-item p {
            font-size: 11px;
            color: #938c9f;
            margin-top: 4px;
        }

        .tickets {
            background: #fff;
        }

        .ticket-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .ticket {
            padding: 30px;
            border: 1px solid #ebe6f2;
            border-radius: 22px;
            background: linear-gradient(145deg, #fff, #faf8ff);
        }

        .ticket.vip {
            background: linear-gradient(145deg, #f1edff, #edf7ff);
            border-color: #dcd4ff;
        }

        .ticket-label {
            font-size: 11px;
            color: #8a8295;
            font-weight: 700;
        }

        .ticket h3 {
            font-size: 22px;
            margin: 7px 0;
        }

        .price {
            font-family: 'Plus Jakarta Sans';
            font-size: 30px;
            font-weight: 800;
        }

        .price small {
            font-size: 11px;
            color: #999;
        }

        .ticket ul {
            list-style: none;
            margin: 20px 0;
            display: grid;
            gap: 10px;
        }

        .ticket li {
            font-size: 12px;
            color: #6e6879;
        }

        .ticket li::before {
            content: '✓';
            color: #7a64d9;
            font-weight: 800;
            margin-right: 8px;
        }

        .buy-btn {
            width: 100%;
            padding: 13px;
            border: 0;
            border-radius: 11px;
            background: var(--dark);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .buy-btn:hover {
            opacity: .9;
        }

        .calculator {
            margin-top: 22px;
            background: #f8f5fc;
            padding: 20px;
            border-radius: 18px;
        }

        .calc-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
        }

        .calc-grid label {
            font-size: 10px;
            font-weight: 700;
            display: block;
            margin-bottom: 6px;
        }

        .calc-grid select,
        .calc-grid input {
            width: 100%;
            padding: 11px;
            border: 1px solid #e8e2f0;
            border-radius: 9px;
            background: #fff;
        }

        .total-box {
            margin-top: 15px;
            font-size: 13px;
        }

        .total-box strong {
            font-size: 22px;
            color: #705bd1;
        }

        .location {
            background: linear-gradient(135deg, #fff4ed, #f0ebff);
        }

        .location-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: stretch;
        }

        .location-info {
            background: #fff;
            border-radius: 20px;
            padding: 28px;
        }

        .location-info h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .location-info p {
            font-size: 13px;
            color: #77718a;
            line-height: 1.8;
        }

        .map {
            min-height: 250px;
            border-radius: 20px;
            background: linear-gradient(135deg, #b8a4ff, #9ccbff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
        }

        .alert {
            max-width: 1200px;
            margin: 90px auto -60px;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 13px;
        }

        .alert.success {
            background: #eafff2;
            color: #197a4a;
        }

        .alert.error {
            background: #fff0f0;
            color: #c0392b;
        }

        footer {
            background: var(--dark);
            color: #fff;
            padding: 45px 6%;
        }

        .footer-inner {
            max-width: 1200px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        footer p {
            font-size: 11px;
            color: #aaa3bb;
            margin-top: 7px;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(48, 38, 77, .55);
            z-index: 200;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.show {
            display: flex;
        }

        .modal-box {
            background: #fff;
            width: 430px;
            max-width: 100%;
            padding: 28px;
            border-radius: 20px;
            position: relative;
            box-shadow: 0 25px 70px rgba(48, 38, 77, .2);
        }

        .modal-box h2 {
            font-size: 20px;
            margin-bottom: 8px;
        }

        #artistSelected {
            font-size: 12px;
            color: #77718a;
            margin-bottom: 20px;
        }

        .close {
            position: absolute;
            right: 18px;
            top: 15px;
            border: 0;
            background: #f3f0f7;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e0eb;
            border-radius: 10px;
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #a996ed;
        }

        @media(max-width:850px) {
            .nav-links {
                display: none;
            }

            .hero-inner {
                grid-template-columns: 1fr;
            }

            .hero-art {
                height: 330px;
            }

            .lineup-grid,
            .ticket-grid,
            .location-box {
                grid-template-columns: 1fr;
            }

            .calc-grid {
                grid-template-columns: 1fr;
            }

            .footer-inner {
                flex-direction: column;
            }
        }

        @media(max-width:550px) {
            .hero {
                padding-top: 120px;
            }

            .hero h1 {
                font-size: 43px;
                letter-spacing: -2px;
            }

            .hero-art {
                height: 280px;
            }

            .sun {
                width: 130px;
                height: 130px;
                right: 45px;
            }

            section {
                padding: 70px 5%;
            }

            .section-head h2 {
                font-size: 27px;
            }

            .hero-info {
                gap: 18px;
            }

            .schedule-item {
                gap: 12px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar" id="navbar">
        <div class="nav-inner">

            <a href="#" class="brand">
                <span class="brand-icon">S</span>
                <span>Senja Lo-Fi</span>
            </a>

            <div class="nav-links">
                <a href="#beranda">Beranda</a>
                <a href="#lineup">Lineup</a>
                <a href="#jadwal">Jadwal</a>
                <a href="#tiket">Tiket</a>
                <a href="#lokasi">Lokasi</a>
            </div>

            <a href="#tiket" class="nav-btn">Beli Tiket</a>

        </div>
    </nav>

    <?php if ($pesan): ?>
        <?= $pesan ?>
    <?php endif; ?>

    <section class="hero" id="beranda">
        <div class="hero-inner">

            <div>

                <div class="eyebrow">
                    28 OKTOBER 2026 · JAKARTA
                </div>

                <h1>
                    Slow down.<br>
                    <span>Feel the music.</span>
                </h1>

                <p>
                    Senja Lo-Fi Festival adalah ruang untuk menikmati musik santai,
                    acoustic, dan suasana hangat bersama teman-teman di bawah langit senja.
                </p>

                <div class="hero-info">

                    <div class="info-item">
                        <strong>28 Okt 2026</strong>
                        <span>Rabu</span>
                    </div>

                    <div class="info-item">
                        <strong>Taman Senja Kota</strong>
                        <span>Jakarta</span>
                    </div>

                </div>

                <div class="hero-buttons">
                    <a href="#tiket" class="btn-main">
                        Beli Tiket Sekarang
                    </a>

                    <a href="#jadwal" class="btn-soft">
                        Lihat Jadwal
                    </a>
                </div>

                <div style="margin-top:25px;font-size:12px;color:#777">
                    Festival dimulai dalam
                    <strong id="countdown">Menghitung...</strong>
                </div>

            </div>

            <div class="hero-art">

                <div class="moon-text">
                    SENJA LO-FI · 2026
                </div>

                <div class="sun"></div>

                <div class="music-note n1">♪</div>
                <div class="music-note n2">♫</div>
                <div class="music-note n3">♪</div>

                <div class="hill"></div>
                <div class="hill two"></div>

            </div>

        </div>
    </section>

    <section class="lineup" id="lineup">
        <div class="section-inner">

            <div class="section-head">
                <span class="small">Lineup</span>

                <h2>
                    Musik untuk menemani senja.
                </h2>

                <p>
                    Klik musisi yang ingin kamu lihat,
                    lalu langsung pesan tiketnya.
                </p>
            </div>

            <div class="lineup-grid">

                <div class="artist" onclick="openModal('regular','Nara Senja')">

                    <span class="num">01</span>

                    <div class="artist-top">

                        <div class="artist-avatar">
                            NS
                        </div>

                        <div>
                            <h3>Nara Senja</h3>
                            <p>Acoustic · Indie Folk</p>
                        </div>

                    </div>

                </div>

                <div class="artist" onclick="openModal('regular','Ruang Teduh')">

                    <span class="num">02</span>

                    <div class="artist-top">

                        <div class="artist-avatar">
                            RT
                        </div>

                        <div>
                            <h3>Ruang Teduh</h3>
                            <p>Lo-Fi · Acoustic Pop</p>
                        </div>

                    </div>

                </div>

                <div class="artist" onclick="openModal('regular','Malam Minggu')">

                    <span class="num">03</span>

                    <div class="artist-top">

                        <div class="artist-avatar">
                            MM
                        </div>

                        <div>
                            <h3>Malam Minggu</h3>
                            <p>Chill · Alternative</p>
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </section>

    <section class="schedule" id="jadwal">
        <div class="section-inner">

            <div class="section-head">

                <span class="small">
                    Schedule
                </span>

                <h2>
                    Ikuti alunan dari siang sampai malam.
                </h2>

                <p>
                    Pilih sesi untuk melihat rangkaian acara.
                </p>

            </div>

            <div class="tabs">

                <button class="active"
                    onclick="showSchedule('siang',this)">
                    Siang
                </button>

                <button
                    onclick="showSchedule('sore',this)">
                    Sore
                </button>

                <button
                    onclick="showSchedule('malam',this)">
                    Malam
                </button>

            </div>

            <div id="scheduleList" class="schedule-list"></div>

        </div>
    </section>

    <section class="tickets" id="tiket">

        <div class="section-inner">

            <div class="section-head">

                <span class="small">
                    Tickets
                </span>

                <h2>
                    Pilih tiket dan nikmati senjanya.
                </h2>

                <p>
                    Pesan tiket lebih awal dan ajak teman-temanmu.
                </p>

            </div>

            <div class="ticket-grid">

                <div class="ticket">

                    <span class="ticket-label">
                        REGULAR PASS
                    </span>

                    <h3>
                        Regular
                    </h3>

                    <div class="price">
                        Rp75.000
                        <small>/ orang</small>
                    </div>

                    <ul>
                        <li>Akses area festival</li>
                        <li>Menikmati seluruh lineup</li>
                        <li>Akses area umum</li>
                    </ul>

                    <button
                        class="buy-btn"
                        onclick="openModal('regular')">
                        Beli Regular
                    </button>

                </div>

                <div class="ticket vip">

                    <span class="ticket-label">
                        VIP SUNSET PASS
                    </span>

                    <h3>
                        VIP
                    </h3>

                    <div class="price">
                        Rp150.000
                        <small>/ orang</small>
                    </div>

                    <ul>
                        <li>Semua fasilitas Regular</li>
                        <li>Area VIP khusus</li>
                        <li>View panggung lebih nyaman</li>
                    </ul>

                    <button
                        class="buy-btn"
                        onclick="openModal('vip')">
                        Beli VIP
                    </button>

                </div>

            </div>

            <div class="calculator">

                <strong>
                    Kalkulator Total Tiket
                </strong>

                <div
                    class="calc-grid"
                    style="margin-top:15px">

                    <div>

                        <label>
                            Jenis Tiket
                        </label>

                        <select
                            id="calcJenis"
                            onchange="calculate()">

                            <option value="75000">
                                Regular - Rp75.000
                            </option>

                            <option value="150000">
                                VIP - Rp150.000
                            </option>

                        </select>

                    </div>

                    <div>

                        <label>
                            Jumlah
                        </label>

                        <input
                            type="number"
                            id="calcJumlah"
                            value="1"
                            min="1"
                            max="10"
                            oninput="calculate()">

                    </div>

                    <div class="total-box">

                        Total

                        <br>

                        <strong id="calcTotal">
                            Rp75.000
                        </strong>

                    </div>

                </div>

            </div>

        </div>

    </section>

    <section class="location" id="lokasi">

        <div class="section-inner">

            <div class="section-head">

                <span class="small">
                    Location
                </span>

                <h2>
                    Sampai jumpa di Taman Senja.
                </h2>

            </div>

            <div class="location-box">

                <div class="location-info">

                    <h3>
                        Taman Senja Kota
                    </h3>

                    <p>
                        Jakarta, Indonesia
                    </p>

                    <p style="margin-top:15px">
                        Datang lebih awal agar kamu bisa menikmati
                        suasana taman sebelum pertunjukan dimulai.
                    </p>

                </div>

                <div class="map">
                    TAMAN SENJA KOTA · JAKARTA
                </div>

            </div>

        </div>

    </section>

    <footer>

        <div class="footer-inner">

            <div>
                <strong>
                    Senja Lo-Fi Festival 2026
                </strong>

                <p>
                    Slow down. Feel the music.
                </p>
            </div>

            <div>
                <p>
                    28 Oktober 2026 · Taman Senja Kota, Jakarta
                </p>
            </div>

        </div>

    </footer>

    <div class="modal" id="modal">

        <div class="modal-box">

            <button
                class="close"
                onclick="closeModal()">
                ×
            </button>

            <h2>
                Pesan Tiket
            </h2>

            <p id="artistSelected"></p>

            <form method="POST">

                <div class="form-group">

                    <label>
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        name="nama_pembeli"
                        required
                        placeholder="Masukkan nama">

                </div>

                <div class="form-group">

                    <label>
                        No. WhatsApp
                    </label>

                    <input
                        type="text"
                        name="no_wa"
                        required
                        placeholder="08xxxxxxxxxx">

                </div>

                <div class="form-group">

                    <label>
                        Jenis Tiket
                    </label>

                    <select
                        name="jenis_tiket"
                        id="modalJenis">

                        <option value="regular">
                            Regular - Rp75.000
                        </option>

                        <option value="vip">
                            VIP - Rp150.000
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label>
                        Jumlah Tiket
                    </label>

                    <input
                        type="number"
                        name="jumlah_tiket"
                        value="1"
                        min="1"
                        max="10"
                        required>

                </div>

                <button
                    type="submit"
                    name="beli_tiket"
                    class="btn-main"
                    style="width:100%">

                    Konfirmasi Pembelian

                </button>

            </form>

        </div>

    </div>

    <script>
        /* NAVBAR */
        window.addEventListener('scroll', function() {
            document
                .getElementById('navbar')
                .classList
                .toggle('scrolled', scrollY > 30);
        });


        /* SHORTCUT ADMIN
           CTRL + SHIFT + A
        */
        document.addEventListener('keydown', function(e) {

            if (
                e.ctrlKey &&
                e.shiftKey &&
                e.key.toLowerCase() === 'a'
            ) {

                e.preventDefault();

                window.location.href = 'admin.php';

            }

        });


        /* JADWAL */
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

            document
                .querySelectorAll('.tabs button')
                .forEach(function(b) {
                    b.classList.remove('active');
                });

            if (btn) {
                btn.classList.add('active');
            }

            document.getElementById('scheduleList').innerHTML =
                scheduleData[session].map(function(x) {

                    return `
                <div class="schedule-item">
                    <div class="time">
                        ${x.time}
                    </div>

                    <div>
                        <h4>
                            ${x.title}
                        </h4>

                        <p>
                            ${x.desc}
                        </p>
                    </div>
                </div>
            `;

                }).join('');
        }


        /* COUNTDOWN */
        const eventDate =
            new Date(
                '2026-10-28T13:00:00+07:00'
            ).getTime();

        setInterval(function() {

            let diff =
                eventDate - Date.now();

            if (diff <= 0) {

                document
                    .getElementById('countdown')
                    .textContent =
                    'Festival sedang berlangsung';

                return;
            }

            let d =
                Math.floor(
                    diff / 86400000
                );

            let h =
                Math.floor(
                    diff % 86400000 / 3600000
                );

            let m =
                Math.floor(
                    diff % 3600000 / 60000
                );

            let s =
                Math.floor(
                    diff % 60000 / 1000
                );

            document
                .getElementById('countdown')
                .textContent =
                `${d} hari ${h} jam ${m} menit ${s} detik`;

        }, 1000);


        /* KALKULATOR */
        function calculate() {

            let harga =
                parseInt(
                    document
                    .getElementById('calcJenis')
                    .value
                );

            let jumlah =
                Math.max(
                    1,
                    Math.min(
                        10,
                        parseInt(
                            document
                            .getElementById('calcJumlah')
                            .value
                        ) || 1
                    )
                );

            document
                .getElementById('calcJumlah')
                .value = jumlah;

            document
                .getElementById('calcTotal')
                .textContent =
                'Rp' +
                (harga * jumlah)
                .toLocaleString('id-ID');
        }


        /* MODAL */
        function openModal(jenis, musisi = '') {

            document
                .getElementById('modal')
                .classList
                .add('show');

            document
                .getElementById('modalJenis')
                .value = jenis;

            if (musisi) {

                document
                    .getElementById('artistSelected')
                    .textContent =
                    'Tiket untuk menyaksikan ' + musisi;

            } else {

                document
                    .getElementById('artistSelected')
                    .textContent = '';

            }
        }

        function closeModal() {

            document
                .getElementById('modal')
                .classList
                .remove('show');
        }


        document
            .getElementById('modal')
            .addEventListener(
                'click',
                function(e) {

                    if (e.target.id === 'modal') {
                        closeModal();
                    }

                }
            );


        /* ESC UNTUK MENUTUP MODAL */
        document.addEventListener(
            'keydown',
            function(e) {

                if (e.key === 'Escape') {
                    closeModal();
                }

            }
        );


        /* JALANKAN JADWAL AWAL */
        showSchedule(
            'siang',
            document.querySelector('.tabs button')
        );
    </script>

</body>

</html>