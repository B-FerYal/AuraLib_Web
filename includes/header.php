<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

include_once "../includes/db.php";
include_once 'languages.php';

$is_logged_in      = isset($_SESSION['id_user']) && !empty($_SESSION['id_user']);
$id_user           = $is_logged_in ? (int)$_SESSION['id_user'] : 0;
$user_role         = $_SESSION['role'] ?? 'client';
$base              = "/MEMOIR";

$cart_count = 0;
$user       = [];

if ($is_logged_in && isset($conn)) {
    if ($user_role === 'client') {
        $res_count = $conn->query("SELECT SUM(pi.quantite) as total FROM panier_item pi JOIN panier p ON pi.id_panier = p.id_panier WHERE p.id_user = $id_user");
        if ($res_count) {
            $row_count  = $res_count->fetch_assoc();
            $cart_count = (int)($row_count['total'] ?? 0);
        }
    }
    $res_user = $conn->query("SELECT firstname, lastname, email FROM users WHERE id = $id_user");
    if ($res_user && $res_user->num_rows > 0) {
        $user = $res_user->fetch_assoc();
    }
}

$first_letter     = strtoupper(substr($user['firstname'] ?? $_SESSION['firstname'] ?? 'U', 0, 1));
$display_name     = htmlspecialchars($user['firstname'] ?? $_SESSION['firstname'] ?? 'Utilisateur');
$display_email    = htmlspecialchars($user['email'] ?? '');
$display_fullname = htmlspecialchars(trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')));

$current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function is_active(string ...$paths): bool {
    global $current_uri;
    foreach ($paths as $p) {
        if (str_contains($current_uri, $p)) return true;
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= ($lang == 'ar') ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <?php include '../includes/dark_init.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">

    <style>
        :root {
            --gold:        #C4A46B;
            --gold2:       #D4B47B;
            --nav-bg:      #2C1F0E;
            --nav-h:       66px;
            --page-bg:     #F5F0E8;
            --page-bg2:    #EDE5D4;
            --page-white:  #FFFDF9;
            --page-text:   #2D2419;
            --page-muted:  #9A8C7E;
            --page-border: #DDD5C8;
            --font-serif:  'EB Garamond', Georgia, serif;
            --font-ui:     'Plus Jakarta Sans', sans-serif;
            --font-ar:     'Tajawal', sans-serif;
        }

        html.dark {
            --page-bg:     #0F0D08;
            --page-bg2:    #1A1610;
            --page-white:  #1A1610;
            --page-text:   #EDE5D4;
            --page-muted:  #9A8C7E;
            --page-border: #3A3020;
            --nav-bg:      #0A0806;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: <?= ($lang == 'ar') ? 'var(--font-ar)' : 'var(--font-ui)' ?>;
            font-size: 15px;
            line-height: 1.6;
            background: var(--page-bg);
            color: var(--page-text);
            transition: background .3s, color .3s;
        }

        <?php if ($lang !== 'ar'): ?>
        h1, h2, h3, h4, h5, h6, .font-serif { font-family: var(--font-serif); }
        <?php endif; ?>

        /* ════ NAVBAR ════════════════════════════════ */
        .admin-nav {
            background: var(--nav-bg);
            height: var(--nav-h);
            display: flex; align-items: center;
            position: fixed; top: 0; left: 0; width: 100%;
            z-index: 1000; padding: 0 32px;
            border-bottom: 1px solid rgba(196,164,107,.22);
            transition: background .3s;
        }
        .nav-container {
            width: 100%; display: flex;
            justify-content: space-between; align-items: center;
        }

        .logo-wrapper {
            text-decoration: none; display: flex;
            flex-direction: column; line-height: 1.1;
        }
        .nav-logo-text {
            font-family: var(--font-serif);
            font-size: 26px; font-weight: 600;
            letter-spacing: .3px; line-height: 1;
        }
        .white-text { color: #FFFFFF; }
        .gold-text  { color: var(--gold); }
        .admin-subtitle {
            font-family: var(--font-ui);
            color: rgba(196,164,107,.45);
            font-size: 9px; letter-spacing: 3px;
            text-transform: uppercase; margin-top: 3px;
            font-weight: 500;
        }

        .nav-links { display: flex; align-items: center; gap: 2px; }
        .nav-link {
            font-family: var(--font-ui);
            color: rgba(237,229,212,.55);
            font-size: 13px; font-weight: 400;
            padding: 7px 13px; border-radius: 6px;
            text-decoration: none;
            transition: color .15s, background .15s;
            position: relative;
        }
        .nav-link:hover { color: rgba(237,229,212,.9); background: rgba(196,164,107,.08); }
        .nav-link.active { color: var(--gold) !important; font-weight: 600; }
        .nav-link.active::after {
            content: '';
            position: absolute; bottom: 2px; left: 13px; right: 13px;
            height: 1.5px; background: var(--gold); border-radius: 2px;
        }
        .nav-link.gold-badge {
            color: var(--gold);
            border: 1px solid rgba(196,164,107,.32);
            font-weight: 600;
        }

        .nav-right { display: flex; align-items: center; gap: 10px; }
        .vsep { width: 1px; height: 20px; background: rgba(196,164,107,.2); flex-shrink: 0; }

        /* ════ LANGUAGE DROPDOWN ════════════════════ */
        .lang-dropdown { position: relative; display: inline-block; }
        .lang-trigger {
            font-family: var(--font-ui);
            background: rgba(196,164,107,.1); border: 1px solid rgba(196,164,107,.3);
            color: var(--gold); padding: 7px 12px; border-radius: 7px; cursor: pointer;
            font-size: 12px; font-weight: 600;
            display: flex; align-items: center; gap: 7px;
            user-select: none; transition: background .15s, border-color .15s;
        }
        .lang-trigger:hover { background: rgba(196,164,107,.18); border-color: rgba(196,164,107,.5); }
        .lang-arrow {
            display: inline-block; width: 0; height: 0;
            border-left: 4px solid transparent; border-right: 4px solid transparent;
            border-top: 4px solid var(--gold);
            transition: transform .2s; flex-shrink: 0;
        }
        .lang-dropdown.open .lang-arrow { transform: rotate(180deg); }
        .lang-menu {
            display: none; position: absolute; top: calc(100% + 6px); right: 0;
            background: #3A2A14; border: 1px solid rgba(196,164,107,.25);
            border-radius: 10px; min-width: 145px; z-index: 9999; overflow: hidden;
            box-shadow: 0 12px 30px rgba(0,0,0,.45);
        }
        html[dir="rtl"] .lang-menu { right: auto; left: 0; }
        .lang-dropdown.open .lang-menu { display: block; animation: fadeUp .16s ease; }
        .lang-item {
            font-family: var(--font-ui);
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px; color: rgba(237,229,212,.7);
            text-decoration: none; font-size: 13px;
            transition: background .15s, color .15s; white-space: nowrap;
        }
        .lang-item:hover  { background: rgba(196,164,107,.15); color: #F5F0E8; }
        .lang-item.active { background: rgba(196,164,107,.12); color: var(--gold); font-weight: 600; }
        .lang-flag { font-size: 16px; line-height: 1; }

        /* ════ CART ════════════════════════════════ */
        .cart-btn {
            font-family: var(--font-ui);
            display: flex; align-items: center; gap: 7px;
            background: rgba(196,164,107,.1); border: 1px solid rgba(196,164,107,.3);
            color: var(--gold); font-size: 12px; font-weight: 600;
            padding: 7px 13px; border-radius: 7px;
            text-decoration: none; transition: all .15s;
        }
        .cart-btn:hover { background: rgba(196,164,107,.2); }
        .cart-badge {
            background: var(--gold); color: #2C1F0E;
            font-size: 10px; font-weight: 700;
            min-width: 17px; height: 17px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center; padding: 0 4px;
        }

        /* ════ PROFILE DROPDOWN ════════════════════ */
        .profile-wrap { position: relative; }
        .profile-trigger {
            display: flex; align-items: center; gap: 9px; cursor: pointer;
            padding: 5px 12px 5px 6px; border-radius: 50px;
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(196,164,107,.15);
            transition: border-color .15s;
        }
        .profile-trigger:hover { border-color: rgba(196,164,107,.38); }

        .nav-avatar {
            width: 33px; height: 33px; border-radius: 50%;
            background: rgba(196,164,107,.14); border: 1.5px solid rgba(196,164,107,.4);
            color: var(--gold); font-weight: 700; font-size: 13px;
            font-family: var(--font-ui);
            display: flex; align-items: center; justify-content: center;
        }
        .nav-greeting { display: flex; flex-direction: column; line-height: 1; }
        .nav-hello {
            font-family: var(--font-ui);
            font-size: 9px; color: rgba(237,229,212,.4);
            text-transform: uppercase; letter-spacing: 1px;
        }
        .nav-username {
            font-family: var(--font-ui);
            font-size: 13px; font-weight: 500; color: #F5F0E8;
        }
        .profile-caret {
            color: rgba(196,164,107,.5);
            font-size: 10px;
            transition: transform .2s;
        }
        .profile-wrap.open .profile-caret { transform: rotate(180deg); }

        .profile-dropdown {
            display: none; position: absolute; top: calc(100% + 10px); right: 0;
            background: #3A2A14; border: 1px solid rgba(196,164,107,.2);
            border-radius: 13px; min-width: 220px; z-index: 2000; overflow: hidden;
            box-shadow: 0 12px 32px rgba(0,0,0,.5);
        }
        html[dir="rtl"] .profile-dropdown { right: auto; left: 0; }
        .profile-wrap.open .profile-dropdown { display: block; animation: fadeUp .18s ease; }

        .dd-head {
            padding: 16px; background: rgba(0,0,0,.25);
            display: flex; gap: 12px; align-items: center;
            border-bottom: 1px solid rgba(196,164,107,.1);
        }
        .dd-av {
            width: 40px; height: 40px; border-radius: 50%;
            background: rgba(196,164,107,.14); border: 1.5px solid rgba(196,164,107,.4);
            color: var(--gold); font-weight: 700; font-size: 16px;
            font-family: var(--font-serif);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .dd-info { min-width: 0; }
        .dd-fullname {
            font-family: var(--font-serif);
            color: #F5F0E8; font-weight: 600; font-size: 15px;
            line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .dd-role {
            font-family: var(--font-ui);
            font-size: 10px; font-weight: 600; letter-spacing: 1.5px;
            text-transform: uppercase; margin-top: 3px;
            color: var(--gold);
        }
        .dd-email {
            font-family: var(--font-ui);
            color: rgba(255,255,255,.3); font-size: 11px; margin-top: 2px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .dd-body { padding: 6px; }
        .dd-item {
            font-family: var(--font-ui);
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; color: rgba(237,229,212,.65);
            text-decoration: none; font-size: 13px; border-radius: 7px;
            transition: all .15s;
        }
        .dd-item:hover { background: rgba(196,164,107,.1); color: var(--gold); }
        .dd-item i { width: 16px; font-size: 13px; text-align: center; }
        .dd-sep { height: 1px; background: rgba(196,164,107,.1); margin: 4px 6px; }
        .dd-logout { color: rgba(255,100,100,.6) !important; }
        .dd-logout:hover { color: rgba(255,100,100,.9) !important; background: rgba(255,80,80,.08) !important; }

        /* ════ DARK MODE TOGGLE ════════════════════ */
        .theme-switch {
            position: relative; width: 50px; height: 26px;
            cursor: pointer; flex-shrink: 0;
        }
        .theme-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
        .sw-track {
            position: absolute; inset: 0;
            background: rgba(196,164,107,.08);
            border: 1px solid rgba(196,164,107,.28);
            border-radius: 13px;
            transition: background .3s, border-color .3s;
        }
        .theme-switch input:checked ~ .sw-track {
            background: rgba(196,164,107,.15);
            border-color: rgba(196,164,107,.5);
        }
        .sw-track::before {
            content: '🌙';
            position: absolute; right: 5px; top: 50%;
            transform: translateY(-50%); font-size: 12px;
            transition: opacity .2s;
        }
        .sw-track::after {
            content: '☀️';
            position: absolute; left: 5px; top: 50%;
            transform: translateY(-50%); font-size: 12px;
            opacity: 0; transition: opacity .2s;
        }
        .theme-switch input:checked ~ .sw-track::before { opacity: 0; }
        .theme-switch input:checked ~ .sw-track::after  { opacity: 1; }
        .sw-thumb {
            position: absolute; top: 3px; left: 3px;
            width: 20px; height: 20px;
            background: var(--gold); border-radius: 50%;
            transition: transform .28s cubic-bezier(.4,0,.2,1), background .3s;
            box-shadow: 0 2px 6px rgba(0,0,0,.35); z-index: 1;
        }
        .theme-switch input:checked ~ .sw-thumb {
            transform: translateX(24px);
            background: #EDE5D4;
        }

        /* ════ AUTH BUTTONS ════════════════════════ */
        .btn-login {
            font-family: var(--font-ui);
            color: rgba(237,229,212,.6); font-size: 13px;
            text-decoration: none; padding: 7px 12px;
            transition: color .15s;
        }
        .btn-login:hover { color: rgba(237,229,212,.9); }
        .btn-signup {
            font-family: var(--font-ui);
            background: var(--gold); color: #2C1F0E;
            font-size: 12px; font-weight: 700;
            padding: 8px 18px; border-radius: 7px;
            text-decoration: none; transition: background .15s;
        }
        .btn-signup:hover { background: var(--gold2); }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(7px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ════ ADMIN SEARCH BAR ════════════════════ */
        .adm-search-form {
            display: flex; align-items: center;
            background: rgba(255,255,255,.08);
            border: 1.5px solid rgba(255,255,255,.15);
            border-radius: 10px;
            padding: 0 4px 0 12px;
            transition: border-color .2s, box-shadow .2s;
        }
        .adm-search-form:focus-within {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(196,164,107,.18);
        }
        .adm-search-input {
            background: transparent; border: none; outline: none;
            color: #fff; font-size: 13px; width: 210px;
            padding: 8px 0; font-family: var(--font-ui);
        }
        .adm-search-input::placeholder { color: rgba(255,255,255,.4); }
        .adm-search-btn {
            background: none; border: none; cursor: pointer;
            padding: 6px 7px; color: rgba(255,255,255,.5);
            display: flex; align-items: center; transition: color .15s;
        }
        .adm-search-btn:hover { color: var(--gold); }

        .adm-search-dropdown {
            position: absolute; top: calc(100% + 8px); left: 0;
            width: 440px; max-height: 500px; overflow-y: auto;
            background: #1a1208;
            border: 1.5px solid rgba(196,164,107,.3);
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0,0,0,.6);
            z-index: 9999;
            scrollbar-width: thin;
            scrollbar-color: rgba(196,164,107,.3) transparent;
            animation: fadeUp .18s ease;
        }
        .adm-search-dropdown::-webkit-scrollbar { width: 4px; }
        .adm-search-dropdown::-webkit-scrollbar-thumb { background: rgba(196,164,107,.3); border-radius: 4px; }

        .adm-sd-header {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 16px 8px;
            font-size: 10px; font-weight: 700; letter-spacing: .1em;
            text-transform: uppercase; color: var(--gold);
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .adm-sd-header svg { width: 13px; height: 13px; }
        .adm-sd-item {
            display: flex; align-items: center; gap: 11px;
            padding: 9px 16px; color: rgba(255,255,255,.82);
            font-size: 13px; transition: background .12s;
            text-decoration: none; border: none; width: 100%;
            background: none; text-align: left; cursor: pointer;
        }
        .adm-sd-item:hover { background: rgba(196,164,107,.1); }
        .adm-sd-item:last-of-type { border-bottom: 1px solid rgba(255,255,255,.05); }
        .adm-sd-avatar {
            width: 34px; height: 34px; border-radius: 7px;
            background: rgba(255,255,255,.07);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: var(--gold);
            flex-shrink: 0; overflow: hidden;
        }
        .adm-sd-avatar img { width:100%; height:100%; object-fit:cover; }
        .adm-sd-main { flex: 1; min-width: 0; }
        .adm-sd-title { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .adm-sd-sub   { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .adm-sd-badge {
            font-size: 10px; font-weight: 700;
            padding: 2px 7px; border-radius: 20px; flex-shrink: 0;
        }
        .badge-active    { background: rgba(34,197,94,.18);  color: #4ade80; }
        .badge-suspended { background: rgba(239,68,68,.18);  color: #f87171; }
        .badge-encours   { background: rgba(234,179,8,.18);  color: #facc15; }
        .badge-rendu     { background: rgba(34,197,94,.18);  color: #4ade80; }
        .badge-refused   { background: rgba(239,68,68,.18);  color: #f87171; }
        .badge-achat     { background: rgba(196,164,107,.18); color: var(--gold); }
        .badge-emprunt   { background: rgba(99,102,241,.18); color: #a5b4fc; }
        .badge-both      { background: rgba(255,255,255,.1); color: rgba(255,255,255,.65); }
        .adm-sd-actions  { display: flex; gap: 5px; flex-shrink: 0; }
        .adm-sd-act-btn {
            display: flex; align-items: center; gap: 4px;
            padding: 3px 8px; border-radius: 5px;
            border: 1.5px solid rgba(255,255,255,.12);
            font-size: 11px; font-weight: 600;
            color: rgba(255,255,255,.75);
            background: rgba(255,255,255,.04);
            text-decoration: none; transition: all .15s;
        }
        .adm-sd-act-btn:hover { background: rgba(196,164,107,.18); border-color: var(--gold); color: #fff; }
        .adm-sd-empty {
            padding: 26px 18px; text-align: center;
            color: rgba(255,255,255,.3); font-size: 13px;
        }
        .adm-sd-footer {
            padding: 9px 16px; text-align: center;
            font-size: 11px; color: rgba(255,255,255,.28);
        }
        .adm-sd-spinner {
            display: flex; align-items: center; justify-content: center;
            gap: 8px; padding: 20px; color: rgba(255,255,255,.35); font-size: 13px;
        }
        .spin-ring {
            width: 16px; height: 16px;
            border: 2px solid rgba(196,164,107,.2);
            border-top-color: var(--gold); border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (max-width: 900px) {
            .admin-subtitle, .nav-hello { display: none; }
            .nav-link { font-size: 12px; padding: 6px 8px; }
            .nav-greeting { display: none; }
        }
    </style>
</head>
<body data-user-role="<?= htmlspecialchars($user_role) ?>">

<nav class="admin-nav">
    <div class="nav-container">

        <!-- ── LOGO ── -->
        <a href="<?= $base ?>/client/library.php" class="logo-wrapper">
            <div class="nav-logo-text">
                <span class="white-text">Aura</span><span class="gold-text">Lib</span>
            </div>
            <div class="admin-subtitle">
                <?= ($user_role === 'admin') ? 'Administration' : 'Library' ?>
            </div>
        </a>

        <!-- ── NAV LINKS ── -->
        <div class="nav-links">
            <a href="<?= $base ?>/client/library.php"
               class="nav-link <?= is_active('library') ? 'active' : '' ?>">
                <?= $text['home'] ?>
            </a>

            <?php if ($is_logged_in): ?>
                <?php if ($user_role === 'admin'): ?>
                    <a href="<?= $base ?>/admin/admin_dashboard.php"
                       class="nav-link gold-badge <?= is_active('admin_dashboard') ? 'active' : '' ?>">
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?= $base ?>/emprunts/mes_emprunts.php"
                       class="nav-link <?= is_active('emprunt', 'mes_emprunt') ? 'active' : '' ?>">
                        <?= $text['borrowed'] ?>
                    </a>
                    <a href="<?= $base ?>/commandes/commande_list.php"
                       class="nav-link <?= is_active('commande') ? 'active' : '' ?>">
                        <?= $text['purchased'] ?>
                    </a>
                    <a href="/MEMOIR/cart/panier.php"
                       class="nav-link <?= is_active('panier') ? 'active' : '' ?>">
                        <i class="fa-solid fa-cart-shopping" style="font-size:11px;margin-right:4px"></i>
                        <?= $text['cart'] ?? 'Panier' ?>
                        <?php if ($cart_count > 0): ?>
                            <span style="background:var(--gold);color:#2C1F0E;font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:4px;"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <a href="<?= $base ?>/client/about.php"
               class="nav-link <?= is_active('about') ? 'active' : '' ?>">
                <?= $text['about'] ?>
            </a>
            <a href="<?= $base ?>/client/contact.php"
               class="nav-link <?= is_active('contact') ? 'active' : '' ?>">
                <?= $text['contact'] ?>
            </a>
        </div>

        <!-- ── RIGHT SIDE ── -->
        <div class="nav-right">

            <?php if ($user_role === 'admin'): ?>
            <!-- ── Admin Search Bar ── -->
            <div class="adm-search-wrap" style="position:relative;">
                <div class="adm-search-form">
                    <input class="adm-search-input"
                           id="adm-search-input"
                           type="search"
                           placeholder="Rechercher docs, utilisateurs, emprunts…"
                           autocomplete="off"
                           aria-label="Recherche admin">
                    <button class="adm-search-btn" type="button">
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" width="17" height="17">
                            <circle cx="8.5" cy="8.5" r="5.5"/>
                            <line x1="13" y1="13" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                <div class="adm-search-dropdown" id="adm-search-dropdown" hidden></div>
            </div>
            <div class="vsep"></div>
            <?php endif; ?>

            <!-- Language dropdown -->
            <div class="lang-dropdown" id="langDropdown">
                <div class="lang-trigger" id="langTrigger">
                    <i class="fa-solid fa-globe" style="font-size:12px"></i>
                    <?php
                    $flags = ['fr' => '🇫🇷', 'en' => '🇬🇧', 'ar' => '🇩🇿'];
                    echo ($flags[$lang] ?? '🌐') . '&nbsp;' . strtoupper($lang);
                    ?>
                    <span class="lang-arrow"></span>
                </div>
                <div class="lang-menu" id="langMenu">
                    <?php
                    $keep = $_GET; unset($keep['lang']);
                    $qs   = count($keep) ? '&' . http_build_query($keep) : '';
                    ?>
                    <a href="?lang=fr<?= $qs ?>" class="lang-item <?= $lang==='fr' ? 'active' : '' ?>">
                        <span class="lang-flag">🇫🇷</span> Français
                    </a>
                    <a href="?lang=en<?= $qs ?>" class="lang-item <?= $lang==='en' ? 'active' : '' ?>">
                        <span class="lang-flag">🇬🇧</span> English
                    </a>
                    <a href="?lang=ar<?= $qs ?>" class="lang-item <?= $lang==='ar' ? 'active' : '' ?>">
                        <span class="lang-flag">🇩🇿</span> العربية
                    </a>
                </div>
            </div>

            <div class="vsep"></div>

            <!-- Dark mode toggle — single toggle, key: auralib_theme -->
            <label class="theme-switch" title="Mode sombre">
                <input type="checkbox" id="darkToggle">
                <span class="sw-track"></span>
                <span class="sw-thumb"></span>
            </label>

            <div class="vsep"></div>

            <?php if ($is_logged_in): ?>



                <!-- Profile dropdown -->
                <div class="profile-wrap" id="profileWrap">
                    <div class="profile-trigger" id="profileTrigger">
                        <div class="nav-avatar"><?= $first_letter ?></div>
                        <div class="nav-greeting">
                            <span class="nav-hello">Bonjour</span>
                            <span class="nav-username"><?= $display_name ?></span>
                        </div>
                        <i class="fa-solid fa-chevron-down profile-caret"></i>
                    </div>

                    <div class="profile-dropdown" id="profileDropdown">
                        <div class="dd-head">
                            <div class="dd-av"><?= $first_letter ?></div>
                            <div class="dd-info">
                                <div class="dd-fullname"><?= $display_fullname ?: $display_name ?></div>
                                <div class="dd-role"><?= ucfirst($user_role) ?></div>
                                <?php if ($display_email): ?>
                                    <div class="dd-email"><?= $display_email ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="dd-body">
                            <a href="<?= $base ?>/client/profile.php" class="dd-item">
                                <i class="fa-regular fa-user"></i> Mon Profil
                            </a>

                            <?php if ($user_role === 'client'): ?>
                            <a href="<?= $base ?>/emprunts/mes_emprunts.php" class="dd-item">
                                <i class="fa-regular fa-clock"></i> Mes Emprunts
                            </a>
                            <a href="<?= $base ?>/commandes/commande_list.php" class="dd-item">
                                <i class="fa-solid fa-receipt"></i> Mes Commandes
                            </a>
                            <a href="<?= $base ?>/cart/panier.php" class="dd-item">
                                <i class="fa-solid fa-cart-shopping"></i>
                                Mon Panier
                                <?php if ($cart_count > 0): ?>
                                    <span style="margin-left:auto;background:var(--gold);color:#2C1F0E;font-size:9px;font-weight:700;padding:2px 7px;border-radius:10px;"><?= $cart_count ?></span>
                                <?php endif; ?>
                            </a>
                            <?php endif; ?>

                            <?php if ($user_role === 'admin'): ?>
                            <a href="<?= $base ?>/admin/admin_dashboard.php" class="dd-item">
                                <i class="fa-solid fa-gauge-high"></i> Dashboard Admin
                            </a>
                            <a href="<?= $base ?>/admin/gerer_documents.php" class="dd-item">
                                <i class="fa-solid fa-book"></i> Gérer Documents
                            </a>
                            <?php endif; ?>

                            <div class="dd-sep"></div>

                            <a href="<?= $base ?>/client/notifications.php" class="dd-item">
                                <i class="fa-regular fa-bell"></i> Notifications
                            </a>

                            <div class="dd-sep"></div>

                            <a href="<?= $base ?>/auth/logout.php" class="dd-item dd-logout">
                                <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                            </a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <a href="<?= $base ?>/auth/login.php" class="btn-login"><?= $text['login'] ?? 'Connexion' ?></a>
                <a href="<?= $base ?>/auth/signup.php" class="btn-signup"><?= $text['signup'] ?? "S'inscrire" ?></a>
            <?php endif; ?>

        </div>
    </div>

</nav>

<script>
(function () {
    /* ── Dark mode — unified key: auralib_theme ── */
    const toggle = document.getElementById('darkToggle');
    const html   = document.documentElement;

    if (localStorage.getItem('auralib_theme') === 'dark') {
        html.classList.add('dark');
        toggle.checked = true;
    }

    toggle.addEventListener('change', () => {
        const dark = toggle.checked;
        html.classList.toggle('dark', dark);
        localStorage.setItem('auralib_theme', dark ? 'dark' : 'light');
    });

    /* ── Language dropdown ── */
    const langTrigger  = document.getElementById('langTrigger');
    const langDropdown = document.getElementById('langDropdown');
    if (langTrigger) {
        langTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            langDropdown.classList.toggle('open');
            document.getElementById('profileWrap')?.classList.remove('open');
        });
    }

    /* ── Profile dropdown ── */
    const profileTrigger = document.getElementById('profileTrigger');
    const profileWrap    = document.getElementById('profileWrap');
    if (profileTrigger) {
        profileTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            profileWrap.classList.toggle('open');
            langDropdown?.classList.remove('open');
        });
    }

    /* ── Close on outside click ── */
    window.addEventListener('click', () => {
        langDropdown?.classList.remove('open');
        profileWrap?.classList.remove('open');
    });
    /* ── Admin Search ── */
    <?php if ($user_role === 'admin'): ?>
    (function() {
        const SEARCH_URL = '/MEMOIR/client/search_engine.php';
        const MIN        = 2;
        const DELAY      = 280;
        const MAX        = 5;

        const input = document.getElementById('adm-search-input');
        const drop  = document.getElementById('adm-search-dropdown');
        if (!input || !drop) return;

        let timer = null, ctrl = null;

        function esc(s) {
            return String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        }
        function hl(t,q) {
            if(!q) return esc(t);
            return esc(t).replace(new RegExp('('+q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+')','gi'),'<mark style="background:rgba(196,164,107,.3);color:#fff;border-radius:2px;padding:0 1px">$1</mark>');
        }
        function badge(cls,lbl){ return '<span class="adm-sd-badge badge-'+cls+'">'+lbl+'</span>'; }
        function statusBadge(s){
            const m={'active':['active','Actif'],'suspended':['suspended','Suspendu'],'en_cours':['encours','En cours'],'rendu':['rendu','Rendu'],'refusée':['refused','Refusée'],'en attente':['encours','Attente']};
            const[c,l]=m[s]??['encours',s]; return badge(c,esc(l));
        }
        function dispoBadge(d){
            const m={achat:'badge-achat',emprunt:'badge-emprunt',both:'badge-both'};
            const l={achat:'Achat',emprunt:'Emprunt',both:'Achat+Emprunt'};
            return '<span class="adm-sd-badge '+(m[d]??'badge-both')+'">'+(l[d]??d)+'</span>';
        }
        function actBtn(href,label){
            return '<a href="'+esc(href)+'" class="adm-sd-act-btn">'+label+'</a>';
        }

        const ICON = {
            documents:'<svg viewBox="0 0 20 20" fill="currentColor" width="13" height="13"><path d="M9 4.5a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-11Zm9 0a1 1 0 0 0-1-1h-5a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-11Z"/></svg>',
            users:'<svg viewBox="0 0 20 20" fill="currentColor" width="13" height="13"><path d="M10 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm-7 7a7 7 0 1 1 14 0H3Z"/></svg>',
            loans:'<svg viewBox="0 0 20 20" fill="currentColor" width="13" height="13"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v4.5c0 .199.079.39.22.53l2.25 2.25a.75.75 0 1 0 1.06-1.06l-2.03-2.03v-4.19Z" clip-rule="evenodd"/></svg>'
        };

        function render(data, q) {
            const {documents=[],users=[],loans=[]} = data.results??{};
            const total = data.total??0;
            if(!total){
                drop.innerHTML='<div class="adm-sd-empty">Aucun résultat pour «&nbsp;'+esc(q)+'&nbsp;»</div>';
                return;
            }
            let h='';
            if(documents.length){
                h+='<div class="adm-sd-header">'+ICON.documents+' Documents <span style="margin-left:auto;opacity:.45">'+documents.length+'</span></div>';
                documents.slice(0,MAX).forEach(d=>{
                    const img=d.image?'/MEMOIR/uploads/'+esc(d.image):'';
                    h+='<div class="adm-sd-item">'
                      +'<div class="adm-sd-avatar">'+(img?'<img src="'+img+'" onerror="this.style.display=\'none\'">':esc(d.titre.charAt(0).toUpperCase()))+'</div>'
                      +'<div class="adm-sd-main"><div class="adm-sd-title">'+hl(d.titre,q)+'</div>'
                      +'<div class="adm-sd-sub">'+esc(d.auteur)+(d.annee?' · '+esc(d.annee):'')+(d.type?' · '+esc(d.type):'')+'</div></div>'
                      +dispoBadge(d.dispo)
                      +'<div class="adm-sd-actions">'+actBtn(d.edit_url,'Modifier')+actBtn(d.detail_url,'Voir')+'</div>'
                      +'</div>';
                });
            }
            if(users.length){
                h+='<div class="adm-sd-header">'+ICON.users+' Utilisateurs <span style="margin-left:auto;opacity:.45">'+users.length+'</span></div>';
                users.slice(0,MAX).forEach(u=>{
                    h+='<div class="adm-sd-item">'
                      +'<div class="adm-sd-avatar" style="background:rgba(196,164,107,.12)">'+esc((u.name||'?').charAt(0).toUpperCase())+'</div>'
                      +'<div class="adm-sd-main"><div class="adm-sd-title">'+hl(u.name,q)+'</div>'
                      +'<div class="adm-sd-sub">'+hl(u.email,q)+(u.phone?' · '+esc(u.phone):'')+'</div></div>'
                      +statusBadge(u.status)
                      +'<div class="adm-sd-actions">'+actBtn(u.profile_url,'Profil')+'</div>'
                      +'</div>';
                });
            }
            if(loans.length){
                h+='<div class="adm-sd-header">'+ICON.loans+' Emprunts <span style="margin-left:auto;opacity:.45">'+loans.length+'</span></div>';
                loans.slice(0,MAX).forEach(l=>{
                    h+='<div class="adm-sd-item">'
                      +'<div class="adm-sd-avatar" style="background:rgba(99,102,241,.12);color:#a5b4fc">'+ICON.loans+'</div>'
                      +'<div class="adm-sd-main"><div class="adm-sd-title">'+hl(l.book_title,q)+'</div>'
                      +'<div class="adm-sd-sub">'+hl(l.user_name,q)+' · '+esc(l.date_debut??'')+'</div></div>'
                      +statusBadge(l.statut)
                      +'<div class="adm-sd-actions">'+actBtn(l.manage_url,'Gérer')+'</div>'
                      +'</div>';
                });
            }
            h+='<div class="adm-sd-footer">Entrée = recherche complète · '+total+' résultat'+(total>1?'s':'')+'</div>';
            drop.innerHTML=h;
        }

        async function doSearch(q) {
            if(ctrl) ctrl.abort();
            ctrl = new AbortController();
            drop.innerHTML='<div class="adm-sd-spinner"><div class="spin-ring"></div> Recherche…</div>';
            drop.hidden=false;
            try {
                const res  = await fetch(SEARCH_URL+'?scope=admin&q='+encodeURIComponent(q),{signal:ctrl.signal,credentials:'same-origin'});
                if(!res.ok) throw new Error('HTTP '+res.status);
                const data = await res.json();
                render(data,q);
                drop.hidden=false;
            } catch(e) {
                if(e.name==='AbortError') return;
                drop.innerHTML='<div class="adm-sd-empty">Erreur ('+esc(e.message)+')</div>';
            }
        }

        input.addEventListener('input',()=>{
            clearTimeout(timer);
            const q=input.value.trim();
            if(q.length<MIN){drop.hidden=true;return;}
            timer=setTimeout(()=>doSearch(q),DELAY);
        });
        input.addEventListener('keydown',e=>{
            if(e.key==='Enter'){e.preventDefault();const q=input.value.trim();if(q.length>=MIN)window.location.href='/MEMOIR/client/library.php?search='+encodeURIComponent(q);}
            if(e.key==='Escape') drop.hidden=true;
        });
        document.addEventListener('click',e=>{
            if(!input.closest('.adm-search-wrap').contains(e.target)) drop.hidden=true;
        });
    })();
    <?php endif; ?>
})();
</script>