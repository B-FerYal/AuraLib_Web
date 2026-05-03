<?php
session_start();
include "../includes/db.php";
require_once '../includes/head.php'; 

if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($_GET['id'])){
    $id = (int)$_GET['id'];
    
    // استخدام "payée" تماماً كما تظهر في قاعدة بياناتك
    $stmt = $conn->prepare("UPDATE commande SET statut = 'payée' WHERE id_commande = ?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()){
        header("Location: all_orders.php?msg=updated");
    } else {
        echo "خطأ في التحديث: " . $conn->error;
    }
}
?>