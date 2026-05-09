<?php
// 1. الاتصال بقاعدة البيانات
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "memoir_db"; 

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
require_once '../includes/head.php'; 

$success_script = "";

// 2. معالجة الحذف
if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    $conn->query("DELETE FROM documents WHERE id_doc = $id_del");
    $success_script = "<script>Swal.fire('Supprimé!', 'Le document a été supprimé.', 'success').then(() => { window.location.href='gerer_documents.php'; });</script>";
}

// 3. معالجة الإضافة
if (isset($_POST['ajouter_livre'])) {
    $id_type = (int)$_POST['id_type'];
    $titre = $conn->real_escape_string($_POST['titre']);
    $sous_titre = $conn->real_escape_string($_POST['sous_titre']);
    $auteur = $conn->real_escape_string($_POST['auteur']);
    $editeur = $conn->real_escape_string($_POST['editeur']);
    $langue = $conn->real_escape_string($_POST['langue']);
    
    // Logic Catégorie
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
    $exemplaires = (int)$_POST['exemplaires']; // القيمة الثابتة

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
        $success_script = "<script>Swal.fire('Succès!', 'Le document a été ajouté avec succès.', 'success').then(() => { window.location.href='gerer_documents.php'; });</script>";
    }
}

$result = $conn->query("SELECT d.*, t.libelle_type FROM documents d LEFT JOIN types_documents t ON d.id_type = t.id_type ORDER BY d.id_doc DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration du Catalogue — UHBC</title>
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
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); padding: 20px; color: #333; }
        .container { max-width: 1300px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid var(--primary); padding-bottom: 10px; }
        .btn-back { background: var(--secondary); color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; font-size: 14px; font-weight: bold; }
        .grid-form { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
        .form-group { display: flex; flex-direction: column; }
        .full-width { grid-column: span 4; }
        label { font-weight: bold; font-size: 13px; color: #555; margin-bottom: 5px; }
        input, select, textarea { padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        .btn-add { background: var(--primary); color: white; padding: 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; grid-column: span 4; margin-top: 10px; transition: 0.3s; }
        .btn-add:hover { background: #5d4037; }
        .dynamic-field { background: #fdfaf4; padding: 20px; border-radius: 8px; border: 2px dashed #e0dcd0; display: none; margin-top: 10px; grid-column: span 4; }
        table { width: 100%; border-collapse: collapse; margin-top: 40px; }
        th { background: #eee; padding: 12px; text-align: left; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
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
        <h2>📚 Administration du Catalogue</h2>
        <a href="admin_dashboard.php" class="btn-back">⬅ Retour à l'Accueil</a>
    </div>

    <!-- ... (نفس نموذج الإدخال السابق بدون تغيير) ... -->
    <form method="POST" enctype="multipart/form-data" id="docForm">
        <div class="grid-form">
            <div class="form-group"><label>Titre *</label><input type="text" name="titre" required></div>
            <div class="form-group"><label>Sous-titre</label><input type="text" name="sous_titre"></div>
            
            <div class="form-group">
                <label>Type de Document *</label>
                <select name="id_type" id="typeSelector" onchange="toggleFields()" required>
                    <option value="">-- Choisir --</option>
                    <option value="1">Livre</option>
                    <option value="2">Thèse / Mémoire</option>
                    <option value="3">Article</option>
                    <option value="4">Journal / Revue</option>
                </select>
            </div>

            <div class="form-group">
                <label>Catégorie</label>
                <select name="categorie" id="catSelector" onchange="checkOtherCat()">
                    <option value="Informatique">Informatique</option>
                    <option value="Mathématiques">Mathématiques</option>
                    <option value="Physique">Physique</option>
                    <option value="Médecine">Médecine</option>
                    <option value="Autre">Autre...</option>
                </select>
            </div>

            <div class="form-group" id="otherCatGroup" style="display:none;">
                <label style="color: #d35400;">Préciser la catégorie *</label>
                <input type="text" name="custom_categorie" id="customCat">
            </div>
            
            <div class="form-group"><label>Auteur</label><input type="text" name="auteur"></div>
            <div class="form-group"><label>Éدiteur</label><input type="text" name="editeur"></div>
            
            <div class="form-group"><label>Année d'édition</label><input type="text" name="annee_edition" placeholder="Ex: 2024"></div>
            <div class="form-group"><label>Date de Publication</label><input type="text" name="date_publication" placeholder="Ex: 15 Mars 2024"></div>

            <div class="form-group"><label>Lieu d'édition</label><input type="text" name="lieu_edition" placeholder="Ex: Chlef, Algérie"></div>
            <div class="form-group">
                <label>Langue</label>
                <select name="langue">
                    <option>Français</option>
                    <option>Arabe</option>
                    <option>Anglais</option>
                </select>
            </div>

            <div class="dynamic-field" id="field_livre">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group"><label>ISBN</label><input type="text" name="isbn"></div>
                    <div class="form-group"><label>Nombre de Pages</label><input type="number" name="nb_pages"></div>
                </div>
            </div>

            <div class="dynamic-field" id="field_these">
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:15px;">
                    <div class="form-group"><label>Encadrant</label><input type="text" name="encadrant"></div>
                    <div class="form-group"><label>Université</label><input type="text" name="universite"></div>
                    <div class="form-group"><label>Spécialité</label><input type="text" name="specialite"></div>
                </div>
            </div>

            <div class="dynamic-field" id="field_revue">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group"><label>ISSN</label><input type="text" name="issn"></div>
                    <div class="form-group"><label>Nom de la Revue</label><input type="text" name="nom_revue"></div>
                    <div class="form-group"><label>Numéro / Issue</label><input type="text" name="numero_issue"></div>
                </div>
            </div>

            <div class="form-group" style="background: #e8f6ef; padding: 10px; border-radius: 8px;">
                <label style="color: #27ae60;">Prix d'Achat</label>
                <input type="number" name="prix_achat" value="100" required>
            </div>
            
            <div class="form-group" style="background: #fdfaf4; padding: 10px; border-radius: 8px;">
                <label style="color: #d4a942;">Prix de Vente</label>
                <input type="number" name="prix_vente" value="150" required>
            </div>

            <div class="form-group" style="background: #f1f1f1; padding: 10px; border-radius: 8px;">
                <label>Stock Initial</label>
                <input type="number" name="exemplaires" value="10" required>
            </div>

            <div class="form-group"><label>Image</label><input type="file" name="image_livre" accept="image/*"></div>
            
            <div class="form-group">
                <label>Transaction</label>
                <select name="type_transaction">
                    <option value="both">Vente & Emprunt</option>
                    <option value="achat">Vente uniquement</option>
                    <option value="emprunt">Emprunt uniquement</option>
                </select>
            </div>

            <div class="form-group full-width"><label>Description</label><textarea name="description" rows="2"></textarea></div>
            <button type="submit" name="ajouter_livre" class="btn-add">💾 Enregistrer le Document</button>
        </div>
    </form>

    <hr>

    <h3>📋 Liste des Documents</h3>
    <table>
        <thead>
            <tr>
                <th>Couverture</th>
                <th>Titre & Type</th>
                <th>Stock Initial</th>
                <th>État du Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    <?php while($row = $result->fetch_assoc()):

// داخل حلقة الـ while
$dispo_type = $row['disponible_pour']; // 'achat', 'emprunt', أو 'both'
$display_stock = ($row['exemplaires_disponibles'] < 0) ? 0 : (int)$row['exemplaires_disponibles'];

if ($dispo_type === 'emprunt') {
    // في حالة الإعارة: التنبيه الأحمر يظهر فقط إذا وصل المخزون لـ 0
    $stock_class = ($display_stock <= 0) ? 'stock-low' : 'stock-actuel';
} else {
    // في حالة البيع (أو كلاهما): التنبيه يظهر عند 2 أو أقل (كما فعلنا سابقاً)
    $stock_class = ($display_stock <= 2) ? 'stock-low' : 'stock-actuel';
}

        
        $stock_initial = (int)$row['exemplaires'];
        
        // 3. حساب عدد الكتب الخارجة (مباعة أو معارة)
        // نستخدم القيمة الأصلية من قاعدة البيانات للحساب الدقيق حتى لو كانت سالبة
        $en_mouvement = $stock_initial - (int)$row['exemplaires_disponibles'];
    ?>
    <tr>
        <td><img src="../uploads/<?= $row['id_doc'] ?>.jpg" class="img-table" onerror="this.src='../assets/img/no-book.png'"></td>
        <td>
            <strong><?= htmlspecialchars($row['titre']) ?></strong><br>
            <small style="color:var(--secondary)"><?= $row['libelle_type'] ?> | <?= $row['categorie'] ?></small>
        </td>
        <td>
            <span class="stock-label">Total entré</span>
            <span class="stock-badge stock-initial"><?= $stock_initial ?> units</span>
        </td>
        <td>
            <span class="stock-label">Disponible</span>
            <span class="stock-badge <?= $stock_class ?>"><?= $display_stock ?> en rayon</span>
            
            <?php if($en_mouvement > 0): ?>
                <div style="margin-top:5px; font-size:11px; color:#e67e22;">
                    ⚠ <?= $en_mouvement ?> sortie(s) (Vente/Prêt)
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
// ... (دوال JavaScript نفسها السابقة) ...
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
        title: 'Supprimer?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui',
        confirmButtonColor: '#3d2b1f'
    }).then((result) => { if (result.isConfirmed) window.location.href = 'gerer_documents.php?delete=' + id; });
}
</script>
<?= $success_script ?>
</body>
</html>