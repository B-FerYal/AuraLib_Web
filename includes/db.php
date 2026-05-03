<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "memoir_db";

// تفعيل تقارير الأخطاء بدلاً من إيقافها لكي تظهر الأخطاء المختفية
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($host, $user, $pass, $db);
    mysqli_set_charset($conn, "utf8mb4"); // لضمان ظهور اللغة العربية بشكل صحيح
} catch (mysqli_sql_exception $e) {
    // إذا كان هناك خطأ، ستتوقف الصفحة وتخبرك بالسبب بدلاً من أن تبقى بيضاء
    die("❌ فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>