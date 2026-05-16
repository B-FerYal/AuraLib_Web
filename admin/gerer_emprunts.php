<?php
session_start();
require_once "../includes/db.php";
require_once '../includes/head.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Auto-update retards
$conn->query("UPDATE emprunt SET statut = 'retard' 
              WHERE date_retour_prevue < CURDATE() 
              AND statut = 'acceptée' 
              AND date_fin IS NULL");

// Messages flash
$msg    = $_GET['msg'] ?? '';
$amende = (int)($_GET['amende'] ?? 0);

$flash_messages = [
    'accepted'       => ['type' => 'success', 'text' => 'Emprunt accepté — stock mis à jour.'],
    'refused'        => ['type' => 'warning', 'text' => 'Emprunt refusé.'],
    'returned'       => ['type' => 'success', 'text' => 'Livre retourné — stock restauré.' . ($amende > 0 ? " Amende : <strong>{$amende} DA</strong>" : '')],
    'no_stock'       => ['type' => 'danger',  'text' => 'Impossible d\'accepter : stock épuisé pour ce document.'],
    'invalid_status' => ['type' => 'warning', 'text' => 'Action non autorisée pour ce statut.'],
    'error'          => ['type' => 'danger',  'text' => 'Une erreur est survenue.'],
];

// Stats rapides
$stats = [];
foreach (['en attente','acceptée','retard','rendu','refusée'] as $s) {
    $r = $conn->query("SELECT COUNT(*) as n FROM emprunt WHERE statut = '$s'");
    $stats[$s] = (int)($r->fetch_assoc()['n'] ?? 0);
}

// Emprunts
$result = $conn->query("
    SELECT e.*, u.firstname, u.lastname, d.titre, d.exemplaires_disponibles
    FROM emprunt e 
    JOIN users u ON e.id_user = u.id 
    JOIN documents d ON e.id_doc = d.id_doc 
    ORDER BY 
        CASE 
            WHEN e.statut = 'en attente' THEN 1 
            WHEN e.statut = 'retard'     THEN 2 
            WHEN e.statut = 'acceptée'   THEN 3 
            ELSE 4 
        END, 
        e.id_emprunt DESC
");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<?php include '../includes/dark_init.php'; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AuraLib · Gestion des Emprunts</title>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<style>
/* ══ TOKENS ══ */
:root {
    --gold:        #C4A46B;
    --gold2:       #D4B47B;
    --gold-deep:   #A8884E;
    --gold-faint:  rgba(196,164,107,.09);
    --gold-border: rgba(196,164,107,.28);
    --amber:       #B8832A;
    --brown:       #7A5C3A;
    --page-bg:     #F2EDE3;
    --page-bg2:    #E8E0D0;
    --page-white:  #FDFAF5;
    --page-text:   #2A1F14;
    --page-muted:  #9A8C7E;
    --page-border: #D8CFC0;
    --danger:      #C0392B;
    --success:     #276749;
    --warning:     #92400E;
    --font-serif:  'EB Garamond', Georgia, serif;
    --font-ui:     'Plus Jakarta Sans', sans-serif;
    --nav-h:       62px;
    --radius:      16px;
    --shadow-sm:   0 3px 10px rgba(42,31,20,.08);
    --shadow-md:   0 8px 28px rgba(42,31,20,.12);
    --shadow-lg:   0 20px 55px rgba(42,31,20,.16);
    --shadow-gold: 0 6px 20px rgba(196,164,107,.25);
    --tr:          .25s cubic-bezier(.4,0,.2,1);
}
html.dark {
    --page-bg:     #100C07;
    --page-bg2:    #1A1308;
    --page-white:  #1E1610;
    --page-text:   #EDE5D4;
    --page-muted:  #9A8C7E;
    --page-border: #3A2E1E;
    --shadow-sm:   0 3px 10px rgba(0,0,0,.3);
    --shadow-md:   0 8px 28px rgba(0,0,0,.4);
    --shadow-lg:   0 20px 55px rgba(0,0,0,.55);
}
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
body {
    font-family: var(--font-ui);
    background: var(--page-bg);
    color: var(--page-text);
    min-height: 100vh;
    padding-top: var(--nav-h);
    transition: background .35s, color .35s;
}

@keyframes fadeUp {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes rowIn {
    from { opacity:0; transform:translateX(-10px); }
    to   { opacity:1; transform:translateX(0); }
}
@keyframes pulse-ring {
    0%   { box-shadow: 0 0 0 0 rgba(192,57,43,.45); }
    70%  { box-shadow: 0 0 0 7px rgba(192,57,43,0); }
    100% { box-shadow: 0 0 0 0 rgba(192,57,43,0); }
}

/* ══ PAGE HERO ══ */
.page-hero {
    background: linear-gradient(135deg, #1A0E05 0%, #2E1D08 55%, #1A0E05 100%);
    padding: 36px 5% 32px;
    position: relative; overflow: hidden;
}
.page-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse 60% 90% at 10% 50%, rgba(196,164,107,.11) 0%, transparent 65%);
    pointer-events: none;
}
.page-hero::after {
    content: '';
    position: absolute; bottom:0; left:0; right:0; height:1px;
    background: linear-gradient(90deg, transparent, rgba(196,164,107,.3), transparent);
}
.hero-inner {
    max-width: 1340px; margin: 0 auto;
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    flex-wrap: wrap;
    animation: fadeUp .5s ease both;
}
.hero-left { display: flex; flex-direction: column; gap: 6px; }
.hero-breadcrumb {
    display: flex; align-items: center; gap: 8px;
    font-size: 11px; color: rgba(196,164,107,.5); letter-spacing: .4px;
}
.hero-breadcrumb a {
    color: rgba(196,164,107,.5); text-decoration: none; transition: color var(--tr);
}
.hero-breadcrumb a:hover { color: var(--gold); }
.hero-breadcrumb i { font-size: 8px; }
.hero-title {
    font-family: var(--font-serif);
    font-size: clamp(26px, 4vw, 44px); font-weight: 700;
    color: #FDFAF5; line-height: 1.05; letter-spacing: -.3px;
}
.hero-title span {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold2) 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.hero-date {
    font-size: 11px; color: rgba(253,250,245,.4); letter-spacing: .5px;
}

/* Back button */
.btn-back {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px; border-radius: 50px;
    font-family: var(--font-ui); font-size: 12px; font-weight: 700;
    color: rgba(196,164,107,.8); letter-spacing: .3px;
    background: rgba(196,164,107,.1);
    backdrop-filter: blur(12px);
    border: 1.5px solid rgba(196,164,107,.25);
    text-decoration: none;
    transition: all var(--tr); flex-shrink: 0;
}
.btn-back:hover {
    background: rgba(196,164,107,.2);
    color: var(--gold2);
    border-color: rgba(196,164,107,.5);
    transform: translateY(-1px);
}

/* ══ STATS BAR ══ */
.stats-bar {
    max-width: 1340px; margin: 28px auto 0;
    padding: 0 5%;
    display: flex; gap: 14px; flex-wrap: wrap;
    animation: fadeUp .5s .1s ease both;
}
.stat-pill {
    display: flex; align-items: center; gap: 9px;
    padding: 10px 18px; border-radius: 50px;
    background: var(--page-white);
    border: 1.5px solid var(--page-border);
    box-shadow: var(--shadow-sm);
    flex-shrink: 0;
}
.stat-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.stat-label { font-size: 11px; color: var(--page-muted); font-weight: 500; }
.stat-num { font-family: var(--font-serif); font-size: 20px; font-weight: 700; color: var(--page-text); line-height: 1; }

/* ══ FLASH ══ */
.flash-wrap { max-width: 1340px; margin: 20px auto 0; padding: 0 5%; }
.flash {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px; border-radius: 14px;
    font-size: 13px; font-weight: 600;
    animation: fadeUp .4s ease both;
}
.flash i { font-size: 15px; flex-shrink: 0; }
.flash.success { background: rgba(39,103,73,.1); border: 1.5px solid rgba(39,103,73,.25); color: var(--success); }
.flash.warning { background: rgba(146,64,14,.09); border: 1.5px solid rgba(146,64,14,.22); color: var(--warning); }
.flash.danger  { background: rgba(192,57,43,.09); border: 1.5px solid rgba(192,57,43,.22); color: var(--danger); }
html.dark .flash.success { background: rgba(39,103,73,.18); }
html.dark .flash.warning { background: rgba(146,64,14,.18); }
html.dark .flash.danger  { background: rgba(192,57,43,.18); }

/* ══ MAIN TABLE WRAPPER ══ */
.table-wrap {
    max-width: 1340px; margin: 24px auto 60px;
    padding: 0 5%;
    animation: fadeUp .5s .15s ease both;
}
.table-card {
    background: var(--page-white);
    border-radius: 20px;
    border: 1px solid var(--page-border);
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

/* ══ TABLE ══ */
table { width: 100%; border-collapse: collapse; }
thead tr {
    background: linear-gradient(135deg, rgba(196,164,107,.07) 0%, rgba(122,92,58,.05) 100%);
    border-bottom: 1.5px solid var(--gold-border);
}
th {
    padding: 14px 16px;
    font-family: var(--font-ui); font-size: 10px; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    color: var(--gold-deep); text-align: left; white-space: nowrap;
}
html.dark th { color: var(--gold); }

tbody tr {
    border-bottom: 1px solid var(--page-border);
    transition: background var(--tr);
    animation: rowIn .35s ease both;
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: var(--gold-faint); }
tbody tr:nth-child(1) { animation-delay: .04s; }
tbody tr:nth-child(2) { animation-delay: .08s; }
tbody tr:nth-child(3) { animation-delay: .12s; }
tbody tr:nth-child(4) { animation-delay: .16s; }
tbody tr:nth-child(5) { animation-delay: .20s; }
tbody tr:nth-child(6) { animation-delay: .24s; }

td {
    padding: 16px 16px;
    font-size: 13px; color: var(--page-text);
    vertical-align: middle;
}
.td-id { font-size: 11px; color: var(--page-muted); font-weight: 600; letter-spacing: .5px; }

/* User + doc cell */
.user-name {
    font-weight: 700; font-size: 14px; color: var(--page-text);
    margin-bottom: 3px;
}
.user-book {
    display: flex; align-items: center; gap: 6px;
    font-size: 11px; color: var(--page-muted); line-height: 1.3;
}
.user-book i { color: var(--gold); font-size: 10px; flex-shrink: 0; }

/* Stock */
.stock-ok   { display:inline-flex; align-items:center; gap:5px; color:#276749; font-size:11px; font-weight:700; }
.stock-zero { display:inline-flex; align-items:center; gap:5px; color:var(--danger); font-size:11px; font-weight:700; }
.stock-ok i, .stock-zero i { font-size:10px; }

/* Dates */
.date-main  { font-size:13px; font-weight:600; color:var(--page-text); }
.date-late  { font-size:10px; color:var(--danger); font-weight:700; margin-top:2px; display:block; }

/* Amende */
.amende-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(184,131,42,.12);
    border: 1.5px solid rgba(184,131,42,.3);
    color: var(--amber); padding: 4px 10px; border-radius: 8px;
    font-size: 11px; font-weight: 700;
}
.dash { color: var(--page-border); font-size: 14px; }

/* ══ STATUS BADGES ══ */
.badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: 20px;
    font-size: 10px; font-weight: 700; letter-spacing: .6px;
    text-transform: uppercase; white-space: nowrap;
}
.badge::before { content:''; width:6px; height:6px; border-radius:50%; flex-shrink:0; }

.s-en-attente {
    background: rgba(234,179,8,.1); border: 1.5px solid rgba(234,179,8,.3); color: #92400E;
}
.s-en-attente::before { background:#F59E0B; }

.s-acceptee {
    background: rgba(39,103,73,.1); border: 1.5px solid rgba(39,103,73,.28); color: var(--success);
}
.s-acceptee::before { background: #276749; }

.s-retard {
    background: rgba(192,57,43,.1); border: 1.5px solid rgba(192,57,43,.3); color: var(--danger);
    animation: pulse-ring 2s infinite;
}
.s-retard::before { background: var(--danger); }

.s-rendu {
    background: rgba(154,140,126,.1); border: 1.5px solid rgba(154,140,126,.25); color: var(--page-muted);
}
.s-rendu::before { background: var(--page-muted); }

.s-refusee {
    background: rgba(136,14,79,.08); border: 1.5px solid rgba(136,14,79,.2); color: #880E4F;
}
.s-refusee::before { background: #880E4F; }

/* ══ ACTION BUTTONS ══ */
.actions-cell { display:flex; align-items:center; gap:7px; flex-wrap:wrap; }

.btn-action {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 50px;
    font-family: var(--font-ui); font-size: 11px; font-weight: 700;
    text-decoration: none; border: none; cursor: pointer;
    transition: all var(--tr); letter-spacing: .2px; white-space: nowrap;
}
.btn-action i { font-size: 10px; }

.btn-approve {
    background: linear-gradient(135deg, #1A0E05 0%, #2E1D08 100%);
    color: var(--gold2);
    border: 1.5px solid rgba(196,164,107,.3);
    box-shadow: 0 4px 14px rgba(42,31,20,.25);
}
.btn-approve:hover {
    background: linear-gradient(135deg, #2E1D08 0%, #3E2A10 100%);
    border-color: rgba(196,164,107,.55);
    color: var(--gold2);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(42,31,20,.35);
}
.btn-approve.disabled {
    opacity: .38; pointer-events: none; cursor: not-allowed;
}

.btn-refuse {
    background: rgba(136,14,79,.07);
    color: #880E4F;
    border: 1.5px solid rgba(136,14,79,.22);
}
.btn-refuse:hover {
    background: rgba(136,14,79,.15);
    transform: translateY(-2px);
}

.btn-return {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-deep) 100%);
    color: #1A0E05;
    border: 1.5px solid transparent;
    box-shadow: var(--shadow-gold);
    font-weight: 800;
}
.btn-return:hover {
    background: linear-gradient(135deg, var(--gold2) 0%, var(--gold) 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(196,164,107,.4);
}

/* ══ EMPTY STATE ══ */
.empty-row td {
    text-align: center; padding: 70px 20px;
}
.empty-icon { font-size: 40px; color: var(--page-border); margin-bottom: 14px; }
.empty-row h3 { font-family: var(--font-serif); font-size:22px; color:var(--page-muted); }
</style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<!-- ══ HERO ══ -->
<div class="page-hero">
    <div class="hero-inner">
        <div class="hero-left">
            <div class="hero-breadcrumb">
                <a href="/MEMOIR/admin/admin_dashboard.php">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
                <i class="fa-solid fa-chevron-right"></i>
                <span>Emprunts</span>
            </div>
            <h1 class="hero-title">Gestion des <span>Emprunts</span></h1>
            <span class="hero-date">Aujourd'hui · <?= date('d F Y') ?></span>
        </div>
        <a href="/MEMOIR/admin/admin_dashboard.php" class="btn-back">
            <i class="fa-solid fa-arrow-left" style="font-size:10px"></i>
            Retour au Dashboard
        </a>
    </div>
</div>

<!-- ══ STATS BAR ══ -->
<div class="stats-bar">
    <div class="stat-pill">
        <span class="stat-dot" style="background:#F59E0B"></span>
        <span class="stat-label">En attente</span>
        <span class="stat-num"><?= $stats['en attente'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:#276749"></span>
        <span class="stat-label">Acceptés</span>
        <span class="stat-num"><?= $stats['acceptée'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:var(--danger)"></span>
        <span class="stat-label">En retard</span>
        <span class="stat-num"><?= $stats['retard'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:var(--page-muted)"></span>
        <span class="stat-label">Rendus</span>
        <span class="stat-num"><?= $stats['rendu'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:#880E4F"></span>
        <span class="stat-label">Refusés</span>
        <span class="stat-num"><?= $stats['refusée'] ?></span>
    </div>
</div>

<!-- ══ FLASH ══ -->
<?php if ($msg && isset($flash_messages[$msg])): $f = $flash_messages[$msg]; ?>
<div class="flash-wrap">
    <div class="flash <?= $f['type'] ?>">
        <?php if($f['type']==='success'): ?><i class="fa-solid fa-circle-check"></i>
        <?php elseif($f['type']==='warning'): ?><i class="fa-solid fa-triangle-exclamation"></i>
        <?php else: ?><i class="fa-solid fa-circle-xmark"></i><?php endif; ?>
        <?= $f['text'] ?>
    </div>
</div>
<?php endif; ?>

<!-- ══ TABLE ══ -->
<div class="table-wrap">
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Lecteur &amp; Document</th>
                    <th>Stock</th>
                    <th>Demande</th>
                    <th>Retour prévu</th>
                    <th>Amende</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            if (empty($rows)): ?>
            <tr class="empty-row">
                <td colspan="8">
                    <div class="empty-icon"><i class="fa-regular fa-folder-open"></i></div>
                    <h3>Aucun emprunt enregistré</h3>
                </td>
            </tr>
            <?php else:
            foreach ($rows as $row):
                $statut_css = match($row['statut']) {
                    'en attente' => 's-en-attente',
                    'acceptée'   => 's-acceptee',
                    'retard'     => 's-retard',
                    'rendu'      => 's-rendu',
                    'refusée'    => 's-refusee',
                    default      => 's-rendu'
                };
                $jours_retard = 0;
                if ($row['statut'] === 'retard' && !empty($row['date_retour_prevue'])) {
                    $jours_retard = (int)(new DateTime())->diff(new DateTime($row['date_retour_prevue']))->days;
                }
            ?>
            <tr>
                <td class="td-id">#<?= str_pad($row['id_emprunt'], 3, '0', STR_PAD_LEFT) ?></td>

                <td>
                    <div class="user-name"><?= htmlspecialchars($row['firstname'].' '.$row['lastname']) ?></div>
                    <div class="user-book">
                        <i class="fa-solid fa-book"></i>
                        <?= htmlspecialchars($row['titre']) ?>
                    </div>
                </td>

                <td>
                    <?php if ((int)$row['exemplaires_disponibles'] > 0): ?>
                        <span class="stock-ok">
                            <i class="fa-solid fa-circle-check"></i>
                            <?= $row['exemplaires_disponibles'] ?> dispo
                        </span>
                    <?php else: ?>
                        <span class="stock-zero">
                            <i class="fa-solid fa-circle-xmark"></i>
                            Épuisé
                        </span>
                    <?php endif; ?>
                </td>

                <td>
                    <span class="date-main">
                        <?= $row['date_debut'] ? date('d/m/Y', strtotime($row['date_debut'])) : '—' ?>
                    </span>
                </td>

                <td>
                    <?php if ($row['date_retour_prevue']): ?>
                        <span class="date-main" style="<?= $row['statut']==='retard' ? 'color:var(--danger)' : '' ?>">
                            <?= date('d/m/Y', strtotime($row['date_retour_prevue'])) ?>
                        </span>
                        <?php if ($row['statut'] === 'retard'): ?>
                            <span class="date-late">
                                <i class="fa-solid fa-clock" style="font-size:9px"></i>
                                <?= $jours_retard ?> j de retard
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="dash">—</span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if ($row['amende'] > 0): ?>
                        <span class="amende-badge">
                            <i class="fa-solid fa-coins" style="font-size:10px"></i>
                            <?= number_format($row['amende'], 0) ?> DA
                        </span>
                    <?php else: ?>
                        <span class="dash">—</span>
                    <?php endif; ?>
                </td>

                <td>
                    <span class="badge <?= $statut_css ?>">
                        <?= ucfirst($row['statut']) ?>
                    </span>
                </td>

                <td>
                    <div class="actions-cell">
                    <?php if ($row['statut'] === 'en attente'): ?>
                        <a href="action_emprunts.php?id=<?= $row['id_emprunt'] ?>&action=accepter"
                           class="btn-action btn-approve <?= (int)$row['exemplaires_disponibles'] <= 0 ? 'disabled' : '' ?>"
                           title="<?= (int)$row['exemplaires_disponibles'] <= 0 ? 'Stock épuisé' : 'Accepter cet emprunt' ?>">
                            <i class="fa-solid fa-check"></i> Accepter
                        </a>
                        <a href="action_emprunts.php?id=<?= $row['id_emprunt'] ?>&action=refuser"
                           class="btn-action btn-refuse"
                           onclick="return confirm('Refuser cet emprunt ?')">
                            <i class="fa-solid fa-xmark"></i> Refuser
                        </a>

                    <?php elseif (in_array($row['statut'], ['acceptée','retard'])): ?>
                        <a href="action_emprunts.php?id=<?= $row['id_emprunt'] ?>&action=rendre"
                           class="btn-action btn-return"
                           onclick="return confirm('Confirmer le retour de ce livre ?')">
                            <i class="fa-solid fa-rotate-left"></i> Marquer Rendu
                        </a>

                    <?php else: ?>
                        <span class="dash">—</span>
                    <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>