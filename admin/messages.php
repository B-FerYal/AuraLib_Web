<?php
include "../includes/header.php";
require_once '../includes/head.php';
include_once '../includes/languages.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../client/library.php");
    exit;
}

// ── Traductions ──────────────────────────────────────────
$pg = [
    'fr' => [
        'page_title'    => 'AuraLib · Messages des Lecteurs',
        'breadcrumb'    => 'Dashboard',
        'bc_messages'   => 'Messages',
        'hero_title'    => 'Messages des',
        'hero_span'     => 'Lecteurs',
        'today'         => "Aujourd'hui",
        'unread_pill'   => 'non lu',
        'unread_pills'  => 'non lus',
        'btn_back'      => 'Retour au Dashboard',
        'stat_total'    => 'Total',
        'stat_unread'   => 'Non lus',
        'stat_read'     => 'Lus',
        'filter_all'    => 'Tous',
        'filter_unread' => 'Non lus',
        'filter_read'   => 'Lus',
        'mark_all'      => 'Tout marquer comme lu',
        'empty_h'       => 'Aucun message',
        'empty_cat'     => ' dans cette catégorie',
        'empty_p'       => 'Les messages envoyés via la page Contact apparaîtront ici.',
        'read_more'     => 'Lire la suite',
        'collapse'      => 'Réduire',
        'verified'      => 'Compte vérifié',
        'replied_lbl'   => 'Répondu',
        'prev_reply'    => 'Votre réponse',
        'title_read'    => 'Marquer comme lu',
        'title_reply'   => 'Répondre',
        'title_delete'  => 'Supprimer',
        'swal_del_title'  => 'Supprimer ce message ?',
        'swal_del_text'   => 'Cette action est irréversible.',
        'swal_del_yes'    => 'Oui, supprimer',
        'swal_del_no'     => 'Annuler',
        'swal_del_done'   => 'Supprimé !',
        'swal_del_ok'     => 'Le message a été supprimé.',
        'reply_title'     => 'Répondre à',
        'reply_subject'   => 'Objet',
        'reply_body'      => 'Votre réponse',
        'reply_ph'        => 'Écrivez votre réponse ici…',
        'reply_send'      => 'Envoyer la réponse',
        'reply_cancel'    => 'Annuler',
        'reply_sending'   => 'Envoi…',
        'reply_success'   => 'Réponse enregistrée !',
        'reply_success_t' => 'La réponse a été sauvegardée avec succès.',
        'reply_error'     => 'Erreur lors de l\'envoi.',
    ],
    'en' => [
        'page_title'    => 'AuraLib · Reader Messages',
        'breadcrumb'    => 'Dashboard',
        'bc_messages'   => 'Messages',
        'hero_title'    => 'Messages from',
        'hero_span'     => 'Readers',
        'today'         => 'Today',
        'unread_pill'   => 'unread',
        'unread_pills'  => 'unread',
        'btn_back'      => 'Back to Dashboard',
        'stat_total'    => 'Total',
        'stat_unread'   => 'Unread',
        'stat_read'     => 'Read',
        'filter_all'    => 'All',
        'filter_unread' => 'Unread',
        'filter_read'   => 'Read',
        'mark_all'      => 'Mark all as read',
        'empty_h'       => 'No messages',
        'empty_cat'     => ' in this category',
        'empty_p'       => 'Messages sent via the Contact page will appear here.',
        'read_more'     => 'Read more',
        'collapse'      => 'Collapse',
        'verified'      => 'Verified account',
        'replied_lbl'   => 'Replied',
        'prev_reply'    => 'Your reply',
        'title_read'    => 'Mark as read',
        'title_reply'   => 'Reply',
        'title_delete'  => 'Delete',
        'swal_del_title'  => 'Delete this message?',
        'swal_del_text'   => 'This action cannot be undone.',
        'swal_del_yes'    => 'Yes, delete',
        'swal_del_no'     => 'Cancel',
        'swal_del_done'   => 'Deleted!',
        'swal_del_ok'     => 'The message has been deleted.',
        'reply_title'     => 'Reply to',
        'reply_subject'   => 'Subject',
        'reply_body'      => 'Your reply',
        'reply_ph'        => 'Write your reply here…',
        'reply_send'      => 'Send reply',
        'reply_cancel'    => 'Cancel',
        'reply_sending'   => 'Saving…',
        'reply_success'   => 'Reply saved!',
        'reply_success_t' => 'The reply has been saved successfully.',
        'reply_error'     => 'Error while saving.',
    ],
    'ar' => [
        'page_title'    => 'AuraLib · رسائل القراء',
        'breadcrumb'    => 'لوحة التحكم',
        'bc_messages'   => 'الرسائل',
        'hero_title'    => 'رسائل',
        'hero_span'     => 'القراء',
        'today'         => 'اليوم',
        'unread_pill'   => 'غير مقروءة',
        'unread_pills'  => 'غير مقروءة',
        'btn_back'      => 'العودة للوحة التحكم',
        'stat_total'    => 'الإجمالي',
        'stat_unread'   => 'غير مقروء',
        'stat_read'     => 'مقروء',
        'filter_all'    => 'الكل',
        'filter_unread' => 'غير مقروء',
        'filter_read'   => 'مقروء',
        'mark_all'      => 'تعليم الكل كمقروء',
        'empty_h'       => 'لا توجد رسائل',
        'empty_cat'     => ' في هذه الفئة',
        'empty_p'       => 'ستظهر الرسائل المرسلة عبر صفحة الاتصال هنا.',
        'read_more'     => 'اقرأ المزيد',
        'collapse'      => 'طي',
        'verified'      => 'حساب موثق',
        'replied_lbl'   => 'تم الرد',
        'prev_reply'    => 'ردك',
        'title_read'    => 'تعليم كمقروء',
        'title_reply'   => 'رد',
        'title_delete'  => 'حذف',
        'swal_del_title'  => 'حذف هذه الرسالة؟',
        'swal_del_text'   => 'لا يمكن التراجع عن هذا الإجراء.',
        'swal_del_yes'    => 'نعم، احذف',
        'swal_del_no'     => 'إلغاء',
        'swal_del_done'   => 'تم الحذف!',
        'swal_del_ok'     => 'تم حذف الرسالة.',
        'reply_title'     => 'رد على',
        'reply_subject'   => 'الموضوع',
        'reply_body'      => 'ردك',
        'reply_ph'        => 'اكتب ردك هنا…',
        'reply_send'      => 'حفظ الرد',
        'reply_cancel'    => 'إلغاء',
        'reply_sending'   => 'جارٍ الحفظ…',
        'reply_success'   => 'تم حفظ الرد!',
        'reply_success_t' => 'تم حفظ ردك بنجاح.',
        'reply_error'     => 'خطأ أثناء الحفظ.',
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

/* ══ Auto-create missing columns (safe on any DB) ══ */
$conn->query("ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS reponse TEXT NULL DEFAULT NULL");
$conn->query("ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS date_reponse DATETIME NULL DEFAULT NULL");

/* ══ AJAX actions ══ */
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

    } elseif ($action === 'reply' && $mid) {
        $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id=?");
        $stmt->bind_param("i", $mid);
        $stmt->execute();
        $orig = $stmt->get_result()->fetch_assoc();

        if (!$orig) {
            echo json_encode(['ok' => false, 'error' => 'Message introuvable']);
            exit;
        }

        $body = trim($_POST['body'] ?? '');
        if (empty($body)) {
            echo json_encode(['ok' => false, 'error' => 'Corps vide']);
            exit;
        }

        // ── 1. Save to DB first — this ALWAYS works ──
        $safe_body = $conn->real_escape_string($body);
        $conn->query("UPDATE contact_messages SET lu=1, reponse='$safe_body', date_reponse=NOW() WHERE id=$mid");

        // ── 2. Try to send email silently (@ suppresses any error) ──
        $to      = $orig['email'];
        $subject = 'Re: ' . ($orig['subject'] ?? 'Votre message');
        $email_html = "
        <div style='font-family:Georgia,serif;max-width:580px;margin:0 auto;background:#FFFDF9;border:1px solid #EDE5D4;border-radius:12px;overflow:hidden'>
            <div style='background:#2C1F0E;padding:24px 28px'>
                <span style='font-size:22px;font-weight:700;color:#FFFDF9'>Aura</span><span style='font-size:22px;font-weight:700;color:#C4A46B'>Lib</span>
                <p style='color:rgba(255,255,255,.5);font-size:11px;margin:4px 0 0;letter-spacing:2px;text-transform:uppercase'>LIBRARY</p>
            </div>
            <div style='padding:28px;'>
                <p style='font-size:13px;color:#9A8C7E;margin-bottom:6px'>En réponse à votre message :</p>
                <div style='background:#FAF6EF;border-left:3px solid #C4A46B;padding:12px 16px;border-radius:6px;font-size:13px;color:#7A6A55;margin-bottom:20px;font-style:italic'>"
                    . nl2br(htmlspecialchars($orig['message'])) .
                "</div>
                <div style='font-size:15px;color:#2C1F0E;line-height:1.75;white-space:pre-line'>"
                    . nl2br(htmlspecialchars($body)) .
                "</div>
            </div>
            <div style='background:#FAF6EF;padding:16px 28px;border-top:1px solid #EDE5D4;text-align:center;font-size:11px;color:#9A8C7E'>
                AuraLib — Bibliothèque en ligne &nbsp;·&nbsp; contact@auralib.dz
            </div>
        </div>";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: AuraLib <contact@auralib.dz>\r\n";
        $headers .= "Reply-To: contact@auralib.dz\r\n";
        @mail($to, $subject, $email_html, $headers); // silent — does not affect ok

        // ── 3. Always return ok=true (DB save is what matters) ──
        echo json_encode(['ok' => true]);
    }
    exit;
}

/* ══ Filter ══ */
$filtre = $_GET['filtre'] ?? 'tous';
$where  = match($filtre) {
    'non_lus' => "WHERE lu = 0",
    'lus'     => "WHERE lu = 1",
    default   => ""
};

/* ══ Data ══ */
$messages = [];
$res = $conn->query("SELECT * FROM contact_messages $where ORDER BY lu ASC, created_at DESC");
if ($res) while ($m = $res->fetch_assoc()) $messages[] = $m;

$total   = (int)$conn->query("SELECT COUNT(*) c FROM contact_messages")->fetch_assoc()['c'];
$non_lus = (int)$conn->query("SELECT COUNT(*) c FROM contact_messages WHERE lu=0")->fetch_assoc()['c'];
$lus     = $total - $non_lus;
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="UTF-8">
<?php include '../includes/dark_init.php'; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $p['page_title'] ?></title>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
/* ══ TOKENS ══ */
:root {
    --gold:        #C4A46B; --gold2:       #D4B47B; --gold-deep:   #A8884E;
    --gold-faint:  rgba(196,164,107,.09); --gold-border: rgba(196,164,107,.28);
    --page-bg:     #F2EDE3; --page-bg2:    #E8E0D0; --page-white:  #FDFAF5;
    --page-text:   #2A1F14; --page-muted:  #9A8C7E; --page-border: #D8CFC0;
    --danger:      #C0392B; --success:     #276749; --warning:     #92400E;
    --font-serif:  'EB Garamond', Georgia, serif;
    --font-ui:     <?= $isRtl ? "'Tajawal', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>;
    --nav-h:       62px;
    --shadow-sm:   0 3px 10px rgba(42,31,20,.08);
    --shadow-md:   0 8px 28px rgba(42,31,20,.12);
    --shadow-gold: 0 6px 20px rgba(196,164,107,.25);
    --tr:          .25s cubic-bezier(.4,0,.2,1);
}
html.dark {
    --page-bg:    #100C07; --page-bg2:   #1A1308; --page-white:  #1E1610;
    --page-text:  #EDE5D4; --page-muted: #9A8C7E; --page-border: #3A2E1E;
    --shadow-sm:  0 3px 10px rgba(0,0,0,.3); --shadow-md: 0 8px 28px rgba(0,0,0,.4);
}
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
body {
    font-family: var(--font-ui);
    background: var(--page-bg); color: var(--page-text);
    min-height: 100vh; padding-top: var(--nav-h);
    transition: background .35s, color .35s;
    direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
}

@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
@keyframes cardIn { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
@keyframes pulse  { 0%,100%{opacity:1} 50%{opacity:.45} }

/* ══ HERO ══ */
.page-hero {
    background: linear-gradient(135deg,#1A0E05 0%,#2E1D08 55%,#1A0E05 100%);
    padding: 36px 5% 32px; position:relative; overflow:hidden;
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
    max-width:1200px; margin:0 auto;
    display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
    animation: fadeUp .5s ease both;
}
.hero-left { display:flex; flex-direction:column; gap:6px; }
.hero-breadcrumb {
    display:flex; align-items:center; gap:8px;
    font-size:11px; color:rgba(196,164,107,.5); letter-spacing:.4px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.hero-breadcrumb a { color:rgba(196,164,107,.5); text-decoration:none; transition:color var(--tr); }
.hero-breadcrumb a:hover { color:var(--gold); }
.hero-breadcrumb i { font-size:8px; }
.hero-title {
    font-family:var(--font-serif); font-size:clamp(26px,4vw,44px);
    font-weight:700; color:#FDFAF5; line-height:1.05;
}
.hero-title span {
    background: linear-gradient(135deg,var(--gold) 0%,var(--gold2) 100%);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.hero-date { font-size:11px; color:rgba(253,250,245,.4); letter-spacing:.5px; }
.hero-right { display:flex; align-items:center; gap:10px; flex-shrink:0; flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>; }

.btn-back {
    display:inline-flex; align-items:center; gap:8px;
    padding:10px 20px; border-radius:50px;
    font-family:var(--font-ui); font-size:12px; font-weight:700;
    color:rgba(196,164,107,.8); background:rgba(196,164,107,.1);
    backdrop-filter:blur(12px); border:1.5px solid rgba(196,164,107,.25);
    text-decoration:none; transition:all var(--tr);
}
.btn-back:hover { background:rgba(196,164,107,.2); color:var(--gold2); border-color:rgba(196,164,107,.5); transform:translateY(-1px); }

.unread-pill {
    display:inline-flex; align-items:center; gap:7px;
    padding:10px 18px; border-radius:50px;
    background:rgba(196,164,107,.15); border:1.5px solid rgba(196,164,107,.35);
    color:var(--gold2); font-size:12px; font-weight:700;
    backdrop-filter:blur(12px);
}
.unread-pill i { animation:pulse 1.8s ease infinite; }

/* ══ STATS BAR ══ */
.stats-bar {
    max-width:1200px; margin:24px auto 0; padding:0 5%;
    display:flex; gap:12px; flex-wrap:wrap;
    animation: fadeUp .5s .1s ease both;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.stat-pill {
    display:flex; align-items:center; gap:9px;
    padding:10px 18px; border-radius:50px;
    background:var(--page-white); border:1.5px solid var(--page-border);
    box-shadow:var(--shadow-sm);
}
.stat-dot  { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.stat-label{ font-size:11px; color:var(--page-muted); font-weight:500; }
.stat-num  { font-family:var(--font-serif); font-size:20px; font-weight:700; color:var(--page-text); line-height:1; }

/* ══ TOOLBAR ══ */
.toolbar {
    max-width:1200px; margin:20px auto 0; padding:0 5%;
    display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
    animation: fadeUp .5s .15s ease both;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.filter-pills { display:flex; gap:8px; flex-wrap:wrap; flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>; }
.fpill {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 16px; border-radius:50px;
    font-family:var(--font-ui); font-size:11px; font-weight:600;
    border:1.5px solid var(--page-border); background:var(--page-white);
    color:var(--page-muted); text-decoration:none; transition:all var(--tr);
}
.fpill:hover  { border-color:var(--gold); color:var(--gold-deep); background:var(--gold-faint); }
.fpill.active { background:var(--gold); border-color:var(--gold); color:#2C1F0E; font-weight:700; box-shadow:var(--shadow-gold); }

.btn-mark-all {
    display:inline-flex; align-items:center; gap:7px;
    padding:8px 18px; border-radius:50px;
    font-family:var(--font-ui); font-size:11px; font-weight:700;
    background:transparent; border:1.5px solid var(--page-border);
    color:var(--page-muted); cursor:pointer; transition:all var(--tr);
}
.btn-mark-all:hover { border-color:var(--gold); color:var(--gold-deep); background:var(--gold-faint); }

/* ══ MESSAGE LIST ══ */
.msg-wrap {
    max-width:1200px; margin:20px auto 60px; padding:0 5%;
    animation: fadeUp .5s .2s ease both;
}
.msg-list { display:flex; flex-direction:column; gap:12px; }

/* ══ MESSAGE CARD ══ */
.msg-card {
    background:var(--page-white);
    border:1px solid var(--page-border);
    border-radius:18px;
    padding:20px 22px;
    display:flex; align-items:flex-start; gap:16px;
    box-shadow:var(--shadow-sm);
    transition:transform var(--tr), box-shadow var(--tr), border-color var(--tr);
    position:relative; overflow:hidden;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.msg-card::before {
    content:''; position:absolute; left:0; top:0; bottom:0;
    width:3px; background:transparent;
    border-radius:3px 0 0 3px; transition:background var(--tr);
}
.msg-card.unread::before { background:var(--gold); }
.msg-card.unread {
    border-color:rgba(196,164,107,.28);
    background: linear-gradient(135deg, rgba(196,164,107,.04) 0%, var(--page-white) 60%);
}
html.dark .msg-card.unread {
    background: linear-gradient(135deg, rgba(196,164,107,.07) 0%, var(--page-white) 60%);
}
.msg-card:hover { transform:translateY(-3px); box-shadow:var(--shadow-md); border-color:rgba(196,164,107,.35); }

.msg-avatar {
    width:44px; height:44px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-family:var(--font-serif); font-size:18px; font-weight:700;
    background:linear-gradient(135deg,var(--gold) 0%,var(--gold-deep) 100%);
    color:#1A0E05; box-shadow:0 3px 10px rgba(196,164,107,.3); transition:all var(--tr);
}
.msg-avatar.read { background:var(--page-bg2); color:var(--page-muted); box-shadow:none; }

.msg-body { flex:1; min-width:0; }
.msg-top  { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:6px; flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>; }
.msg-name    { font-size:15px; font-weight:700; color:var(--page-text); }
.msg-email   { font-size:11px; color:var(--page-muted); }
.msg-subject {
    font-size:10px; font-weight:700; letter-spacing:.5px;
    padding:3px 10px; border-radius:20px;
    background:var(--gold-faint); color:var(--gold-deep); border:1px solid var(--gold-border);
}
html.dark .msg-subject { color:var(--gold); }
.verified-tag {
    display:inline-flex; align-items:center; gap:4px;
    font-size:10px; font-weight:700; color:var(--success);
    background:rgba(39,103,73,.1); border:1px solid rgba(39,103,73,.25);
    padding:2px 8px; border-radius:20px;
}
.replied-tag {
    display:inline-flex; align-items:center; gap:4px;
    font-size:10px; font-weight:700; color:#2563eb;
    background:rgba(37,99,235,.08); border:1px solid rgba(37,99,235,.22);
    padding:2px 8px; border-radius:20px;
}
.msg-text { font-size:13px; color:var(--page-muted); line-height:1.65; margin-top:2px; text-align:<?= $isRtl?'right':'left' ?>; }
.msg-text.expanded { color:var(--page-text); }

.prev-reply {
    margin-top:10px;
    background: rgba(196,164,107,.06);
    border-left: 3px solid var(--gold);
    padding: 10px 14px;
    border-radius: 0 8px 8px 0;
    font-size:12px; color:var(--page-muted); line-height:1.6;
}
html.dark .prev-reply { background: rgba(196,164,107,.04); }
.prev-reply strong { color:var(--gold-deep); font-size:10px; letter-spacing:.8px; text-transform:uppercase; display:block; margin-bottom:4px; }

.read-more {
    display:inline-flex; align-items:center; gap:4px;
    font-size:11px; font-weight:700; color:var(--gold-deep);
    background:none; border:none; cursor:pointer; padding:4px 0; margin-top:4px;
    transition:color var(--tr); font-family:var(--font-ui);
}
html.dark .read-more { color:var(--gold); }
.read-more:hover { color:var(--gold2); }

.msg-time {
    display:inline-flex; align-items:center; gap:5px;
    font-size:10px; color:var(--page-muted); margin-top:8px;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.msg-time i { font-size:9px; }
.unread-dot {
    width:9px; height:9px; background:var(--gold); border-radius:50%;
    flex-shrink:0; animation:pulse 2s ease infinite;
    box-shadow:0 0 0 3px rgba(196,164,107,.2);
}
.msg-actions { display:flex; flex-direction:column; align-items:flex-end; gap:6px; flex-shrink:0; }
.act-btn {
    width:32px; height:32px; border-radius:10px; border:none;
    background:transparent; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    transition:all var(--tr); color:var(--page-muted); font-size:13px;
}
.act-btn:hover          { background:var(--gold-faint); color:var(--gold-deep); }
.act-btn.act-reply:hover{ background:rgba(37,99,235,.1); color:#2563eb; }
.act-btn.act-del:hover  { background:rgba(192,57,43,.1); color:var(--danger); }

/* ══ EMPTY STATE ══ */
.empty-state { text-align:center; padding:80px 20px; animation: fadeUp .5s ease both; }
.empty-icon  { font-size:48px; color:var(--page-border); margin-bottom:16px; }
.empty-state h3 { font-family:var(--font-serif); font-size:24px; color:var(--page-muted); margin-bottom:6px; }
.empty-state p  { font-size:13px; color:var(--page-muted); }

/* ══ REPLY MODAL ══ */
.reply-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(20,12,4,.72); backdrop-filter:blur(8px);
    z-index:1000; align-items:center; justify-content:center; padding:20px;
}
.reply-overlay.open { display:flex; }
.reply-modal {
    background:var(--page-white); border:1px solid var(--page-border);
    border-radius:20px; width:100%; max-width:540px;
    box-shadow:0 30px 80px rgba(20,12,4,.45); overflow:hidden;
    animation:replyIn .25s cubic-bezier(.4,0,.2,1) both;
}
@keyframes replyIn {
    from{opacity:0;transform:translateY(20px) scale(.97)}
    to  {opacity:1;transform:translateY(0) scale(1)}
}
.reply-modal-head {
    background:linear-gradient(135deg,#1A0E05 0%,#2E1D08 100%);
    padding:20px 24px; display:flex; align-items:center; justify-content:space-between;
    flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
}
.reply-modal-title { font-family:var(--font-serif); font-size:20px; font-weight:700; color:#FDFAF5; }
.reply-modal-title em { color:var(--gold); font-style:normal; }
.reply-close {
    width:30px; height:30px; border-radius:50%; border:none;
    background:rgba(255,255,255,.08); color:rgba(255,255,255,.6);
    cursor:pointer; font-size:14px; display:flex; align-items:center; justify-content:center;
    transition:all var(--tr);
}
.reply-close:hover { background:rgba(255,255,255,.18); color:#fff; }
.reply-modal-body { padding:24px; }
.reply-quote {
    background:var(--gold-faint); border-left:3px solid var(--gold);
    padding:10px 14px; border-radius:0 8px 8px 0;
    font-size:12px; color:var(--page-muted); line-height:1.6;
    margin-bottom:18px; font-style:italic;
}
html.dark .reply-quote { background:rgba(196,164,107,.04); }
.reply-field-lbl {
    font-size:10px; font-weight:700; letter-spacing:1px; text-transform:uppercase;
    color:var(--page-muted); margin-bottom:6px; display:block;
    text-align:<?= $isRtl?'right':'left' ?>;
}
.reply-subject-input {
    width:100%; padding:10px 14px; border:1.5px solid var(--page-border); border-radius:10px;
    font-family:var(--font-ui); font-size:13px; color:var(--page-text);
    background:var(--page-bg); outline:none; margin-bottom:14px;
    transition:border-color var(--tr); direction:<?= $isRtl?'rtl':'ltr' ?>;
}
.reply-subject-input:focus { border-color:var(--gold-border); }
.reply-textarea {
    width:100%; padding:12px 14px; border:1.5px solid var(--page-border); border-radius:10px;
    font-family:var(--font-ui); font-size:13px; color:var(--page-text);
    background:var(--page-bg); outline:none; resize:vertical; min-height:130px;
    margin-bottom:18px; transition:border-color var(--tr); direction:<?= $isRtl?'rtl':'ltr' ?>;
}
.reply-textarea:focus { border-color:var(--gold-border); box-shadow:0 0 0 3px rgba(196,164,107,.1); }
.reply-modal-foot {
    display:flex; gap:10px; justify-content:flex-end;
    flex-direction:<?= $isRtl?'row-reverse':'row' ?>;
}
.btn-reply-send {
    display:inline-flex; align-items:center; gap:7px; padding:11px 24px; border-radius:50px;
    background:var(--gold); color:#2C1F0E; font-family:var(--font-ui); font-size:12px;
    font-weight:700; border:none; cursor:pointer; transition:all var(--tr);
}
.btn-reply-send:hover { background:var(--gold2); transform:translateY(-1px); box-shadow:var(--shadow-gold); }
.btn-reply-send:disabled { opacity:.6; cursor:not-allowed; transform:none; }
.btn-reply-cancel {
    display:inline-flex; align-items:center; gap:7px; padding:11px 20px; border-radius:50px;
    background:transparent; color:var(--page-muted); font-family:var(--font-ui); font-size:12px;
    font-weight:600; border:1.5px solid var(--page-border); cursor:pointer; transition:all var(--tr);
}
.btn-reply-cancel:hover { border-color:var(--gold); color:var(--gold-deep); }

/* card stagger */
<?php for($i=1;$i<=30;$i++): ?>
.msg-list .msg-card:nth-child(<?=$i?>) { animation:cardIn .4s <?=round(($i-1)*.05,2)?>s ease both; }
<?php endfor; ?>
</style>
</head>
<body>

<!-- ══ HERO ══ -->
<div class="page-hero">
    <div class="hero-inner">
        <div class="hero-left">
            <div class="hero-breadcrumb">
                <a href="/MEMOIR/admin/admin_dashboard.php">
                    <i class="fa-solid fa-gauge-high"></i> <?= $p['breadcrumb'] ?>
                </a>
                <i class="fa-solid fa-chevron-right"></i>
                <span><?= $p['bc_messages'] ?></span>
            </div>
            <h1 class="hero-title"><?= $p['hero_title'] ?> <span><?= $p['hero_span'] ?></span></h1>
            <span class="hero-date"><?= $p['today'] ?> · <?= date('d F Y') ?></span>
        </div>
        <div class="hero-right">
            <?php if ($non_lus > 0): ?>
            <div class="unread-pill">
                <i class="fa-solid fa-envelope"></i>
                <?= $non_lus ?> <?= $non_lus > 1 ? $p['unread_pills'] : $p['unread_pill'] ?>
            </div>
            <?php endif; ?>
            <a href="/MEMOIR/admin/admin_dashboard.php" class="btn-back">
                <i class="fa-solid fa-arrow-left" style="font-size:10px"></i>
                <?= $p['btn_back'] ?>
            </a>
        </div>
    </div>
</div>

<!-- ══ STATS BAR ══ -->
<div class="stats-bar">
    <div class="stat-pill">
        <span class="stat-dot" style="background:var(--gold)"></span>
        <span class="stat-label"><?= $p['stat_total'] ?></span>
        <span class="stat-num"><?= $total ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:var(--danger)"></span>
        <span class="stat-label"><?= $p['stat_unread'] ?></span>
        <span class="stat-num"><?= $non_lus ?></span>
    </div>
    <div class="stat-pill">
        <span class="stat-dot" style="background:var(--success)"></span>
        <span class="stat-label"><?= $p['stat_read'] ?></span>
        <span class="stat-num"><?= $lus ?></span>
    </div>
</div>

<!-- ══ TOOLBAR ══ -->
<div class="toolbar">
    <div class="filter-pills">
        <a href="?filtre=tous"    class="fpill <?= $filtre==='tous'   ?'active':'' ?>">
            <i class="fa-solid fa-inbox" style="font-size:10px"></i> <?= $p['filter_all'] ?> (<?= $total ?>)
        </a>
        <a href="?filtre=non_lus" class="fpill <?= $filtre==='non_lus'?'active':'' ?>">
            <i class="fa-solid fa-circle" style="font-size:8px;color:var(--danger)"></i> <?= $p['filter_unread'] ?> (<?= $non_lus ?>)
        </a>
        <a href="?filtre=lus"     class="fpill <?= $filtre==='lus'    ?'active':'' ?>">
            <i class="fa-solid fa-circle-check" style="font-size:10px;color:var(--success)"></i> <?= $p['filter_read'] ?> (<?= $lus ?>)
        </a>
    </div>
    <?php if ($non_lus > 0): ?>
    <button class="btn-mark-all" onclick="markAllRead()">
        <i class="fa-solid fa-check-double" style="font-size:10px"></i>
        <?= $p['mark_all'] ?>
    </button>
    <?php endif; ?>
</div>

<!-- ══ MESSAGE LIST ══ -->
<div class="msg-wrap">
    <?php if (empty($messages)): ?>
    <div class="empty-state">
        <div class="empty-icon"><i class="fa-regular fa-envelope-open"></i></div>
        <h3><?= $p['empty_h'] ?><?= $filtre!=='tous' ? $p['empty_cat'] : '' ?></h3>
        <p><?= $p['empty_p'] ?></p>
    </div>
    <?php else: ?>
    <div class="msg-list" id="msgList">
    <?php foreach ($messages as $m):
        $is_lu     = (int)$m['lu'] === 1;
        $letter    = strtoupper(substr($m['name'] ?? 'U', 0, 1));
        $full      = $m['message'];
        $preview   = mb_strlen($full) > 130 ? mb_substr($full, 0, 130).'…' : $full;
        $time      = date('d/m/Y à H:i', strtotime($m['created_at']));
        $has_reply = !empty($m['reponse']);
    ?>
    <div class="msg-card <?= $is_lu?'read':'unread' ?>" id="msg-<?= $m['id'] ?>">

        <div class="msg-avatar <?= $is_lu?'read':'' ?>"><?= $letter ?></div>

        <div class="msg-body">
            <div class="msg-top">
                <span class="msg-name"><?= htmlspecialchars($m['name']) ?></span>
                <span class="msg-email"><?= htmlspecialchars($m['email']) ?></span>
                <span class="msg-subject"><?= htmlspecialchars($m['subject'] ?? 'Message') ?></span>
                <?php if (!empty($m['id_user'])): ?>
                <span class="verified-tag">
                    <i class="fa-solid fa-circle-check" style="font-size:9px"></i> <?= $p['verified'] ?>
                </span>
                <?php endif; ?>
                <?php if ($has_reply): ?>
                <span class="replied-tag">
                    <i class="fa-solid fa-reply" style="font-size:9px"></i> <?= $p['replied_lbl'] ?>
                </span>
                <?php endif; ?>
            </div>

            <div class="msg-text" id="txt-<?= $m['id'] ?>">
                <?= nl2br(htmlspecialchars($preview)) ?>
            </div>

            <?php if (mb_strlen($full) > 130): ?>
            <button class="read-more" id="btn-<?= $m['id'] ?>"
                    onclick="toggleMsg(<?= $m['id'] ?>, <?= htmlspecialchars(json_encode($full)) ?>)">
                <i class="fa-solid fa-chevron-down" style="font-size:9px"></i> <?= $p['read_more'] ?>
            </button>
            <?php endif; ?>

            <?php if ($has_reply): ?>
            <div class="prev-reply">
                <strong><?= $p['prev_reply'] ?></strong>
                <?= nl2br(htmlspecialchars($m['reponse'])) ?>
            </div>
            <?php endif; ?>

            <div class="msg-time">
                <i class="fa-regular fa-clock"></i>
                <?= $time ?>
            </div>
        </div>

        <div class="msg-actions">
            <?php if (!$is_lu): ?>
            <div class="unread-dot" id="dot-<?= $m['id'] ?>"></div>
            <button class="act-btn" onclick="markRead(<?= $m['id'] ?>)" title="<?= $p['title_read'] ?>">
                <i class="fa-solid fa-check"></i>
            </button>
            <?php endif; ?>
            <button class="act-btn act-reply"
                    onclick="openReply(<?= $m['id'] ?>, <?= htmlspecialchars(json_encode($m['name'])) ?>, <?= htmlspecialchars(json_encode($m['email'])) ?>, <?= htmlspecialchars(json_encode($m['subject'] ?? '')) ?>, <?= htmlspecialchars(json_encode($full)) ?>)"
                    title="<?= $p['title_reply'] ?>">
                <i class="fa-solid fa-reply"></i>
            </button>
            <button class="act-btn act-del" onclick="deleteMsg(<?= $m['id'] ?>)" title="<?= $p['title_delete'] ?>">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>

    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ══ REPLY MODAL ══ -->
<div class="reply-overlay" id="replyOverlay" onclick="closeReplyOnOverlay(event)">
    <div class="reply-modal" id="replyModal">
        <div class="reply-modal-head">
            <div class="reply-modal-title">
                <?= $p['reply_title'] ?> <em id="replyToName"></em>
            </div>
            <button class="reply-close" onclick="closeReply()">✕</button>
        </div>
        <div class="reply-modal-body">
            <div class="reply-quote" id="replyQuote"></div>
            <label class="reply-field-lbl"><?= $p['reply_subject'] ?></label>
            <input type="text" class="reply-subject-input" id="replySubject">
            <label class="reply-field-lbl"><?= $p['reply_body'] ?></label>
            <textarea class="reply-textarea" id="replyBody" placeholder="<?= htmlspecialchars($p['reply_ph']) ?>"></textarea>
            <div class="reply-modal-foot">
                <button class="btn-reply-cancel" onclick="closeReply()"><?= $p['reply_cancel'] ?></button>
                <button class="btn-reply-send" id="btnSendReply" onclick="sendReply()">
                    <i class="fa-solid fa-paper-plane" style="font-size:10px"></i>
                    <?= $p['reply_send'] ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

<script>
const L = {
    delTitle : <?= json_encode($p['swal_del_title']) ?>,
    delText  : <?= json_encode($p['swal_del_text']) ?>,
    delYes   : <?= json_encode($p['swal_del_yes']) ?>,
    delNo    : <?= json_encode($p['swal_del_no']) ?>,
    delDone  : <?= json_encode($p['swal_del_done']) ?>,
    delOk    : <?= json_encode($p['swal_del_ok']) ?>,
    readMore : <?= json_encode($p['read_more']) ?>,
    collapse : <?= json_encode($p['collapse']) ?>,
    sending  : <?= json_encode($p['reply_sending']) ?>,
    sendBtn  : <?= json_encode($p['reply_send']) ?>,
    success  : <?= json_encode($p['reply_success']) ?>,
    successT : <?= json_encode($p['reply_success_t']) ?>,
    error    : <?= json_encode($p['reply_error']) ?>,
};

let currentReplyId = null;

function post(data) {
    return fetch(window.location.pathname, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    }).then(r => r.json());
}

function markRead(id) {
    post({action:'mark_read', id}).then(() => {
        const card = document.getElementById('msg-' + id);
        if (!card) return;
        card.classList.replace('unread', 'read');
        const av  = card.querySelector('.msg-avatar');
        const dot = document.getElementById('dot-' + id);
        const bar = card.querySelector('.act-btn:not(.act-del):not(.act-reply)');
        if (av)  av.classList.add('read');
        if (dot) { dot.style.opacity = '0'; setTimeout(() => dot.remove(), 300); }
        if (bar) { bar.style.opacity = '0'; setTimeout(() => bar.remove(), 300); }
    });
}

function markAllRead() {
    post({action: 'mark_all_read'}).then(() => location.reload());
}

function deleteMsg(id) {
    Swal.fire({
        title: L.delTitle, text: L.delText, icon: 'warning',
        showCancelButton: true,
        confirmButtonText: L.delYes, cancelButtonText: L.delNo,
        confirmButtonColor: '#C0392B', cancelButtonColor: '#C4A46B',
        background: '#FFFDF9', color: '#2A1F14',
    }).then(result => {
        if (!result.isConfirmed) return;
        post({action: 'delete', id}).then(() => {
            Swal.fire({
                title: L.delDone, text: L.delOk, icon: 'success',
                timer: 1600, showConfirmButton: false,
                background: '#FFFDF9', color: '#2A1F14',
            });
            const card = document.getElementById('msg-' + id);
            if (!card) return;
            card.style.transition = 'opacity .3s, transform .3s, max-height .4s';
            card.style.opacity = '0';
            card.style.transform = 'translateX(24px)';
            card.style.maxHeight = '0';
            card.style.overflow = 'hidden';
            setTimeout(() => card.remove(), 420);
        });
    });
}

function toggleMsg(id, full) {
    const txt = document.getElementById('txt-' + id);
    const btn = document.getElementById('btn-' + id);
    if (!txt || !btn) return;
    const isCollapsed = btn.dataset.state !== 'expanded';
    if (isCollapsed) {
        txt.innerHTML = full.replace(/\n/g, '<br>');
        txt.classList.add('expanded');
        btn.innerHTML = '<i class="fa-solid fa-chevron-up" style="font-size:9px"></i> ' + L.collapse;
        btn.dataset.state = 'expanded';
    } else {
        const short = full.length > 130 ? full.slice(0, 130) + '…' : full;
        txt.innerHTML = short.replace(/\n/g, '<br>');
        txt.classList.remove('expanded');
        btn.innerHTML = '<i class="fa-solid fa-chevron-down" style="font-size:9px"></i> ' + L.readMore;
        btn.dataset.state = 'collapsed';
    }
}

function openReply(id, name, email, subject, msgFull) {
    currentReplyId = id;
    document.getElementById('replyToName').textContent  = name + ' <' + email + '>';
    document.getElementById('replyQuote').innerHTML     = (msgFull || '').replace(/\n/g, '<br>');
    document.getElementById('replySubject').value       = 'Re: ' + (subject || '');
    document.getElementById('replyBody').value          = '';
    document.getElementById('replyOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('replyBody').focus(), 250);
}

function closeReply() {
    document.getElementById('replyOverlay').classList.remove('open');
    document.body.style.overflow = '';
    currentReplyId = null;
}

function closeReplyOnOverlay(e) {
    if (e.target === document.getElementById('replyOverlay')) closeReply();
}

function sendReply() {
    if (!currentReplyId) return;
    const body = document.getElementById('replyBody').value.trim();
    if (!body) { document.getElementById('replyBody').focus(); return; }

    const btn = document.getElementById('btnSendReply');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="font-size:10px"></i> ' + L.sending;

    post({action: 'reply', id: currentReplyId, body})
        .then(res => {
            closeReply();
            if (res.ok) {
                Swal.fire({
                    title: L.success, text: L.successT, icon: 'success',
                    timer: 2000, showConfirmButton: false,
                    background: '#FFFDF9', color: '#2A1F14',
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    title: L.error, text: res.error || '',
                    icon: 'error', background: '#FFFDF9', color: '#2A1F14',
                });
            }
        })
        .catch(() => {
            closeReply();
            Swal.fire({ title: L.error, icon: 'error', background: '#FFFDF9', color: '#2A1F14' });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane" style="font-size:10px"></i> ' + L.sendBtn;
        });
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeReply();
});
</script>
</body>
</html>