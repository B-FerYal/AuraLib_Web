<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "../includes/db.php";

// التأكد من تسجيل الدخول
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

include "../includes/header.php"; // الهيدر الذي يحتوي على مصفوفة اللغات $text

$id_user = (int)$_SESSION['id_user'];

// 1. معالجة العمليات (حذف أو تحديث الحالة)
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'mark_read' && isset($_GET['id'])) {
        $id_notif = (int)$_GET['id'];
        $conn->query("UPDATE notifications SET lu = 1 WHERE id = $id_notif AND id_user = $id_user");
    } 
    elseif ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id_notif = (int)$_GET['id'];
        $conn->query("DELETE FROM notifications WHERE id = $id_notif AND id_user = $id_user");
    }
    elseif ($_GET['action'] === 'mark_all_read') {
        $conn->query("UPDATE notifications SET lu = 1 WHERE id_user = $id_user");
    }
    header("Location: notifications.php"); // إعادة التوجيه لتجنب تكرار العملية عند التحديث
    exit;
}

// 2. جلب جميع الإشعارات
$query = "SELECT * FROM notifications WHERE id_user = $id_user ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= ($lang == 'ar') ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <style>
        :root {
            --taupe: #2C1F0E; --gold: #C4A46B; --cream: #F5F0E8; --white: #FFFDF9;
        }
        .notif-page { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .page-header h2 { font-family: 'Playfair Display', serif; color: var(--taupe); }
        
        .notif-list { display: flex; flex-direction: column; gap: 15px; }
        .notif-card { 
            background: var(--white); border: 1px solid #EDE5D4; padding: 20px; border-radius: 12px;
            display: flex; gap: 15px; position: relative; transition: 0.3s;
        }
        .notif-card.unread { border-left: 4px solid var(--gold); background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        html[dir="rtl"] .notif-card.unread { border-left: 1px solid #EDE5D4; border-right: 4px solid var(--gold); }

        .notif-icon { 
            width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; 
            justify-content: center; flex-shrink: 0; font-size: 18px;
        }
        .type-info { background: #e0f2fe; color: #0369a1; }
        .type-success { background: #dcfce7; color: #15803d; }
        .type-warning { background: #fef9c3; color: #854d0e; }
        .type-danger { background: #fee2e2; color: #dc2626; }

        .notif-content { flex: 1; }
        .notif-title { font-weight: 700; color: var(--taupe); margin-bottom: 5px; font-size: 15px; }
        .notif-msg { color: #6b7280; font-size: 14px; line-height: 1.5; }
        .notif-time { font-size: 11px; color: #9ca3af; margin-top: 10px; display: block; }

        .notif-actions { display: flex; gap: 10px; align-items: center; }
        .btn-action { color: #9ca3af; text-decoration: none; font-size: 14px; transition: 0.2s; }
        .btn-action:hover { color: var(--gold); }
        .btn-delete:hover { color: #ef4444; }

        .empty-state { text-align: center; padding: 60px; color: #9ca3af; }
        .mark-all { color: var(--gold); text-decoration: none; font-size: 13px; font-weight: 600; }
    </style>
</head>
<body>

<div class="notif-page">
    <div class="page-header">
        <h2><i class="fa-solid fa-bell"></i> <?= $text['notifications'] ?? 'Notifications' ?></h2>
        <?php if($result->num_rows > 0): ?>
            <a href="?action=mark_all_read" class="mark-all"><?= ($lang=='ar'?'تحديد الكل كمقروء':'Tout marquer كـ lu') ?></a>
        <?php endif; ?>
    </div>
<div class="notif-list">
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): 
                $icon = 'fa-info-circle';
                if($row['type'] == 'success') $icon = 'fa-check-circle';
                if($row['type'] == 'warning') $icon = 'fa-exclamation-triangle';
                if($row['type'] == 'danger')  $icon = 'fa-times-circle';
            ?>
                <div class="notif-card <?= $row['lu'] == 0 ? 'unread' : '' ?>">
                    <div class="notif-icon type-<?= $row['type'] ?>">
                        <i class="fa-solid <?= $icon ?>"></i>
                    </div>
                    
                    <div class="notif-content">
                        <div class="notif-title"><?= htmlspecialchars($row['titre']) ?></div>
                        <div class="notif-msg">
                            <?= nl2br(htmlspecialchars($row['message'])) ?>
                            <?php if($row['lien']): ?>
                                <br><a href="<?= $row['lien'] ?>" style="color:var(--gold); font-weight:600; font-size:12px;"><?= ($lang=='ar'?'عرض التفاصيل':'Voir les détails') ?> →</a>
                            <?php endif; ?>
                        </div>
                        <span class="notif-time"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></span>
                    </div>

                    <div class="notif-actions">
                        <?php if($row['lu'] == 0): ?>
                            <a href="?action=mark_read&id=<?= $row['id'] ?>" class="btn-action" title="Marquer comme lu"><i class="fa-solid fa-check"></i></a>
                        <?php endif; ?>
                        <a href="?action=delete&id=<?= $row['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Supprimer?')" title="Supprimer"><i class="fa-solid fa-trash-can"></i></a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-bell-slash fa-3x" style="margin-bottom:15px; opacity:0.3;"></i>
                <p><?= ($lang=='ar'?'لا توجد إشعارات حالياً':'Aucune notification pour le moment') ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>