<?php
ob_start();
session_start();
require_once "../includes/db.php";

function redirect_with_msg(string $url, string $type, string $msg): never {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_msg']  = $msg;
    header("Location: $url");
    exit;
}

// ── POST handler ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_doc   = (int)($_POST['id_doc']   ?? 0);
    $quantite = (int)($_POST['quantite'] ?? 0);
    $note     = trim($_POST['note'] ?? '');

    if ($id_doc <= 0 || $quantite <= 0)
        redirect_with_msg("restock.php?id=$id_doc", 'error', 'Quantité invalide.');

    $stmt = $conn->prepare("SELECT * FROM documents WHERE id_doc = ?");
    $stmt->bind_param("i", $id_doc); $stmt->execute();
    $doc = $stmt->get_result()->fetch_assoc(); $stmt->close();

    if (!$doc) redirect_with_msg("stock_epuise.php", 'error', 'Document introuvable.');

    $ancien_stock  = max(0, (int)$doc['exemplaires_disponibles']);
    $ancien_total  = max(0, (int)$doc['exemplaires']);
    $nouveau_stock = $ancien_stock + $quantite;
    $nouveau_total = $ancien_total + $quantite;
    $nouveau_prix  = !empty($_POST['nouveau_prix']) ? (float)$_POST['nouveau_prix'] : (float)$doc['prix'];

    $conn->begin_transaction();
    try {
        $upd = $conn->prepare("UPDATE documents SET exemplaires_disponibles=?, exemplaires=?, prix=? WHERE id_doc=?");
        $upd->bind_param("iidi", $nouveau_stock, $nouveau_total, $nouveau_prix, $id_doc);
        $upd->execute();

        $admin_id  = $_SESSION['id_user'] ?? $_SESSION['id'] ?? 1;
        $type_doc  = $doc['disponible_pour'] ?? 'both';
        $ins = $conn->prepare("INSERT INTO restock_transactions (id_doc, admin_id, type, quantite_ajoutee, ancien_stock, nouveau_stock, ancien_prix, nouveau_prix, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->bind_param("iiisiidds", $id_doc, $admin_id, $type_doc, $quantite, $ancien_stock, $nouveau_stock, $doc['prix'], $nouveau_prix, $note);
        $ins->execute(); $ins->close();
        $conn->commit();
        redirect_with_msg("stock_epuise.php", 'success', 'Stock mis à jour avec succès !');
    } catch (Throwable $e) {
        $conn->rollback();
        redirect_with_msg("restock.php?id=$id_doc", 'error', 'Erreur : ' . $e->getMessage());
    }
}

// ── GET ───────────────────────────────────────────────────
include "../includes/header.php";
include '../includes/dark_init.php';
include_once '../includes/languages.php';

$id_doc = (int)($_GET['id'] ?? 0);
if ($id_doc <= 0) { header("Location: stock_epuise.php"); exit; }

$stmt = $conn->prepare("SELECT d.*, t.libelle_type AS nom_type FROM documents d LEFT JOIN types_documents t ON d.id_type = t.id_type WHERE d.id_doc = ?");
$stmt->bind_param("i", $id_doc); $stmt->execute();
$doc = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$doc) redirect_with_msg("stock_epuise.php", 'error', 'Document introuvable.');

$type     = $doc['disponible_pour'];
$is_pret  = in_array($type, ['emprunt','both']);
$is_vente = in_array($type, ['achat','both']);
$isRtl    = ($lang === 'ar');

// Borrowers
$borrowers = [];
if ($is_pret) {
    $bq = $conn->prepare("SELECT e.id_emprunt,e.date_debut,e.date_fin,e.date_retour_prevue,e.statut,u.firstname,u.lastname,u.email FROM emprunt e JOIN users u ON e.id_user=u.id WHERE e.id_doc=? AND e.statut='en_cours' ORDER BY e.date_retour_prevue ASC");
    $bq->bind_param("i", $id_doc); $bq->execute();
    $res = $bq->get_result();
    while ($row = $res->fetch_assoc()) $borrowers[] = $row;
    $bq->close();
}

// History
$history_res = $conn->query("SELECT rt.*, u.firstname AS admin_name FROM restock_transactions rt JOIN users u ON rt.admin_id=u.id WHERE rt.id_doc=$id_doc ORDER BY rt.created_at DESC LIMIT 10");
$history = [];
if ($history_res) while ($h = $history_res->fetch_assoc()) $history[] = $h;

$flash_type = $_SESSION['flash_type'] ?? '';
$flash_msg  = $_SESSION['flash_msg']  ?? '';
unset($_SESSION['flash_type'], $_SESSION['flash_msg']);

// Cover image
$img = '/MEMOIR/uploads/' . (int)$doc['id_doc'] . '.jpg';
$img_exists = file_exists($_SERVER['DOCUMENT_ROOT'] . $img);
if (!$img_exists && !empty($doc['image_doc'])) {
    $img = '/MEMOIR/uploads/' . $doc['image_doc'];
    $img_exists = file_exists($_SERVER['DOCUMENT_ROOT'] . $img);
}
if (!$img_exists) $img = '';

// Labels
$pg = [
    'fr' => [
        'title'=>'Réapprovisionner le stock','sub'=>'Ajoutez des exemplaires et mettez à jour les informations',
        'back'=>'Retour','breadcrumb_dash'=>'Tableau de bord','breadcrumb_stock'=>'Stocks épuisés',
        'borrowers_title'=>'Emprunteurs actuels','borrowers_none'=>'Aucun emprunt actif',
        'form_title_both'=>'Réapprovisionner (Prêt & Vente)','form_title_vente'=>'Réapprovisionner & Tarification','form_title_pret'=>'Ajouter des exemplaires',
        'qty_lbl'=>'Exemplaires à ajouter','qty_ph'=>'ex: 5','qty_hint'=>'Stock actuel : 0 → deviendra',
        'price_lbl'=>'Nouveau prix de vente (DA)','price_opt'=>'optionnel',
        'note_lbl'=>'Note (optionnelle)','note_ph'=>'Ex: Livraison fournisseur du 12/05/2026…',
        'submit'=>'Confirmer le réapprovisionnement',
        'history_title'=>'Historique des réapprovisionnements','history_sub'=>'10 dernières opérations',
        'disponibles'=>'Disponibles','total_ex'=>'Total ex.','prix_actuel'=>'Prix actuel','en_cours'=>'En cours',
        'lecteur'=>'Lecteur','email'=>'Email','emprunte_le'=>'Emprunté le','retour_prevu'=>'Retour prévu','statut'=>'Statut',
        'en_retard'=>'En retard','bientot'=>'Bientôt','a_temps'=>'À temps',
        'par'=>'Par','stock_arrow'=>'Stock','prix_arrow'=>'Prix',
        'pret_lbl'=>'Prêt','vente_lbl'=>'Vente','both_lbl'=>'Prêt & Vente','epuise_lbl'=>'Épuisé',
    ],
    'en' => [
        'title'=>'Restock document','sub'=>'Add copies and update document information',
        'back'=>'Back','breadcrumb_dash'=>'Dashboard','breadcrumb_stock'=>'Out of stock',
        'borrowers_title'=>'Current borrowers','borrowers_none'=>'No active loans',
        'form_title_both'=>'Restock (Loan & Sale)','form_title_vente'=>'Restock & Pricing','form_title_pret'=>'Add copies',
        'qty_lbl'=>'Copies to add','qty_ph'=>'e.g. 5','qty_hint'=>'Current stock: 0 → will become',
        'price_lbl'=>'New sale price (DA)','price_opt'=>'optional',
        'note_lbl'=>'Note (optional)','note_ph'=>'e.g. Supplier delivery 12/05/2026…',
        'submit'=>'Confirm restock',
        'history_title'=>'Restock history','history_sub'=>'Last 10 operations',
        'disponibles'=>'Available','total_ex'=>'Total copies','prix_actuel'=>'Current price','en_cours'=>'Active',
        'lecteur'=>'Reader','email'=>'Email','emprunte_le'=>'Borrowed on','retour_prevu'=>'Due date','statut'=>'Status',
        'en_retard'=>'Overdue','bientot'=>'Soon','a_temps'=>'On time',
        'par'=>'By','stock_arrow'=>'Stock','prix_arrow'=>'Price',
        'pret_lbl'=>'Loan','vente_lbl'=>'Sale','both_lbl'=>'Loan & Sale','epuise_lbl'=>'Out of stock',
    ],
    'ar' => [
        'title'=>'إعادة تخزين الوثيقة','sub'=>'أضف نسخاً وحدّث معلومات الوثيقة',
        'back'=>'رجوع','breadcrumb_dash'=>'لوحة التحكم','breadcrumb_stock'=>'المخزون النافد',
        'borrowers_title'=>'المستعيرون الحاليون','borrowers_none'=>'لا توجد استعارات نشطة',
        'form_title_both'=>'إعادة التخزين (استعارة وبيع)','form_title_vente'=>'إعادة التخزين والتسعير','form_title_pret'=>'إضافة نسخ',
        'qty_lbl'=>'النسخ المراد إضافتها','qty_ph'=>'مثال: 5','qty_hint'=>'المخزون الحالي: 0 → سيصبح',
        'price_lbl'=>'السعر الجديد (دج)','price_opt'=>'اختياري',
        'note_lbl'=>'ملاحظة (اختيارية)','note_ph'=>'مثال: تسليم مورد بتاريخ…',
        'submit'=>'تأكيد إعادة التخزين',
        'history_title'=>'سجل إعادة التخزين','history_sub'=>'آخر 10 عمليات',
        'disponibles'=>'متاح','total_ex'=>'إجمالي النسخ','prix_actuel'=>'السعر الحالي','en_cours'=>'نشطة',
        'lecteur'=>'القارئ','email'=>'البريد الإلكتروني','emprunte_le'=>'تاريخ الاستعارة','retour_prevu'=>'تاريخ الإعادة','statut'=>'الحالة',
        'en_retard'=>'متأخر','bientot'=>'قريباً','a_temps'=>'في الوقت',
        'par'=>'بواسطة','stock_arrow'=>'المخزون','prix_arrow'=>'السعر',
        'pret_lbl'=>'استعارة','vente_lbl'=>'بيع','both_lbl'=>'استعارة وبيع','epuise_lbl'=>'نافد',
    ],
];
$l = $pg[$lang] ?? $pg['fr'];
?>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════════════════
   AURALIB · restock.php — Premium Luxury CSS
   ═══════════════════════════════════════════════════════════ */
:root {
    --gold:          #C4A46B;
    --gold2:         #D4B47B;
    --gold-deep:     #A8884E;
    --gold-faint:    rgba(196,164,107,.08);
    --gold-border:   rgba(196,164,107,.22);
    --gold-shadow:   0 6px 24px rgba(196,164,107,.18);
    --ink:           #1A0E05;
    --ink2:          #2C1F0E;
    --ink3:          #3A2A14;
    --page-bg:       #F2EDE3;
    --page-bg2:      #EDE5D4;
    --page-white:    #FDFAF5;
    --page-text:     #2A1F14;
    --page-muted:    #9A8C7E;
    --page-border:   #D8CFC0;
    --success:       #276749;
    --success-bg:    rgba(39,103,73,.08);
    --success-border:rgba(39,103,73,.22);
    --danger:        #C0392B;
    --danger-bg:     rgba(192,57,43,.08);
    --danger-border: rgba(192,57,43,.22);
    --warning:       #92400E;
    --warning-bg:    rgba(146,64,14,.08);
    --info:          #1B4F8A;
    --info-bg:       rgba(27,79,138,.08);
    --font-serif:    'EB Garamond', Georgia, serif;
    --font-ui:       'Plus Jakarta Sans', sans-serif;
    --nav-h:         68px;
    --radius:        16px;
    --radius-sm:     10px;
    --shadow-sm:     0 2px 12px rgba(42,31,20,.06);
    --shadow-md:     0 8px 30px rgba(42,31,20,.10);
    --ease:          cubic-bezier(.4,0,.2,1);
    --tr:            .22s var(--ease);
}
html.dark {
    --page-bg: #100C07; --page-bg2: #1A1308; --page-white: #1E1610;
    --page-text: #EDE5D4; --page-muted: #9A8C7E; --page-border: #3A2E1E;
    --ink: #0A0603; --ink2: #1A1308; --ink3: #2A1F0E;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
@keyframes fadeUp  { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
@keyframes slideIn { from{opacity:0;transform:translateX(-8px)} to{opacity:1;transform:translateX(0)} }

.rs-wrap{
    width:100%;
    min-height:100vh;
    background:var(--page-bg);
    padding-top:var(--nav-h);
    direction:<?= $isRtl ? 'rtl' : 'ltr' ?>;
}
.rs-main{
    width:min(96%, 1800px);
    margin:auto;
    padding:48px 0 90px;
}
.rs-hero,
.rs-book-card,
.rs-card{
    border-radius:24px;
}
.rs-book-card{
    min-height:260px;
}
.rs-book-cover-wrap{
    width:190px;
}

/* ── FLASH ───────────────────────────────────────────────── */
.rs-flash {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 18px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 600; margin-bottom: 24px;
    animation: slideIn .3s var(--ease) both;
}
.rs-flash.success { background: var(--success-bg); border: 1px solid var(--success-border); color: var(--success); }
.rs-flash.error   { background: var(--danger-bg);  border: 1px solid var(--danger-border);  color: var(--danger); }

/* ── HERO ────────────────────────────────────────────────── */
.rs-hero {
    background:
        radial-gradient(ellipse 55% 120% at 8% 55%, rgba(196,164,107,.07) 0%, transparent 55%),
        linear-gradient(155deg, #0D0805 0%, #1C1208 50%, #0D0805 100%);
    border-radius: var(--radius); padding: 28px 32px; margin-bottom: 28px;
    display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap;
    position: relative; overflow: hidden;
    border: 1px solid rgba(196,164,107,.12);
    box-shadow: 0 8px 40px rgba(0,0,0,.22);
    animation: fadeUp .45s var(--ease) both;
}
.rs-hero::after { content:''; position:absolute; bottom:0; left:0; right:0; height:1px; background:linear-gradient(90deg,transparent,rgba(196,164,107,.3),transparent); }
.rs-breadcrumb {
    font-size: 10px; color: rgba(196,164,107,.38); letter-spacing: 1px;
    margin-bottom: 7px; display: flex; align-items: center; gap: 7px;
}
.rs-breadcrumb a { color: rgba(196,164,107,.55); text-decoration: none; transition: color var(--tr); }
.rs-breadcrumb a:hover { color: var(--gold); }
.rs-title {
    font-family: var(--font-serif); font-size: clamp(22px,3.5vw,36px);
    font-weight: 700; color: #FDFAF5; line-height: 1;
}
.rs-title span { background: linear-gradient(135deg,var(--gold),var(--gold2)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
.rs-sub { font-size: 12px; color: rgba(255,255,255,.3); margin-top: 6px; }
.rs-back-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 22px; border-radius: 50px;
    background: var(--gold-faint); border: 1.5px solid var(--gold-border);
    color: rgba(196,164,107,.75); font-family: var(--font-ui);
    font-size: 12px; font-weight: 700; text-decoration: none; transition: all var(--tr);
}
.rs-back-btn:hover { background: rgba(196,164,107,.16); color: var(--gold); }

/* ── BOOK IDENTITY ───────────────────────────────────────── */
.rs-book-card {
    background: var(--page-white); border: 1px solid var(--page-border);
    border-radius: var(--radius); overflow: hidden; margin-bottom: 24px;
    box-shadow: var(--shadow-sm); display: flex; gap: 0;
    animation: fadeUp .45s .06s var(--ease) both;
    border-left: 3px solid var(--gold);
}
.rs-book-cover-wrap {
    width: 130px; flex-shrink: 0; position: relative; overflow: hidden;
    background: linear-gradient(135deg, var(--ink) 0%, var(--ink3) 100%);
}
.rs-book-cover-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.rs-book-cover-fallback {
    width: 100%; height: 100%; min-height: 160px;
    display: flex; align-items: center; justify-content: center; padding: 14px;
}
.rs-book-spine {
    padding: 12px 10px; border-radius: 5px; text-align: center;
    box-shadow: 0 4px 16px rgba(0,0,0,.4);
    display: flex; flex-direction: column; align-items: center; gap: 7px;
    width: 90px;
}
.rs-book-spine-title { font-family: var(--font-serif); font-size: 10px; color: rgba(255,255,255,.8); line-height: 1.4; word-break: break-word; }
.rs-book-spine-line  { width: 35px; height: 1px; background: rgba(255,255,255,.2); }

.rs-book-body { flex: 1; padding: 22px 24px; min-width: 0; }
.rs-book-title {
    font-family: var(--font-serif); font-size: 22px; font-weight: 700;
    color: var(--page-text); margin-bottom: 4px; line-height: 1.2;
}
.rs-book-author { font-size: 12px; color: var(--page-muted); margin-bottom: 14px; display: flex; align-items: center; gap: 5px; }
.rs-book-pills  { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 16px; }
.rs-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 10px; font-weight: 700; padding: 4px 12px; border-radius: 20px; border: 1.5px solid;
}
.rs-pill-pret   { background: var(--info-bg);    border-color: rgba(27,79,138,.22);  color: var(--info);    }
.rs-pill-vente  { background: var(--warning-bg); border-color: rgba(146,64,14,.22);  color: var(--warning); }
.rs-pill-both   { background: var(--success-bg); border-color: var(--success-border);color: var(--success); }
.rs-pill-epuise { background: var(--danger-bg);  border-color: var(--danger-border); color: var(--danger);  }

.rs-book-stats { display: flex; gap: 24px; flex-wrap: wrap; padding-top: 14px; border-top: 1px solid var(--page-border); }
.rs-stat-item { display: flex; flex-direction: column; gap: 3px; }
.rs-stat-val {
    font-family: var(--font-ui); font-size: 24px; font-weight: 700;
    color: var(--page-text); line-height: 1;
}
.rs-stat-val.danger { color: var(--danger); }
.rs-stat-val.warning{ color: var(--warning); }
.rs-stat-lbl { font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--page-muted); }

/* ── SECTION HEADER ──────────────────────────────────────── */
.rs-section-head {
    display: flex; align-items: center; gap: 14px;
    margin-bottom: 16px; margin-top: 28px;
    animation: fadeUp .45s .1s var(--ease) both;
}
.rs-section-icon {
    width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid;
}
.si-gold  { background: var(--gold-faint);   border-color: var(--gold-border); }
.si-gold  i { color: var(--gold-deep); }
.si-blue  { background: var(--info-bg);      border-color: rgba(27,79,138,.2); }
.si-blue  i { color: var(--info); }
.si-green { background: var(--success-bg);   border-color: var(--success-border); }
.si-green i { color: var(--success); }
.rs-section-icon i { font-size: 16px; }
.rs-section-title { font-family: var(--font-serif); font-size: 18px; font-weight: 700; color: var(--page-text); }
.rs-section-sub   { font-size: 11px; color: var(--page-muted); margin-top: 2px; }

/* ── LUX CARD ────────────────────────────────────────────── */
.rs-card {
    background: var(--page-white); border: 1px solid var(--page-border);
    border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden;
    animation: fadeUp .45s .12s var(--ease) both;
}

/* ── BORROWERS TABLE ─────────────────────────────────────── */
.rs-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.rs-table th {
    padding: 10px 18px; text-align: <?= $isRtl ? 'right' : 'left' ?>;
    font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;
    color: var(--page-muted); background: var(--page-bg2); border-bottom: 1px solid var(--page-border);
}
.rs-table td {
    padding: 12px 18px; border-bottom: 1px solid var(--page-border);
    color: var(--page-text); text-align: <?= $isRtl ? 'right' : 'left' ?>;
    transition: background var(--tr);
}
.rs-table tbody tr:last-child td { border-bottom: none; }
.rs-table tbody tr:hover td { background: var(--gold-faint); }
.rs-status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; border: 1px solid;
}
.sb-red   { background: var(--danger-bg);  color: var(--danger);  border-color: var(--danger-border); }
.sb-amber { background: var(--warning-bg); color: var(--warning); border-color: rgba(146,64,14,.2); }
.sb-green { background: var(--success-bg); color: var(--success); border-color: var(--success-border); }

/* ── FORM ────────────────────────────────────────────────── */
.rs-form-body { padding: 24px; }
.rs-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
.rs-form-grid.single { grid-template-columns: 1fr; }
.rs-field { display: flex; flex-direction: column; gap: 6px; }
.rs-label {
    font-size: 10px; font-weight: 700; letter-spacing: .8px;
    text-transform: uppercase; color: var(--page-muted);
}
.rs-label .req { color: var(--danger); }
.rs-label .opt { font-weight: 400; letter-spacing: 0; text-transform: none; color: var(--page-muted); font-size: 10px; }
.rs-input, .rs-textarea {
    padding: 11px 14px; border: 1.5px solid var(--page-border);
    border-radius: var(--radius-sm); font-family: var(--font-ui); font-size: 13px;
    color: var(--page-text); background: var(--page-bg); outline: none;
    transition: border-color var(--tr), box-shadow var(--tr); width: 100%;
}
.rs-input:focus, .rs-textarea:focus {
    border-color: var(--gold-border); box-shadow: 0 0 0 3px rgba(196,164,107,.1);
    background: var(--page-white);
}
.rs-input-hint { font-size: 10px; color: var(--page-muted); }
.rs-input-hint strong { color: var(--gold); }
.rs-textarea { resize: vertical; min-height: 80px; }

/* Price preview */
.rs-price-preview {
    display: none; align-items: center; gap: 10px; flex-wrap: wrap;
    background: var(--gold-faint); border: 1px solid var(--gold-border);
    border-radius: var(--radius-sm); padding: 10px 14px;
    font-size: 12px; color: var(--page-muted); margin-top: 6px;
}
.rs-price-preview.show { display: flex; }
.rs-price-preview strong { color: var(--page-text); }
.rs-price-arrow { color: var(--gold); font-size: 16px; font-weight: 700; }

/* Submit */
.rs-submit-wrap { padding: 0 24px 24px; }
.rs-submit {
    width: 100%; padding: 14px 24px;
    background: linear-gradient(135deg, var(--ink2) 0%, var(--ink3) 100%);
    color: rgba(237,229,212,.9); border: 1.5px solid rgba(196,164,107,.25);
    border-radius: var(--radius-sm); font-family: var(--font-ui); font-size: 13px; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px;
    transition: all var(--tr); letter-spacing: .3px;
}
.rs-submit:hover {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-deep) 100%);
    color: var(--ink2); border-color: var(--gold);
    box-shadow: var(--gold-shadow); transform: translateY(-1px);
}
.rs-submit i { font-size: 14px; }

/* ── HISTORY ─────────────────────────────────────────────── */
.rs-history { padding: 8px 0; }
.rs-hist-item {
    display: flex; align-items: flex-start; gap: 16px;
    padding: 14px 22px; border-bottom: 1px solid var(--page-border);
    transition: background var(--tr);
}
.rs-hist-item:last-child { border-bottom: none; }
.rs-hist-item:hover { background: var(--gold-faint); }
.rs-hist-dot {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    background: var(--gold-faint); border: 1.5px solid var(--gold-border);
    display: flex; align-items: center; justify-content: center; margin-top: 2px;
}
.rs-hist-dot i { color: var(--gold-deep); font-size: 12px; }
.rs-hist-title { font-size: 13px; font-weight: 700; color: var(--page-text); line-height: 1.4; }
.rs-hist-title span { color: var(--success); }
.rs-hist-meta  { font-size: 11px; color: var(--page-muted); margin-top: 3px; }
.rs-hist-note  { font-size: 11px; color: var(--page-muted); font-style: italic; margin-top: 4px; opacity: .8; }

/* ── RESPONSIVE ──────────────────────────────────────────── */
@media (max-width: 640px) {
   .rs-main{
    width:100%;
    max-width:100%;
    padding:40px 2% 80px;
}
</style>

<div class="rs-wrap">
<div class="rs-main">

<?php if ($flash_msg): ?>
<div class="rs-flash <?= htmlspecialchars($flash_type) ?>">
    <i class="fa-solid fa-<?= $flash_type==='success'?'circle-check':'circle-exclamation' ?>"></i>
    <?= htmlspecialchars($flash_msg) ?>
</div>
<?php endif; ?>

<!-- HERO -->
<div class="rs-hero">
    <div>
        <div class="rs-breadcrumb">
            <a href="admin_dashboard.php"><i class="fa-solid fa-house" style="font-size:9px"></i> <?= $l['breadcrumb_dash'] ?></a>
            <i class="fa-solid fa-chevron-right" style="font-size:8px;opacity:.4"></i>
            <a href="stock_epuise.php"><?= $l['breadcrumb_stock'] ?></a>
            <i class="fa-solid fa-chevron-right" style="font-size:8px;opacity:.4"></i>
            <span><?= $l['title'] ?></span>
        </div>
        <div class="rs-title"><?php
            $parts = explode(' ', $l['title'], 2);
            echo htmlspecialchars($parts[0]);
            if (isset($parts[1])) echo ' <span>' . htmlspecialchars($parts[1]) . '</span>';
        ?></div>
        <div class="rs-sub"><?= $l['sub'] ?></div>
    </div>
    <a href="stock_epuise.php" class="rs-back-btn">
        <i class="fa-solid fa-arrow-<?= $isRtl?'right':'left' ?>" style="font-size:10px"></i>
        <?= $l['back'] ?>
    </a>
</div>

<!-- BOOK IDENTITY CARD -->
<div class="rs-book-card">
    <div class="rs-book-cover-wrap">
        <?php if ($img_exists && $img): ?>
        <img class="rs-book-cover-img"
             src="<?= htmlspecialchars($img) ?>"
             alt="<?= htmlspecialchars($doc['titre']) ?>"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="rs-book-cover-fallback" style="display:none">
        <?php else: ?>
        <div class="rs-book-cover-fallback">
        <?php endif; ?>
            <div class="rs-book-spine" style="background:<?= htmlspecialchars($doc['cover_color'] ?: 'linear-gradient(145deg,#1a0a2e,#3d1054)') ?>">
                <div class="rs-book-spine-title"><?= htmlspecialchars(mb_substr($doc['titre'],0,35)) ?></div>
                <div class="rs-book-spine-line"></div>
                <?php if (!empty($doc['auteur'])): ?>
                <div style="font-size:9px;color:rgba(255,255,255,.45);text-align:center"><?= htmlspecialchars(mb_substr($doc['auteur'],0,18)) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="rs-book-body">
        <div class="rs-book-title"><?= htmlspecialchars($doc['titre']) ?></div>
        <?php if (!empty($doc['auteur'])): ?>
        <div class="rs-book-author"><i class="fa-solid fa-user-pen" style="font-size:10px"></i> <?= htmlspecialchars($doc['auteur']) ?></div>
        <?php endif; ?>
        <div class="rs-book-pills">
            <?php if ($is_pret && !$is_vente): ?>
            <span class="rs-pill rs-pill-pret"><i class="fa-solid fa-book-open" style="font-size:9px"></i> <?= $l['pret_lbl'] ?></span>
            <?php elseif ($is_vente && !$is_pret): ?>
            <span class="rs-pill rs-pill-vente"><i class="fa-solid fa-cart-shopping" style="font-size:9px"></i> <?= $l['vente_lbl'] ?></span>
            <?php else: ?>
            <span class="rs-pill rs-pill-both"><i class="fa-solid fa-layer-group" style="font-size:9px"></i> <?= $l['both_lbl'] ?></span>
            <?php endif; ?>
            <span class="rs-pill rs-pill-epuise"><i class="fa-solid fa-triangle-exclamation" style="font-size:9px"></i> <?= $l['epuise_lbl'] ?></span>
            <?php if (!empty($doc['langue'])): ?>
            <span class="rs-pill" style="background:var(--page-bg2);border-color:var(--page-border);color:var(--page-muted)">
                <i class="fa-solid fa-globe" style="font-size:9px"></i> <?= htmlspecialchars($doc['langue']) ?>
            </span>
            <?php endif; ?>
        </div>
        <div class="rs-book-stats">
            <div class="rs-stat-item">
                <div class="rs-stat-val danger">0</div>
                <div class="rs-stat-lbl"><?= $l['disponibles'] ?></div>
            </div>
            <div class="rs-stat-item">
                <div class="rs-stat-val"><?= (int)$doc['exemplaires'] ?></div>
                <div class="rs-stat-lbl"><?= $l['total_ex'] ?></div>
            </div>
            <?php if ($doc['prix'] > 0): ?>
            <div class="rs-stat-item">
                <div class="rs-stat-val"><?= number_format($doc['prix'], 0, ',', ' ') ?></div>
                <div class="rs-stat-lbl"><?= $l['prix_actuel'] ?> (DA)</div>
            </div>
            <?php endif; ?>
            <?php if (count($borrowers) > 0): ?>
            <div class="rs-stat-item">
                <div class="rs-stat-val warning"><?= count($borrowers) ?></div>
                <div class="rs-stat-lbl"><?= $l['en_cours'] ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- BORROWERS -->
<?php if ($is_pret): ?>
<div class="rs-section-head">
    <div class="rs-section-icon si-blue"><i class="fa-solid fa-users"></i></div>
    <div>
        <div class="rs-section-title"><?= $l['borrowers_title'] ?></div>
        <div class="rs-section-sub">
            <?= count($borrowers) > 0 ? count($borrowers) . ' exemplaire(s) en circulation' : $l['borrowers_none'] ?>
        </div>
    </div>
</div>
<?php if (count($borrowers) > 0): ?>
<div class="rs-card">
    <table class="rs-table">
        <thead><tr>
            <th><?= $l['lecteur'] ?></th><th><?= $l['email'] ?></th>
            <th><?= $l['emprunte_le'] ?></th><th><?= $l['retour_prevu'] ?></th><th><?= $l['statut'] ?></th>
        </tr></thead>
        <tbody>
        <?php foreach ($borrowers as $b):
            $today  = new DateTime();
            $due    = $b['date_retour_prevue'] ? new DateTime($b['date_retour_prevue']) : null;
            $diff   = $due ? (int)$today->diff($due)->format('%r%a') : null;
            $bc     = 'sb-green'; $bt = $l['a_temps'];
            if ($diff !== null) {
                if ($diff < 0)    { $bc = 'sb-red';   $bt = $l['en_retard'] . ' (' . abs($diff) . ' j)'; }
                elseif ($diff < 5){ $bc = 'sb-amber';  $bt = $l['bientot'] . ' (' . $diff . ' j)'; }
            }
        ?>
        <tr>
            <td style="font-weight:700"><?= htmlspecialchars($b['firstname'].' '.$b['lastname']) ?></td>
            <td style="color:var(--page-muted)"><?= htmlspecialchars($b['email']) ?></td>
            <td><?= $b['date_debut'] ? date('d/m/Y', strtotime($b['date_debut'])) : '—' ?></td>
            <td style="<?= $diff < 0 ? 'color:var(--danger);font-weight:700' : '' ?>"><?= $due ? $due->format('d/m/Y') : '—' ?></td>
            <td><span class="rs-status-badge <?= $bc ?>"><?= $bt ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; endif; ?>

<!-- RESTOCK FORM -->
<div class="rs-section-head">
    <div class="rs-section-icon si-gold"><i class="fa-solid fa-rotate-right"></i></div>
    <div>
        <div class="rs-section-title">
            <?php if ($is_vente && $is_pret) echo $l['form_title_both'];
            elseif ($is_vente)               echo $l['form_title_vente'];
            else                             echo $l['form_title_pret']; ?>
        </div>
        <div class="rs-section-sub">
            <?= $is_vente ? 'Mise à jour du stock et du prix de vente' : 'Ajout manuel de copies physiques' ?>
        </div>
    </div>
</div>

<div class="rs-card">
    <div class="rs-form-body">
        <form method="POST" action="restock.php">
            <input type="hidden" name="id_doc" value="<?= $id_doc ?>">
            <div class="rs-form-grid <?= !$is_vente ? 'single' : '' ?>">
                <div class="rs-field">
                    <label class="rs-label" for="quantite"><?= $l['qty_lbl'] ?> <span class="req">*</span></label>
                    <input type="number" id="quantite" name="quantite" class="rs-input"
                           min="1" max="9999" placeholder="<?= $l['qty_ph'] ?>" required oninput="updatePreview()">
                    <span class="rs-input-hint">
                        <?= $l['qty_hint'] ?> <strong id="new-stock-preview">0</strong>
                    </span>
                </div>
                <?php if ($is_vente): ?>
                <div class="rs-field">
                    <label class="rs-label" for="nouveau_prix">
                        <?= $l['price_lbl'] ?> <span class="opt">(<?= $l['price_opt'] ?>)</span>
                    </label>
                    <input type="number" id="nouveau_prix" name="nouveau_prix" class="rs-input"
                           min="0" step="50" placeholder="<?= number_format($doc['prix'],0) ?>"
                           oninput="updatePricePreview()">
                    <div class="rs-price-preview" id="price-preview">
                        <span><?= $l['prix_actuel'] ?> : <strong><?= number_format($doc['prix'],0,',','.')  ?> DA</strong></span>
                        <span class="rs-price-arrow">→</span>
                        <span><strong id="new-price-val">—</strong></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="rs-field" style="margin-bottom:24px">
                <label class="rs-label" for="note"><?= $l['note_lbl'] ?></label>
                <textarea id="note" name="note" class="rs-textarea" placeholder="<?= htmlspecialchars($l['note_ph']) ?>"></textarea>
            </div>
    </div>
    <div class="rs-submit-wrap">
            <button type="submit" class="rs-submit">
                <i class="fa-solid fa-rotate-right"></i>
                <?= $l['submit'] ?>
            </button>
        </form>
    </div>
</div>

<!-- HISTORY -->
<?php if (!empty($history)): ?>
<div class="rs-section-head">
    <div class="rs-section-icon si-green"><i class="fa-solid fa-clock-rotate-left"></i></div>
    <div>
        <div class="rs-section-title"><?= $l['history_title'] ?></div>
        <div class="rs-section-sub"><?= $l['history_sub'] ?></div>
    </div>
</div>
<div class="rs-card">
    <div class="rs-history">
        <?php foreach ($history as $h): ?>
        <div class="rs-hist-item">
            <div class="rs-hist-dot"><i class="fa-solid fa-plus"></i></div>
            <div>
                <div class="rs-hist-title">
                    <span>+<?= (int)$h['quantite_ajoutee'] ?> ex.</span>
                    — <?= $l['stock_arrow'] ?> : <?= $h['ancien_stock'] ?> → <?= $h['nouveau_stock'] ?>
                    <?php if ($h['ancien_prix'] != $h['nouveau_prix'] && $h['nouveau_prix']): ?>
                    · <?= $l['prix_arrow'] ?> : <?= number_format($h['ancien_prix'],0) ?> → <?= number_format($h['nouveau_prix'],0) ?> DA
                    <?php endif; ?>
                </div>
                <div class="rs-hist-meta">
                    <?= $l['par'] ?> <?= htmlspecialchars($h['admin_name']) ?>
                    · <?= date('d/m/Y à H:i', strtotime($h['created_at'])) ?>
                </div>
                <?php if (!empty($h['note'])): ?>
                <div class="rs-hist-note">"<?= htmlspecialchars($h['note']) ?>"</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

</div></div>

<script>
function updatePreview() {
    const q = parseInt(document.getElementById('quantite').value) || 0;
    document.getElementById('new-stock-preview').textContent = q;
}
function updatePricePreview() {
    const el = document.getElementById('nouveau_prix');
    if (!el) return;
    const preview = document.getElementById('price-preview');
    const newVal  = document.getElementById('new-price-val');
    const v = parseFloat(el.value);
    if (v > 0) {
        newVal.textContent = v.toLocaleString('fr-DZ') + ' DA';
        preview.classList.add('show');
    } else {
        preview.classList.remove('show');
    }
}
</script>

<?php include "../includes/footer.php"; ?>