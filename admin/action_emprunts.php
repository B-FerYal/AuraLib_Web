<?php
session_start();
include_once "../includes/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: gerer_emprunts.php");
    exit;
}

$id     = intval($_GET['id']);
$action = $_GET['action'];
$today  = date('Y-m-d');

// نجيبو معلومات الإعارة
$stmt = $conn->prepare("
    SELECT e.*, d.exemplaires_disponibles, d.titre 
    FROM emprunt e 
    JOIN documents d ON e.id_doc = d.id_doc 
    WHERE e.id_emprunt = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$emprunt = $stmt->get_result()->fetch_assoc();

if (!$emprunt) {
    header("Location: gerer_emprunts.php?msg=not_found");
    exit;
}

// ════════════════════════════════
// ACCEPTER
// ════════════════════════════════
if ($action === 'accepter') {

    if ($emprunt['statut'] !== 'en attente') {
        header("Location:gerer_emprunts.php?msg=invalid_status");
        exit;
    }

    // المخزون محجوز من قبل — غير نبدلو الحالة فقط
    $upd = $conn->prepare("
        UPDATE emprunt SET 
            statut = 'acceptée',
            date_debut = ?,
            date_retour_prevue = DATE_ADD(?, INTERVAL 15 DAY),
            date_fin = NULL
        WHERE id_emprunt = ?
    ");
    $upd->bind_param("ssi", $today, $today, $id);
    $upd->execute();

    header("Location: gerer_emprunts.php?msg=accepted");
    exit;

// ════════════════════════════════
// REFUSER
// ════════════════════════════════
} elseif ($action === 'refuser') {

    if ($emprunt['statut'] !== 'en attente') {
        header("Location: gerer_emprunts.php?msg=invalid_status");
        exit;
    }

    $conn->begin_transaction();
    try {
        // نبدلو الحالة لـ refusée
        $upd = $conn->prepare("UPDATE emprunt SET statut = 'refusée' WHERE id_emprunt = ?");
        $upd->bind_param("i", $id);
        $upd->execute();

        // نرجعو الكتاب للمخزون
        $conn->query("
            UPDATE documents 
            SET exemplaires_disponibles = exemplaires_disponibles + 1 
            WHERE id_doc = {$emprunt['id_doc']}
        ");

        $conn->commit();
        header("Location: gerer_emprunts.php?msg=refused");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: gerer_emprunts.php?msg=error");
    }
    exit;

// ════════════════════════════════
// RENDRE (إرجاع الكتاب)
// ════════════════════════════════
} elseif ($action === 'rendre') {

    if (!in_array($emprunt['statut'], ['acceptée', 'retard'])) {
        header("Location: gerer_emprunts.php?msg=invalid_status");
        exit;
    }

    // نحسبو الغرامة إذا كان متأخر (10 DA في اليوم)
    $amende = 0;
    if ($emprunt['statut'] === 'retard' && !empty($emprunt['date_retour_prevue'])) {
        $date_prevue = new DateTime($emprunt['date_retour_prevue']);
        $date_retour = new DateTime($today);
        $jours_retard = (int)$date_prevue->diff($date_retour)->days;
        $amende = $jours_retard * 10;
    }

    $conn->begin_transaction();
    try {
        // نبدلو الحالة لـ rendu + نسجلو الغرامة
        $upd = $conn->prepare("
            UPDATE emprunt SET 
                statut = 'rendu',
                date_fin = ?,
                amende = ?
            WHERE id_emprunt = ?
        ");
        $upd->bind_param("sdi", $today, $amende, $id);
        $upd->execute();

        // نرجعو الكتاب للمخزون
        $conn->query("
            UPDATE documents 
            SET exemplaires_disponibles = exemplaires_disponibles + 1 
            WHERE id_doc = {$emprunt['id_doc']}
        ");

        $conn->commit();
        $redirect = "gerer_emprunts.php?msg=returned";
        if ($amende > 0) $redirect .= "&amende=$amende";
        header("Location: $redirect");

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: gerer_emprunts.php?msg=error");
    }
    exit;

} else {
    header("Location: gerer_emprunts.php");
    exit;
}
?>