<?php
include 'includes/db.php';
include 'includes/header.php';

$id = $_GET['id'] ?? 0;
$livre = $pdo->query("SELECT * FROM livres WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
$auteurs = $pdo->query("SELECT * FROM auteurs")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $auteur_id = $_POST['auteur_id'];
    $categorie_id = $_POST['categorie_id'];
    $quantite = $_POST['quantite'];

    $stmt = $pdo->prepare("UPDATE livres SET titre=?, auteur_id=?, categorie_id=?, quantite=? WHERE id=?");
    $stmt->execute([$titre, $auteur_id, $categorie_id, $quantite, $id]);

    header("Location: livres.php");
    exit;
}
?>

<h2>تعديل الكتاب</h2>
<form method="post">
    <label>العنوان:</label><br>
    <input type="text" name="titre" value="<?= htmlspecialchars($livre['titre']) ?>" required><br><br>

    <label>المؤلف:</label><br>
    <select name="auteur_id" required>
        <?php foreach($auteurs as $a): ?>
            <option value="<?= $a['id'] ?>" <?= $a['id']==$livre['auteur_id']?'selected':'' ?>>
                <?= htmlspecialchars($a['nom']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>الفئة:</label><br>
    <select name="categorie_id" required>
        <?php foreach($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $c['id']==$livre['categorie_id']?'selected':'' ?>>
                <?= htmlspecialchars($c['nom']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>الكمية:</label><br>
    <input type="number" name="quantite" value="<?= $livre['quantite'] ?>" min="1" required><br><br>

    <button type="submit">تحديث</button>
</form>

<?php include 'includes/footer.php'; ?>