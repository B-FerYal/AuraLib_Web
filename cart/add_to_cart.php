<?php
session_start();
require_once __DIR__ . "/../includes/db.php";

// التأكد من تسجيل الدخول
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_doc'])) {
    $id_doc = (int)$_POST['id_doc'];
    $id_user = (int)$_SESSION['id_user'];

    // 1. الحصول على id_panier أو إنشاؤه إذا لم يوجد
    $res = $conn->query("SELECT id_panier FROM panier WHERE id_user = $id_user");
    
    if ($res->num_rows > 0) {
        $panier = $res->fetch_assoc();
        $id_panier = $panier['id_panier'];
    } else {
        // إنشاء سلة جديدة للمستخدم إذا لم تكن لديه واحدة
        $conn->query("INSERT INTO panier (id_user, date_creation) VALUES ($id_user, NOW())");
        $id_panier = $conn->insert_id;
    }

    // 2. جلب سعر الوثيقة (تأكدي من إضافة عمود prix في جدول documents)
    // ملاحظة: إذا لم يكن هناك سعر في الجدول حالياً، سنضع سعراً افتراضياً لتجنب الخطأ
    $doc_res = $conn->query("SELECT prix FROM documents WHERE id_doc = $id_doc");
    $prix_actuel = 0;
    if ($doc_res && $doc_data = $doc_res->fetch_assoc()) {
        $prix_actuel = $doc_data['prix'];
    }

    // 3. التحقق إذا كانت الوثيقة موجودة في السلة لزيادة الكمية أو إضافتها
    $check = $conn->query("SELECT * FROM panier_item WHERE id_panier = $id_panier AND id_doc = $id_doc");
    
    if ($check->num_rows > 0) {
        // تحديث الكمية
        $conn->query("UPDATE panier_item SET quantite = quantite + 1 WHERE id_panier = $id_panier AND id_doc = $id_doc");
    } else {
        // إضافة عنصر جديد
        $stmt = $conn->prepare("INSERT INTO panier_item (id_panier, id_doc, quantite, prix_unitaire, type_transaction) VALUES (?, ?, 1, ?, 'achat')");
        $stmt->bind_param("iid", $id_panier, $id_doc, $prix_actuel);
        $stmt->execute();
    }

    header("Location: panier.php");
    exit;
}