<?php
session_start();
require_once __DIR__ . "/../includes/db.php"; 
require_once '../includes/head.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// تبديل حالة المستخدم
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $new_status = ($_GET['toggle_status'] == 'active') ? 'suspended' : 'active';
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'client'");
    $stmt->bind_param("si", $new_status, $user_id);
    $stmt->execute();
    header("Location: users.php");
    exit;
}

$query = "SELECT id, firstname, lastname, email, status FROM users WHERE role = 'client' ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Lecteur - AuraLib</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root { --primary-color: #C4A46B; --dark-color: #3D2E25; --bg-color: #f4f1ea; --white: #FFFDF9; }
        body { font-family: 'Lato', sans-serif; background: var(--bg-color); padding: 40px; }
        .container { max-width: 1100px; margin: auto; background: var(--white); padding: 40px; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-retour { text-decoration: none; color: #7a6e64; padding: 8px 16px; border: 1.5px solid #eeebe6; border-radius: 12px; font-size: 13px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #7a6e64; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid var(--bg-color); }
        td { padding: 15px; border-bottom: 1px solid var(--bg-color); font-size: 14px; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .active { background: #f6ffed; color: #52c41a; border: 1px solid #b7eb8f; }
        .suspended { background: #fff1f0; color: #d85c5c; border: 1px solid #ffccc7; }
        .btn-action { padding: 7px 14px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 700; transition: 0.3s; cursor: pointer; border: none; }
        .btn-toggle { background: var(--dark-color); color: white; }
        
        /* تخصيص ألوان SweetAlert لتناسب ثيم المكتبة */
        .swal2-styled.swal2-confirm { background-color: var(--primary-color) !important; }
        .swal2-styled.swal2-cancel { background-color: var(--dark-color) !important; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1 style="color: var(--dark-color); font-size: 24px;">Gestion des Lecteur</h1>
        <a href="admin_dashboard.php" class="btn-retour">← Dashboard</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Lecteur</th>
                <th>Email</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td>
                    <span class="status-badge <?php echo $row['status']; ?>">
                        <?php echo ($row['status'] == 'active') ? 'Actif' : 'Suspendu'; ?>
                    </span>
                </td>
                <td>
                    <button class="btn-action btn-toggle" 
                            onclick="confirmStatusUpdate('<?php echo $row['id']; ?>', '<?php echo $row['status']; ?>', '<?php echo $row['firstname']; ?>')">
                        <?php echo ($row['status'] == 'active') ? 'Suspendre' : 'Activer'; ?>
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function confirmStatusUpdate(userId, currentStatus, userName) {
    const action = (currentStatus === 'active') ? 'suspendre' : 'activer';
    const message = (currentStatus === 'active') 
        ? `Voulez-vous vraiment suspendre le compte de ${userName} ?` 
        : `Voulez-vous vraiment réactiver le compte de ${userName} ?`;

    Swal.fire({
        title: 'Confirmation',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, confirmer',
        cancelButtonText: 'Non, annuler',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // التوجيه إلى رابط التحديث في حال ضغط المستخدم على Oui
            window.location.href = `users.php?toggle_status=${currentStatus}&id=${userId}`;
        }
    });
}
</script>

</body>
</html>