<?php
// ══════════════════════════════════════════════════════════════════
//  AuraLib · admin/notifications.php
// ══════════════════════════════════════════════════════════════════
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "../includes/db.php";

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}
if (($_SESSION['role'] ?? 'client') !== 'admin') {
    header("Location: ../client/notifications.php");
    exit;
}

$id_user = (int)$_SESSION['id_user'];

// ── Include header first to get $lang ────────────────────────────
include "../includes/header.php";
include_once '../includes/languages.php';

// ── نصوص الصفحة حسب اللغة ──────────────────────────────────────
$pg = [
    'fr' => [
        'page_title'      => 'AuraLib Admin · Notifications',
        'eyebrow'         => 'Notifications',
        'hero_title'      => 'Vos',
        'hero_span'       => 'Alertes',
        'admin_badge'     => 'Admin',
        'btn_back'        => 'Retour au Dashboard',
        'non_lues'        => 'non lues',
        'filter_all'      => 'Toutes',
        'filter_unread'   => 'Non lues',
        'mark_all'        => 'Tout marquer comme lu',
        'delete_all'      => 'Tout supprimer',
        'confirm_del_all' => 'Supprimer toutes les notifications ?',
        'confirm_del_one' => 'Supprimer cette notification ?',
        'details'         => 'Voir les détails',
        'mark_read'       => 'Marquer comme lu',
        'delete'          => 'Supprimer',
        'empty_title'     => 'Aucune notification',
        'empty_sub'       => 'Vos alertes système apparaîtront ici.',
        'sep_read'        => 'Lu',
        'time_instant'    => "à l'instant",
        'time_ago'        => 'il y a',
        'time_d'          => 'j',
        'time_h'          => 'h',
        'time_m'          => 'min',
        'date_fmt'        => 'd/m/Y à H:i',
        // Auto-generated late loan notification texts
        'notif_late_title'=> "⚠️ Retard · Emprunt #",
        'notif_late_msg1' => " a dépassé la date de retour du document « ",
        'notif_late_msg2' => " ».\nRetard : ",
        'notif_late_msg3' => " jour(s) — Amende : ",
        'notif_late_msg4' => " DA.\nVeuillez prendre les mesures nécessaires.",
    ],
    'en' => [
        'page_title'      => 'AuraLib Admin · Notifications',
        'eyebrow'         => 'Notifications',
        'hero_title'      => 'Your',
        'hero_span'       => 'Alerts',
        'admin_badge'     => 'Admin',
        'btn_back'        => 'Back to Dashboard',
        'non_lues'        => 'unread',
        'filter_all'      => 'All',
        'filter_unread'   => 'Unread',
        'mark_all'        => 'Mark all as read',
        'delete_all'      => 'Delete all',
        'confirm_del_all' => 'Delete all notifications?',
        'confirm_del_one' => 'Delete this notification?',
        'details'         => 'View details',
        'mark_read'       => 'Mark as read',
        'delete'          => 'Delete',
        'empty_title'     => 'No notifications',
        'empty_sub'       => 'Your system alerts will appear here.',
        'sep_read'        => 'Read',
        'time_instant'    => 'just now',
        'time_ago'        => '',
        'time_d'          => 'd ago',
        'time_h'          => 'h ago',
        'time_m'          => 'min ago',
        'date_fmt'        => 'd/m/Y H:i',
        // Auto-generated late loan notification texts
        'notif_late_title'=> "⚠️ Late Return · Loan #",
        'notif_late_msg1' => " has exceeded the return date for « ",
        'notif_late_msg2' => " ».\nDelay: ",
        'notif_late_msg3' => " day(s) — Fine: ",
        'notif_late_msg4' => " DA.\nPlease take the necessary action.",
    ],
    'ar' => [
        'page_title'      => 'AuraLib Admin · الإشعارات',
        'eyebrow'         => 'الإشعارات',
        'hero_title'      => 'تنبيهاتك',
        'hero_span'       => '',
        'admin_badge'     => 'مشرف',
        'btn_back'        => 'العودة للوحة التحكم',
        'non_lues'        => 'غير مقروءة',
        'filter_all'      => 'الكل',
        'filter_unread'   => 'غير المقروءة',
        'mark_all'        => 'تحديد الكل كمقروء',
        'delete_all'      => 'حذف الكل',
        'confirm_del_all' => 'حذف جميع الإشعارات؟',
        'confirm_del_one' => 'حذف هذا الإشعار؟',
        'details'         => 'عرض التفاصيل',
        'mark_read'       => 'وضع علامة مقروء',
        'delete'          => 'حذف',
        'empty_title'     => 'لا توجد إشعارات',
        'empty_sub'       => 'ستظهر إشعاراتك هنا.',
        'sep_read'        => 'مقروءة',
        'time_instant'    => 'الآن',
        'time_ago'        => 'منذ',
        'time_d'          => 'يوم',
        'time_h'          => 'ساعة',
        'time_m'          => 'دقيقة',
        'date_fmt'        => 'd/m/Y H:i',
        // Auto-generated late loan notification texts
        'notif_late_title'=> "⚠️ تأخير · استعارة #",
        'notif_late_msg1' => " تجاوز تاريخ إعادة « ",
        'notif_late_msg2' => " ».\nالتأخير: ",
        'notif_late_msg3' => " يوم — الغرامة: ",
        'notif_late_msg4' => " دج.\nيرجى اتخاذ الإجراءات اللازمة.",
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$is_ar = ($lang === 'ar');
$dir   = $is_ar ? 'rtl' : 'ltr';

// ── 0. Génération automatique des notifications admin ─────────────
$sql_retards = "
    SELECT e.id_emprunt, e.amende,
           DATEDIFF(CURDATE(), e.date_retour_prevue) AS jours_retard,
           d.titre,
           u.firstname, u.lastname
    FROM emprunt e
    JOIN documents d ON d.id_doc = e.id_doc
    JOIN users     u ON u.id     = e.id_user
    WHERE e.statut = 'retard'
      AND NOT EXISTS (
          SELECT 1 FROM notifications n
          WHERE n.id_user = ?
            AND n.lien LIKE CONCAT('%emprunt_id=', e.id_emprunt)
      )
";
$stmt_r = $conn->prepare($sql_retards);
$stmt_r->bind_param('i', $id_user);
$stmt_r->execute();
$retards = $stmt_r->get_result();

while ($row_r = $retards->fetch_assoc()) {
    $titre_n   = $p['notif_late_title'] . $row_r['id_emprunt'];
    $message_n = $row_r['firstname'] . ' ' . $row_r['lastname']
               . $p['notif_late_msg1'] . $row_r['titre']
               . $p['notif_late_msg2'] . $row_r['jours_retard']
               . $p['notif_late_msg3'] . $row_r['amende']
               . $p['notif_late_msg4'];
    $lien_n    = "/MEMOIR/admin/gestion_emprunts.php?emprunt_id={$row_r['id_emprunt']}";

    $ins = $conn->prepare(
        "INSERT INTO notifications (id_user, type, titre, message, lien) VALUES (?, 'warning', ?, ?, ?)"
    );
    $ins->bind_param('isss', $id_user, $titre_n, $message_n, $lien_n);
    $ins->execute();
}

// ── 1. Actions ────────────────────────────────────────────────────
if (isset($_GET['action'])) {
    $action   = $_GET['action'];
    $id_notif = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($action === 'mark_read' && $id_notif) {
        $stmt = $conn->prepare("UPDATE notifications SET lu=1 WHERE id=? AND id_user=?");
        $stmt->bind_param('ii', $id_notif, $id_user);
        $stmt->execute();
    } elseif ($action === 'delete' && $id_notif) {
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id=? AND id_user=?");
        $stmt->bind_param('ii', $id_notif, $id_user);
        $stmt->execute();
    } elseif ($action === 'mark_all_read') {
        $stmt = $conn->prepare("UPDATE notifications SET lu=1 WHERE id_user=?");
        $stmt->bind_param('i', $id_user);
        $stmt->execute();
    } elseif ($action === 'delete_all') {
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id_user=?");
        $stmt->bind_param('i', $id_user);
        $stmt->execute();
    }
    header("Location: notifications.php");
    exit;
}

// ── 2. Récupérer les notifications ────────────────────────────────
$stmt = $conn->prepare(
    "SELECT * FROM notifications WHERE id_user=? ORDER BY lu ASC, created_at DESC"
);
$stmt->bind_param('i', $id_user);
$stmt->execute();
$result = $stmt->get_result();

$total    = $result->num_rows;
$non_lues = 0;
$rows     = [];
while ($r = $result->fetch_assoc()) {
    if (!$r['lu']) $non_lues++;
    $rows[] = $r;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<?php include '../includes/dark_init.php'; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $p['page_title'] ?></title>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<style>
/* ══ TOKENS ══ */
:root {
    --gold:        #C4A46B;
    --gold2:       #D4B47B;
    --gold-deep:   #A8884E;
    --gold-faint:  rgba(196,164,107,.08);
    --gold-border: rgba(196,164,107,.25);
    --amber:       #B8832A;
    --ink:         #1A0E05;
    --ink2:        #2E1D08;
    --page-bg:     #F2EDE3;
    --page-white:  #FDFAF5;
    --page-text:   #2A1F14;
    --page-muted:  #9A8C7E;
    --page-border: #D8CFC0;
    --danger:      #C0392B;
    --success:     #276749;
    --warning:     #92400E;
    --info:        #0369A1;
    --font-serif:  'EB Garamond', Georgia, serif;
    --font-ui:     <?= $is_ar ? "'Tajawal', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>;
    --nav-h:       62px;
    --shadow-sm:   0 3px 10px rgba(42,31,20,.07);
    --shadow-md:   0 8px 28px rgba(42,31,20,.11);
    --shadow-gold: 0 6px 20px rgba(196,164,107,.2);
    --radius:      14px;
    --tr:          .25s cubic-bezier(.4,0,.2,1);
}
html.dark {
    --page-bg:    #100C07;
    --page-white: #1E1610;
    --page-text:  #EDE5D4;
    --page-muted: #9A8C7E;
    --page-border:#3A2E1E;
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
<?php if ($is_ar): ?>
.hero-inner,
.toolbar,
.bulk-actions,
.notif-header,
.confirmed-text { flex-direction: row-reverse; }
.notif-card.unread {
    border-left: 1px solid var(--page-border) !important;
    border-right: 3.5px solid var(--gold) !important;
}
<?php endif; ?>

@keyframes fadeUp  { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }
@keyframes cardIn  { from{opacity:0;transform:translateX(-12px)} to{opacity:1;transform:translateX(0)} }
@keyframes pulse-dot { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.4);opacity:.7} }

/* ══ HERO ══ */
.page-hero {
    background: linear-gradient(135deg, var(--ink) 0%, var(--ink2) 55%, var(--ink) 100%);
    padding: 38px 5% 34px;
    position: relative; overflow: hidden;
}
.page-hero::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(ellipse 70% 100% at 15% 50%, rgba(196,164,107,.10) 0%, transparent 65%);
    pointer-events: none;
}
.page-hero::after {
    content: ''; position: absolute; bottom:0; left:0; right:0; height:1px;
    background: linear-gradient(90deg, transparent, rgba(196,164,107,.28), transparent);
}
.hero-inner {
    max-width: 860px; margin: 0 auto;
    display: flex; align-items: flex-end;
    justify-content: space-between; gap: 20px; flex-wrap: wrap;
    animation: fadeUp .5s ease both;
}
.hero-left { display:flex; flex-direction:column; gap:8px; }
.hero-eyebrow {
    display: flex; align-items: center; gap: 8px;
    font-size: 10px; color: rgba(196,164,107,.45);
    letter-spacing: 3px; text-transform: uppercase;
}
.hero-title {
    font-family: var(--font-serif);
    font-size: clamp(28px, 5vw, 46px); font-weight: 700;
    color: #FDFAF5; line-height: 1;
}
.hero-title span {
    background: linear-gradient(135deg, var(--gold), var(--gold2));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}
.admin-badge {
    display: inline-flex; align-items: center; gap: 6px;
    align-self: flex-start; padding: 4px 12px; border-radius: 30px;
    background: rgba(196,164,107,.10); border: 1.5px solid rgba(196,164,107,.28);
    color: rgba(196,164,107,.75); font-size: 10px; font-weight: 800;
    letter-spacing: 2px; text-transform: uppercase;
}
.hero-right { display:flex; flex-direction:column; align-items:flex-end; gap:12px; }
.hero-unread-badge {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 6px 14px; border-radius: 30px;
    background: rgba(192,57,43,.18); border: 1.5px solid rgba(192,57,43,.35);
    color: #ef9090; font-size: 12px; font-weight: 700;
}
.hero-unread-badge .dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #ef4444;
    animation: pulse-dot 1.5s ease-in-out infinite;
}
.btn-back {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 9px 20px; border-radius: 30px;
    background: rgba(196,164,107,.08); border: 1.5px solid rgba(196,164,107,.25);
    color: rgba(196,164,107,.7); font-size: 12px; font-weight: 700;
    text-decoration: none; transition: all var(--tr);
}
.btn-back:hover {
    background: rgba(196,164,107,.18); color: var(--gold);
    border-color: rgba(196,164,107,.5); transform: translateY(-1px);
}

/* ══ TOOLBAR ══ */
.toolbar {
    max-width: 860px; margin: 24px auto 0; padding: 0 5%;
    display: flex; align-items: center;
    justify-content: space-between; gap: 14px; flex-wrap: wrap;
    animation: fadeUp .5s .1s ease both;
}
.filter-tabs { display:flex; gap:6px; }
.filter-tab {
    padding: 7px 16px; border-radius: 30px;
    font-size: 12px; font-weight: 600;
    border: 1.5px solid var(--page-border);
    background: var(--page-white); color: var(--page-muted);
    cursor: pointer; transition: all var(--tr);
}
.filter-tab.active,
.filter-tab:hover {
    background: var(--gold-faint); border-color: var(--gold-border);
    color: var(--gold-deep);
}
html.dark .filter-tab { background: var(--page-white); }
html.dark .filter-tab.active,
html.dark .filter-tab:hover { color: var(--gold); }

.bulk-actions { display:flex; gap:10px; }
.btn-bulk {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 8px 16px; border-radius: 30px;
    font-size: 11px; font-weight: 700; letter-spacing: .2px;
    text-decoration: none; border: 1.5px solid transparent;
    transition: all var(--tr); cursor: pointer;
}
.btn-bulk-read {
    background: var(--gold-faint); border-color: var(--gold-border); color: var(--gold-deep);
}
.btn-bulk-read:hover { background: rgba(196,164,107,.18); color: var(--gold); }
.btn-bulk-del {
    background: rgba(192,57,43,.07); border-color: rgba(192,57,43,.22); color: var(--danger);
}
.btn-bulk-del:hover { background: rgba(192,57,43,.14); }
html.dark .btn-bulk-read { color: var(--gold); }

/* ══ LIST ══ */
.notif-list {
    max-width: 860px; margin: 22px auto 60px;
    padding: 0 5%; display:flex; flex-direction:column; gap:12px;
}

/* ══ CARD ══ */
.notif-card {
    background: var(--page-white); border: 1px solid var(--page-border);
    border-radius: var(--radius); padding: 20px 22px;
    display: flex; align-items: flex-start; gap: 16px;
    position: relative; overflow: hidden;
    transition: transform var(--tr), box-shadow var(--tr);
    animation: cardIn .4s ease both; box-shadow: var(--shadow-sm);
}
.notif-card:hover { transform: translateX(4px); box-shadow: var(--shadow-md); }
.notif-card.unread { border-left: 3.5px solid var(--gold); }
.notif-card.unread::before {
    content: ''; position:absolute; top:0; left:0; right:0; height:2px;
    background: linear-gradient(90deg, transparent, rgba(196,164,107,.3), transparent);
}
.notif-card:nth-child(1) { animation-delay:.04s; }
.notif-card:nth-child(2) { animation-delay:.08s; }
.notif-card:nth-child(3) { animation-delay:.12s; }
.notif-card:nth-child(4) { animation-delay:.16s; }
.notif-card:nth-child(5) { animation-delay:.20s; }
.notif-card:nth-child(6) { animation-delay:.24s; }

/* ── Icon ── */
.notif-icon {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; flex-shrink: 0;
}
.t-info    { background:rgba(3,105,161,.12);  color:var(--info); }
.t-success { background:rgba(39,103,73,.12);  color:var(--success); }
.t-warning { background:rgba(146,64,14,.12);  color:var(--warning); }
.t-danger  { background:rgba(192,57,43,.12);  color:var(--danger); }

/* ── Body ── */
.notif-body { flex:1; min-width:0; }
.notif-header { display:flex; align-items:center; gap:10px; margin-bottom:5px; flex-wrap:wrap; }
.notif-title { font-weight:700; font-size:14px; color:var(--page-text); line-height:1.3; }
.unread-dot {
    width:7px; height:7px; border-radius:50%; background:var(--gold); flex-shrink:0;
    animation: pulse-dot 2s ease-in-out infinite;
}
.notif-msg { font-size:13px; color:var(--page-muted); line-height:1.6; margin-bottom:10px; }
.notif-link {
    display:inline-flex; align-items:center; gap:5px;
    color:var(--gold); font-size:11px; font-weight:700;
    text-decoration:none; letter-spacing:.3px; transition:color var(--tr);
}
.notif-link:hover { color:var(--gold2); }
.notif-time {
    display:block; font-size:10px;
    color:rgba(154,140,126,.6); margin-top:8px; letter-spacing:.5px;
}

/* ── Action buttons ── */
.notif-actions { display:flex; flex-direction:column; align-items:center; gap:8px; flex-shrink:0; }
.btn-notif-action {
    width:32px; height:32px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:13px; text-decoration:none;
    border:1.5px solid var(--page-border);
    background:var(--page-bg); color:var(--page-muted);
    transition: all var(--tr);
}
.btn-notif-action:hover { transform:scale(1.1); }
.btn-mark:hover  { border-color:var(--gold-border); color:var(--gold); background:var(--gold-faint); }
.btn-trash:hover { border-color:rgba(192,57,43,.3); color:var(--danger); background:rgba(192,57,43,.07); }

/* ══ SEPARATOR ══ */
.section-sep {
    display:flex; align-items:center; gap:12px;
    max-width:860px; margin:0 auto; padding:0 5%;
    font-size:10px; font-weight:700; letter-spacing:2px;
    text-transform:uppercase; color:var(--page-muted);
}
.section-sep::before, .section-sep::after {
    content:''; flex:1; height:1px; background:var(--page-border);
}

/* ══ EMPTY STATE ══ */
.empty-state {
    max-width:860px; margin:60px auto; padding:0 5%;
    text-align:center; animation:fadeUp .6s ease both;
}
.empty-icon-wrap {
    width:90px; height:90px; border-radius:50%;
    background: linear-gradient(135deg, var(--ink), var(--ink2));
    border:2px solid var(--gold-border);
    display:flex; align-items:center; justify-content:center;
    margin:0 auto 24px; box-shadow:var(--shadow-gold);
}
.empty-icon-wrap i { font-size:34px; color:var(--gold); opacity:.6; }
.empty-title {
    font-family:var(--font-serif); font-size:28px;
    color:var(--page-text); margin-bottom:10px;
}
.empty-sub { font-size:14px; color:var(--page-muted); line-height:1.6; }

@media (max-width: 600px) {
    .hero-inner { flex-direction:column; align-items:flex-start; }
    .hero-right { align-items:flex-start; }
    .notif-card { flex-wrap:wrap; }
    .notif-actions { flex-direction:row; width:100%; justify-content:flex-end; }
}
</style>
</head>
<body>

<!-- ══ HERO ══ -->
<div class="page-hero">
    <div class="hero-inner">
        <div class="hero-left">
            <div class="hero-eyebrow">
                <i class="fa-solid fa-bell" style="font-size:9px"></i>
                <?= $p['eyebrow'] ?>
            </div>
            <h1 class="hero-title">
                <?= $p['hero_title'] ?><?php if ($p['hero_span']): ?> <span><?= $p['hero_span'] ?></span><?php endif; ?>
            </h1>
            <span class="admin-badge">
                <i class="fa-solid fa-shield-halved" style="font-size:9px"></i>
                <?= $p['admin_badge'] ?>
            </span>
        </div>
        <div class="hero-right">
            <?php if ($non_lues > 0): ?>
            <div class="hero-unread-badge">
                <span class="dot"></span>
                <?= $non_lues ?> <?= $p['non_lues'] ?>
            </div>
            <?php endif; ?>
            <a href="admin_dashboard.php" class="btn-back">
                <i class="fa-solid fa-arrow-<?= $is_ar ? 'right' : 'left' ?>" style="font-size:10px"></i>
                <?= $p['btn_back'] ?>
            </a>
        </div>
    </div>
</div>

<!-- ══ TOOLBAR ══ -->
<?php if ($total > 0): ?>
<div class="toolbar">
    <div class="filter-tabs">
        <button class="filter-tab active" onclick="filterCards('all', this)">
            <?= $p['filter_all'] ?> (<?= $total ?>)
        </button>
        <?php if ($non_lues > 0): ?>
        <button class="filter-tab" onclick="filterCards('unread', this)">
            <?= $p['filter_unread'] ?> (<?= $non_lues ?>)
        </button>
        <?php endif; ?>
    </div>
    <div class="bulk-actions">
        <?php if ($non_lues > 0): ?>
        <a href="?action=mark_all_read" class="btn-bulk btn-bulk-read">
            <i class="fa-solid fa-check-double" style="font-size:10px"></i>
            <?= $p['mark_all'] ?>
        </a>
        <?php endif; ?>
        <a href="?action=delete_all"
           class="btn-bulk btn-bulk-del"
           onclick="return confirm('<?= addslashes($p['confirm_del_all']) ?>')">
            <i class="fa-solid fa-trash-can" style="font-size:10px"></i>
            <?= $p['delete_all'] ?>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- ══ LIST ══ -->
<div class="notif-list" id="notifList">

<?php if ($total === 0): ?>
    <div class="empty-state">
        <div class="empty-icon-wrap">
            <i class="fa-regular fa-bell-slash"></i>
        </div>
        <h2 class="empty-title"><?= $p['empty_title'] ?></h2>
        <p class="empty-sub"><?= $p['empty_sub'] ?></p>
    </div>

<?php else:
    $sep_shown = false;
    foreach ($rows as $i => $row):

        // ── Separator between unread / read ──
        if (!$sep_shown && $row['lu'] == 1 && $non_lues > 0):
            $sep_shown = true;
?>
</div>
<div class="section-sep" style="margin:16px auto;"><?= $p['sep_read'] ?></div>
<div class="notif-list" id="notifList2">
<?php endif; ?>

<?php
    $icon = match($row['type']) {
        'success' => 'fa-circle-check',
        'warning' => 'fa-triangle-exclamation',
        'danger'  => 'fa-circle-xmark',
        default   => 'fa-circle-info',
    };
    $is_unread = (int)$row['lu'] === 0;

    // ── Time-ago label ──
    $diff = (new DateTime())->diff(new DateTime($row['created_at']));
    if ($diff->days > 0)
        $time_label = $p['time_ago'] . ' ' . $diff->days . ' ' . $p['time_d'];
    elseif ($diff->h > 0)
        $time_label = $p['time_ago'] . ' ' . $diff->h   . ' ' . $p['time_h'];
    elseif ($diff->i > 0)
        $time_label = $p['time_ago'] . ' ' . $diff->i   . ' ' . $p['time_m'];
    else
        $time_label = $p['time_instant'];
?>
<div class="notif-card <?= $is_unread ? 'unread' : 'read' ?>" data-read="<?= $row['lu'] ?>">

    <div class="notif-icon t-<?= htmlspecialchars($row['type']) ?>">
        <i class="fa-solid <?= $icon ?>"></i>
    </div>

    <div class="notif-body">
        <div class="notif-header">
            <?php if ($is_unread): ?><span class="unread-dot"></span><?php endif; ?>
            <span class="notif-title"><?= htmlspecialchars($row['titre']) ?></span>
        </div>
        <p class="notif-msg"><?= nl2br(htmlspecialchars($row['message'])) ?></p>
        <?php if (!empty($row['lien'])): ?>
        <a href="<?= htmlspecialchars($row['lien']) ?>" class="notif-link">
            <?= $p['details'] ?> <i class="fa-solid fa-arrow-right" style="font-size:9px"></i>
        </a>
        <?php endif; ?>
        <span class="notif-time">
            <i class="fa-regular fa-clock" style="font-size:9px"></i>
            <?= date($p['date_fmt'], strtotime($row['created_at'])) ?>
            &nbsp;·&nbsp; <?= $time_label ?>
        </span>
    </div>

    <div class="notif-actions">
        <?php if ($is_unread): ?>
        <a href="?action=mark_read&id=<?= $row['id'] ?>"
           class="btn-notif-action btn-mark"
           title="<?= $p['mark_read'] ?>">
            <i class="fa-solid fa-check"></i>
        </a>
        <?php endif; ?>
        <a href="?action=delete&id=<?= $row['id'] ?>"
           class="btn-notif-action btn-trash"
           title="<?= $p['delete'] ?>"
           onclick="return confirm('<?= addslashes($p['confirm_del_one']) ?>')">
            <i class="fa-solid fa-trash-can"></i>
        </a>
    </div>
</div>

<?php endforeach; endif; ?>
</div>

<script>
function filterCards(type, btn) {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.notif-card').forEach(card => {
        if (type === 'all') {
            card.style.display = '';
        } else {
            card.style.display = (card.dataset.read === '0') ? '' : 'none';
        }
    });
}
</script>

</body>
</html>