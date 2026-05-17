<?php
// 1. الاتصال بقاعدة البيانات
$host = "localhost"; $user = "root"; $pass = ""; $dbname = "memoir_db";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
require_once '../includes/head.php';
include_once '../includes/languages.php';

// ── Traductions ──────────────────────────────────────────
$pg = [
    'fr' => [
        'page_title'   => 'Modifier le Document — AuraLib',
        'hero_title'   => 'Modifier le Document',
        'hero_sub'     => 'Mettre à jour les informations du document',
        'doc_num'      => 'Document #',
        'btn_cancel'   => '← Annuler',
        'lbl_title'    => 'Titre du document',
        'lbl_author'   => 'Auteur',
        'lbl_trans'    => 'Type de Transaction',
        'opt_both'     => 'Vente & Emprunt',
        'opt_achat'    => 'Vente uniquement',
        'opt_emprunt'  => 'Emprunt uniquement',
        'lbl_price'    => 'Prix (DA)',
        'lbl_copies'   => "Nombre d'exemplaires",
        'lbl_desc'     => 'Description',
        'btn_save'     => 'Sauvegarder les modifications',
        'swal_ok_title'  => 'Mis à jour !',
        'swal_ok_text'   => 'Le document a été modifié avec succès.',
        'swal_err_title' => 'Erreur',
    ],
    'en' => [
        'page_title'   => 'Edit Document — AuraLib',
        'hero_title'   => 'Edit Document',
        'hero_sub'     => 'Update the document information',
        'doc_num'      => 'Document #',
        'btn_cancel'   => '← Cancel',
        'lbl_title'    => 'Document title',
        'lbl_author'   => 'Author',
        'lbl_trans'    => 'Transaction type',
        'opt_both'     => 'Sale & Loan',
        'opt_achat'    => 'Sale only',
        'opt_emprunt'  => 'Loan only',
        'lbl_price'    => 'Price (DA)',
        'lbl_copies'   => 'Number of copies',
        'lbl_desc'     => 'Description',
        'btn_save'     => 'Save changes',
        'swal_ok_title'  => 'Updated!',
        'swal_ok_text'   => 'The document has been updated successfully.',
        'swal_err_title' => 'Error',
    ],
    'ar' => [
        'page_title'   => 'تعديل الوثيقة — AuraLib',
        'hero_title'   => 'تعديل الوثيقة',
        'hero_sub'     => 'تحديث معلومات الوثيقة',
        'doc_num'      => 'وثيقة رقم #',
        'btn_cancel'   => 'إلغاء ←',
        'lbl_title'    => 'عنوان الوثيقة',
        'lbl_author'   => 'المؤلف',
        'lbl_trans'    => 'نوع المعاملة',
        'opt_both'     => 'بيع واستعارة',
        'opt_achat'    => 'بيع فقط',
        'opt_emprunt'  => 'استعارة فقط',
        'lbl_price'    => 'السعر (دج)',
        'lbl_copies'   => 'عدد النسخ',
        'lbl_desc'     => 'الوصف',
        'btn_save'     => 'حفظ التعديلات',
        'swal_ok_title'  => 'تم التحديث!',
        'swal_ok_text'   => 'تم تعديل الوثيقة بنجاح.',
        'swal_err_title' => 'خطأ',
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

$success_script = "";

// 2. التحقق من وجود ID
if (isset($_GET['id'])) {
    $id  = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM documents WHERE id_doc = $id");
    $doc = $res->fetch_assoc();
    if (!$doc) { die("Document non trouvé !"); }
} else {
    header("Location: gerer_documents.php");
    exit();
}

// 3. معالجة التحديث
if (isset($_POST['modifier_livre'])) {
    $titre       = $conn->real_escape_string($_POST['titre']);
    $auteur      = $conn->real_escape_string($_POST['auteur']);
    $prix        = $_POST['prix_vente'];
    $dispo       = $_POST['type_transaction'];
    $exemplaires = $_POST['exemplaires'];
    $desc        = $conn->real_escape_string($_POST['description']);

    $update_sql = "UPDATE documents SET
                   titre            = '$titre',
                   auteur           = '$auteur',
                   prix             = '$prix',
                   disponible_pour  = '$dispo',
                   exemplaires      = '$exemplaires',
                   description_longue = '$desc'
                   WHERE id_doc = $id";

    if ($conn->query($update_sql) === TRUE) {
        $success_script = "<script>
            Swal.fire(
                " . json_encode($p['swal_ok_title']) . ",
                " . json_encode($p['swal_ok_text']) . ",
                'success'
            ).then(() => { window.location.href = 'gerer_documents.php'; });
        </script>";
    } else {
        $success_script = "<script>Swal.fire(" . json_encode($p['swal_err_title']) . ", '" . addslashes($conn->error) . "', 'error');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <?php include '../includes/dark_init.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $p['page_title'] ?></title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <script>(function(){ if(localStorage.getItem('auralib_theme')==='dark') document.documentElement.classList.add('dark'); })();</script>
    <style>
    /* ══ TOKENS AuraLib ══ */
    :root {
        --gold:        #C4A46B;
        --gold2:       #D4B47B;
        --gold-deep:   #A8884E;
        --gold-faint:  rgba(196,164,107,.08);
        --gold-border: rgba(196,164,107,.25);
        --ink:         #2C1F0E;
        --ink2:        #3A2A14;
        --page-bg:     #F5F0E8;
        --page-bg2:    #EDE5D4;
        --page-white:  #FFFDF9;
        --page-text:   #2C1F0E;
        --page-muted:  #9A8C7E;
        --page-border: #DDD5C8;
        --success:     #2E7D52;
        --font-serif:  'Cormorant Garamond', Georgia, serif;
        --font-ui:     <?= $isRtl ? "'Tajawal', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>;
        --nav-h:       66px;
        --radius:      12px;
        --shadow-sm:   0 3px 12px rgba(44,31,14,.07);
        --shadow-md:   0 8px 30px rgba(44,31,14,.12);
        --shadow-gold: 0 6px 20px rgba(196,164,107,.25);
        --tr:          .22s cubic-bezier(.4,0,.2,1);
    }
    html.dark {
        --page-bg:    #100C07; --page-bg2:   #1A1308;
        --page-white: #1E1610; --page-text:  #EDE5D4;
        --page-muted: #9A8C7E; --page-border:#3A2E1E;
    }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    body {
        font-family: var(--font-ui);
        background: var(--page-bg);
        color: var(--page-text);
        padding-top: var(--nav-h);
        min-height: 100vh;
        transition: background .35s, color .35s;
        direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
    }

    /* ══ HERO ══ */
    .md-hero {
        background: linear-gradient(135deg, #1A0E05 0%, #2E1D08 55%, #1A0E05 100%);
        padding: 28px 5%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>;
        border-bottom: 1px solid rgba(196,164,107,.15);
    }
    .md-hero-left { display:flex; flex-direction:column; gap:4px; }
    .md-hero-title {
        font-family: var(--font-serif);
        font-size: clamp(20px, 3vw, 30px);
        font-weight: 700; color: #FDFAF5; line-height: 1;
    }
    .md-hero-title em { color: var(--gold); font-style: normal; }
    .md-hero-sub { font-size: 12px; color: rgba(253,250,245,.4); letter-spacing:.3px; margin-top:3px; }

    .btn-cancel {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 20px; border-radius: 50px;
        font-family: var(--font-ui); font-size: 12px; font-weight: 700;
        color: rgba(196,164,107,.8); background: rgba(196,164,107,.1);
        border: 1.5px solid rgba(196,164,107,.25); text-decoration: none;
        transition: all var(--tr);
    }
    .btn-cancel:hover { background: rgba(196,164,107,.2); color: var(--gold2); border-color: rgba(196,164,107,.5); }

    /* ══ WRAP ══ */
    .md-wrap {
        max-width: 860px;
        margin: 36px auto;
        padding: 0 20px 80px;
    }

    /* ══ FORM CARD ══ */
    .md-card {
        background: var(--page-white);
        border: 1px solid var(--page-border);
        border-radius: 18px;
        padding: 32px 32px 28px;
        box-shadow: var(--shadow-md);
        position: relative;
        overflow: hidden;
    }
    .md-card::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--gold) 0%, var(--gold2) 100%);
    }

    /* Grid */
    .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .full-width { grid-column: span 2; }
    @media (max-width: 600px) {
        .grid-form { grid-template-columns: 1fr; }
        .full-width { grid-column: span 1; }
    }

    .form-group { display: flex; flex-direction: column; gap: 6px; }

    label {
        font-size: 11px; font-weight: 700;
        letter-spacing: <?= $isRtl ? '0' : '.8px' ?>;
        text-transform: uppercase;
        color: var(--page-muted);
        text-align: <?= $isRtl ? 'right' : 'left' ?>;
    }
    input, select, textarea {
        padding: 11px 14px;
        border: 1.5px solid var(--page-border);
        border-radius: 10px;
        font-family: var(--font-ui);
        font-size: 14px;
        color: var(--page-text);
        background: var(--page-bg);
        outline: none;
        width: 100%;
        transition: border-color var(--tr), box-shadow var(--tr);
        text-align: <?= $isRtl ? 'right' : 'left' ?>;
        direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
    }
    input:focus, select:focus, textarea:focus {
        border-color: var(--gold-border);
        box-shadow: 0 0 0 3px rgba(196,164,107,.1);
        background: var(--page-white);
    }
    textarea { resize: vertical; min-height: 110px; }

    /* Save button */
    .btn-save {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; margin-top: 24px;
        padding: 15px 32px; border-radius: 50px;
        background: var(--ink); color: var(--gold);
        border: 1.5px solid rgba(196,164,107,.3);
        font-family: var(--font-ui); font-size: 13px; font-weight: 700;
        cursor: pointer; letter-spacing: .3px;
        transition: all var(--tr);
    }
    .btn-save:hover {
        background: var(--gold); color: var(--ink);
        border-color: var(--gold);
        box-shadow: var(--shadow-gold);
        transform: translateY(-1px);
    }

    /* Doc info pill */
    .doc-info-pill {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--gold-faint);
        border: 1px solid var(--gold-border);
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 24px;
        font-size: 12px; color: var(--page-muted);
    }
    .doc-info-pill strong { color: var(--page-text); font-size: 14px; }
    .doc-info-pill i { color: var(--gold); }
    </style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<!-- HERO -->
<div class="md-hero">
    <div class="md-hero-left">
        <div class="md-hero-title">
            <?= $p['hero_title'] ?> <em><?= $p['doc_num'] ?><?= $id ?></em>
        </div>
        <div class="md-hero-sub"><?= $p['hero_sub'] ?></div>
    </div>
    <a href="gerer_documents.php" class="btn-cancel"><?= $p['btn_cancel'] ?></a>
</div>

<div class="md-wrap">
    <div class="md-card">

        <!-- Doc info -->
        <div class="doc-info-pill">
            <i class="fa-solid fa-book"></i>
            <strong><?= htmlspecialchars($doc['titre']) ?></strong>
            <?php if (!empty($doc['auteur'])): ?>
            &nbsp;·&nbsp; <?= htmlspecialchars($doc['auteur']) ?>
            <?php endif; ?>
        </div>

        <form method="POST">
            <div class="grid-form">

                <div class="form-group">
                    <label><?= $p['lbl_title'] ?></label>
                    <input type="text" name="titre" value="<?= htmlspecialchars($doc['titre']) ?>" required>
                </div>

                <div class="form-group">
                    <label><?= $p['lbl_author'] ?></label>
                    <input type="text" name="auteur" value="<?= htmlspecialchars($doc['auteur']) ?>" required>
                </div>

                <div class="form-group">
                    <label><?= $p['lbl_trans'] ?></label>
                    <select name="type_transaction">
                        <option value="both"    <?= $doc['disponible_pour'] === 'both'    ? 'selected' : '' ?>><?= $p['opt_both'] ?></option>
                        <option value="achat"   <?= $doc['disponible_pour'] === 'achat'   ? 'selected' : '' ?>><?= $p['opt_achat'] ?></option>
                        <option value="emprunt" <?= $doc['disponible_pour'] === 'emprunt' ? 'selected' : '' ?>><?= $p['opt_emprunt'] ?></option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= $p['lbl_price'] ?></label>
                    <input type="number" step="0.01" name="prix_vente" value="<?= $doc['prix'] ?>">
                </div>

                <div class="form-group full-width">
                    <label><?= $p['lbl_copies'] ?></label>
                    <input type="number" name="exemplaires" value="<?= $doc['exemplaires'] ?>" min="1">
                </div>

                <div class="form-group full-width">
                    <label><?= $p['lbl_desc'] ?></label>
                    <textarea name="description"><?= htmlspecialchars($doc['description_longue']) ?></textarea>
                </div>

            </div>

            <button type="submit" name="modifier_livre" class="btn-save">
                <i class="fa-solid fa-floppy-disk" style="font-size:12px"></i>
                <?= $p['btn_save'] ?>
            </button>
        </form>

    </div>
</div>

<?= $success_script ?>
</body>
</html>