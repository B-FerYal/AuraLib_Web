<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = (int)$_SESSION['id_user'];
$tab     = $_GET['tab'] ?? 'dashboard';
$success = '';
$error   = '';

// ── Fetch user ────────────────────────────────────────────
$stmt = $conn->prepare("SELECT id, firstname, lastname, email, phone, Gender, role, created_at, password FROM users WHERE id = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$role = $user['role'] ?? 'client';
$first_letter     = strtoupper(substr($user['firstname'] ?? $user['email'] ?? 'U', 0, 1));
$display_name     = htmlspecialchars($user['firstname'] ?? '');
$display_email    = htmlspecialchars($user['email']     ?? '');
$display_fullname = htmlspecialchars(trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')));
// ── Valeurs par défaut (évite les warnings d'IDE) ──
$nb_emprunts = $nb_retours = $nb_en_cours = $nb_retards = $nb_commandes = 0;
$total_achats = 0.0;
$emprunts_actifs = $hist_emprunts = $hist_commandes = null;
$wishlist_items = [];
$nb_wishlist = 0;
$notifications = [];

$nb_users = $nb_livres = $nb_emprunts_actifs_total = $nb_retards_total = 0;
$chiffre_affaires = 0.0;
$last_orders = null;
// ── POST handlers ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update_info') {
        $new_firstname = trim($_POST['firstname'] ?? '');
        $new_lastname  = trim($_POST['lastname']  ?? '');
        $new_email     = trim($_POST['email']     ?? '');
        $new_phone     = trim($_POST['phone']     ?? '');
        $new_gender    = trim($_POST['gender']    ?? '');

        if (empty($new_firstname) || empty($new_email)) {
            $error = "Le prénom et l'email sont obligatoires.";
            $tab   = 'settings';
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Adresse email invalide.";
            $tab   = 'settings';
        } else {
            $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $chk->bind_param("si", $new_email, $id_user);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $error = "Cette adresse email est déjà utilisée.";
                $tab   = 'settings';
            } else {
                $upd = $conn->prepare("UPDATE users SET firstname=?, lastname=?, email=?, phone=?, Gender=? WHERE id=?");
                $upd->bind_param("sssssi", $new_firstname, $new_lastname, $new_email, $new_phone, $new_gender, $id_user);
                $upd->execute();
                $user['firstname'] = $new_firstname; $user['lastname'] = $new_lastname;
                $user['email']     = $new_email;     $user['phone']    = $new_phone;
                $user['Gender']    = $new_gender;
                $first_letter     = strtoupper(substr($new_firstname, 0, 1));
                $display_name     = htmlspecialchars($new_firstname);
                $display_email    = htmlspecialchars($new_email);
                $display_fullname = htmlspecialchars(trim("$new_firstname $new_lastname"));
                $success = "Informations mises à jour avec succès !";
                $tab     = 'settings';
            }
        }

    } elseif ($_POST['action'] === 'update_password') {
        $current = $_POST['current_password'] ?? '';
        $new_pwd = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new_pwd) || empty($confirm)) {
            $error = "Tous les champs sont obligatoires.";
        } elseif ($new_pwd !== $confirm) {
            $error = "Les nouveaux mots de passe ne correspondent pas.";
        } elseif (strlen($new_pwd) < 6) {
            $error = "Le mot de passe doit contenir au moins 6 caractères.";
        } elseif (!password_verify($current, $user['password'] ?? '')) {
            $error = "Mot de passe actuel incorrect.";
        } else {
            $hash = password_hash($new_pwd, PASSWORD_DEFAULT);
            $upd  = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $upd->bind_param("si", $hash, $id_user);
            $upd->execute();
            $success = "Mot de passe modifié avec succès !";
        }
        $tab = 'settings';
    }
}

// ════════════════════════════════════════════════
//  DONNÉES SELON LE RÔLE
// ════════════════════════════════════════════════

if ($role === 'client') {

    // ── Stats lecteur ──
    $nb_emprunts = $nb_retours = $nb_en_cours = $nb_retards = $nb_commandes = 0;
    $total_achats = 0.0; $amende_total = 0;

    $r = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE id_user=$id_user");
    if ($r) $nb_emprunts = (int)$r->fetch_assoc()['c'];

    $r = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE id_user=$id_user AND statut='retourne'");
    if ($r) $nb_retours = (int)$r->fetch_assoc()['c'];

    $r = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE id_user=$id_user AND statut='en_cours'");
    if ($r) $nb_en_cours = (int)$r->fetch_assoc()['c'];

    $r = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE id_user=$id_user AND statut='en_cours' AND date_fin < CURDATE()");
    if ($r) $nb_retards = (int)$r->fetch_assoc()['c'];

    $r = $conn->query("SELECT COUNT(*) c FROM commande WHERE id_user=$id_user");
    if ($r) $nb_commandes = (int)$r->fetch_assoc()['c'];

    $r = $conn->query("SELECT COALESCE(SUM(total),0) s FROM commande WHERE id_user=$id_user AND statut='payee'");
    if ($r) $total_achats = (float)$r->fetch_assoc()['s'];

    // Emprunts actifs avec jours restants
    $emprunts_actifs = $conn->query("
        SELECT e.*, d.titre,
               DATEDIFF(e.date_fin, CURDATE()) AS jours_restants
        FROM emprunt e
        JOIN documents d ON e.id_doc = d.id_doc
        WHERE e.id_user = $id_user AND e.statut = 'en_cours'
        ORDER BY e.date_fin ASC
        LIMIT 5
    ");

    // Historique emprunts
    $hist_emprunts = $conn->query("
        SELECT e.*, d.titre FROM emprunt e
        JOIN documents d ON e.id_doc = d.id_doc
        WHERE e.id_user = $id_user
        ORDER BY e.date_debut DESC LIMIT 5
    ");

    // Historique commandes
    $hist_commandes = $conn->query("SELECT * FROM commande WHERE id_user=$id_user ORDER BY id_commande DESC LIMIT 5");

    // ── Wishlist ──────────────────────────────────────────
    $wishlist_result = $conn->query("
        SELECT w.id_wishlist, d.id_doc, d.titre, d.prix
        FROM wishlist w
        JOIN documents d ON w.id_doc = d.id_doc
        WHERE w.id_user = $id_user
        ORDER BY w.created_at DESC
    ");
    $wishlist_items = [];
    if ($wishlist_result) {
        while ($w = $wishlist_result->fetch_assoc()) $wishlist_items[] = $w;
    }
    $nb_wishlist = count($wishlist_items);

    // ── Notifications ─────────────────────────────────────
    $notifications = [];

    $r = $conn->query("
        SELECT d.titre, DATEDIFF(CURDATE(), e.date_fin) AS jours_retard
        FROM emprunt e JOIN documents d ON e.id_doc = d.id_doc
        WHERE e.id_user = $id_user AND e.statut = 'en_cours' AND e.date_fin < CURDATE()
        ORDER BY e.date_fin ASC LIMIT 5
    ");
    if ($r) while ($n = $r->fetch_assoc()) {
        $notifications[] = ['type' => 'danger', 'text' => 'Retour dépassé : <strong>' . htmlspecialchars($n['titre']) . '</strong> (' . $n['jours_retard'] . ' j de retard)', 'time' => 'Urgent'];
    }

    $r = $conn->query("
        SELECT d.titre, DATEDIFF(e.date_fin, CURDATE()) AS jours_restants
        FROM emprunt e JOIN documents d ON e.id_doc = d.id_doc
        WHERE e.id_user = $id_user AND e.statut = 'en_cours'
          AND e.date_fin >= CURDATE() AND DATEDIFF(e.date_fin, CURDATE()) <= 3
        ORDER BY e.date_fin ASC LIMIT 5
    ");
    if ($r) while ($n = $r->fetch_assoc()) {
        $notifications[] = ['type' => 'warning', 'text' => 'Rappel : <strong>' . htmlspecialchars($n['titre']) . '</strong> à rendre dans ' . $n['jours_restants'] . ' jour(s)', 'time' => 'Bientôt'];
    }

    $r = $conn->query("SELECT id_commande, date_commande FROM commande WHERE id_user=$id_user AND statut='payee' ORDER BY date_commande DESC LIMIT 2");
    if ($r) while ($n = $r->fetch_assoc()) {
        $notifications[] = ['type' => 'success', 'text' => 'Commande <strong>#' . str_pad($n['id_commande'], 3, '0', STR_PAD_LEFT) . '</strong> confirmée', 'time' => date('d/m/Y', strtotime($n['date_commande']))];
    }

} else {
    // ── Stats admin ──
    $nb_users = $nb_livres = $nb_emprunts_actifs_total = $nb_retards_total = 0;
    $chiffre_affaires = 0.0;

    $r = $conn->query("SELECT COUNT(*) c FROM users WHERE role='client'"); if($r) $nb_users = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) c FROM documents"); if($r) $nb_livres = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE statut='en_cours'"); if($r) $nb_emprunts_actifs_total = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE statut='en_cours' AND date_fin < CURDATE()"); if($r) $nb_retards_total = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COALESCE(SUM(total),0) s FROM commande WHERE statut='payee'"); if($r) $chiffre_affaires = (float)$r->fetch_assoc()['s'];

    // Dernières commandes
    $last_orders = $conn->query("
        SELECT c.id_commande, c.total, c.statut, c.date_commande,
               u.firstname, u.lastname
        FROM commande c JOIN users u ON c.id_user = u.id
        ORDER BY c.date_commande DESC LIMIT 5
    ");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon profil — AuraLibre</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--taupe:#2C1F0E;--taupe2:#3A2A14;--gold:#C4A46B;--gold2:#D4B47B;--cream:#F5F0E8;--cream2:#EDE5D4;--white:#FFFDF9;--border:#DDD5C8;--muted:#9A8C7E}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--cream);color:var(--taupe)}

.profile-page{max-width:900px;margin:36px auto;padding:0 16px 60px}

/* ── Hero card ── */
.profile-hero{background:var(--taupe);border-radius:14px;border:1px solid rgba(196,164,107,.25);padding:24px 28px;display:flex;align-items:center;gap:18px;margin-bottom:16px}
.hero-avatar{width:62px;height:62px;border-radius:50%;background:var(--gold);color:var(--taupe);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;flex-shrink:0;border:3px solid rgba(196,164,107,.35)}
.hero-info h1{font-size:19px;font-weight:600;color:var(--gold);line-height:1}
.hero-info p{font-size:12px;color:rgba(255,255,255,.4);margin-top:4px}
.hero-meta{display:flex;gap:6px;margin-top:8px;flex-wrap:wrap}
.hero-meta span{font-size:10px;color:rgba(255,255,255,.4);display:flex;align-items:center;gap:4px}
.hero-meta svg{width:11px;height:11px;opacity:.5}
.hero-role{margin-left:auto;flex-shrink:0;background:rgba(196,164,107,.12);border:1px solid rgba(196,164,107,.3);color:var(--gold);font-size:10px;font-weight:700;padding:4px 12px;border-radius:20px;text-transform:uppercase;letter-spacing:.8px}

/* ── Stats row ── */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px}
.stat-card{background:var(--white);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center;border-top:2px solid var(--gold)}
.stat-card.green{border-top-color:#4ade80}.stat-card.amber{border-top-color:#fbbf24}.stat-card.red{border-top-color:#f87171}
.stat-num{font-size:22px;font-weight:600;color:var(--taupe)}
.stat-lbl{font-size:10px;color:var(--muted);margin-top:2px}

/* ── Tabs ── */
.tabs{display:flex;gap:2px;border-bottom:1px solid var(--border);margin-bottom:18px;overflow-x:auto}
.tab-btn{display:flex;align-items:center;gap:6px;padding:9px 14px;font-size:12px;font-weight:500;color:#7a6a5a;background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;text-decoration:none;transition:color .15s,border-color .15s;white-space:nowrap;flex-shrink:0}
.tab-btn:hover{color:var(--taupe)}.tab-btn.active{color:var(--taupe);border-bottom-color:var(--gold)}
.tab-btn svg{width:13px;height:13px}
.tab-badge{background:#C4A46B;color:var(--taupe);font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:2px}

/* ── Panels ── */
.panel{display:none}.panel.active{display:block}

/* ── Dashboard grid ── */
.dash-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px}
.dash-card{background:var(--white);border:1px solid var(--border);border-radius:10px;padding:14px}
.dash-card h4{font-size:12px;font-weight:600;color:var(--taupe);margin-bottom:10px;display:flex;align-items:center;gap:6px;padding-bottom:8px;border-bottom:1px solid #f5f0e8}
.dash-card h4 svg{width:13px;height:13px;flex-shrink:0}

/* Emprunts actifs list */
.borrow-item{display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:0.5px solid #f5f0e8}
.borrow-item:last-child{border-bottom:none}
.borrow-title{font-size:12px;color:var(--taupe);font-weight:500}
.borrow-date{font-size:10px;color:var(--muted);margin-top:1px}
.day-badge{font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;flex-shrink:0}
.db-ok{background:#dcfce7;color:#15803d}
.db-soon{background:#fef9c3;color:#854d0e}
.db-late{background:#fee2e2;color:#dc2626}
.db-none{background:#f3f4f6;color:#6b7280}

/* Notifications list */
.notif-item{display:flex;align-items:flex-start;gap:8px;padding:7px 0;border-bottom:0.5px solid #f5f0e8}
.notif-item:last-child{border-bottom:none}
.notif-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;margin-top:4px}
.nd-danger{background:#f87171}.nd-warning{background:#fbbf24}.nd-success{background:#4ade80}.nd-info{background:#60a5fa}
.notif-text{font-size:11px;color:#5A4A3A;line-height:1.5}
.notif-time{font-size:10px;color:var(--muted);margin-top:1px}

/* ── Wishlist grid ── */
.wish-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.wish-card{background:var(--white);border:1px solid var(--border);border-radius:10px;overflow:hidden;transition:transform .15s,box-shadow .15s}
.wish-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(61,46,37,.1)}
.wish-cover{height:120px;background:var(--cream2);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
.wish-cover img{width:100%;height:100%;object-fit:cover}
.wish-cover-placeholder{width:55px;height:75px;background:var(--taupe);border-radius:3px;display:flex;align-items:center;justify-content:center}
.wish-type-badge{position:absolute;top:7px;right:7px;font-size:9px;font-weight:700;padding:2px 8px;border-radius:10px;text-transform:uppercase}
.wish-body{padding:11px 12px}
.wish-title{font-size:12px;font-weight:600;color:var(--taupe);margin-bottom:3px;line-height:1.3;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden}
.wish-author{font-size:10px;color:var(--muted);margin-bottom:7px}
.wish-price{font-size:13px;font-weight:700;color:var(--taupe);margin-bottom:9px}
.wish-price.free{color:#15803d;font-size:12px}
.wish-actions{display:flex;gap:6px}
.wish-btn-voir{
    flex:1;background:var(--gold);color:var(--taupe);border:none;
    padding:7px 0;border-radius:6px;font-size:11px;font-weight:700;
    text-align:center;text-decoration:none;display:block;
    font-family:'Inter',sans-serif;cursor:pointer;transition:background .15s;
}
.wish-btn-voir:hover{background:var(--gold2)}
.wish-btn-del{
    background:#fee2e2;color:#dc2626;border:none;
    padding:7px 10px;border-radius:6px;font-size:11px;font-weight:700;
    text-decoration:none;display:flex;align-items:center;justify-content:center;
    cursor:pointer;transition:background .15s;
}
.wish-btn-del:hover{background:#fecaca}

/* ── Forms ── */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-card{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:20px}
.form-card h3{font-size:13px;font-weight:600;color:var(--taupe);margin-bottom:14px;padding-bottom:9px;border-bottom:1px solid #f0e8d8;display:flex;align-items:center;gap:6px}
.form-card h3 svg{width:14px;height:14px;flex-shrink:0}
.form-group{margin-bottom:12px}
.form-group:last-of-type{margin-bottom:0}
.form-group label{display:block;font-size:11px;color:#7a6a5a;margin-bottom:4px;font-weight:500}
.form-group input,.form-group select{width:100%;padding:8px 11px;border:1px solid #e0d8cc;border-radius:7px;font-size:12px;font-family:'Inter',sans-serif;color:var(--taupe);background:#faf8f4;outline:none;transition:border-color .15s}
.form-group input:focus,.form-group select:focus{border-color:var(--gold);background:white}
.form-group input[readonly]{background:#f2ece0;color:#9a8878;cursor:not-allowed}
.btn-save{display:inline-flex;align-items:center;gap:5px;background:var(--gold);color:var(--taupe);border:none;padding:8px 18px;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;font-family:'Inter',sans-serif;transition:background .15s;margin-top:4px}
.btn-save:hover{background:var(--gold2)}

/* ── Alerts ── */
.alert{padding:10px 13px;border-radius:8px;font-size:12px;margin-bottom:16px;display:flex;align-items:center;gap:7px}
.alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d}
.alert-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626}
.alert svg{width:14px;height:14px;flex-shrink:0}

/* ── Tables ── */
.table-wrap{background:var(--white);border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:14px}
.table-header{padding:11px 14px;border-bottom:1px solid #f0e8d8;font-size:12px;font-weight:600;color:var(--taupe);display:flex;align-items:center;gap:6px}
.table-header svg{width:14px;height:14px}
.data-table{width:100%;border-collapse:collapse;font-size:12px}
.data-table th{text-align:left;padding:8px 12px;font-size:10px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);background:#faf8f4}
.data-table td{padding:10px 12px;border-bottom:0.5px solid #f5f0e8;color:var(--taupe)}
.data-table tr:last-child td{border-bottom:none}
.data-table tr:hover td{background:#fdf8f0}
.empty-row td{text-align:center;color:#b0a090;padding:20px}

/* ── Badges ── */
.badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600}
.badge-green{background:#dcfce7;color:#15803d}.badge-amber{background:#fef9c3;color:#854d0e}
.badge-red{background:#fee2e2;color:#dc2626}.badge-gray{background:#f3f4f6;color:#6b7280}

/* ── Quick actions ── */
.qa-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px}
.qa-btn{background:var(--white);border:1px solid var(--border);border-radius:9px;padding:14px 12px;text-align:center;text-decoration:none;color:var(--taupe);transition:border-color .15s,background .15s;display:block}
.qa-btn:hover{border-color:var(--gold);background:#fdf8f0}
.qa-icon{font-size:20px;margin-bottom:6px}
.qa-label{font-size:11px;font-weight:600;color:var(--taupe)}
.qa-sub{font-size:10px;color:var(--muted);margin-top:2px}

.section-lbl{font-size:10px;font-weight:600;color:var(--muted);letter-spacing:1px;text-transform:uppercase;margin-bottom:10px;padding-bottom:6px;border-bottom:0.5px solid var(--border)}
.back-link{font-size:12px;color:#7a6a5a;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
.back-link:hover{color:var(--taupe)}

/* ── Info fields ── */
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.info-field{background:#faf8f4;border:1px solid #f0e8d8;border-radius:8px;padding:10px 12px}
.info-field label{font-size:10px;color:var(--muted);font-weight:500;display:block;margin-bottom:3px;text-transform:uppercase;letter-spacing:.5px}
.info-field span{font-size:13px;color:var(--taupe);font-weight:500}

/* ── Empty state ── */
.empty-state{text-align:center;padding:40px 20px;background:var(--white);border:1px solid var(--border);border-radius:10px}
.empty-state .empty-icon{font-size:36px;margin-bottom:12px}
.empty-state p{font-size:13px;color:var(--muted);margin-bottom:14px}
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<!-- ════════════════════════════════════════════
     TOUT EST DANS .profile-page — rien en dehors
════════════════════════════════════════════ -->
<div class="profile-page">

<?php if ($success): ?>
<div class="alert alert-success">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
    <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<!-- ══ HERO ══ -->
<div class="profile-hero">
    <div class="hero-avatar"><?= $first_letter ?></div>
    <div class="hero-info">
        <h1><?= $display_fullname ?: $display_email ?></h1>
        <p><?= $display_email ?></p>
        <div class="hero-meta">
            <?php if (!empty($user['phone'])): ?>
            <span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.54 19.79 19.79 0 0 1 1.61 4.94 2 2 0 0 1 3.58 2.75h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l.72-.72a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.5 17z"/></svg>
                <?= htmlspecialchars($user['phone']) ?>
            </span>
            <?php endif; ?>
            <?php if (!empty($user['Gender'])): ?>
            <span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/></svg>
                <?= htmlspecialchars($user['Gender']) ?>
            </span>
            <?php endif; ?>
            <?php if (!empty($user['created_at'])): ?>
            <span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Membre depuis <?= date('M Y', strtotime($user['created_at'])) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <span class="hero-role"><?= $role === 'admin' ? 'Administrateur' : 'Lecteur' ?></span>
</div>

<!-- ══ STATS ══ -->
<?php if ($role === 'client'): ?>
<div class="stats-row">
    <div class="stat-card"><div class="stat-num"><?= $nb_emprunts ?></div><div class="stat-lbl">Emprunts total</div></div>
    <div class="stat-card green"><div class="stat-num"><?= $nb_en_cours ?></div><div class="stat-lbl">Emprunts actifs</div></div>
    <div class="stat-card amber"><div class="stat-num"><?= $nb_retards ?></div><div class="stat-lbl">En retard</div></div>
    <div class="stat-card"><div class="stat-num"><?= number_format($total_achats, 0) ?><small style="font-size:11px"> DA</small></div><div class="stat-lbl">Total achats</div></div>
</div>
<?php else: ?>
<div class="stats-row">
    <div class="stat-card"><div class="stat-num"><?= $nb_users ?></div><div class="stat-lbl">Lecteurs inscrits</div></div>
    <div class="stat-card green"><div class="stat-num"><?= $nb_livres ?></div><div class="stat-lbl">Documents</div></div>
    <div class="stat-card amber"><div class="stat-num"><?= $nb_emprunts_actifs_total ?></div><div class="stat-lbl">Emprunts actifs</div></div>
    <div class="stat-card red"><div class="stat-num"><?= $nb_retards_total ?></div><div class="stat-lbl">En retard</div></div>
</div>
<?php endif; ?>

<!-- ══ TABS ══ -->
<div class="tabs">
    <?php if ($role === 'client'): ?>
    <a href="?tab=dashboard" class="tab-btn <?= $tab==='dashboard'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Tableau de bord
    </a>
    <?php endif; ?>

    <a href="?tab=profil" class="tab-btn <?= $tab==='profil'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Mon profil
    </a>

    <a href="?tab=settings" class="tab-btn <?= $tab==='settings'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06-.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Paramètres
    </a>

    <?php if ($role === 'client'): ?>
    <a href="?tab=historique" class="tab-btn <?= $tab==='historique'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="12 8 12 12 14 14"/><path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5"/></svg>
        Historique
    </a>

    <!-- ══ TAB WISHLIST — à sa place correcte dans la liste ══ -->
    <a href="?tab=wishlist" class="tab-btn <?= $tab==='wishlist'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        Ma liste de souhaits
        <?php if (!empty($nb_wishlist) && $nb_wishlist > 0): ?>
            <span class="tab-badge"><?= $nb_wishlist ?></span>
        <?php endif; ?>
    </a>
    <?php endif; ?>

    <?php if ($role === 'admin'): ?>
    <a href="?tab=admin_orders" class="tab-btn <?= $tab==='admin_orders'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        Commandes récentes
    </a>
    <?php endif; ?>
</div>

<!-- ════════════════════════════════════════════
     PANEL : TABLEAU DE BORD
════════════════════════════════════════════ -->
<?php if ($role === 'client'): ?>
<div class="panel <?= $tab==='dashboard'?'active':'' ?>">
    <div class="section-lbl">Tableau de bord lecteur</div>
    <div class="dash-grid">
        <div class="dash-card">
            <h4>
                <svg viewBox="0 0 24 24" fill="none" stroke="#C4A46B" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                Emprunts en cours
            </h4>
            <?php if ($emprunts_actifs && $emprunts_actifs->num_rows > 0): ?>
                <?php while ($e = $emprunts_actifs->fetch_assoc()):
                    $j = (int)$e['jours_restants'];
                    if ($j < 0)      { $badge_cls = 'db-late'; $badge_txt = 'En retard'; }
                    elseif ($j <= 3) { $badge_cls = 'db-soon'; $badge_txt = 'J-'.$j; }
                    else             { $badge_cls = 'db-ok';   $badge_txt = 'J-'.$j; }
                ?>
                <div class="borrow-item">
                    <div>
                        <div class="borrow-title"><?= htmlspecialchars($e['titre']) ?></div>
                        <div class="borrow-date">Retour : <?= date('d/m/Y', strtotime($e['date_fin'])) ?></div>
                    </div>
                    <span class="day-badge <?= $badge_cls ?>"><?= $badge_txt ?></span>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="font-size:12px;color:var(--muted);text-align:center;padding:14px 0">Aucun emprunt actif</p>
            <?php endif; ?>
            <div style="margin-top:10px">
                <a href="/MEMOIR/emprunts/emprunt.php" style="font-size:11px;color:var(--gold);text-decoration:none;font-weight:600">Voir tous mes emprunts →</a>
            </div>
        </div>

        <div class="dash-card">
            <h4>
                <svg viewBox="0 0 24 24" fill="none" stroke="#C4A46B" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Notifications
                <?php if (count($notifications) > 0): ?>
                    <span style="background:#C4A46B;color:var(--taupe);font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:auto"><?= count($notifications) ?></span>
                <?php endif; ?>
            </h4>
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $n): ?>
                <div class="notif-item">
                    <div class="notif-dot nd-<?= $n['type'] ?>"></div>
                    <div>
                        <div class="notif-text"><?= $n['text'] ?></div>
                        <div class="notif-time"><?= $n['time'] ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="font-size:12px;color:var(--muted);text-align:center;padding:14px 0">Aucune notification</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="section-lbl">Actions rapides</div>
    <div class="qa-grid">
        <a href="/MEMOIR/client/library.php" class="qa-btn">
            <div class="qa-icon">📚</div>
            <div class="qa-label">Catalogue</div>
            <div class="qa-sub">Chercher un livre</div>
        </a>
        <a href="/MEMOIR/emprunts/emprunt.php" class="qa-btn">
            <div class="qa-icon">📖</div>
            <div class="qa-label">Mes emprunts</div>
            <div class="qa-sub">Voir & renouveler</div>
        </a>
        <a href="/MEMOIR/cart/panier.php" class="qa-btn">
            <div class="qa-icon">🛒</div>
            <div class="qa-label">Mon panier</div>
            <div class="qa-sub">Finaliser mes achats</div>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════════
     PANEL : MON PROFIL
════════════════════════════════════════════ -->
<div class="panel <?= $tab==='profil'?'active':'' ?>">
    <div class="form-card" style="margin-bottom:14px">
        <h3>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Informations personnelles
        </h3>
        <div class="info-grid">
            <div class="info-field"><label>Prénom</label><span><?= htmlspecialchars($user['firstname'] ?? '—') ?></span></div>
            <div class="info-field"><label>Nom de famille</label><span><?= htmlspecialchars($user['lastname'] ?? '—') ?></span></div>
            <div class="info-field"><label>Email</label><span><?= htmlspecialchars($user['email'] ?? '—') ?></span></div>
            <div class="info-field"><label>Téléphone</label><span><?= htmlspecialchars($user['phone'] ?? '—') ?></span></div>
            <div class="info-field"><label>Genre</label><span><?= htmlspecialchars($user['Gender'] ?? '—') ?></span></div>
            <div class="info-field"><label>Rôle</label><span><?= $role === 'admin' ? 'Administrateur' : 'Lecteur' ?></span></div>
            <div class="info-field"><label>Membre depuis</label><span><?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '—' ?></span></div>
        </div>
        <div style="margin-top:14px">
            <a href="?tab=settings" class="btn-save">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Modifier
            </a>
        </div>
    </div>
    <?php if ($role === 'admin'): ?>
    <div class="section-lbl">Accès rapide administration</div>
    <div class="qa-grid">
        <a href="/MEMOIR/admin/admin_dashboard.php" class="qa-btn"><div class="qa-icon">📊</div><div class="qa-label">Dashboard</div><div class="qa-sub">Vue globale</div></a>
        <a href="/MEMOIR/admin/gerer_documents"   class="qa-btn"><div class="qa-icon">📚</div><div class="qa-label">Gérer livres</div><div class="qa-sub">Ajouter, modifier</div></a>
        <a href="/MEMOIR/admin/users.php"           class="qa-btn"><div class="qa-icon">👥</div><div class="qa-label">Utilisateurs</div><div class="qa-sub">Gérer les comptes</div></a>
    </div>
    <?php endif; ?>
</div>

<!-- ════════════════════════════════════════════
     PANEL : PARAMÈTRES
════════════════════════════════════════════ -->
<div class="panel <?= $tab==='settings'?'active':'' ?>">
    <div class="form-grid">
        <div class="form-card">
            <h3>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Modifier mes informations
            </h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_info">
                <div class="form-group"><label>Prénom <span style="color:#e74c3c">*</span></label><input type="text" name="firstname" value="<?= htmlspecialchars($user['firstname']??'') ?>" required></div>
                <div class="form-group"><label>Nom de famille</label><input type="text" name="lastname" value="<?= htmlspecialchars($user['lastname']??'') ?>"></div>
                <div class="form-group"><label>Email <span style="color:#e74c3c">*</span></label><input type="email" name="email" value="<?= htmlspecialchars($user['email']??'') ?>" required></div>
                <div class="form-group"><label>Téléphone</label><input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>" placeholder="0XXXXXXXXX"></div>
                <div class="form-group">
                    <label>Genre</label>
                    <select name="gender">
                        <option value="">— Sélectionner —</option>
                        <option value="Homme" <?= ($user['Gender']??'')==='Homme'?'selected':'' ?>>Homme</option>
                        <option value="Femme" <?= ($user['Gender']??'')==='Femme'?'selected':'' ?>>Femme</option>
                        <option value="Autre" <?= ($user['Gender']??'')==='Autre'?'selected':'' ?>>Autre</option>
                    </select>
                </div>
                <button type="submit" class="btn-save">Enregistrer</button>
            </form>
        </div>
        <div class="form-card">
            <h3>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Changer le mot de passe
            </h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_password">
                <div class="form-group"><label>Mot de passe actuel</label><input type="password" name="current_password" placeholder="••••••••" required></div>
                <div class="form-group"><label>Nouveau mot de passe</label><input type="password" name="new_password" placeholder="••••••••" required minlength="6"></div>
                <div class="form-group"><label>Confirmer le nouveau</label><input type="password" name="confirm_password" placeholder="••••••••" required></div>
                <p style="font-size:10px;color:var(--muted);margin:6px 0 10px">Minimum 6 caractères.</p>
                <button type="submit" class="btn-save">Changer</button>
            </form>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════
     PANEL : HISTORIQUE
════════════════════════════════════════════ -->
<?php if ($role === 'client'): ?>
<div class="panel <?= $tab==='historique'?'active':'' ?>">
    <div class="table-wrap">
        <div class="table-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            Mes derniers emprunts
        </div>
        <table class="data-table">
            <thead><tr><th>Livre</th><th>Date emprunt</th><th>Date retour</th><th>Statut</th></tr></thead>
            <tbody>
            <?php if ($hist_emprunts && $hist_emprunts->num_rows > 0):
                while ($e = $hist_emprunts->fetch_assoc()):
                    $s  = $e['statut'] ?? '';
                    $bc = $s==='retourne'?'badge-green':($s==='en_cours'?'badge-amber':'badge-gray');
                    $bl = $s==='retourne'?'Retourné':($s==='en_cours'?'En cours':ucfirst($s));
            ?>
            <tr>
                <td><?= htmlspecialchars($e['titre']) ?></td>
                <td><?= $e['date_debut']?date('d/m/Y',strtotime($e['date_debut'])):'—'?></td>
                <td><?= !empty($e['date_fin'])?date('d/m/Y',strtotime($e['date_fin'])):'—'?></td>
                <td><span class="badge <?= $bc ?>"><?= htmlspecialchars($bl) ?></span></td>
            </tr>
            <?php endwhile; else: ?>
            <tr class="empty-row"><td colspan="4">Aucun emprunt enregistré</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="table-wrap">
        <div class="table-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Mes dernières commandes
        </div>
        <table class="data-table">
            <thead><tr><th>N° commande</th><th>Montant</th><th>Statut</th></tr></thead>
            <tbody>
            <?php if ($hist_commandes && $hist_commandes->num_rows > 0):
                while ($c = $hist_commandes->fetch_assoc()):
                    $s  = $c['statut'] ?? '';
                    $bc = $s==='payee'?'badge-green':($s==='en_attente'?'badge-amber':($s==='annulee'?'badge-red':'badge-gray'));
                    $bl = $s==='payee'?'Payée':($s==='en_attente'?'En attente':($s==='annulee'?'Annulée':ucfirst($s)));
            ?>
            <tr>
                <td>#<?= str_pad($c['id_commande'],3,'0',STR_PAD_LEFT) ?></td>
                <td><?= number_format((float)$c['total'],2) ?> DA</td>
                <td><span class="badge <?= $bc ?>"><?= htmlspecialchars($bl) ?></span></td>
            </tr>
            <?php endwhile; else: ?>
            <tr class="empty-row"><td colspan="3">Aucune commande enregistrée</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <a href="library.php" class="back-link">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la bibliothèque
    </a>
</div>

<!-- ════════════════════════════════════════════
     PANEL : MA WISHLIST — correctement ici
════════════════════════════════════════════ -->
<div class="panel <?= $tab==='wishlist'?'active':'' ?>">

    <div class="section-lbl">
        Ma liste de souhaits (<?= $nb_wishlist ?> livre<?= $nb_wishlist > 1 ? 's' : '' ?>)
    </div>

    <?php if ($nb_wishlist === 0): ?>
        <div class="empty-state">
            <div class="empty-icon">🤍</div>
            <p>Votre liste de souhaits est vide.</p>
            <a href="/MEMOIR/client/library.php" class="btn-save">Explorer le catalogue</a>
        </div>

    <?php else: ?>
        <div class="wish-grid">
        <?php foreach ($wishlist_items as $w):
            $dispo     = $w['disponible_pour'] ?? 'both';
            $badge_cls = $dispo==='achat' ? 'badge-amber' : ($dispo==='emprunt' ? 'badge-green' : 'badge-gray');
            $badge_lbl = $dispo==='achat' ? 'ACHAT'       : ($dispo==='emprunt' ? 'EMPRUNT'    : 'LES DEUX');
        ?>
            <div class="wish-card">

                <!-- Couverture -->
                <div class="wish-cover">
                    <?php if (!empty($w['image_doc'])): ?>
                        <img src="/MEMOIR/uploads/<?= htmlspecialchars($w['image_doc']) ?>"
                             alt="<?= htmlspecialchars($w['titre']) ?>">
                    <?php else: ?>
                        <div class="wish-cover-placeholder">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#C4A46B" stroke-width="2" opacity="0.7"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        </div>
                    <?php endif; ?>
                    <span class="wish-type-badge <?= $badge_cls ?>"><?= $badge_lbl ?></span>
                </div>

                <!-- Infos -->
                <div class="wish-body">
                    <div class="wish-title"><?= htmlspecialchars($w['titre']) ?></div>

                    <?php if (!empty($w['auteur'])): ?>
                        <div class="wish-author"><?= htmlspecialchars($w['auteur']) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($w['prix']) && (float)$w['prix'] > 0): ?>
                        <div class="wish-price"><?= number_format((float)$w['prix'], 2) ?> DA</div>
                    <?php else: ?>
                        <div class="wish-price free">Gratuit</div>
                    <?php endif; ?>

                    <div class="wish-actions">
                        <!--
                            BOUTON VOIR :
                            Redirige vers library.php avec l'ID du livre
                            pour que l'utilisateur puisse l'emprunter ou l'acheter
                            depuis la page catalogue — PAS dcmnt.php
                        -->
                        <a href="/MEMOIR/client/library.php?id=<?= (int)$w['id_doc'] ?>"
                           class="wish-btn-voir">
                            Voir le livre
                        </a>

                        <!-- Supprimer de la wishlist -->
                        <a href="/MEMOIR/client/remove_wish.php?id=<?= (int)$w['id_wishlist'] ?>"
                           class="wish-btn-del"
                           onclick="return confirm('Retirer ce livre de votre liste de souhaits ?')"
                           title="Retirer">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14H6L5 6"/>
                                <path d="M10 11v6"/><path d="M14 11v6"/>
                                <path d="M9 6V4h6v2"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

        <div style="margin-top:16px">
            <a href="/MEMOIR/client/library.php" class="back-link">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                Continuer à explorer
            </a>
        </div>
    <?php endif; ?>

</div>
<?php endif; /* end role=client for wishlist+historique */ ?>

<!-- ════════════════════════════════════════════
     PANEL : COMMANDES RÉCENTES (admin)
════════════════════════════════════════════ -->
<?php if ($role === 'admin'): ?>
<div class="panel <?= $tab==='admin_orders'?'active':'' ?>">
    <div class="table-wrap">
        <div class="table-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Dernières commandes du site
        </div>
        <table class="data-table">
            <thead><tr><th>#</th><th>Client</th><th>Montant</th><th>Date</th><th>Statut</th></tr></thead>
            <tbody>
            <?php if ($last_orders && $last_orders->num_rows > 0):
                while ($o = $last_orders->fetch_assoc()):
                    $s  = $o['statut'] ?? '';
                    $bc = $s==='payee'?'badge-green':($s==='en_attente'?'badge-amber':($s==='annulee'?'badge-red':'badge-gray'));
                    $bl = $s==='payee'?'Payée':($s==='en_attente'?'En attente':($s==='annulee'?'Annulée':ucfirst($s)));
            ?>
            <tr>
                <td>#<?= str_pad($o['id_commande'],3,'0',STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars(trim(($o['firstname']??'').' '.($o['lastname']??''))) ?></td>
                <td><?= number_format((float)$o['total'],2) ?> DA</td>
                <td><?= !empty($o['date_commande'])?date('d/m/Y',strtotime($o['date_commande'])):'—'?></td>
                <td><span class="badge <?= $bc ?>"><?= htmlspecialchars($bl) ?></span></td>
            </tr>
            <?php endwhile; else: ?>
            <tr class="empty-row"><td colspan="5">Aucune commande</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <a href="/MEMOIR/admin/all_orders.php" style="font-size:11px;color:var(--gold);text-decoration:none;font-weight:600">Voir toutes les commandes →</a>
</div>
<?php endif; ?>

</div><!-- END .profile-page -->
</body>
</html>