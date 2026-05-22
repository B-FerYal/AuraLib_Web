<?php
// 1. الاتصال بقاعدة البيانات
$host = "localhost"; $user = "root"; $pass = ""; $dbname = "memoir_db";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
require_once '../includes/head.php';
include_once '../includes/languages.php';

$pg = [
    'fr' => [
        'page_title'      => 'Gestion du Catalogue — AuraLib',
        'header_title'    => 'Gestion du Catalogue',
        'header_sub'      => 'Ajouter, modifier et supprimer des documents',
        'btn_back'        => '← Retour',
        'lbl_title'       => 'Titre *',
        'lbl_subtitle'    => 'Sous-titre',
        'lbl_type'        => 'Type de Document *',
        'opt_choose'      => '-- Choisir --',
        'opt_livre'       => 'Livre',
        'opt_these'       => 'Thèse / Mémoire',
        'opt_article'     => 'Article',
        'opt_journal'     => 'Journal / Revue',
        'lbl_cat'         => 'Catégorie',
        'opt_info'        => 'Informatique',
        'opt_math'        => 'Mathématiques',
        'opt_phys'        => 'Physique',
        'opt_med'         => 'Médecine',
        'opt_other'       => 'Autre...',
        'lbl_other_cat'   => 'Préciser la catégorie *',
        'lbl_author'      => 'Auteur',
        'lbl_editor'      => 'Éditeur',
        'lbl_year'        => "Année d'édition",
        'ph_year'         => 'Ex: 2024',
        'lbl_pub_date'    => 'Date de Publication',
        'ph_pub_date'     => 'Ex: 15 Mars 2024',
        'lbl_place'       => "Lieu d'édition",
        'ph_place'        => 'Ex: Chlef, Algérie',
        'lbl_lang'        => 'Langue',
        'opt_fr'          => 'Français',
        'opt_ar'          => 'Arabe',
        'opt_en'          => 'Anglais',
        'lbl_isbn'        => 'ISBN',
        'lbl_pages'       => 'Nombre de Pages',
        'lbl_encadrant'   => 'Encadrant',
        'lbl_univ'        => 'Université',
        'lbl_spec'        => 'Spécialité',
        'lbl_issn'        => 'ISSN',
        'lbl_revue'       => 'Nom de la Revue',
        'lbl_issue'       => 'Numéro / Issue',
        'lbl_prix_achat'  => "Prix d'Achat",
        'lbl_prix_vente'  => 'Prix de Vente',
        'lbl_stock'       => 'Stock Initial',
        'lbl_image'       => 'Image de couverture',
        'lbl_transaction' => 'Transaction',
        'opt_both'        => 'Vente & Emprunt',
        'opt_achat'       => 'Vente uniquement',
        'opt_emprunt'     => 'Emprunt uniquement',
        'lbl_desc'        => 'Description',
        'btn_save'        => 'Enregistrer le Document',
        'section_form'    => 'Ajouter un nouveau document',
        'section_list'    => 'Documents du catalogue',
        'th_cover'        => 'Couverture',
        'th_title_type'   => 'Titre & Type',
        'th_stock_init'   => 'Stock Initial',
        'th_stock_state'  => 'État du Stock',
        'th_actions'      => 'Actions',
        'lbl_total'       => 'Total entré',
        'lbl_units'       => 'ex.',
        'lbl_available'   => 'Disponible',
        'lbl_on_shelf'    => 'en rayon',
        'lbl_movement'    => 'sortie(s)',
        'swal_delete_title'  => 'Supprimer ce document ?',
        'swal_delete_text'   => 'Cette action est irréversible.',
        'swal_delete_yes'    => 'Oui, supprimer',
        'swal_delete_no'     => 'Annuler',
        'swal_deleted_title' => 'Supprimé !',
        'swal_deleted_text'  => 'Le document a été supprimé.',
        'swal_added_title'   => 'Succès !',
        'swal_added_text'    => 'Le document a été ajouté avec succès.',
    ],
    'en' => [
        'page_title'      => 'Catalogue Management — AuraLib',
        'header_title'    => 'Catalogue Management',
        'header_sub'      => 'Add, edit and delete documents',
        'btn_back'        => '← Back',
        'lbl_title'       => 'Title *',
        'lbl_subtitle'    => 'Subtitle',
        'lbl_type'        => 'Document Type *',
        'opt_choose'      => '-- Choose --',
        'opt_livre'       => 'Book',
        'opt_these'       => 'Thesis / Dissertation',
        'opt_article'     => 'Article',
        'opt_journal'     => 'Journal / Review',
        'lbl_cat'         => 'Category',
        'opt_info'        => 'Computer Science',
        'opt_math'        => 'Mathematics',
        'opt_phys'        => 'Physics',
        'opt_med'         => 'Medicine',
        'opt_other'       => 'Other...',
        'lbl_other_cat'   => 'Specify category *',
        'lbl_author'      => 'Author',
        'lbl_editor'      => 'Publisher',
        'lbl_year'        => 'Edition year',
        'ph_year'         => 'e.g. 2024',
        'lbl_pub_date'    => 'Publication date',
        'ph_pub_date'     => 'e.g. 15 March 2024',
        'lbl_place'       => 'Place of edition',
        'ph_place'        => 'e.g. Chlef, Algeria',
        'lbl_lang'        => 'Language',
        'opt_fr'          => 'French',
        'opt_ar'          => 'Arabic',
        'opt_en'          => 'English',
        'lbl_isbn'        => 'ISBN',
        'lbl_pages'       => 'Number of pages',
        'lbl_encadrant'   => 'Supervisor',
        'lbl_univ'        => 'University',
        'lbl_spec'        => 'Speciality',
        'lbl_issn'        => 'ISSN',
        'lbl_revue'       => 'Journal name',
        'lbl_issue'       => 'Issue / Number',
        'lbl_prix_achat'  => 'Purchase price',
        'lbl_prix_vente'  => 'Sale price',
        'lbl_stock'       => 'Initial stock',
        'lbl_image'       => 'Cover image',
        'lbl_transaction' => 'Transaction',
        'opt_both'        => 'Sale & Loan',
        'opt_achat'       => 'Sale only',
        'opt_emprunt'     => 'Loan only',
        'lbl_desc'        => 'Description',
        'btn_save'        => 'Save Document',
        'section_form'    => 'Add a new document',
        'section_list'    => 'Catalogue documents',
        'th_cover'        => 'Cover',
        'th_title_type'   => 'Title & Type',
        'th_stock_init'   => 'Initial Stock',
        'th_stock_state'  => 'Stock Status',
        'th_actions'      => 'Actions',
        'lbl_total'       => 'Total in',
        'lbl_units'       => 'copies',
        'lbl_available'   => 'Available',
        'lbl_on_shelf'    => 'on shelf',
        'lbl_movement'    => 'movement(s)',
        'swal_delete_title'  => 'Delete this document?',
        'swal_delete_text'   => 'This action cannot be undone.',
        'swal_delete_yes'    => 'Yes, delete',
        'swal_delete_no'     => 'Cancel',
        'swal_deleted_title' => 'Deleted!',
        'swal_deleted_text'  => 'The document has been deleted.',
        'swal_added_title'   => 'Success!',
        'swal_added_text'    => 'The document has been added successfully.',
    ],
    'ar' => [
        'page_title'      => 'إدارة الكتالوج — AuraLib',
        'header_title'    => 'إدارة الكتالوج',
        'header_sub'      => 'إضافة وتعديل وحذف الوثائق',
        'btn_back'        => 'رجوع ←',
        'lbl_title'       => 'العنوان *',
        'lbl_subtitle'    => 'العنوان الفرعي',
        'lbl_type'        => 'نوع الوثيقة *',
        'opt_choose'      => '-- اختر --',
        'opt_livre'       => 'كتاب',
        'opt_these'       => 'أطروحة / مذكرة',
        'opt_article'     => 'مقال',
        'opt_journal'     => 'مجلة / جريدة',
        'lbl_cat'         => 'الفئة',
        'opt_info'        => 'إعلام آلي',
        'opt_math'        => 'رياضيات',
        'opt_phys'        => 'فيزياء',
        'opt_med'         => 'طب',
        'opt_other'       => 'أخرى...',
        'lbl_other_cat'   => 'حدد الفئة *',
        'lbl_author'      => 'المؤلف',
        'lbl_editor'      => 'دار النشر',
        'lbl_year'        => 'سنة الإصدار',
        'ph_year'         => 'مثال: 2024',
        'lbl_pub_date'    => 'تاريخ النشر',
        'ph_pub_date'     => 'مثال: 15 مارس 2024',
        'lbl_place'       => 'مكان الإصدار',
        'ph_place'        => 'مثال: الشلف، الجزائر',
        'lbl_lang'        => 'اللغة',
        'opt_fr'          => 'الفرنسية',
        'opt_ar'          => 'العربية',
        'opt_en'          => 'الإنجليزية',
        'lbl_isbn'        => 'ISBN',
        'lbl_pages'       => 'عدد الصفحات',
        'lbl_encadrant'   => 'المشرف',
        'lbl_univ'        => 'الجامعة',
        'lbl_spec'        => 'التخصص',
        'lbl_issn'        => 'ISSN',
        'lbl_revue'       => 'اسم المجلة',
        'lbl_issue'       => 'العدد / الإصدار',
        'lbl_prix_achat'  => 'سعر الشراء',
        'lbl_prix_vente'  => 'سعر البيع',
        'lbl_stock'       => 'المخزون الأولي',
        'lbl_image'       => 'صورة الغلاف',
        'lbl_transaction' => 'نوع المعاملة',
        'opt_both'        => 'بيع واستعارة',
        'opt_achat'       => 'بيع فقط',
        'opt_emprunt'     => 'استعارة فقط',
        'lbl_desc'        => 'الوصف',
        'btn_save'        => 'حفظ الوثيقة',
        'section_form'    => 'إضافة وثيقة جديدة',
        'section_list'    => 'وثائق الكتالوج',
        'th_cover'        => 'الغلاف',
        'th_title_type'   => 'العنوان والنوع',
        'th_stock_init'   => 'المخزون الأولي',
        'th_stock_state'  => 'حالة المخزون',
        'th_actions'      => 'الإجراءات',
        'lbl_total'       => 'المجموع المُدخَل',
        'lbl_units'       => 'وحدة',
        'lbl_available'   => 'المتاح',
        'lbl_on_shelf'    => 'في الرف',
        'lbl_movement'    => 'خروج',
        'swal_delete_title'  => 'حذف هذه الوثيقة؟',
        'swal_delete_text'   => 'لا يمكن التراجع عن هذا الإجراء.',
        'swal_delete_yes'    => 'نعم، احذف',
        'swal_delete_no'     => 'إلغاء',
        'swal_deleted_title' => 'تم الحذف!',
        'swal_deleted_text'  => 'تم حذف الوثيقة.',
        'swal_added_title'   => 'نجاح!',
        'swal_added_text'    => 'تمت إضافة الوثيقة بنجاح.',
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

$success_script = "";

// 2. معالجة الحذف
if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    $conn->query("DELETE FROM documents WHERE id_doc = $id_del");
    $success_script = "<script>Swal.fire(" . json_encode($p['swal_deleted_title']) . ", " . json_encode($p['swal_deleted_text']) . ", 'success').then(() => { window.location.href='gerer_documents.php'; });</script>";
}

// 3. معالجة الإضافة
if (isset($_POST['ajouter_livre'])) {
    $id_type      = (int)$_POST['id_type'];
    $titre        = $conn->real_escape_string($_POST['titre']);
    $sous_titre   = $conn->real_escape_string($_POST['sous_titre']);
    $auteur       = $conn->real_escape_string($_POST['auteur']);
    $editeur      = $conn->real_escape_string($_POST['editeur']);
    $langue       = $conn->real_escape_string($_POST['langue']);
    $categorie    = $_POST['categorie'];
    if ($categorie === "Autre" && !empty($_POST['custom_categorie'])) {
        $categorie = $conn->real_escape_string($_POST['custom_categorie']);
    } else {
        $categorie = $conn->real_escape_string($categorie);
    }
    $annee_edition = "'" . $conn->real_escape_string($_POST['annee_edition']) . "'";
    $date_pub      = "'" . $conn->real_escape_string($_POST['date_publication']) . "'";
    $lieu_edition  = "'" . $conn->real_escape_string($_POST['lieu_edition']) . "'";
    $nb_pages      = !empty($_POST['nb_pages']) ? (int)$_POST['nb_pages'] : "NULL";
    $isbn          = $conn->real_escape_string($_POST['isbn']);
    $issn          = $conn->real_escape_string($_POST['issn']);
    $dispo         = $_POST['type_transaction'];
    $desc          = $conn->real_escape_string($_POST['description']);
    $encadrant     = $conn->real_escape_string($_POST['encadrant']);
    $universite    = $conn->real_escape_string($_POST['universite']);
    $specialite    = $conn->real_escape_string($_POST['specialite']);
    $nom_revue     = $conn->real_escape_string($_POST['nom_revue']);
    $numero_issue  = $conn->real_escape_string($_POST['numero_issue']);
    $prix_vente    = $_POST['prix_vente'];
    $prix_achat    = $_POST['prix_achat'];
    $exemplaires   = (int)$_POST['exemplaires'];

    $sql = "INSERT INTO documents (
                titre, sous_titre, auteur, editeur, annee_edition,
                nb_pages, isbn, issn, langue, categorie, id_type, disponible_pour,
                description_longue, encadrant, universite, specialite, nom_revue,
                numero_issue, date_publication, lieu_edition, prix, prix_achat, exemplaires, exemplaires_disponibles, status
            ) VALUES (
                '$titre', '$sous_titre', '$auteur', '$editeur', $annee_edition,
                $nb_pages, '$isbn', '$issn', '$langue', '$categorie', $id_type, '$dispo',
                '$desc', '$encadrant', '$universite', '$specialite', '$nom_revue',
                '$numero_issue', $date_pub, $lieu_edition, '$prix_vente', '$prix_achat', '$exemplaires', '$exemplaires', 1
            )";

    if ($conn->query($sql) === TRUE) {
        $new_id = $conn->insert_id;
        if (isset($_FILES['image_livre']) && $_FILES['image_livre']['error'] == 0) {
            move_uploaded_file($_FILES['image_livre']['tmp_name'], "../uploads/" . $new_id . ".jpg");
        }
        $success_script = "<script>Swal.fire(" . json_encode($p['swal_added_title']) . ", " . json_encode($p['swal_added_text']) . ", 'success').then(() => { window.location.href='gerer_documents.php'; });</script>";
    }
}

$result = $conn->query("SELECT d.*, t.libelle_type FROM documents d LEFT JOIN types_documents t ON d.id_type = t.id_type ORDER BY d.id_doc DESC");
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <?php include '../includes/dark_init.php'; ?>
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
        --amber:       #B8832A;
        --ink:         #2C1F0E;
        --ink2:        #3A2A14;
        --page-bg:     #F5F0E8;
        --page-bg2:    #EDE5D4;
        --page-white:  #FFFDF9;
        --page-text:   #2C1F0E;
        --page-muted:  #9A8C7E;
        --page-border: #DDD5C8;
        --success:     #2E7D52;
        --success-bg:  rgba(46,125,82,.08);
        --danger:      #C0392B;
        --danger-bg:   rgba(192,57,43,.08);
        --font-serif:  'Cormorant Garamond', Georgia, serif;
        --font-ui:     <?= $isRtl ? "'Tajawal', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>;
        --nav-h:       66px;
        --radius:      12px;
        --shadow-sm:   0 3px 12px rgba(44,31,14,.07);
        --shadow-md:   0 8px 30px rgba(44,31,14,.10);
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

    /* ══ PAGE HERO ══ */
    .gd-hero {
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
    .gd-hero-left { display: flex; flex-direction: column; gap: 4px; }
    .gd-hero-title {
        font-family: var(--font-serif);
        font-size: clamp(22px, 3vw, 34px);
        font-weight: 700;
        color: #FDFAF5;
        line-height: 1;
    }
    .gd-hero-title span { color: var(--gold); }
    .gd-hero-sub { font-size: 12px; color: rgba(253,250,245,.4); letter-spacing: .3px; }
    .btn-back {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 20px; border-radius: 50px;
        font-family: var(--font-ui); font-size: 12px; font-weight: 700;
        color: rgba(196,164,107,.8); background: rgba(196,164,107,.1);
        border: 1.5px solid rgba(196,164,107,.25); text-decoration: none;
        transition: all var(--tr);
    }
    .btn-back:hover { background: rgba(196,164,107,.2); color: var(--gold2); border-color: rgba(196,164,107,.5); }

    /* ══ WRAP ══ */
    .gd-wrap { max-width: 1300px; margin: 32px auto; padding: 0 5% 80px; }

    /* ══ SECTION LABELS ══ */
    .section-label {
        display: flex; align-items: center; gap: 10px;
        font-family: var(--font-serif);
        font-size: 22px; font-weight: 700;
        color: var(--page-text);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--page-border);
    }
    .section-label i { color: var(--gold); font-size: 16px; }

    /* ══ FORM CARD ══ */
    .form-card {
        background: var(--page-white);
        border: 1px solid var(--page-border);
        border-radius: 18px;
        padding: 28px 28px 24px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 40px;
    }
    .grid-form {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }
    @media (max-width: 900px) { .grid-form { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 540px) { .grid-form { grid-template-columns: 1fr; } }

    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .full-width { grid-column: span 4; }
    @media (max-width: 900px) { .full-width { grid-column: span 2; } }
    @media (max-width: 540px) { .full-width { grid-column: span 1; } }

    label {
        font-size: 11px; font-weight: 700;
        letter-spacing: <?= $isRtl ? '0' : '.8px' ?>;
        text-transform: uppercase;
        color: var(--page-muted);
        text-align: <?= $isRtl ? 'right' : 'left' ?>;
    }
    input, select, textarea {
        padding: 10px 13px;
        border: 1.5px solid var(--page-border);
        border-radius: 9px;
        font-family: var(--font-ui);
        font-size: 13px;
        color: var(--page-text);
        background: var(--page-bg);
        outline: none;
        transition: border-color var(--tr), box-shadow var(--tr);
        text-align: <?= $isRtl ? 'right' : 'left' ?>;
        direction: <?= $isRtl ? 'rtl' : 'ltr' ?>;
        width: 100%;
    }
    input:focus, select:focus, textarea:focus {
        border-color: var(--gold-border);
        box-shadow: 0 0 0 3px rgba(196,164,107,.1);
        background: var(--page-white);
    }
    textarea { resize: vertical; min-height: 80px; }

    /* Field highlight colors */
    .field-green { background: rgba(46,125,82,.06); border-radius: 10px; padding: 12px; }
    .field-green label { color: var(--success); }
    .field-gold  { background: var(--gold-faint); border-radius: 10px; padding: 12px; }
    .field-gold label { color: var(--gold-deep); }
    .field-gray  { background: var(--page-bg2); border-radius: 10px; padding: 12px; }
    html.dark .field-green { background: rgba(46,125,82,.08); }
    html.dark .field-gold  { background: rgba(196,164,107,.06); }
    html.dark .field-gray  { background: rgba(255,255,255,.04); }

    /* Dynamic fields */
    .dynamic-field {
        background: var(--gold-faint);
        padding: 18px;
        border-radius: 10px;
        border: 1.5px dashed var(--gold-border);
        display: none;
        grid-column: span 4;
    }
    .dynamic-field .inner-grid {
        display: grid;
        gap: 14px;
    }

    /* Save button */
    .btn-add {
        grid-column: span 4;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 14px 32px;
        background: var(--ink);
        color: var(--gold);
        border: 1.5px solid rgba(196,164,107,.3);
        border-radius: 50px;
        font-family: var(--font-ui);
        font-size: 13px; font-weight: 700;
        cursor: pointer;
        transition: all var(--tr);
        letter-spacing: .3px;
        margin-top: 6px;
    }
    .btn-add:hover {
        background: var(--gold);
        color: var(--ink);
        border-color: var(--gold);
        box-shadow: 0 6px 20px rgba(196,164,107,.3);
        transform: translateY(-1px);
    }

    /* ══ TABLE ══ */
    .table-card {
        background: var(--page-white);
        border: 1px solid var(--page-border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }
    .table-scroll { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead tr { background: var(--ink); }
    th {
        padding: 13px 16px;
        text-align: <?= $isRtl ? 'right' : 'left' ?>;
        font-size: 10px; font-weight: 700;
        letter-spacing: <?= $isRtl ? '0' : '1px' ?>;
        text-transform: uppercase;
        color: rgba(196,164,107,.7);
        white-space: nowrap;
        border-bottom: none;
    }
    td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--page-border);
        color: var(--page-text);
        text-align: <?= $isRtl ? 'right' : 'left' ?>;
        vertical-align: middle;
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: var(--gold-faint); }

    .img-table {
        width: 40px; height: 54px;
        object-fit: cover; border-radius: 5px;
        border: 1px solid var(--page-border);
        box-shadow: var(--shadow-sm);
    }

    /* Stock badges */
    .stock-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 700;
    }
    .stock-initial { background: var(--page-bg2); color: var(--page-muted); border: 1px solid var(--page-border); }
    .stock-actuel  { background: var(--success-bg); color: var(--success); border: 1px solid rgba(46,125,82,.2); }
    .stock-low     { background: var(--danger-bg); color: var(--danger); border: 1px solid rgba(192,57,43,.2); }
    .stock-label   { font-size: 9px; text-transform: uppercase; color: var(--page-muted); letter-spacing: .5px; display: block; margin-bottom: 3px; }

    /* Action buttons */
    .act-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 6px 12px; border-radius: 8px;
        font-size: 11px; font-weight: 700;
        text-decoration: none; border: none; cursor: pointer;
        transition: all var(--tr); font-family: var(--font-ui);
    }
    .act-edit {
        background: var(--gold-faint); color: var(--gold-deep);
        border: 1px solid var(--gold-border);
    }
    .act-edit:hover { background: var(--gold); color: var(--ink); border-color: var(--gold); }
    .act-del {
        background: var(--danger-bg); color: var(--danger);
        border: 1px solid rgba(192,57,43,.2);
    }
    .act-del:hover { background: rgba(192,57,43,.18); }
    html.dark .act-edit { color: var(--gold); }
    </style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<!-- HERO -->
<div class="gd-hero">
    <div class="gd-hero-left">
        <div class="gd-hero-title"><?= $p['header_title'] ?></div>
        <div class="gd-hero-sub"><?= $p['header_sub'] ?></div>
    </div>
    <a href="admin_dashboard.php" class="btn-back"><?= $p['btn_back'] ?></a>
</div>

<div class="gd-wrap">

    <!-- ══ FORM SECTION ══ -->
    <div class="section-label">
        <i class="fa-solid fa-plus-circle"></i>
        <?= $p['section_form'] ?>
    </div>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data" id="docForm">
            <div class="grid-form">

                <div class="form-group"><label><?= $p['lbl_title'] ?></label><input type="text" name="titre" required></div>
                <div class="form-group"><label><?= $p['lbl_subtitle'] ?></label><input type="text" name="sous_titre"></div>

                <div class="form-group">
                    <label><?= $p['lbl_type'] ?></label>
                    <select name="id_type" id="typeSelector" onchange="toggleFields()" required>
                        <option value=""><?= $p['opt_choose'] ?></option>
                        <option value="1"><?= $p['opt_livre'] ?></option>
                        <option value="2"><?= $p['opt_these'] ?></option>
                        <option value="3"><?= $p['opt_article'] ?></option>
                        <option value="4"><?= $p['opt_journal'] ?></option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= $p['lbl_cat'] ?></label>
                    <select name="categorie" id="catSelector" onchange="checkOtherCat()">
                        <option value="Informatique"><?= $p['opt_info'] ?></option>
                        <option value="Mathématiques"><?= $p['opt_math'] ?></option>
                        <option value="Physique"><?= $p['opt_phys'] ?></option>
                        <option value="Médecine"><?= $p['opt_med'] ?></option>
                        <option value="Autre"><?= $p['opt_other'] ?></option>
                    </select>
                </div>

                <div class="form-group" id="otherCatGroup" style="display:none;">
                    <label style="color:#d35400;"><?= $p['lbl_other_cat'] ?></label>
                    <input type="text" name="custom_categorie" id="customCat">
                </div>

                <div class="form-group"><label><?= $p['lbl_author'] ?></label><input type="text" name="auteur"></div>
                <div class="form-group"><label><?= $p['lbl_editor'] ?></label><input type="text" name="editeur"></div>
                <div class="form-group"><label><?= $p['lbl_year'] ?></label><input type="text" name="annee_edition" placeholder="<?= htmlspecialchars($p['ph_year']) ?>"></div>
                <div class="form-group"><label><?= $p['lbl_pub_date'] ?></label><input type="text" name="date_publication" placeholder="<?= htmlspecialchars($p['ph_pub_date']) ?>"></div>
                <div class="form-group"><label><?= $p['lbl_place'] ?></label><input type="text" name="lieu_edition" placeholder="<?= htmlspecialchars($p['ph_place']) ?>"></div>
                <div class="form-group">
                    <label><?= $p['lbl_lang'] ?></label>
                    <select name="langue">
                        <option><?= $p['opt_fr'] ?></option>
                        <option><?= $p['opt_ar'] ?></option>
                        <option><?= $p['opt_en'] ?></option>
                    </select>
                </div>

                <!-- Dynamic: Livre -->
                <div class="dynamic-field" id="field_livre">
                    <div class="inner-grid" style="grid-template-columns:1fr 1fr">
                        <div class="form-group"><label><?= $p['lbl_isbn'] ?></label><input type="text" name="isbn"></div>
                        <div class="form-group"><label><?= $p['lbl_pages'] ?></label><input type="number" name="nb_pages"></div>
                    </div>
                </div>

                <!-- Dynamic: Thèse -->
                <div class="dynamic-field" id="field_these">
                    <div class="inner-grid" style="grid-template-columns:1fr 1fr 1fr">
                        <div class="form-group"><label><?= $p['lbl_encadrant'] ?></label><input type="text" name="encadrant"></div>
                        <div class="form-group"><label><?= $p['lbl_univ'] ?></label><input type="text" name="universite"></div>
                        <div class="form-group"><label><?= $p['lbl_spec'] ?></label><input type="text" name="specialite"></div>
                    </div>
                </div>

                <!-- Dynamic: Revue -->
                <div class="dynamic-field" id="field_revue">
                    <div class="inner-grid" style="grid-template-columns:1fr 1fr 1fr">
                        <div class="form-group"><label><?= $p['lbl_issn'] ?></label><input type="text" name="issn"></div>
                        <div class="form-group"><label><?= $p['lbl_revue'] ?></label><input type="text" name="nom_revue"></div>
                        <div class="form-group"><label><?= $p['lbl_issue'] ?></label><input type="text" name="numero_issue"></div>
                    </div>
                </div>

                <div class="form-group field-green">
                    <label><?= $p['lbl_prix_achat'] ?></label>
                    <input type="number" name="prix_achat" value="100" required>
                </div>
                <div class="form-group field-gold">
                    <label><?= $p['lbl_prix_vente'] ?></label>
                    <input type="number" name="prix_vente" value="150" required>
                </div>
                <div class="form-group field-gray">
                    <label><?= $p['lbl_stock'] ?></label>
                    <input type="number" name="exemplaires" value="10" required>
                </div>
                <div class="form-group">
                    <label><?= $p['lbl_image'] ?></label>
                    <input type="file" name="image_livre" accept="image/*">
                </div>
                <div class="form-group">
                    <label><?= $p['lbl_transaction'] ?></label>
                    <select name="type_transaction">
                        <option value="both"><?= $p['opt_both'] ?></option>
                        <option value="achat"><?= $p['opt_achat'] ?></option>
                        <option value="emprunt"><?= $p['opt_emprunt'] ?></option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label><?= $p['lbl_desc'] ?></label>
                    <textarea name="description" rows="3"></textarea>
                </div>

                <button type="submit" name="ajouter_livre" class="btn-add">
                    <i class="fa-solid fa-floppy-disk" style="font-size:12px"></i>
                    <?= $p['btn_save'] ?>
                </button>

            </div>
        </form>
    </div>

    <!-- ══ TABLE SECTION ══ -->
    <div class="section-label">
        <i class="fa-solid fa-list"></i>
        <?= $p['section_list'] ?>
    </div>

    <div class="table-card">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th><?= $p['th_cover'] ?></th>
                        <th><?= $p['th_title_type'] ?></th>
                        <th><?= $p['th_stock_init'] ?></th>
                        <th><?= $p['th_stock_state'] ?></th>
                        <th><?= $p['th_actions'] ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()):
                    $dispo_type    = $row['disponible_pour'];
                    $display_stock = ($row['exemplaires_disponibles'] < 0) ? 0 : (int)$row['exemplaires_disponibles'];
                    $stock_class   = ($dispo_type === 'emprunt')
                        ? (($display_stock <= 0) ? 'stock-low' : 'stock-actuel')
                        : (($display_stock <= 2) ? 'stock-low' : 'stock-actuel');
                    $stock_initial = (int)$row['exemplaires'];
                    $en_mouvement  = $stock_initial - (int)$row['exemplaires_disponibles'];
                ?>
                <tr>
                    <td><img src="../uploads/<?= $row['id_doc'] ?>.jpg" class="img-table" onerror="this.src='../assets/img/no-book.png'"></td>
                    <td>
                        <strong style="font-family:var(--font-serif);font-size:15px"><?= htmlspecialchars($row['titre']) ?></strong><br>
                        <small style="color:var(--page-muted);font-size:11px"><?= htmlspecialchars($row['libelle_type'] ?? '—') ?> · <?= htmlspecialchars($row['categorie'] ?? '—') ?></small>
                    </td>
                    <td>
                        <span class="stock-label"><?= $p['lbl_total'] ?></span>
                        <span class="stock-badge stock-initial"><?= $stock_initial ?> <?= $p['lbl_units'] ?></span>
                    </td>
                    <td>
                        <span class="stock-label"><?= $p['lbl_available'] ?></span>
                        <span class="stock-badge <?= $stock_class ?>"><?= $display_stock ?> <?= $p['lbl_on_shelf'] ?></span>
                        <?php if($en_mouvement > 0): ?>
                        <div style="margin-top:5px;font-size:11px;color:var(--amber)">
                            <i class="fa-solid fa-arrow-up" style="font-size:9px"></i>
                            <?= $en_mouvement ?> <?= $p['lbl_movement'] ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-direction:<?= $isRtl?'row-reverse':'row' ?>">
                            <a href="modifier_document.php?id=<?= $row['id_doc'] ?>" class="act-btn act-edit">
                                <i class="fa-solid fa-pen" style="font-size:10px"></i>
                            </a>
                            <button onclick="confirmDelete(<?= $row['id_doc'] ?>)" class="act-btn act-del">
                                <i class="fa-solid fa-trash" style="font-size:10px"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /gd-wrap -->

<script>
const SWAL = {
    deleteTitle : <?= json_encode($p['swal_delete_title']) ?>,
    deleteText  : <?= json_encode($p['swal_delete_text']) ?>,
    deleteYes   : <?= json_encode($p['swal_delete_yes']) ?>,
    deleteNo    : <?= json_encode($p['swal_delete_no']) ?>,
};

function toggleFields() {
    const val = document.getElementById('typeSelector').value;
    document.getElementById('field_livre').style.display = (val === "1") ? 'block' : 'none';
    document.getElementById('field_these').style.display = (val === "2") ? 'block' : 'none';
    document.getElementById('field_revue').style.display = (val === "3" || val === "4") ? 'block' : 'none';
}
function checkOtherCat() {
    const sel   = document.getElementById('catSelector');
    const group = document.getElementById('otherCatGroup');
    group.style.display = (sel.value === "Autre") ? 'flex' : 'none';
}
function confirmDelete(id) {
    Swal.fire({
        title: SWAL.deleteTitle,
        text:  SWAL.deleteText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: SWAL.deleteYes,
        cancelButtonText:  SWAL.deleteNo,
        confirmButtonColor: '#C0392B',
        cancelButtonColor:  '#C4A46B',
        background: '#FFFDF9',
        color: '#2C1F0E',
    }).then(result => {
        if (result.isConfirmed) window.location.href = 'gerer_documents.php?delete=' + id;
    });
}
</script>
<?= $success_script ?>
</body>
</html>