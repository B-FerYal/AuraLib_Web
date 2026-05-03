<?php
include_once __DIR__ . "/../includes/header.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_emprunt'])) {
    $id_emp = (int)$_POST['id_emprunt'];

    // تحديث تاريخ النهاية بإضافة 7 أيام
    $updateQuery = "UPDATE emprunt 
                    SET date_fin = DATE_ADD(date_fin, INTERVAL 7 DAY) 
                    WHERE id_emprunt = $id_emp 
                    AND id_user = $id_user 
                    AND statut = 'en_cours'";

    if ($conn->query($updateQuery)) {
        header("Location: mes_emprunts.php?success=1");
    } else {
        header("Location: mes_emprunts.php?error=1");
    }
    exit;
}