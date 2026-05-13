<?php
ob_start(); // حل مشكلة الـ Headers
session_start();
require_once "../includes/db.php"; 

// دالة التوجيه - يجب أن تكون قبل استخدامها
function redirect_with_msg(string $url, string $type, string $msg): never {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_msg']  = $msg;
    header("Location: $url");
    exit;
}

// 1. معالجة الـ POST (يجب أن تكون قبل أي HTML أو Include)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_doc   = (int)($_POST['id_doc']   ?? 0);
    $quantite = (int)($_POST['quantite'] ?? 0);
    $note     = trim($_POST['note'] ?? '');

    if ($id_doc <= 0 || $quantite <= 0) {
        redirect_with_msg("restock.php?id=$id_doc", 'error', 'Quantité invalide.');
    }

    // جلب البيانات الحالية
    $stmt = $conn->prepare("SELECT * FROM documents WHERE id_doc = ?");
    $stmt->bind_param("i", $id_doc);
    $stmt->execute();
    $doc = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$doc) redirect_with_msg("stock_epuise.php", 'error', 'Document introuvable.');

    // الحساب الصحيح (نضمن عدم وجود قيم سالبة)
    $ancien_stock  = max(0, (int)$doc['exemplaires_disponibles']);
    $ancien_total  = max(0, (int)$doc['exemplaires']);
    $nouveau_stock = $ancien_stock + $quantite;
    $nouveau_total = $ancien_total + $quantite;
    $nouveau_prix  = (float)$doc['prix'];

    if (!empty($_POST['nouveau_prix'])) {
        $nouveau_prix = (float)$_POST['nouveau_prix'];
    }

    $conn->begin_transaction();
    try {
        // التحديث باستخدام الأعمدة الصحيحة فقط (حسب الـ SQL الخاص بك)
        $upd = $conn->prepare("
            UPDATE documents
            SET exemplaires_disponibles = ?,
                exemplaires             = ?,
                prix                    = ?
            WHERE id_doc = ?
        ");
        // iidi تعني (integer, integer, double, integer)
        $upd->bind_param("iidi", $nouveau_stock, $nouveau_total, $nouveau_prix, $id_doc);
        $upd->execute();
        
        // تسجيل العملية في التاريخ
      // 1. جلب معرف المسؤول من الجلسة (تأكد من الاسم الصحيح للمفتاح)
$admin_id = $_SESSION['id'] ?? $_SESSION['id_user'] ?? null;

// 2. إذا لم نجد المعرف، نضع رقم مستخدم موجود فعلياً (مثلاً 1) أو نلغي التسجيل لتجنب توقف التحديث
if (!$admin_id) {
    // يمكنك إما توجيه خطأ أو وضع ID افتراضي للمدير الرئيسي
    $admin_id = 1; 
}

// 3. التحضير للإدخال
$ins = $conn->prepare("INSERT INTO restock_transactions (id_doc, admin_id, type, quantite_ajoutee, ancien_stock, nouveau_stock, ancien_prix, nouveau_prix, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

// تأكد من جلب $type_doc من بيانات الكتاب
$type_doc = $doc['disponible_pour'] ?? 'both';

// ربط البيانات
$ins->bind_param("iiisiidds", 
    $id_doc, 
    $admin_id, 
    $type_doc, 
    $quantite, 
    $ancien_stock, 
    $nouveau_stock, 
    $doc['prix'], 
    $nouveau_prix, 
    $note
);

// تنفيذ الاستعلام
$ins->execute();
$ins->close();
        $conn->commit();
        redirect_with_msg("stock_epuise.php", 'success', 'Stock mis à jour avec succès !');

    } catch (Throwable $e) {
        $conn->rollback();
        redirect_with_msg("restock.php?id=$id_doc", 'error', 'Erreur SQL: ' . $e->getMessage());
    }
}

// الآن نضع الـ Includes والـ GET
include "../includes/header.php";
include '../includes/dark_init.php';

// ... باقي كود الـ GET الموجود في ملفك لجلب $doc وعرض الفورم ...


// ════════════════════════════════════════════════════════════════════════════
// GET — Show Restock Panel
// ════════════════════════════════════════════════════════════════════════════
$id_doc = (int)($_GET['id'] ?? 0);
if ($id_doc <= 0) {
    header("Location: stock_epuise.php");
    exit;
}

// Fetch document
$stmt = $conn->prepare("
    SELECT d.*, t.libelle_type AS nom_type
    FROM documents d
    LEFT JOIN types_documents t ON d.id_type = t.id_type
    WHERE d.id_doc = ?
");
$stmt->bind_param("i", $id_doc);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$doc) {
    redirect_with_msg("stock_epuise.php", 'error', 'Document introuvable.');
}

$type        = $doc['disponible_pour']; // 'emprunt' | 'achat' | 'both'
$is_pret     = ($type === 'emprunt' || $type === 'both');
$is_vente    = ($type === 'achat'   || $type === 'both');

// ── Active borrowers (for Prêt) ─────────────────────────────────────────────
$borrowers = [];
if ($is_pret) {
    $bq = $conn->prepare("
        SELECT e.id_emprunt, e.date_debut, e.date_fin, e.date_retour_prevue, e.statut,
               u.firstname, u.lastname, u.email
        FROM emprunt e
        JOIN users u ON e.id_user = u.id
        WHERE e.id_doc = ? AND e.statut = 'en_cours'
        ORDER BY e.date_retour_prevue ASC
    ");
    $bq->bind_param("i", $id_doc);
    $bq->execute();
    $res = $bq->get_result();
    while ($row = $res->fetch_assoc()) $borrowers[] = $row;
    $bq->close();
}

// ── Restock history ─────────────────────────────────────────────────────────
$history = $conn->query("
    SELECT rt.*, u.firstname AS admin_name
    FROM restock_transactions rt
    JOIN users u ON rt.admin_id = u.id 
    WHERE rt.id_doc = $id_doc
    ORDER BY rt.created_at DESC
    LIMIT 10
");



// ── Flash message ────────────────────────────────────────────────────────────
$flash_type = $_SESSION['flash_type'] ?? '';
$flash_msg  = $_SESSION['flash_msg']  ?? '';
unset($_SESSION['flash_type'], $_SESSION['flash_msg']);
?>
<!DOCTYPE html>
<html>
<head>
<style>
/* ════════════════════════════════════════════════════════
   AuraLib — restock.php  ·  Luxury warm editorial palette
════════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@300;400;500;600&display=swap');

:root {
  --sand:   #F5F0E8;
  --cream:  #FFFDF9;
  --border: #DDD5C8;
  --gold:   #C4A46B;
  --gold2:  #D4B47B;
  --ink:    #2C1F0E;
  --muted:  #9A8C7E;
  --light:  #B8A898;
  --danger: #EF4444;
  --green:  #16a34a;
  --amber:  #d97706;
  --blue:   #2563eb;
  --radius: 12px;
}

.rs-wrap {
  display: flex; min-height: 100vh;
  background: var(--sand); padding-top: 66px;
}
.rs-main {
  flex: 1; padding: 40px 40px 80px; min-width: 0; max-width: 900px; margin: 0 auto;
}

/* ── Breadcrumb ── */
.rs-breadcrumb {
  font-size: 11px; color: var(--light); display: flex; align-items: center; gap: 6px;
  letter-spacing: .4px; margin-bottom: 20px;
}
.rs-breadcrumb a { color: var(--gold); text-decoration: none; }
.rs-breadcrumb a:hover { text-decoration: underline; }

/* ── Page header ── */
.rs-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  flex-wrap: wrap; gap: 16px; margin-bottom: 32px;
}
.rs-title {
  font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700;
  color: var(--ink); line-height: 1.15;
}
.rs-title span { color: var(--gold); }
.rs-sub { font-size: 12px; color: var(--muted); margin-top: 4px; }

.rs-back {
  display: flex; align-items: center; gap: 7px; background: var(--cream);
  border: 1px solid var(--border); color: var(--ink); padding: 9px 18px;
  border-radius: 9px; text-decoration: none; font-size: 12px; font-weight: 600;
  font-family: 'DM Sans', sans-serif; transition: border-color .15s, box-shadow .15s;
  white-space: nowrap;
}
.rs-back:hover { border-color: var(--gold); box-shadow: 0 2px 8px rgba(196,164,107,.15); }

/* ── Flash ── */
.rs-flash {
  padding: 13px 18px; border-radius: 10px; font-size: 13px; font-weight: 500;
  margin-bottom: 22px; display: flex; align-items: center; gap: 10px;
  font-family: 'DM Sans', sans-serif;
}
.rs-flash.success { background: #f0fdf4; border: 1px solid #86efac; color: var(--green); }
.rs-flash.error   { background: #fef2f2; border: 1px solid #fca5a5; color: var(--danger); }

/* ── Book identity card ── */
.rs-book-card {
  background: var(--cream); border: 1px solid var(--border); border-radius: var(--radius);
  padding: 22px 24px; display: flex; gap: 20px; align-items: flex-start;
  margin-bottom: 28px; border-left: 4px solid var(--gold);
}
.rs-book-cover {
  width: 62px; height: 84px; border-radius: 6px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Playfair Display', serif; font-size: 9px; color: rgba(255,255,255,.7);
  text-align: center; padding: 6px; line-height: 1.3;
}
.rs-book-info { flex: 1; min-width: 0; }
.rs-book-title {
  font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 700;
  color: var(--ink); margin-bottom: 4px; line-height: 1.2;
}
.rs-book-author { font-size: 12px; color: var(--muted); margin-bottom: 10px; }
.rs-book-pills  { display: flex; flex-wrap: wrap; gap: 6px; }
.rs-pill {
  font-size: 11px; font-family: 'DM Sans', sans-serif; padding: 3px 10px;
  border-radius: 20px; border: 1px solid; font-weight: 500;
}
.rs-pill-pret   { background:#dbeafe; border-color:#93c5fd; color: var(--blue); }
.rs-pill-vente  { background:#fef9c3; border-color:#fde047; color: var(--amber); }
.rs-pill-both   { background:#f0fdf4; border-color:#86efac; color: var(--green); }
.rs-pill-epuise { background:#fef2f2; border-color:#fca5a5; color: var(--danger); }

.rs-book-stats { display: flex; gap: 24px; margin-top: 14px; }
.rs-stat { text-align: center; }
.rs-stat-val { font-family: 'Playfair Display', serif; font-size: 22px; color: var(--ink); font-weight: 700; line-height: 1; }
.rs-stat-lbl { font-size: 10px; color: var(--muted); letter-spacing: .5px; text-transform: uppercase; margin-top: 3px; }
.rs-stat-val.danger { color: var(--danger); }

/* ── Section headers ── */
.rs-section-head {
  display: flex; align-items: center; gap: 10px; margin-bottom: 16px;
}
.rs-section-icon {
  width: 34px; height: 34px; border-radius: 9px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.rs-section-icon svg { width: 16px; height: 16px; }
.si-red   { background: #fee2e2; }
.si-red svg { stroke: var(--danger); }
.si-blue  { background: #dbeafe; }
.si-blue svg { stroke: var(--blue); }
.si-gold  { background: #fef3c7; }
.si-gold svg { stroke: var(--amber); }
.si-green { background: #dcfce7; }
.si-green svg { stroke: var(--green); }
.rs-section-title { font-family: 'Playfair Display', serif; font-size: 16px; font-weight: 700; color: var(--ink); }
.rs-section-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }

/* ── Card wrapper ── */
.rs-card {
  background: var(--cream); border: 1px solid var(--border); border-radius: var(--radius);
  padding: 24px; margin-bottom: 22px;
}

/* ── Borrowers table ── */
.rs-table { width: 100%; border-collapse: collapse; font-size: 12px; font-family: 'DM Sans', sans-serif; }
.rs-table th {
  text-align: left; padding: 9px 12px; font-size: 10px; letter-spacing: .8px;
  text-transform: uppercase; color: var(--muted); border-bottom: 1px solid var(--border);
  font-weight: 600;
}
.rs-table td { padding: 11px 12px; border-bottom: 1px solid #F0EBE3; color: var(--ink); vertical-align: middle; }
.rs-table tr:last-child td { border-bottom: none; }
.rs-table tr:hover td { background: var(--sand); }
.overdue { color: var(--danger); font-weight: 600; }
.badge-due {
  font-size: 10px; padding: 2px 8px; border-radius: 20px; font-weight: 600;
  font-family: 'DM Sans', sans-serif;
}
.badge-overdue { background:#fef2f2; color:var(--danger); }
.badge-soon    { background:#fef9c3; color:var(--amber); }
.badge-ok      { background:#f0fdf4; color:var(--green); }

/* ── Form ── */
.rs-form-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 20px;
}
.rs-form-grid.single { grid-template-columns: 1fr; }
.rs-field { display: flex; flex-direction: column; gap: 6px; }
.rs-label {
  font-size: 11px; font-family: 'DM Sans', sans-serif; font-weight: 600;
  color: var(--ink); letter-spacing: .3px;
}
.rs-label .req { color: var(--danger); }
.rs-input, .rs-textarea {
  border: 1px solid var(--border); border-radius: 9px; padding: 10px 14px;
  font-size: 13px; font-family: 'DM Sans', sans-serif; color: var(--ink);
  background: var(--sand); outline: none; transition: border-color .15s, box-shadow .15s;
}
.rs-input:focus, .rs-textarea:focus {
  border-color: var(--gold); box-shadow: 0 0 0 3px rgba(196,164,107,.15);
}
.rs-input-hint { font-size: 10px; color: var(--light); }
.rs-textarea   { resize: vertical; min-height: 80px; }

/* ── Price change preview ── */
.rs-price-preview {
  background: var(--sand); border: 1px dashed var(--border); border-radius: 9px;
  padding: 12px 16px; font-size: 12px; color: var(--muted); margin-top: 8px;
  display: none;
}
.rs-price-preview.show { display: flex; align-items: center; gap: 10px; }
.rs-price-preview strong { color: var(--ink); }
.rs-price-arrow { color: var(--gold); font-size: 16px; }

/* ── Submit button ── */
.rs-submit {
  width: 100%; padding: 14px 24px; background: var(--ink); color: var(--cream);
  border: none; border-radius: 10px; font-size: 14px; font-weight: 600;
  font-family: 'DM Sans', sans-serif; cursor: pointer; display: flex; align-items: center;
  justify-content: center; gap: 9px; transition: background .15s, transform .1s;
  margin-top: 4px;
}
.rs-submit:hover { background: #3d2f1a; }
.rs-submit:active { transform: scale(.99); }
.rs-submit svg { width: 17px; height: 17px; }

/* ── History timeline ── */
.rs-history { display: flex; flex-direction: column; gap: 0; }
.rs-hist-item {
  display: flex; gap: 14px; padding: 14px 0;
  border-bottom: 1px solid var(--border);
}
.rs-hist-item:last-child { border-bottom: none; }
.rs-hist-dot {
  width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  background: var(--sand); border: 1px solid var(--border);
  margin-top: 1px;
}
.rs-hist-dot svg { width: 14px; height: 14px; stroke: var(--gold); }
.rs-hist-body { flex: 1; }
.rs-hist-title { font-size: 13px; font-weight: 600; color: var(--ink); font-family: 'DM Sans', sans-serif; }
.rs-hist-title span { color: var(--green); }
.rs-hist-meta  { font-size: 11px; color: var(--muted); margin-top: 3px; }
.rs-hist-note  { font-size: 11px; color: var(--light); font-style: italic; margin-top: 4px; }

/* ── Empty state ── */
.rs-empty {
  text-align: center; padding: 32px; color: var(--muted);
  font-size: 12px; font-family: 'DM Sans', sans-serif;
}
.rs-empty svg { width: 32px; height: 32px; stroke: var(--border); margin-bottom: 10px; }

/* ── Dark mode ── */
html.dark .rs-wrap     { background: #110C06; }
html.dark .rs-book-card,
html.dark .rs-card     { background: #1C1410; border-color: #3E3228; }
html.dark .rs-input,
html.dark .rs-textarea { background: #2C2418; border-color: #3E3228; color: #F0E8D8; }
html.dark .rs-input:focus,
html.dark .rs-textarea:focus { border-color: var(--gold); }
html.dark .rs-table td { color: #F0E8D8; }
html.dark .rs-title,
html.dark .rs-book-title { color: #F0E8D8; }
html.dark .rs-hist-title { color: #F0E8D8; }
html.dark .rs-stat-val   { color: #F0E8D8; }
html.dark .rs-submit     { background: var(--gold); color: var(--ink); }
html.dark .rs-submit:hover { background: var(--gold2); }

@media (max-width: 640px) {
  .rs-main { padding: 22px 16px 60px; }
  .rs-form-grid { grid-template-columns: 1fr; }
  .rs-book-stats { gap: 14px; }
}
</style>

<div class="rs-wrap">
<div class="rs-main">

  <!-- ── Breadcrumb ── -->
  <div class="rs-breadcrumb">
    <a href="admin_dashboard.php">Tableau de bord</a>
    <span>›</span>
    <a href="stock_epuise.php">Stocks épuisés</a>
    <span>›</span>
    Réapprovisionner
  </div>

  <?php if ($flash_msg): ?>
  <div class="rs-flash <?= htmlspecialchars($flash_type) ?>">
    <?php if ($flash_type === 'success'): ?>
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" width="16" height="16" style="stroke:#16a34a;flex-shrink:0"><polyline points="20 6 9 17 4 12"/></svg>
    <?php else: ?>
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" width="16" height="16" style="stroke:#EF4444;flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?php endif; ?>
    <?= htmlspecialchars($flash_msg) ?>
  </div>
  <?php endif; ?>

  <!-- ── Header ── -->
  <div class="rs-header">
    <div>
      <div class="rs-title">Réapprovisionner <span>le stock</span></div>
      <div class="rs-sub">Ajoutez des exemplaires et mettez à jour les informations du document</div>
    </div>
    <a href="stock_epuise.php" class="rs-back">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" width="13" height="13" style="stroke:currentColor"><polyline points="15 18 9 12 15 6"/></svg>
      Retour
    </a>
  </div>

  <!-- ══════════════════════════════════════════════════════
       BOOK IDENTITY CARD
  ══════════════════════════════════════════════════════ -->
  <div class="rs-book-card">
    <div class="rs-book-cover" style="background:<?= htmlspecialchars($doc['cover_color'] ?: 'linear-gradient(145deg,#2c1a1a,#4a322d)') ?>">
      <?= htmlspecialchars(mb_substr($doc['titre'], 0, 30)) ?>
    </div>
    <div class="rs-book-info">
      <div class="rs-book-title"><?= htmlspecialchars($doc['titre']) ?></div>
      <?php if ($doc['auteur']): ?>
        <div class="rs-book-author">par <?= htmlspecialchars($doc['auteur']) ?></div>
      <?php endif; ?>
      <div class="rs-book-pills">
        <?php if ($is_pret && !$is_vente): ?>
          <span class="rs-pill rs-pill-pret">Prêt</span>
        <?php elseif ($is_vente && !$is_pret): ?>
          <span class="rs-pill rs-pill-vente">Vente</span>
        <?php else: ?>
          <span class="rs-pill rs-pill-both">Prêt &amp; Vente</span>
        <?php endif; ?>
        <span class="rs-pill rs-pill-epuise">⚠ Épuisé</span>
        <?php if ($doc['langue']): ?>
          <span class="rs-pill" style="background:#f5f5f5;border-color:#d1d5db;color:#6b7280"><?= htmlspecialchars($doc['langue']) ?></span>
        <?php endif; ?>
      </div>
      <div class="rs-book-stats">
        <div class="rs-stat">
          <div class="rs-stat-val danger">0</div>
          <div class="rs-stat-lbl">Disponibles</div>
        </div>
        <div class="rs-stat">
          <div class="rs-stat-val"><?= (int)$doc['exemplaires'] ?></div>
          <div class="rs-stat-lbl">Total ex.</div>
        </div>
        <?php if ($doc['prix'] > 0): ?>
        <div class="rs-stat">
          <div class="rs-stat-val"><?= number_format($doc['prix'], 0, ',', ' ') ?> DA</div>
          <div class="rs-stat-lbl">Prix actuel</div>
        </div>
        <?php endif; ?>
        <?php if (count($borrowers) > 0): ?>
        <div class="rs-stat">
          <div class="rs-stat-val" style="color:var(--amber)"><?= count($borrowers) ?></div>
          <div class="rs-stat-lbl">En cours</div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($is_pret && count($borrowers) > 0): ?>
  <!-- ══════════════════════════════════════════════════════
       SCENARIO 1 — BORROWERS STATUS TABLE
  ══════════════════════════════════════════════════════ -->
  <div class="rs-section-head">
    <div class="rs-section-icon si-blue">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div>
      <div class="rs-section-title">Emprunteurs actuels</div>
      <div class="rs-section-sub"><?= count($borrowers) ?> exemplaire(s) actuellement en circulation</div>
    </div>
  </div>
  <div class="rs-card" style="padding:0;overflow:hidden">
    <table class="rs-table">
      <thead>
        <tr>
          <th>Lecteur</th>
          <th>Email</th>
          <th>Emprunté le</th>
          <th>Retour prévu</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($borrowers as $b):
          $today        = new DateTime();
          $due_date_obj = $b['date_retour_prevue'] ? new DateTime($b['date_retour_prevue']) : null;
          $diff_days    = $due_date_obj ? (int)$today->diff($due_date_obj)->format('%r%a') : null;

          $badge_class  = 'badge-ok';
          $badge_text   = 'À temps';
          if ($diff_days !== null) {
            if ($diff_days < 0)    { $badge_class = 'badge-overdue'; $badge_text = 'En retard (' . abs($diff_days) . ' j)'; }
            elseif ($diff_days < 5){ $badge_class = 'badge-soon';    $badge_text = 'Bientôt (' . $diff_days . ' j)'; }
          }
        ?>
        <tr>
          <td><strong><?= htmlspecialchars($b['firstname'] . ' ' . $b['lastname']) ?></strong></td>
          <td style="color:var(--muted)"><?= htmlspecialchars($b['email']) ?></td>
          <td><?= htmlspecialchars($b['date_debut'] ?? '-') ?></td>
          <td class="<?= $diff_days < 0 ? 'overdue' : '' ?>">
            <?= $due_date_obj ? $due_date_obj->format('d/m/Y') : '—' ?>
          </td>
          <td><span class="badge-due <?= $badge_class ?>"><?= $badge_text ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php elseif ($is_pret): ?>
  <div class="rs-section-head">
    <div class="rs-section-icon si-blue">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
    </div>
    <div>
      <div class="rs-section-title">Emprunteurs actuels</div>
      <div class="rs-section-sub">Aucun emprunt actif pour ce document</div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ══════════════════════════════════════════════════════
       RESTOCK FORM
  ══════════════════════════════════════════════════════ -->
  <div class="rs-section-head">
    <div class="rs-section-icon si-gold">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
    </div>
    <div>
      <div class="rs-section-title">
        <?php if ($is_vente && $is_pret): ?>Réapprovisionner (Prêt &amp; Vente)
        <?php elseif ($is_vente):         ?>Réapprovisionner &amp; Tarification
        <?php else:                       ?>Ajouter des exemplaires
        <?php endif; ?>
      </div>
      <div class="rs-section-sub">
        <?php if ($is_vente): ?>Mise à jour du stock et du prix de vente<?php else: ?>Ajout manuel de copies physiques<?php endif; ?>
      </div>
    </div>
  </div>

  <div class="rs-card">
    <form method="POST" action="restock.php">
      <input type="hidden" name="id_doc" value="<?= $id_doc ?>">

      <div class="rs-form-grid <?= !$is_vente ? 'single' : '' ?>">

        <!-- Quantité — always shown -->
        <div class="rs-field">
          <label class="rs-label" for="quantite">
            Exemplaires à ajouter <span class="req">*</span>
          </label>
          <input
            type="number" id="quantite" name="quantite"
            class="rs-input" min="1" max="9999" placeholder="ex: 5" required
            oninput="updatePreview()"
          >
          <span class="rs-input-hint">
            Stock actuel : 0 → deviendra <strong id="new-stock-preview">0</strong> après ajout
          </span>
        </div>

        <?php if ($is_vente): ?>
        <!-- Nouveau prix — Vente / both only -->
        <div class="rs-field">
          <label class="rs-label" for="nouveau_prix">
            Nouveau prix de vente (DA)
            <span style="font-weight:400;color:var(--muted);font-size:10px"> — optionnel</span>
          </label>
          <input
            type="number" id="nouveau_prix" name="nouveau_prix"
            class="rs-input" min="0" step="50"
            placeholder="<?= number_format($doc['prix'], 0) ?>"
            oninput="updatePricePreview()"
          >
          <div class="rs-price-preview" id="price-preview">
            <span>Prix actuel : <strong><?= number_format($doc['prix'], 0, ',', ' ') ?> DA</strong></span>
            <span class="rs-price-arrow">→</span>
            <span>Nouveau : <strong id="new-price-val">—</strong></span>
          </div>
        </div>
        <?php endif; ?>

      </div>

      <!-- Note -->
      <div class="rs-field" style="margin-bottom:22px">
        <label class="rs-label" for="note">Note (optionnelle)</label>
        <textarea id="note" name="note" class="rs-textarea"
          placeholder="Ex: Livraison fournisseur du 12/05/2026 — facture #INV-442"></textarea>
      </div>

      <button type="submit" class="rs-submit">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke="currentColor"><path d="M12 5v14M5 12h14"/></svg>
        Confirmer le réapprovisionnement
      </button>
    </form>
  </div>

  <!-- ══════════════════════════════════════════════════════
       RESTOCK HISTORY
  ══════════════════════════════════════════════════════ -->
  <?php if (!empty($history)): ?>
  <div class="rs-section-head" style="margin-top:10px">
    <div class="rs-section-icon si-green">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    </div>
    <div>
      <div class="rs-section-title">Historique des réapprovisionnements</div>
      <div class="rs-section-sub">10 dernières opérations pour ce document</div>
    </div>
  </div>
  <div class="rs-card">
    <div class="rs-history">
      <?php foreach ($history as $h): ?>
      <div class="rs-hist-item">
        <div class="rs-hist-dot">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        </div>
        <div class="rs-hist-body">
          <div class="rs-hist-title">
            <span>+<?= (int)$h['quantite_ajoutee'] ?> ex.</span>
            — Stock : <?= $h['ancien_stock'] ?> → <?= $h['nouveau_stock'] ?>
            <?php if ($h['ancien_prix'] != $h['nouveau_prix'] && $h['nouveau_prix']): ?>
              · Prix : <?= number_format($h['ancien_prix'], 0) ?> → <?= number_format($h['nouveau_prix'], 0) ?> DA
            <?php endif; ?>
          </div>
          <div class="rs-hist-meta">
            Par <?= htmlspecialchars($h['admin_name']) ?> · <?= date('d/m/Y à H:i', strtotime($h['created_at'])) ?>
          </div>
          <?php if ($h['note']): ?>
            <div class="rs-hist-note">"<?= htmlspecialchars($h['note']) ?>"</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div><!-- .rs-main -->
</div><!-- .rs-wrap -->

<script>
// Live stock preview
function updatePreview() {
  const q = parseInt(document.getElementById('quantite').value) || 0;
  document.getElementById('new-stock-preview').textContent = q;
}

// Live price preview (Vente only)
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
