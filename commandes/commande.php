<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . "/../includes/db.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['id_user'])) {
    header("Location:../auth/login.php");
    exit;
}

$id_user = (int)$_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['total'])) {

    $total = (float)$_POST['total'];

    try {
        // بدء العملية (Transaction) لضمان سلامة البيانات
        $conn->begin_transaction();

        // 1. إنشاء سجل في جدول الطلبات (commande)
        $conn->query("
            INSERT INTO commande(id_user, statut, date_commande)

            VALUES($id_user, 'en_attente', NOW())
        ");

        $id_commande = $conn->insert_id;

        // 2. جلب محتوى السلة مع ربطها بجدول الوثائق الجديد (documents)
        $items = $conn->query("
            SELECT i.id_doc, i.quantite, i.prix_unitaire, d.nb_pages 
            FROM panier_item i
            JOIN panier p ON p.id_panier = i.id_panier
            JOIN documents d ON d.id_doc = i.id_doc
            WHERE p.id_user = $id_user
        ");

        if ($items->num_rows == 0) {
            throw new Exception("السلة فارغة، لا يمكن إتمام الطلب.");
        }

        while ($it = $items->fetch_assoc()) {
            $id_doc = $it['id_doc'];
            $quantite = $it['quantite'];
            $prix = $it['prix_unitaire'];

            // 3. إضافة الوثائق المشتراة إلى تفاصيل الطلب (commande_item)
            // ملاحظة: تأكد أن جدول commande_item يحتوي الآن على عمود id_doc بدلاً من id_livre
            $stmt_item = $conn->prepare("
                INSERT INTO commande_item(id_commande, id_doc, quantite, prix)


                VALUES(?, ?, ?, ?)
                
            ");
            
            $stmt_item->bind_param("iiid", $id_commande, $id_doc, $quantite, $prix);
            
            if (!$stmt_item->execute()) {
                throw new Exception("خطأ أثناء إضافة العناصر للطلب: " . $conn->error);
            }

            // ملاحظة: في نظام الوثائق/المخطوطات، غالباً لا ينقص المخزون إلا إذا كانت نسخاً ورقية محدودة.
            // إذا كنت تريد إنقاص عمود معين، يمكنك إضافته هنا.
        }

        // 4. تفريغ السلة بعد نجاح إنشاء الطلب
        $conn->query("
            DELETE i FROM panier_item i
            JOIN panier p ON p.id_panier = i.id_panier
            WHERE p.id_user = $id_user
        ");

        // 5. إرسال إشعار للمستخدم
        $message = "تم إنشاء طلبك رقم $id_commande بنجاح. نحن في انتظار إتمام عملية الدفع.";
        $stmt_notif = $conn->prepare("
            INSERT INTO notifications(id_user, message, statut, date_envoi)
            VALUES(?, ?, 'غير مقروء', NOW())
        ");
        $stmt_notif->bind_param("is", $id_user, $message);
        $stmt_notif->execute();

        // تأكيد العملية بالكامل
        $conn->commit();

        // التحويل لصفحة الدفع
        header("Location:paiement.php?id=$id_commande&total=$total");
        exit;

    } catch (Exception $e) {
        // في حال حدوث أي خطأ، يتم التراجع عن كل ما تم تنفيذه في هذا الطلب
        $conn->rollback();
        echo "❌ حدث خطأ أثناء معالجة الطلب: " . $e->getMessage();
    }
}
?>