<?php
// 1. الاتصال بقاعدة البيانات
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "memoir_db"; 

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
require_once '../includes/head.php'; 

// ── Charger les traductions ──────────────────────────────
include_once '../includes/languages.php';
// $lang متاح من languages.php

$pg = [
    'fr' => [
        'page_title'      => 'Administration du Catalogue — UHBC',
        'header_title'    => '📚 Administration du Catalogue',
        'btn_back'        => '⬅ Retour à l\'Accueil',
        // Form labels
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
        'lbl_year'        => 'Année d\'édition',
        'ph_year'         => 'Ex: 2024',
        'lbl_pub_date'    => 'Date de Publication',
        'ph_pub_date'     => 'Ex: 15 Mars 2024',
        'lbl_place'       => 'Lieu d\'édition',
        'ph_place'        => 'Ex: Chlef, Algérie',
        'lbl_lang'        => 'Langue',
        'opt_fr'          => 'Français',
        'opt_ar'          => 'Arabe',
        'opt_en'          => 'Anglais',
        // Dynamic fields
        'lbl_isbn'        => 'ISBN',
        'lbl_pages'       => 'Nombre de Pages',
        'lbl_encadrant'   => 'Encadrant',
        'lbl_univ'        => 'Université',
        'lbl_spec'        => 'Spécialité',
        'lbl_issn'        => 'ISSN',
        'lbl_revue'       => 'Nom de la Revue',
        'lbl_issue'       => 'Numéro / Issue',
        // Prices & stock
        'lbl_prix_achat'  => 'Prix d\'Achat',
        'lbl_prix_vente'  => 'Prix de Vente',
        'lbl_stock'       => 'Stock Initial',
        'lbl_image'       => 'Image',
        'lbl_transaction' => 'Transaction',
        'opt_both'        => 'Vente & Emprunt',
        'opt_achat'       => 'Vente uniquement',
        'opt_emprunt'     => 'Emprunt uniquement',
        'lbl_desc'        => 'Description',
        'btn_save'        => '💾 Enregistrer le Document',
        // Table
        'list_title'      => '📋 Liste des Documents',
        'th_cover'        => 'Couverture',
        'th_title_type'   => 'Titre & Type',
        'th_stock_init'   => 'Stock Initial',
        'th_stock_state'  => 'État du Stock',
        'th_actions'      => 'Actions',
        'lbl_total'       => 'Total entré',
        'lbl_units'       => 'units',
        'lbl_available'   => 'Disponible',
        'lbl_on_shelf'    => 'en rayon',
        'lbl_movement'    => 'sortie(s) (Vente/Prêt)',
        // Alerts
        'swal_delete_title'  => 'Supprimer?',
        'swal_delete_yes'    => 'Oui',
        'swal_deleted_title' => 'Supprimé!',
        'swal_deleted_text'  => 'Le document a été supprimé.',
        'swal_added_title'   => 'Succès!',
        'swal_added_text'    => 'Le document a été ajouté avec succès.',
    ],
    'en' => [
        'page_title'      => 'Catalogue Administration — UHBC',
        'header_title'    => '📚 Catalogue Administration',
        'btn_back'        => '⬅ Back to Home',
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
        'lbl_image'       => 'Image',
        'lbl_transaction' => 'Transaction',
        'opt_both'        => 'Sale & Loan',
        'opt_achat'       => 'Sale only',
        'opt_emprunt'     => 'Loan only',
        'lbl_desc'        => 'Description',
        'btn_save'        => '💾 Save Document',
        'list_title'      => '📋 Document List',
        'th_cover'        => 'Cover',
        'th_title_type'   => 'Title & Type',
        'th_stock_init'   => 'Initial Stock',
        'th_stock_state'  => 'Stock Status',
        'th_actions'      => 'Actions',
        'lbl_total'       => 'Total in',
        'lbl_units'       => 'units',
        'lbl_available'   => 'Available',
        'lbl_on_shelf'    => 'on shelf',
        'lbl_movement'    => 'movement(s) (Sale/Loan)',
        'swal_delete_title'  => 'Delete?',
        'swal_delete_yes'    => 'Yes',
        'swal_deleted_title' => 'Deleted!',
        'swal_deleted_text'  => 'The document has been deleted.',
        'swal_added_title'   => 'Success!',
        'swal_added_text'    => 'The document has been added successfully.',
    ],
    'ar' => [
        'page_title'      => 'إدارة الكتالوج — UHBC',
        'header_title'    => '📚 إدارة الكتالوج',
        'btn_back'        => 'العودة للرئيسية ←',
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
        'lbl_image'       => 'صورة',
        'lbl_transaction' => 'نوع المعاملة',
        'opt_both'        => 'بيع واستعارة',
        'opt_achat'       => 'بيع فقط',
        'opt_emprunt'     => 'استعارة فقط',
        'lbl_desc'        => 'الوصف',
        'btn_save'        => '💾 حفظ الوثيقة',
        'list_title'      => '📋 قائمة الوثائق',
        'th_cover'        => 'الغلاف',
        'th_title_type'   => 'العنوان والنوع',
        'th_stock_init'   => 'المخزون الأولي',
        'th_stock_state'  => 'حالة المخزون',
        'th_actions'      => 'الإجراءات',
        'lbl_total'       => 'المجموع المُدخَل',
        'lbl_units'       => 'وحدة',
        'lbl_available'   => 'المتاح',
        'lbl_on_shelf'    => 'في الرف',
        'lbl_movement'    => 'خروج (بيع/إعارة)',
        'swal_delete_title'  => 'حذف؟',
        'swal_delete_yes'    => 'نعم',
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
    $id_type = (int)$_POST['id_type'];
    $titre = $conn->real_escape_string($_POST['titre']);
    $sous_titre = $conn->real_escape_string($_POST['sous_titre']);
    $auteur = $conn->real_escape_string($_POST['auteur']);
    $editeur = $conn->real_escape_string($_POST['editeur']);
    $langue = $conn->real_escape_string($_POST['langue']);
    
    $categorie = $_POST['categorie'];
    if ($categorie === "Autre" && !empty($_POST['custom_categorie'])) {
        $categorie = $conn->real_escape_string($_POST['custom_categorie']);
    } else {
        $categorie = $conn->real_escape_string($categorie);
    }

    $annee_edition = "'" . $conn->real_escape_string($_POST['annee_edition']) . "'";
    $date_pub = "'" . $conn->real_escape_string($_POST['date_publication']) . "'";
    $lieu_edition = "'" . $conn->real_escape_string($_POST['lieu_edition']) . "'";
    
    $nb_pages = !empty($_POST['nb_pages']) ? (int)$_POST['nb_pages'] : "NULL";
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $issn = $conn->real_escape_string($_POST['issn']);
    $dispo = $_POST['type_transaction']; 
    $desc = $conn->real_escape_string($_POST['description']);
    $encadrant = $conn->real_escape_string($_POST['encadrant']);
    $universite = $conn->real_escape_string($_POST['universite']);
    $specialite = $conn->real_escape_string($_POST['specialite']);
    $nom_revue = $conn->real_escape_string($_POST['nom_revue']);
    $numero_issue = $conn->real_escape_string($_POST['numero_issue']);
    
    $prix_vente = $_POST['prix_vente'];
    $prix_achat = $_POST['prix_achat'];
    $exemplaires = (int)$_POST['exemplaires'];

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
    <link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<script>
    (function(){
        if(localStorage.getItem('auralib_theme')==='dark')
            document.documentElement.classList.add('dark');
    })();
</script>
    <style>
        :root { --primary: #3d2b1f; --bg: #f4f1ea; --secondary: #7f8c8d; --success: #27ae60; --danger: #e74c3c; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); padding: 20px; color: #333; direction: <?= $isRtl ? 'rtl' : 'ltr' ?>; }
        .container { max-width: 1300px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid var(--primary); padding-bottom: 10px; flex-direction: <?= $isRtl ? 'row-reverse' : 'row' ?>; }
        .btn-back { background: var(--secondary); color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; font-size: 14px; font-weight: bold; }
        .grid-form { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
        .form-group { display: flex; flex-direction: column; }
        .full-width { grid-column: span 4; }
        label { font-weight: bold; font-size: 13px; color: #555; margin-bottom: 5px; text-align: <?= $isRtl ? 'right' : 'left' ?>; }
        input, select, textarea { padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; text-align: <?= $isRtl ? 'right' : 'left' ?>; direction: <?= $isRtl ? 'rtl' : 'ltr' ?>; }
        .btn-add { background: var(--primary); color: white; padding: 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; grid-column: span 4; margin-top: 10px; transition: 0.3s; }
        .btn-add:hover { background: #5d4037; }
        .dynamic-field { background: #fdfaf4; padding: 20px; border-radius: 8px; border: 2px dashed #e0dcd0; display: none; margin-top: 10px; grid-column: span 4; }
        table { width: 100%; border-collapse: collapse; margin-top: 40px; }
        th { background: #eee; padding: 12px; text-align: <?= $isRtl ? 'right' : 'left' ?>; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; text-align: <?= $isRtl ? 'right' : 'left' ?>; }
        .img-table { width: 45px; height: 60px; object-fit: cover; border-radius: 4px; }
        
        .stock-badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; display: inline-block; }
        .stock-initial { background: #f1f1f1; color: #666; border: 1px solid #ccc; }
        .stock-actuel { background: #e8f6ef; color: var(--success); border: 1px solid #c2eadd; }
        .stock-low { background: #ffebee; color: var(--danger); border: 1px solid #ffcdd2; }
        .stock-label { font-size: 10px; text-transform: uppercase; color: #999; display: block; margin-bottom: 2px; }
    </style>
</head>
<body>

<div class="container">
    <div class="admin-header">
        <h2><?= $p['header_title'] ?></h2>
        <a href="admin_dashboard.php" class="btn-back"><?= $p['btn_back'] ?></a>
    </div>

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
                <label style="color: #d35400;"><?= $p['lbl_other_cat'] ?></label>
                <input type="text" name="custom_categorie" id="customCat">
            </div>
            
            <div class="form-group"><label><?= $p['lbl_author'] ?></label><input type="text" name="auteur"></div>
            <div class="form-group"><label><?= $p['lbl_editor'] ?></label><input type="text" name="editeur"></div>
            
            <div class="form-group"><label><?= $p['lbl_year'] ?></label><input type="text" name="annee_edition" placeholder="<?= $p['ph_year'] ?>"></div>
            <div class="form-group"><label><?= $p['lbl_pub_date'] ?></label><input type="text" name="date_publication" placeholder="<?= $p['ph_pub_date'] ?>"></div>

            <div class="form-group"><label><?= $p['lbl_place'] ?></label><input type="text" name="lieu_edition" placeholder="<?= $p['ph_place'] ?>"></div>
            <div class="form-group">
                <label><?= $p['lbl_lang'] ?></label>
                <select name="langue">
                    <option><?= $p['opt_fr'] ?></option>
                    <option><?= $p['opt_ar'] ?></option>
                    <option><?= $p['opt_en'] ?></option>
                </select>
            </div>

            <div class="dynamic-field" id="field_livre">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group"><label><?= $p['lbl_isbn'] ?></label><input type="text" name="isbn"></div>
                    <div class="form-group"><label><?= $p['lbl_pages'] ?></label><input type="number" name="nb_pages"></div>
                </div>
            </div>

            <div class="dynamic-field" id="field_these">
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:15px;">
                    <div class="form-group"><label><?= $p['lbl_encadrant'] ?></label><input type="text" name="encadrant"></div>
                    <div class="form-group"><label><?= $p['lbl_univ'] ?></label><input type="text" name="universite"></div>
                    <div class="form-group"><label><?= $p['lbl_spec'] ?></label><input type="text" name="specialite"></div>
                </div>
            </div>

            <div class="dynamic-field" id="field_revue">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group"><label><?= $p['lbl_issn'] ?></label><input type="text" name="issn"></div>
                    <div class="form-group"><label><?= $p['lbl_revue'] ?></label><input type="text" name="nom_revue"></div>
                    <div class="form-group"><label><?= $p['lbl_issue'] ?></label><input type="text" name="numero_issue"></div>
                </div>
            </div>

            <div class="form-group" style="background: #e8f6ef; padding: 10px; border-radius: 8px;">
                <label style="color: #27ae60;"><?= $p['lbl_prix_achat'] ?></label>
                <input type="number" name="prix_achat" value="100" required>
            </div>
            
            <div class="form-group" style="background: #fdfaf4; padding: 10px; border-radius: 8px;">
                <label style="color: #d4a942;"><?= $p['lbl_prix_vente'] ?></label>
                <input type="number" name="prix_vente" value="150" required>
            </div>

            <div class="form-group" style="background: #f1f1f1; padding: 10px; border-radius: 8px;">
                <label><?= $p['lbl_stock'] ?></label>
                <input type="number" name="exemplaires" value="10" required>
            </div>

            <div class="form-group"><label><?= $p['lbl_image'] ?></label><input type="file" name="image_livre" accept="image/*"></div>
            
            <div class="form-group">
                <label><?= $p['lbl_transaction'] ?></label>
                <select name="type_transaction">
                    <option value="both"><?= $p['opt_both'] ?></option>
                    <option value="achat"><?= $p['opt_achat'] ?></option>
                    <option value="emprunt"><?= $p['opt_emprunt'] ?></option>
                </select>
            </div>

            <div class="form-group full-width"><label><?= $p['lbl_desc'] ?></label><textarea name="description" rows="2"></textarea></div>
            <button type="submit" name="ajouter_livre" class="btn-add"><?= $p['btn_save'] ?></button>
        </div>
    </form>

    <hr>

    <h3><?= $p['list_title'] ?></h3>
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

        if ($dispo_type === 'emprunt') {
            $stock_class = ($display_stock <= 0) ? 'stock-low' : 'stock-actuel';
        } else {
            $stock_class = ($display_stock <= 2) ? 'stock-low' : 'stock-actuel';
        }

        $stock_initial  = (int)$row['exemplaires'];
        $en_mouvement   = $stock_initial - (int)$row['exemplaires_disponibles'];
    ?>
    <tr>
        <td><img src="../uploads/<?= $row['id_doc'] ?>.jpg" class="img-table" onerror="this.src='../assets/img/no-book.png'"></td>
        <td>
            <strong><?= htmlspecialchars($row['titre']) ?></strong><br>
            <small style="color:var(--secondary)"><?= $row['libelle_type'] ?> | <?= $row['categorie'] ?></small>
        </td>
        <td>
            <span class="stock-label"><?= $p['lbl_total'] ?></span>
            <span class="stock-badge stock-initial"><?= $stock_initial ?> <?= $p['lbl_units'] ?></span>
        </td>
        <td>
            <span class="stock-label"><?= $p['lbl_available'] ?></span>
            <span class="stock-badge <?= $stock_class ?>"><?= $display_stock ?> <?= $p['lbl_on_shelf'] ?></span>
            
            <?php if($en_mouvement > 0): ?>
                <div style="margin-top:5px; font-size:11px; color:#e67e22;">
                    ⚠ <?= $en_mouvement ?> <?= $p['lbl_movement'] ?>
                </div>
            <?php endif; ?>
        </td>
        <td>
            <a href="modifier_document.php?id=<?= $row['id_doc'] ?>" style="color:#3498db; text-decoration:none;">📝</a> &nbsp;
            <a href="#" onclick="confirmDelete(<?= $row['id_doc'] ?>)" style="color:#e74c3c; text-decoration:none;">🗑️</a>
        </td>
    </tr>
    <?php endwhile; ?>
</tbody>
    </table>
</div>

<script>
// نصوص الـ SweetAlert من PHP
const SWAL = {
    deleteTitle : <?= json_encode($p['swal_delete_title']) ?>,
    deleteYes   : <?= json_encode($p['swal_delete_yes']) ?>,
};

function toggleFields() {
    const val = document.getElementById('typeSelector').value;
    document.getElementById('field_livre').style.display = (val === "1") ? 'block' : 'none';
    document.getElementById('field_these').style.display = (val === "2") ? 'block' : 'none';
    document.getElementById('field_revue').style.display = (val === "3" || val === "4") ? 'block' : 'none';
}
function checkOtherCat() {
    const sel = document.getElementById('catSelector');
    const group = document.getElementById('otherCatGroup');
    if (sel.value === "Autre") group.style.display = 'flex';
    else group.style.display = 'none';
}
function confirmDelete(id) {
    Swal.fire({
        title: SWAL.deleteTitle,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: SWAL.deleteYes,
        confirmButtonColor: '#3d2b1f'
    }).then((result) => { if (result.isConfirmed) window.location.href = 'gerer_documents.php?delete=' + id; });
}
</script>
<?= $success_script ?>
</body>
</html>