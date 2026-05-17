<?php
require_once __DIR__ . "/../includes/db.php";
require_once '../includes/head.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$current_admin_id = intval($_SESSION['id_user']);

if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $user_id    = intval($_GET['id']);
    $new_status = ($_GET['toggle_status'] == 'active') ? 'suspended' : 'active';
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'utilisateur'");
    $stmt->bind_param("si", $new_status, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: users.php");
    exit;
}

$query  = "SELECT id, firstname, lastname, email, status, role FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $query);

/* ── Stats ── */
$stats     = ['total'=>0,'active'=>0,'suspended'=>0,'admin'=>0];
$all_users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $all_users[] = $row;
    $stats['total']++;
    if ($row['status'] === 'active')    $stats['active']++;
    if ($row['status'] === 'suspended') $stats['suspended']++;
    if ($row['role']   === 'admin')     $stats['admin']++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<?php include '../includes/dark_init.php'; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AuraLib · Gestion des Lecteurs</title>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    --info:        #1D4ED8;
    --font-serif:  'EB Garamond', Georgia, serif;
    --font-ui:     'Plus Jakarta Sans', sans-serif;
    --nav-h:       62px;
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

@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
@keyframes rowIn  { from{opacity:0;transform:translateX(-8px)}  to{opacity:1;transform:translateX(0)} }

/* ══ HERO ══ */
.page-hero {
    background: linear-gradient(135deg,#1A0E05 0%,#2E1D08 55%,#1A0E05 100%);
    padding: 36px 5% 32px;
    position: relative; overflow: hidden;
}
.page-hero::before {
    content:''; position:absolute; inset:0;
    background: radial-gradient(ellipse 60% 90% at 10% 50%,rgba(196,164,107,.11) 0%,transparent 65%);
    pointer-events:none;
}
.page-hero::after {
    content:''; position:absolute; bottom:0; left:0; right:0; height:1px;
    background: linear-gradient(90deg,transparent,rgba(196,164,107,.3),transparent);
}
.hero-inner {
    max-width:1340px; margin:0 auto;
    display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap;
    animation: fadeUp .5s ease both;
}
.hero-left { display:flex; flex-direction:column; gap:6px; }
.hero-breadcrumb {
    display:flex; align-items:center; gap:8px;
    font-size:11px; color:rgba(196,164,107,.5); letter-spacing:.4px;
}
.hero-breadcrumb a { color:rgba(196,164,107,.5); text-decoration:none; transition:color var(--tr); }
.hero-breadcrumb a:hover { color:var(--gold); }
.hero-breadcrumb i { font-size:8px; }
.hero-title {
    font-family:var(--font-serif);
    font-size:clamp(26px,4vw,44px); font-weight:700;
    color:#FDFAF5; line-height:1.05; letter-spacing:-.3px;
}
.hero-title span {
    background:linear-gradient(135deg,var(--gold) 0%,var(--gold2) 100%);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.hero-date { font-size:11px; color:rgba(253,250,245,.4); letter-spacing:.5px; }

.btn-back {
    display:inline-flex; align-items:center; gap:8px;
    padding:10px 20px; border-radius:50px;
    font-family:var(--font-ui); font-size:12px; font-weight:700;
    color:rgba(196,164,107,.8); letter-spacing:.3px;
    background:rgba(196,164,107,.1); backdrop-filter:blur(12px);
    border:1.5px solid rgba(196,164,107,.25);
    text-decoration:none; transition:all var(--tr);
}
.btn-back:hover {
    background:rgba(196,164,107,.2); color:var(--gold2);
    border-color:rgba(196,164,107,.5); transform:translateY(-1px);
}

/* ══ SEARCH BAR ══ */
.search-wrap {
    max-width:1340px; margin:24px auto 0; padding:0 5%;
    animation: fadeUp .5s .08s ease both;
}
.search-inner {
    display:flex; align-items:center; gap:10px;
}
.search-input-box {
    flex:1; max-width:420px;
    display:flex; align-items:center;
    background:var(--page-white); border:1.5px solid var(--page-border);
    border-radius:50px; padding:0 6px 0 40px; position:relative;
    transition:border-color var(--tr), box-shadow var(--tr);
}
.search-input-box:focus-within {
    border-color:var(--gold-border);
    box-shadow:0 0 0 3px rgba(196,164,107,.1);
}
.search-input-box i {
    position:absolute; left:14px; top:50%; transform:translateY(-50%);
    color:var(--page-muted); font-size:12px; pointer-events:none;
}
.search-input-box input {
    flex:1; background:transparent; border:none; outline:none;
    font-family:var(--font-ui); font-size:13px; color:var(--page-text);
    padding:10px 0; min-width:0;
}
.search-input-box input::placeholder { color:var(--page-muted); opacity:.65; }

/* filter pills */
.filter-pills { display:flex; gap:8px; flex-wrap:wrap; }
.fpill {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 14px; border-radius:50px;
    font-family:var(--font-ui); font-size:11px; font-weight:600;
    border:1.5px solid var(--page-border);
    background:var(--page-white); color:var(--page-muted);
    cursor:pointer; transition:all var(--tr); user-select:none;
}
.fpill:hover { border-color:var(--gold); color:var(--gold-deep); background:var(--gold-faint); }
.fpill.active { background:var(--gold); border-color:var(--gold); color:#2C1F0E; font-weight:700; box-shadow:var(--shadow-gold); }
.fpill-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }

/* ══ STATS BAR ══ */
.stats-bar {
    max-width:1340px; margin:16px auto 0; padding:0 5%;
    display:flex; gap:12px; flex-wrap:wrap;
    animation: fadeUp .5s .12s ease both;
}
.stat-pill {
    display:flex; align-items:center; gap:9px;
    padding:10px 18px; border-radius:50px;
    background:var(--page-white); border:1.5px solid var(--page-border);
    box-shadow:var(--shadow-sm);
}
.stat-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.stat-label { font-size:11px; color:var(--page-muted); font-weight:500; }
.stat-num { font-family:var(--font-serif); font-size:20px; font-weight:700; color:var(--page-text); line-height:1; }

/* ══ TABLE ══ */
.table-wrap {
    max-width:1340px; margin:20px auto 60px; padding:0 5%;
    animation: fadeUp .5s .18s ease both;
}
.table-card {
    background:var(--page-white);
    border-radius:20px; border:1px solid var(--page-border);
    overflow:hidden; box-shadow:var(--shadow-md);
}

table { width:100%; border-collapse:collapse; }
thead tr {
    background:linear-gradient(135deg,rgba(196,164,107,.07) 0%,rgba(122,92,58,.05) 100%);
    border-bottom:1.5px solid var(--gold-border);
}
th {
    padding:14px 16px;
    font-size:10px; font-weight:700; letter-spacing:2px; text-transform:uppercase;
    color:var(--gold-deep); text-align:left; white-space:nowrap;
}
html.dark th { color:var(--gold); }

tbody tr {
    border-bottom:1px solid var(--page-border);
    transition:background var(--tr);
}
tbody tr:last-child { border-bottom:none; }
tbody tr:hover td { background:var(--gold-faint); }
tbody tr.row-me td { background:rgba(196,164,107,.06); }

/* row staggered animation */
<?php for($i=1;$i<=30;$i++): ?>
tbody tr:nth-child(<?=$i?>) { animation:rowIn .35s <?=round(($i-1)*.035,3)?>s ease both; }
<?php endfor; ?>

td {
    padding:15px 16px; font-size:13px; color:var(--page-text); vertical-align:middle;
}

/* avatar cell */
.user-cell { display:flex; align-items:center; gap:12px; }
.user-avatar {
    width:38px; height:38px; border-radius:50%; flex-shrink:0;
    background:linear-gradient(135deg,var(--gold) 0%,var(--gold-deep) 100%);
    display:flex; align-items:center; justify-content:center;
    font-family:var(--font-serif); font-size:16px; font-weight:700; color:#1A0E05;
    box-shadow:0 3px 10px rgba(196,164,107,.3);
}
.user-avatar.admin-av {
    background:linear-gradient(135deg,#1A0E05 0%,#3E2A10 100%);
    color:var(--gold2); box-shadow:0 3px 10px rgba(42,31,20,.35);
}
.user-meta { display:flex; flex-direction:column; gap:2px; }
.user-name { font-weight:700; font-size:14px; color:var(--page-text); }
.user-me-tag {
    display:inline-flex; align-items:center; gap:4px;
    font-size:9px; font-weight:700; letter-spacing:1px; text-transform:uppercase;
    color:var(--gold-deep); background:var(--gold-faint);
    border:1px solid var(--gold-border); border-radius:20px; padding:1px 7px;
}

/* id tag */
.id-tag {
    font-family:'Monaco','Consolas',monospace; font-size:11px; font-weight:700;
    color:var(--gold-deep); background:var(--gold-faint);
    border:1px solid var(--gold-border); padding:3px 9px; border-radius:7px;
}
html.dark .id-tag { color:var(--gold); }

/* email */
.email-cell { font-size:12px; color:var(--page-muted); }

/* status badge */
.status-badge {
    display:inline-flex; align-items:center; gap:6px;
    padding:5px 12px; border-radius:20px;
    font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase;
}
.status-badge::before { content:''; width:6px; height:6px; border-radius:50%; flex-shrink:0; }
.s-active {
    background:rgba(39,103,73,.1); border:1.5px solid rgba(39,103,73,.28); color:var(--success);
}
.s-active::before { background:var(--success); }
.s-suspended {
    background:rgba(192,57,43,.08); border:1.5px solid rgba(192,57,43,.22); color:var(--danger);
}
.s-suspended::before { background:var(--danger); }
html.dark .s-active    { background:rgba(39,103,73,.18); }
html.dark .s-suspended { background:rgba(192,57,43,.14); }

/* role badge */
.role-badge {
    display:inline-flex; align-items:center; gap:6px;
    padding:5px 12px; border-radius:20px;
    font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase;
}
.role-badge i { font-size:9px; }
.role-admin {
    background:rgba(42,31,20,.08); border:1.5px solid rgba(42,31,20,.2); color:var(--page-text);
}
html.dark .role-admin { background:rgba(196,164,107,.12); border-color:rgba(196,164,107,.25); color:var(--gold); }
.role-utilisateur {
    background:rgba(29,78,216,.07); border:1.5px solid rgba(29,78,216,.2); color:#1D4ED8;
}
html.dark .role-utilisateur { background:rgba(29,78,216,.14); border-color:rgba(29,78,216,.3); color:#7EB3E8; }

/* ══ ACTION BUTTONS ══ */
.actions-cell { display:flex; gap:7px; flex-wrap:wrap; }

.btn-action {
    display:inline-flex; align-items:center; gap:6px;
    padding:8px 15px; border-radius:50px;
    font-family:var(--font-ui); font-size:11px; font-weight:700;
    border:none; cursor:pointer; transition:all var(--tr);
    letter-spacing:.2px; white-space:nowrap;
}
.btn-action i { font-size:10px; }

.btn-toggle-suspend {
    background:rgba(192,57,43,.08); color:var(--danger);
    border:1.5px solid rgba(192,57,43,.22);
}
.btn-toggle-suspend:hover { background:var(--danger); color:#fff; transform:translateY(-2px); box-shadow:0 6px 18px rgba(192,57,43,.35); }

.btn-toggle-activate {
    background:rgba(39,103,73,.08); color:var(--success);
    border:1.5px solid rgba(39,103,73,.25);
}
.btn-toggle-activate:hover { background:var(--success); color:#fff; transform:translateY(-2px); box-shadow:0 6px 18px rgba(39,103,73,.35); }

.btn-role-change {
    background:linear-gradient(135deg,#1A0E05 0%,#2E1D08 100%);
    color:var(--gold2); border:1.5px solid rgba(196,164,107,.3);
    box-shadow:0 3px 10px rgba(42,31,20,.2);
}
.btn-role-change:hover {
    border-color:rgba(196,164,107,.55); color:var(--gold2);
    transform:translateY(-2px); box-shadow:0 8px 22px rgba(42,31,20,.35);
}

.btn-disabled {
    display:inline-flex; align-items:center; gap:6px;
    padding:8px 15px; border-radius:50px;
    font-family:var(--font-ui); font-size:11px; font-weight:700;
    border:1.5px solid var(--page-border); background:transparent;
    color:var(--page-muted); cursor:not-allowed; opacity:.45;
    letter-spacing:.2px;
}

/* no results */
.no-results {
    text-align:center; padding:60px 20px;
    font-size:14px; color:var(--page-muted); display:none;
}
.no-results i { font-size:36px; color:var(--page-border); display:block; margin-bottom:12px; }

/* SweetAlert2 gold theme */
.swal2-styled.swal2-confirm { background:var(--gold) !important; color:#1A0E05 !important; }
.swal2-styled.swal2-cancel  { background:rgba(42,31,20,.08) !important; color:var(--page-text) !important; border:1.5px solid var(--page-border) !important; }
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
                <span>Lecteurs</span>
            </div>
            <h1 class="hero-title">Gestion des <span>Lecteurs</span></h1>
            <span class="hero-date">Aujourd'hui · <?= date('d F Y') ?></span>
        </div>
        <a href="/MEMOIR/admin/admin_dashboard.php" class="btn-back">
            <i class="fa-solid fa-arrow-left" style="font-size:10px"></i>
            Retour au Dashboard
        </a>
    </div>
</div>

<!-- ══ SEARCH + FILTERS ══ -->
<div class="search-wrap">
    <div class="search-inner">
        <div class="search-input-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Nom, email, ID…" oninput="filterTable()">
        </div>
        <div class="filter-pills">
            <span class="filter-label" style="font-size:9px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--page-muted);align-self:center">Filtrer :</span>
            <button class="fpill active" data-filter="all" onclick="setFilter('all',this)">
                <span class="fpill-dot" style="background:var(--page-muted)"></span> Tous
            </button>
            <button class="fpill" data-filter="active" onclick="setFilter('active',this)">
                <span class="fpill-dot" style="background:var(--success)"></span> Actifs
            </button>
            <button class="fpill" data-filter="suspended" onclick="setFilter('suspended',this)">
                <span class="fpill-dot" style="background:var(--danger)"></span> Suspendus
            </button>
            <button class="fpill" data-filter="admin" onclick="setFilter('admin',this)">
                <span class="fpill-dot" style="background:var(--gold)"></span> Admins
            </button>
        </div>
    </div>
</div>

<!-- ══ STATS BAR ══ -->
<div class="stats-bar">
    <div class="stat-pill">
        <span class="stat-dot" style="background:var(--gold)"></span>
        <span class="stat-label">Total</span>
        <span class="stat-num"><?= $stats['total'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:var(--success)"></span>
        <span class="stat-label">Actifs</span>
        <span class="stat-num"><?= $stats['active'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:var(--danger)"></span>
        <span class="stat-label">Suspendus</span>
        <span class="stat-num"><?= $stats['suspended'] ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:#1A0E05"></span>
        <span class="stat-label">Admins</span>
        <span class="stat-num"><?= $stats['admin'] ?></span>
    </div>
</div>

<!-- ══ TABLE ══ -->
<div class="table-wrap">
    <div class="table-card">
        <table id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Lecteur</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Rôle</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($all_users as $row):
                $uid       = intval($row['id']);
                $fullname  = htmlspecialchars($row['firstname'].' '.$row['lastname']);
                $firstname = htmlspecialchars($row['firstname']);
                $email     = htmlspecialchars($row['email']);
                $status    = $row['status'];
                $role      = !empty($row['role']) ? $row['role'] : 'utilisateur';
                $newRole   = ($role === 'admin') ? 'utilisateur' : 'admin';
                $newRoleLabel = ($newRole === 'admin') ? 'Admin' : 'Lecteur';
                $isMe      = ($uid === $current_admin_id);
                $initials  = strtoupper(substr($row['firstname'],0,1).substr($row['lastname'],0,1));
            ?>
            <tr id="row-<?= $uid ?>"
                class="<?= $isMe?'row-me':'' ?>"
                data-status="<?= $status ?>"
                data-role="<?= $role ?>"
                data-name="<?= strtolower($fullname) ?>"
                data-email="<?= strtolower($email) ?>">

                <!-- ID -->
                <td><span class="id-tag"><?= sprintf('%03d', $uid) ?></span></td>

                <!-- Lecteur -->
                <td>
                    <div class="user-cell">
                        <div class="user-avatar <?= $role==='admin'?'admin-av':'' ?>">
                            <?= $initials ?>
                        </div>
                        <div class="user-meta">
                            <span class="user-name"><?= $fullname ?></span>
                            <?php if ($isMe): ?>
                            <span class="user-me-tag">
                                <i class="fa-solid fa-user" style="font-size:8px"></i> Mon compte
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>

                <!-- Email -->
                <td class="email-cell"><?= $email ?></td>

                <!-- Statut -->
                <td>
                    <span class="status-badge <?= $status==='active'?'s-active':'s-suspended' ?>">
                        <?= $status==='active' ? 'Actif' : 'Suspendu' ?>
                    </span>
                </td>

                <!-- Rôle -->
                <td>
                    <span class="role-badge role-<?= $role ?>" id="role-badge-<?= $uid ?>">
                        <?php if($role==='admin'): ?>
                            <i class="fa-solid fa-shield-halved"></i> Admin
                        <?php else: ?>
                            <i class="fa-solid fa-user"></i> Lecteur
                        <?php endif; ?>
                    </span>
                </td>

                <!-- Actions -->
                <td>
                    <div class="actions-cell">
                    <?php if (!$isMe): ?>
                        <!-- Toggle statut -->
                        <?php if ($status === 'active'): ?>
                        <button class="btn-action btn-toggle-suspend"
                            onclick="confirmStatusUpdate('<?= $uid ?>','<?= $status ?>','<?= $firstname ?>')">
                            <i class="fa-solid fa-ban"></i> Suspendre
                        </button>
                        <?php else: ?>
                        <button class="btn-action btn-toggle-activate"
                            onclick="confirmStatusUpdate('<?= $uid ?>','<?= $status ?>','<?= $firstname ?>')">
                            <i class="fa-solid fa-circle-check"></i> Activer
                        </button>
                        <?php endif; ?>

                        <!-- Changer rôle -->
                        <button class="btn-action btn-role-change"
                            id="role-btn-<?= $uid ?>"
                            onclick="confirmRoleChange(<?= $uid ?>,'<?= $firstname ?>','<?= $role ?>','<?= $newRole ?>','<?= $newRoleLabel ?>')">
                            <i class="fa-solid fa-arrows-rotate"></i> → <?= $newRoleLabel ?>
                        </button>
                    <?php else: ?>
                        <button class="btn-disabled" disabled><i class="fa-solid fa-lock" style="font-size:9px"></i> Mon compte</button>
                    <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="no-results" id="noResults">
            <i class="fa-regular fa-face-frown"></i>
            Aucun lecteur trouvé pour cette recherche.
        </div>
    </div>
</div>

<script>
/* ══ FILTER & SEARCH ══ */
let currentFilter = 'all';

function setFilter(val, btn) {
    currentFilter = val;
    document.querySelectorAll('.fpill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    filterTable();
}

function filterTable() {
    const q    = document.getElementById('searchInput').value.toLowerCase().trim();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    let visible = 0;

    rows.forEach(row => {
        const name   = row.dataset.name  || '';
        const email  = row.dataset.email || '';
        const status = row.dataset.status || '';
        const role   = row.dataset.role  || '';

        const matchSearch = !q || name.includes(q) || email.includes(q);
        const matchFilter =
            currentFilter === 'all'       ? true :
            currentFilter === 'active'    ? status === 'active' :
            currentFilter === 'suspended' ? status === 'suspended' :
            currentFilter === 'admin'     ? role   === 'admin' : true;

        const show = matchSearch && matchFilter;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    document.getElementById('noResults').style.display = visible === 0 ? 'block' : 'none';
}

/* ══ ROLE CHANGE ══ */
function confirmRoleChange(userId, userName, currentRole, newRole, newRoleLabel) {
    const icon  = newRole === 'admin' ? '🛡️' : '👤';
    Swal.fire({
        title: 'Changer le rôle ?',
        html: `<p style="font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;color:#2A1F14">
                 Changer le rôle de <strong>${userName}</strong> en
                 <strong style="color:#A8884E">${icon} ${newRoleLabel}</strong> ?
               </p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Oui, confirmer',
        cancelButtonText: 'Annuler',
        reverseButtons: true,
        customClass: { popup:'swal-aura' }
    }).then(result => {
        if (!result.isConfirmed) return;
        const fd = new FormData();
        fd.append('user_id', userId);
        fd.append('new_role', newRole);
        fetch('update_role.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('role-badge-'+userId);
                const btn   = document.getElementById('role-btn-'+userId);
                const isAdmin = newRole === 'admin';

                badge.className = 'role-badge role-'+newRole;
                badge.innerHTML = isAdmin
                    ? '<i class="fa-solid fa-shield-halved"></i> Admin'
                    : '<i class="fa-solid fa-user"></i> Lecteur';

                const next      = isAdmin ? 'utilisateur' : 'admin';
                const nextLabel = isAdmin ? 'Lecteur' : 'Admin';
                btn.innerHTML = `<i class="fa-solid fa-arrows-rotate"></i> → ${nextLabel}`;
                btn.setAttribute('onclick',
                    `confirmRoleChange(${userId},'${userName}','${newRole}','${next}','${nextLabel}')`
                );

                // Update data attrs for filter
                document.getElementById('row-'+userId).dataset.role = newRole;

                Swal.fire({ title:'Rôle mis à jour !', icon:'success', timer:1800, showConfirmButton:false });
            } else {
                Swal.fire('Erreur', data.message || 'Une erreur est survenue.', 'error');
            }
        });
    });
}

/* ══ STATUS TOGGLE ══ */
function confirmStatusUpdate(userId, currentStatus, userName) {
    const isSuspending = currentStatus === 'active';
    Swal.fire({
        title: isSuspending ? 'Suspendre ce compte ?' : 'Réactiver ce compte ?',
        html: `<p style="font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;color:#2A1F14">
                 ${isSuspending
                   ? `Voulez-vous suspendre le compte de <strong>${userName}</strong> ?<br>
                      <small style="color:#9A8C7E">Le lecteur ne pourra plus se connecter.</small>`
                   : `Voulez-vous réactiver le compte de <strong>${userName}</strong> ?`}
               </p>`,
        icon: isSuspending ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: isSuspending ? '🚫 Suspendre' : '✅ Réactiver',
        cancelButtonText: 'Annuler',
        reverseButtons: true
    }).then(result => {
        if (result.isConfirmed) {
            window.location.href = `users.php?toggle_status=${currentStatus}&id=${userId}`;
        }
    });
}
</script>
</body>
</html>