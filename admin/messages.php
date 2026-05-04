<?php
include "../includes/header.php";
require_once '../includes/head.php'; 

// Fixed: Added logical OR (||)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../client/library.php");
    exit;
}

// ── Action : marquer comme lu ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $mid    = (int)($_POST['id'] ?? 0);

    if ($action === 'mark_read' && $mid) {
        $conn->query("UPDATE contact_messages SET lu=1 WHERE id=$mid");
        echo json_encode(['ok' => true]);
    } elseif ($action === 'mark_all_read') {
        $conn->query("UPDATE contact_messages SET lu=1");
        echo json_encode(['ok' => true]);
    } elseif ($action === 'delete' && $mid) {
        $conn->query("DELETE FROM contact_messages WHERE id=$mid");
        echo json_encode(['ok' => true]);
    }
    exit;
}

// ── Filtre ──────────────────────────────────────────────
$filtre = $_GET['filtre'] ?? 'tous';
$where  = match($filtre) {
    'non_lus' => "WHERE lu = 0",
    'lus'     => "WHERE lu = 1",
    default   => ""
};

// ── Récupérer les messages ──────────────────────────────
$messages = [];
$res = $conn->query("SELECT * FROM contact_messages $where ORDER BY lu ASC, created_at DESC");
if ($res) while ($m = $res->fetch_assoc()) $messages[] = $m;

// ── Comptes ────────────────────────────────────────────
$total    = (int)$conn->query("SELECT COUNT(*) c FROM contact_messages")->fetch_assoc()['c'];
$non_lus  = (int)$conn->query("SELECT COUNT(*) c FROM contact_messages WHERE lu=0")->fetch_assoc()['c'];
$lus      = $total - $non_lus;
?>
<title>Messages — AuraLib Admin</title>
<style>
.adm-wrap  { display:flex; min-height:100vh; }
.adm-main  { flex:1; margin-left:0; padding:100px 100px 100px; }

.page-title { font-family:'Playfair Display',serif; font-size:26px; font-weight:700; color:var(--page-text,#2C1F0E); margin-bottom:4px; display:flex; align-items:center; gap:12px; }
.page-sub   { font-size:13px; color:var(--page-muted,#9A8C7E); margin-bottom:28px; }
.unread-badge { background:#C4A46B; color:#2C1F0E; font-size:12px; font-weight:700; padding:3px 11px; border-radius:20px; }

/* Stats mini */
.msg-stats { display:flex; gap:12px; margin-bottom:22px; flex-wrap:wrap; }
.ms-item   { background:var(--page-white,#FFFDF9); border:1px solid var(--page-border,#DDD5C8); border-radius:10px; padding:14px 20px; display:flex; align-items:center; gap:10px; }
.ms-num    { font-family:'Playfair Display',serif; font-size:22px; color:var(--page-text,#2C1F0E); }
.ms-lbl    { font-size:11px; color:var(--page-muted,#9A8C7E); }
.ms-dot    { width:10px; height:10px; border-radius:50%; flex-shrink:0; }

/* Filtres */
.filter-bar { display:flex; gap:6px; margin-bottom:18px; flex-wrap:wrap; align-items:center; justify-content:space-between; }
.filter-left { display:flex; gap:6px; }
.fpill { padding:6px 14px; border-radius:20px; font-size:12px; font-weight:500; text-decoration:none; border:1px solid var(--page-border,#DDD5C8); color:var(--page-muted,#9A8C7E); background:var(--page-white,#FFFDF9); transition:all .15s; }
.fpill:hover  { border-color:#C4A46B; color:#C4A46B; }
.fpill.active { background:#2C1F0E; border-color:#2C1F0E; color:#C4A46B; }
.btn-mark-all { background:transparent; border:1px solid var(--page-border,#DDD5C8); border-radius:8px; padding:7px 14px; font-size:12px; color:var(--page-muted,#9A8C7E); cursor:pointer; font-family:inherit; transition:all .15s; }
.btn-mark-all:hover { border-color:#C4A46B; color:#C4A46B; }

/* Message cards */
.msg-list { display:flex; flex-direction:column; gap:10px; }
.msg-card {
    background:var(--page-white,#FFFDF9); border:1px solid var(--page-border,#DDD5C8);
    border-radius:12px; padding:18px 20px;
    display:flex; align-items:flex-start; gap:14px;
    transition:border-color .15s;
}
.msg-card:hover   { border-color:rgba(196,164,107,.4); }
.msg-card.unread  { border-left:3px solid #C4A46B; background:#FFFDF5; }
html.dark .msg-card.unread { background:#2A2418; }

/* Avatar */
.msg-avatar {
    width:40px; height:40px; border-radius:50%;
    background:#C4A46B; color:#2C1F0E;
    display:flex; align-items:center; justify-content:center;
    font-size:16px; font-weight:700; flex-shrink:0;
}
.msg-avatar.read { background:var(--page-border,#DDD5C8); color:var(--page-muted,#9A8C7E); }

/* Content */
.msg-body  { flex:1; min-width:0; }
.msg-top   { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:4px; }
.msg-name  { font-size:14px; font-weight:600; color:var(--page-text,#2C1F0E); }
.msg-email { font-size:11px; color:var(--page-muted,#9A8C7E); }
.msg-subject-badge {
    font-size:10px; font-weight:600; padding:2px 9px; border-radius:10px;
    background:rgba(196,164,107,.12); color:#C4A46B; border:1px solid rgba(196,164,107,.2);
}
.msg-text { font-size:13px; color:var(--page-muted,#9A8C7E); line-height:1.55; margin-top:6px; }
.msg-text.expanded { color:var(--page-text,#2C1F0E); }
.msg-time { font-size:10px; color:var(--page-muted,#9A8C7E); margin-top:6px; }

/* Actions */
.msg-actions { display:flex; gap:4px; flex-shrink:0; flex-direction:column; align-items:flex-end; }
.act-btn {
    width:30px; height:30px; border-radius:7px; border:none;
    background:transparent; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:background .12s; color:var(--page-muted,#9A8C7E);
}
.act-btn:hover { background:var(--page-bg,#F5F0E8); color:var(--page-text,#2C1F0E); }
.act-btn.del:hover { background:#fee2e2; color:#dc2626; }
.unread-dot { width:9px; height:9px; background:#C4A46B; border-radius:50%; margin-top:2px; }
.read-more  { font-size:11px; color:#C4A46B; cursor:pointer; margin-top:4px; font-weight:600; background:none; border:none; padding:0; }

/* Empty */
.empty-state { text-align:center; padding:48px 20px; color:var(--page-muted,#9A8C7E); }
.empty-state svg { width:40px; height:40px; margin:0 auto 12px; display:block; opacity:.3; }
</style>

<div class="adm-wrap">
   

    <div class="adm-main">
        <div class="page-title">
            Messages des lecteurs
            <?php if ($non_lus > 0): ?>
                <span class="unread-badge"><?= $non_lus ?> non lu<?= $non_lus > 1 ? 's' : '' ?></span>
            <?php endif; ?>
        </div>
        <div class="page-sub">Tous les messages envoyés via la page Contact</div>

        <div class="msg-stats">
            <div class="ms-item">
                <div class="ms-dot" style="background:#C4A46B"></div>
                <div><div class="ms-num"><?= $total ?></div><div class="ms-lbl">Total</div></div>
            </div>
            <div class="ms-item">
                <div class="ms-dot" style="background:#f87171"></div>
                <div><div class="ms-num"><?= $non_lus ?></div><div class="ms-lbl">Non lus</div></div>
            </div>
            <div class="ms-item">
                <div class="ms-dot" style="background:#4ade80"></div>
                <div><div class="ms-num"><?= $lus ?></div><div class="ms-lbl">Lus</div></div>
            </div>
        </div>

        <div class="filter-bar">
            <div class="filter-left">
                <a href="?filtre=tous"     class="fpill <?= $filtre==='tous'    ?'active':'' ?>">Tous (<?= $total ?>)</a>
                <a href="?filtre=non_lus"  class="fpill <?= $filtre==='non_lus' ?'active':'' ?>">Non lus (<?= $non_lus ?>)</a>
                <a href="?filtre=lus"      class="fpill <?= $filtre==='lus'     ?'active':'' ?>">Lus (<?= $lus ?>)</a>
            </div>
            <?php if ($non_lus > 0): ?>
            <button class="btn-mark-all" onclick="markAllRead()">Tout marquer comme lu</button>
            <?php endif; ?>
        </div>

        <div class="msg-list" id="msgList">
            <?php if (empty($messages)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <p>Aucun message<?= $filtre !== 'tous' ? ' dans cette catégorie' : '' ?></p>
            </div>
            <?php else: ?>
            <?php foreach ($messages as $m):
                $is_lu   = (int)$m['lu'] === 1;
                $letter  = strtoupper(substr($m['name'] ?? 'U', 0, 1));
                $preview = mb_strlen($m['message']) > 120 ? mb_substr($m['message'], 0, 120) . '...' : $m['message'];
                $full    = $m['message'];
                $time    = date('d/m/Y à H:i', strtotime($m['created_at']));
            ?>
            <div class="msg-card <?= $is_lu ? 'read' : 'unread' ?>" id="msg-<?= $m['id'] ?>">
                <div class="msg-avatar <?= $is_lu ? 'read' : '' ?>"><?= $letter ?></div>
                <div class="msg-body">
                    <div class="msg-top">
                        <span class="msg-name"><?= htmlspecialchars($m['name']) ?></span>
                        <span class="msg-email"><?= htmlspecialchars($m['email']) ?></span>
                        <span class="msg-subject-badge"><?= htmlspecialchars($m['subject'] ?? 'Question') ?></span>
                        <?php if (!empty($m['id_user'])): ?>
                        <span style="font-size:10px;color:#4ade80;font-weight:600">✓ Compte vérifié</span>
                        <?php endif; ?>
                    </div>
                    <div class="msg-text" id="txt-<?= $m['id'] ?>"><?= nl2br(htmlspecialchars($preview)) ?></div>
                    <?php if (mb_strlen($full) > 120): ?>
                    <button class="read-more" id="btn-<?= $m['id'] ?>" onclick="toggleMsg(<?= $m['id'] ?>, <?= htmlspecialchars(json_encode($full)) ?>)">
                        Lire la suite ▾
                    </button>
                    <?php endif; ?>
                    <div class="msg-time">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:3px"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <?= $time ?>
                    </div>
                </div>
                <div class="msg-actions">
                    <?php if (!$is_lu): ?>
                    <div class="unread-dot" id="dot-<?= $m['id'] ?>"></div>
                    <button class="act-btn" onclick="markRead(<?= $m['id'] ?>)" title="Marquer comme lu">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                    <?php endif; ?>
                    <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=Re: <?= urlencode($m['subject'] ?? 'Votre message') ?>"
                       class="act-btn" title="Répondre par email">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </a>
                    <button class="act-btn del" onclick="deleteMsg(<?= $m['id'] ?>)" title="Supprimer">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function post(data) {
    return fetch(window.location.pathname, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    }).then(r => r.json());
}

function markRead(id) {
    post({action:'mark_read', id}).then(() => {
        var card = document.getElementById('msg-'+id);
        if (card) {
            card.classList.remove('unread');
            card.classList.add('read');
            var dot = document.getElementById('dot-'+id);
            var btn = card.querySelector('.act-btn:not(.del)');
            if (dot) dot.remove();
            if (btn && btn.querySelector('polyline')) btn.remove();
        }
        var av = card ? card.querySelector('.msg-avatar') : null;
        if (av) av.classList.add('read');
    });
}

function markAllRead() {
    post({action:'mark_all_read'}).then(() => location.reload());
}

function deleteMsg(id) {
    if (!confirm('Supprimer ce message ?')) return;
    post({action:'delete', id}).then(() => {
        var card = document.getElementById('msg-'+id);
        if (card) {
            card.style.opacity = '0';
            card.style.transition = 'opacity .25s';
            setTimeout(() => card.remove(), 260);
        }
    });
}

function toggleMsg(id, full) {
    var txt = document.getElementById('txt-'+id);
    var btn = document.getElementById('btn-'+id);
    // Fixed: Added logical OR (||)
    if (!txt || !btn) return;
    
    if (btn.textContent.includes('suite')) {
        txt.innerHTML = full.replace(/\n/g,'<br>');
        txt.classList.add('expanded');
        btn.textContent = 'Réduire ▴';
    } else {
        var short = full.length > 120 ? full.slice(0,120) + '...' : full;
        txt.innerHTML = short.replace(/\n/g,'<br>');
        txt.classList.remove('expanded');
        btn.textContent = 'Lire la suite ▾';
    }
}
</script>

<?php include "../includes/footer.php"; ?>