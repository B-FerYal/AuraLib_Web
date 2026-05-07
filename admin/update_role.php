<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// ١. التحقق من صلاحية المشرف
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'غير مصرح لك.']);
    exit;
}

// ٢. التحقق من البيانات المرسلة
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id'], $_POST['new_role'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات ناقصة.']);
    exit;
}

$target_id = intval($_POST['user_id']);
$new_role  = $_POST['new_role'];
$admin_id  = intval($_SESSION['id_user']); // ✅ تم التصحيح من user_id إلى id_user

// ٣. منع المشرف من تغيير دوره الخاص
if ($target_id === $admin_id) {
    echo json_encode(['success' => false, 'message' => 'لا يمكنك تغيير دورك الخاص!']);
    exit;
}

// ٤. التحقق من صحة قيمة الدور
$allowed_roles = ['admin', 'client'];
if (!in_array($new_role, $allowed_roles)) {
    echo json_encode(['success' => false, 'message' => 'قيمة الدور غير صالحة.']);
    exit;
}

// ٥. تنفيذ التحديث
$stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->bind_param("si", $new_role, $target_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'تم تغيير الدور بنجاح.', 'new_role' => $new_role]);
} else {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات.']);
}
$stmt->close();
?>
