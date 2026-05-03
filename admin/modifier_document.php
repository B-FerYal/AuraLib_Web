<?php
// 1. الاتصال بقاعدة البيانات
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "memoir_db"; 

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}
require_once '../includes/head.php'; 

$success_script = "";

// 2. التحقق من وجود ID الكتاب المطلوب تعديله
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM documents WHERE id_doc = $id");
    $doc = $res->fetch_assoc();

    if (!$doc) {
        die("Document non trouvé !");
    }
} else {
    header("Location: gerer_documents.php");
    exit();
}

// 3. معالجة تحديث البيانات عند إرسال الفورم
if (isset($_POST['modifier_livre'])) {
    $titre = $conn->real_escape_string($_POST['titre']);
    $auteur = $conn->real_escape_string($_POST['auteur']);
    $prix = $_POST['prix_vente'];
    $dispo = $_POST['type_transaction']; 
    $exemplaires = $_POST['exemplaires'];
    $desc = $conn->real_escape_string($_POST['description']);

    $update_sql = "UPDATE documents SET 
                   titre = '$titre', 
                   auteur = '$auteur', 
                   prix = '$prix', 
                   disponible_pour = '$dispo', 
                   exemplaires = '$exemplaires', 
                   description_longue = '$desc' 
                   WHERE id_doc = $id";

    if ($conn->query($update_sql) === TRUE) {
        $success_script = "
        <script>
            Swal.fire({
                title: 'Mis à jour !',
                text: 'Le document a été modifié مع نجاح.',
                icon: 'success',
                confirmButtonColor: '#3d2b1f'
            }).then(() => { window.location.href = 'gerer_documents.php'; });
        </script>";
    } else {
        $success_script = "<script>Swal.fire('Erreur', '" . $conn->error . "', 'error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Document - AuraLib</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary-color: #3d2b1f; --bg-color: #f4f1ea; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg-color); padding: 40px 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        
        .header-edit { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        h2 { color: var(--primary-color); margin: 0; }
        
        .btn-back { background: transparent; color: #777; border: 1px solid #ccc; padding: 8px 15px; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; transition: 0.3s; }
        .btn-back:hover { background: #eee; }

        .grid-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .full-width { grid-column: span 2; }
        
        label { font-weight: bold; margin-bottom: 8px; color: #555; }
        input, select, textarea { padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; }
        input:focus { border-color: var(--primary-color); outline: none; }
        
        .btn-update { background: var(--primary-color); color: white; padding: 15px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 16px; margin-top: 20px; transition: 0.3s; }
        .btn-update:hover { background: #5a402d; transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="container">
    <div class="header-edit">
        <h2>📝 Modifier le Document #<?php echo $id; ?></h2>
        <a href="gerer_documents.php" class="btn-back">⬅ Annuler</a>
    </div>

    <form method="POST">
        <div class="grid-form">
            <div class="form-group">
                <label>Titre du document</label>
                <input type="text" name="titre" value="<?php echo htmlspecialchars($doc['titre']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Auteur</label>
                <input type="text" name="auteur" value="<?php echo htmlspecialchars($doc['auteur']); ?>" required>
            </div>

            <div class="form-group">
                <label>Type Transaction</label>
                <select name="type_transaction">
                    <option value="both" <?php if($doc['disponible_pour'] == 'both') echo 'selected'; ?>>Vente & Emprunt</option>
                    <option value="achat" <?php if($doc['disponible_pour'] == 'achat') echo 'selected'; ?>>Vente uniquement</option>
                    <option value="emprunt" <?php if($doc['disponible_pour'] == 'emprunt') echo 'selected'; ?>>Emprunt uniquement</option>
                </select>
            </div>

            <div class="form-group">
                <label>Prix (DA)</label>
                <input type="number" step="0.01" name="prix_vente" value="<?php echo $doc['prix']; ?>">
            </div>

            <div class="form-group full-width">
                <label>Nombre d'exemplaires</label>
                <input type="number" name="exemplaires" value="<?php echo $doc['exemplaires']; ?>" min="1">
            </div>

            <div class="form-group full-width">
                <label>Description</label>
                <textarea name="description" rows="4"><?php echo htmlspecialchars($doc['description_longue']); ?></textarea>
            </div>
        </div>

        <button type="submit" name="modifier_livre" class="btn-update">✅ Sauvegarder les modifications</button>
    </form>
</div>

<?php echo $success_script; ?>

</body>
</html>