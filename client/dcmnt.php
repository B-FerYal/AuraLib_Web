<?php
include '../includes/db.php';

/* جلب كل الوثائق مع جلب اسم النوع من جدول types_documents */
$query = "
    SELECT d.*, t.libelle_type 
    FROM documents d
    LEFT JOIN types_documents t ON d.id_type = t.id_type
";

$result = mysqli_query($conn, $query);
$documents = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<?php include '../includes/dark_init.php'; ?>
<title>تسيير الوثائق</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<script>
(function(){
    if(localStorage.getItem('auralib_theme')==='dark')
        document.documentElement.classList.add('dark');
})();
</script>

<style>
    body { font-family: Arial, sans-serif; background-color: #f4f7f6; }
    #search { padding: 10px; width: 350px; border: 1px solid #ddd; border-radius: 25px; outline: none; }
    .search-container { text-align: center; margin: 30px 0; }
    
    /* تصميم بطاقة الوثيقة */
    .doc-table { width: 95%; margin: auto; border-collapse: collapse; background: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .doc-table th { background: #2c3e50; color: #fff; padding: 12px; }
    .doc-table td { padding: 10px; border-bottom: 1px solid #eee; }
    
    .img-preview { width: 60px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
    .type-badge { background: #3498db; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px; }
    
    .desc-text { color: #666; font-size: 13px; max-width: 200px; display: block; 
                 overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    
    .button { padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 13px; cursor: pointer; border:none; }
    .edit { background: #27ae60; color: white; }
    .delete { background: #e74c3c; color: white; }
    .emprunt { background: #f39c12; color: white; }
</style>

</head>
<body>

<h2 style="text-align:center; color: #2c3e50; margin-top: 20px;">📚 إدارة مستودع الوثائق</h2>

<div class="search-container">
    <input type="text" id="search" placeholder="🔍 ابحث عن (عنوان، مؤلف، أو محتوى الوصف)...">
</div>

<p style="text-align:center;">
    <a class="button edit" href="../admin/ajouter_dcmnt.php">+ إضافة وثيقة جديدة</a>
</p>

<div id="resultat">

<table class="doc-table">
<tr>
    <th>الغلاف</th>
    <th>العنوان المعطيات</th>
    <th>النوع</th>
    <th>المؤلف/المشرف</th>
    <th>الوصف المختصر</th>
    <th>الكمية</th>
    <th>العمليات</th>
</tr>

<?php foreach ($documents as $d): ?>
<tr>
    <td>
        <?php $img = !empty($d['image_doc']) ? $d['image_doc'] : 'default.png'; ?>
        <img src="../uploads/<?= $img ?>" class="img-preview" alt="Cover">
    </td>

    <td style="text-align: right;">
        <strong><?= htmlspecialchars($d['titre']) ?></strong><br>
        <small style="color: #7f8c8d;">
            <?php 
                if($d['libelle_type'] == 'Thèse') echo "جامعة: " . $d['universite'];
                elseif($d['libelle_type'] == 'Article') echo "مجلة: " . $d['nom_revue'];
                else echo "الناشر: " . $d['editeur'];
            ?>
        </small>
    </td>

    <td><span class="type-badge"><?= $d['libelle_type'] ?></span></td>
    
    <td>
        <?= htmlspecialchars($d['auteur']) ?>
        <?= !empty($d['encadrant']) ? "<br><small>(إشراف: ".$d['encadrant'].")</small>" : "" ?>
    </td>

    <td><span class="desc-text" title="<?= htmlspecialchars($d['description_longue']) ?>">
        <?= htmlspecialchars($d['description_longue']) ?>
    </span></td>

    <td><?= $d['nb_pages'] ?> ص</td>

    <td>
        <a class="button edit" href="../admin/modifier_dcmnt.php?id=<?= $d['id_doc'] ?>">تعديل</a>
        
        <a class="button delete" 
           href="../admin/supprimer_dcmnt.php?id=<?= $d['id_doc'] ?>" 
           onclick="return confirm('هل أنت متأكد من حذف هذه الوثيقة؟');">حذف</a>

        <form action="../emprunts/emprunt.php" method="POST" style="display:inline;">
            <input type="hidden" name="id_doc" value="<?= $d['id_doc'] ?>">
            <button class="button emprunt" type="submit" name="emprunter">📖 إعارة</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>

</table>

</div>

<script>
document.getElementById("search").addEventListener("keyup", function() {
    let valeur = this.value;
    let xhr = new XMLHttpRequest();
    // تأكد من إنشاء ملف recherche_dcmnt.php ليقوم بالبحث في الجدول الجديد
    xhr.open("GET", "recherche_dcmnt.php?search=" + valeur, true);
    xhr.onload = function() {
        if (this.status === 200) {
            document.getElementById("resultat").innerHTML = this.responseText;
        }
    };
    xhr.send();
});
</script>

</body>
</html>