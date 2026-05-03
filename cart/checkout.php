<?php
// منع ظهور أي مخرجات قبل التوجيه
ob_start();
session_start();

require_once __DIR__ . "/../includes/db.php";

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = (int)$_SESSION['id_user'];
$total = isset($_POST['total_global']) ? (float)$_POST['total_global'] : 0;

if ($total <= 0) {
    header("Location: panier.php");
    exit;
}

$conn->begin_transaction();

try {
    // 1. جلب عناصر السلة أولاً والتحقق من المخزون المتاح لكل كتاب
    $check_stock_query = "SELECT i.id_doc, i.quantite, d.titre, d.exemplaires_disponibles 
                          FROM panier_item i 
                          JOIN documents d ON i.id_doc = d.id_doc 
                          JOIN panier p ON i.id_panier = p.id_panier 
                          WHERE p.id_user = $id_user";
    
    $cart_items = $conn->query($check_stock_query);

    if ($cart_items->num_rows === 0) {
        throw new Exception("سلة التسوق الخاصة بك فارغة.");
    }

    // مراجعة كل عنصر في السلة ومقارنته بالمخزون المتاح
    while ($item = $cart_items->fetch_assoc()) {
        $id_doc = (int)$item['id_doc'];
        $quantite_demandee = (int)$item['quantite'];
        $stock_disponible = (int)$item['exemplaires_disponibles'];
        $titre = $item['titre'];

        if ($quantite_demandee > $stock_disponible) {
            // إذا كانت الكمية المطلوبة أكبر من المتوفر، نوقف العملية فوراً
            throw new Exception("الكمية المطلوبة من الكتاب ('$titre') غير متوفرة بالكامل. المتاح حالياً في الرفوف: $stock_disponible نسخة فقط.");
        }

        // 2. تحديث المخزون الفعلي (خصم الكمية المباعة)
        $update_stock = "UPDATE documents SET exemplaires_disponibles = exemplaires_disponibles - $quantite_demandee WHERE id_doc = $id_doc";
        $conn->query($update_stock);
    }

    // 3. إنشاء الطلب
    $stmt = $conn->prepare("INSERT INTO commande (id_user, total, statut, date_commande) VALUES (?, ?, 'en attente', NOW())");
    $stmt->bind_param("id", $id_user, $total);
    $stmt->execute();
    $id_commande = $conn->insert_id;

    // 4. نقل العناصر إلى commande_item
    $insert_items = "INSERT INTO commande_item (id_commande, id_doc, quantite, prix) 
                     SELECT $id_commande, i.id_doc, i.quantite, d.prix 
                     FROM panier_item i 
                     JOIN documents d ON i.id_doc = d.id_doc 
                     JOIN panier p ON i.id_panier = p.id_panier 
                     WHERE p.id_user = $id_user";
    $conn->query($insert_items);

    // 5. تفريغ السلة
    $conn->query("DELETE i FROM panier_item i JOIN panier p ON i.id_panier = p.id_panier WHERE p.id_user = $id_user");

    $conn->commit();
    
    // 6. التوجيه الفوري لصفحة الدفع
    header("Location: ../commandes/paiement.php?id=" . $id_commande . "&total=" . $total);
    ob_end_flush();
    exit;

} catch (Exception $e) {
    $conn->rollback();
    ob_end_clean();
    
    // إرجاع رسالة خطأ واضحة للمستخدم في حال فشل العملية
    echo "<!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <title>Erreur de commande</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body style='background-color:#f4f1ea;'>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Désolé!',
                text: '" . addslashes($e->getMessage()) . "',
                confirmButtonColor: '#3d2b1f'
            }).then(() => {
                window.location.href = 'panier.php';
            });
        </script>
    </body>
    </html>";
    exit;
}