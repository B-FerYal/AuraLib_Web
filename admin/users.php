<?php
session_start();
require_once __DIR__ . "/../includes/db.php";
require_once '../includes/head.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$current_admin_id = intval($_SESSION['id_user']); // ✅ مصحح

// تبديل حالة المستخدم (Suspendre / Activer)
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $user_id    = intval($_GET['id']);
    $new_status = ($_GET['toggle_status'] == 'active') ? 'suspended' : 'active';
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'client'");
    $stmt->bind_param("si", $new_status, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: users.php");
    exit;
}

// جلب المستخدمين (clients فقط)
$query = "SELECT id, firstname, lastname, email, status, role FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Lecteurs - AuraLib</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-color: #C4A46B;
            --dark-color:    #3D2E25;
            --bg-color:      #f4f1ea;
            --white:         #FFFDF9;
        }

        body { font-family: 'Lato', sans-serif; background: var(--bg-color); padding: 40px; }

        .container {
            max-width: 1150px; margin: auto;
            background: var(--white); padding: 40px;
            border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }

        .btn-retour {
            text-decoration: none; color: #7a6e64;
            padding: 8px 16px; border: 1.5px solid #eeebe6;
            border-radius: 12px; font-size: 13px; font-weight: 700;
        }

        table { width: 100%; border-collapse: collapse; }

        th {
            text-align: left; padding: 15px; color: #7a6e64;
            font-size: 12px; text-transform: uppercase;
            border-bottom: 2px solid var(--bg-color);
        }

        td { padding: 15px; border-bottom: 1px solid var(--bg-color); font-size: 14px; vertical-align: middle; }

        /* ===== Badges Statut ===== */
        .status-badge {
            padding: 5px 12px; border-radius: 20px;
            font-size: 11px; font-weight: 700; text-transform: uppercase;
        }
        .active    { background: #f6ffed; color: #52c41a; border: 1px solid #b7eb8f; }
        .suspended { background: #fff1f0; color: #d85c5c; border: 1px solid #ffccc7; }

        /* ===== Badges Rôle ===== */
        .role-badge {
            padding: 5px 12px; border-radius: 20px;
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            display: inline-block;
        }
        .role-admin  { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .role-client { background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }

        /* ===== Boutons ===== */
        .btn-action {
            padding: 7px 14px; border-radius: 8px;
            text-decoration: none; font-size: 12px; font-weight: 700;
            transition: 0.2s; cursor: pointer; border: none;
        }
        .btn-toggle { background: var(--dark-color); color: white; }
        .btn-toggle:hover { opacity: .85; }

        .btn-role { background: #1d4ed8; color: white; }
        .btn-role:hover { background: #1e40af; }

        .btn-disabled {
            background: #e5e7eb; color: #9ca3af;
            cursor: not-allowed; padding: 7px 14px;
            border-radius: 8px; font-size: 12px; font-weight: 700; border: none;
        }

        .actions-cell { display: flex; gap: 8px; flex-wrap: wrap; }

        /* ===== SweetAlert thème AuraLib ===== */
        .swal2-styled.swal2-confirm { background-color: var(--primary-color) !important; }
        .swal2-styled.swal2-cancel  { background-color: var(--dark-color)    !important; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1 style="color: var(--dark-color); font-size: 24px;">Gestion des Lecteurs</h1>
        <a href="admin_dashboard.php" class="btn-retour">← Dashboard</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Lecteur</th>
                <th>Email</th>
                <th>Statut</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <?php
                $uid         = intval($row['id']);
                $fullname    = htmlspecialchars($row['firstname'] . ' ' . $row['lastname']);
                $firstname   = htmlspecialchars($row['firstname']);
                $email       = htmlspecialchars($row['email']);
                $status      = $row['status'];
                $role        = $row['role'] ?? 'client';
                $newRole     = ($role === 'admin') ? 'client' : 'admin';
                $newRoleLabel = ($newRole === 'admin') ? 'Admin' : 'Client';
                $isMe        = ($uid === $current_admin_id);
            ?>
            <tr id="row-<?= $uid ?>">
                <td>#<?= $uid ?></td>
                <td><strong><?= $fullname ?></strong></td>
                <td><?= $email ?></td>

                <!-- Statut -->
                <td>
                    <span class="status-badge <?= $status ?>">
                        <?= ($status === 'active') ? 'Actif' : 'Suspendu' ?>
                    </span>
                </td>

                <!-- Rôle -->
                <td>
                    <span class="role-badge role-<?= $role ?>" id="role-badge-<?= $uid ?>">
                        <?= ucfirst($role) ?>
                    </span>
                </td>

                <!-- Actions -->
                <td>
                    <div class="actions-cell">

                        <!-- Bouton Suspendre / Activer -->
                        <?php if (!$isMe): ?>
                            <button class="btn-action btn-toggle"
                                onclick="confirmStatusUpdate('<?= $uid ?>', '<?= $status ?>', '<?= $firstname ?>')">
                                <?= ($status === 'active') ? 'Suspendre' : 'Activer' ?>
                            </button>
                        <?php else: ?>
                            <button class="btn-disabled" disabled>Mon compte</button>
                        <?php endif; ?>

                        <!-- Bouton Changer le Rôle -->
                        <?php if (!$isMe): ?>
                            <button class="btn-action btn-role"
                                id="role-btn-<?= $uid ?>"
                                onclick="confirmRoleChange(<?= $uid ?>, '<?= $firstname ?>', '<?= $role ?>', '<?= $newRole ?>', '<?= $newRoleLabel ?>')">
                                → <?= $newRoleLabel ?>
                            </button>
                        <?php else: ?>
                            <button class="btn-disabled" disabled>Mon rôle</button>
                        <?php endif; ?>

                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
// ===== Changer le Rôle =====
function confirmRoleChange(userId, userName, currentRole, newRole, newRoleLabel) {
    Swal.fire({
        title: 'Changer le rôle ?',
        html: `<p>Voulez-vous changer le rôle de <strong>${userName}</strong> en <strong>${newRoleLabel}</strong> ?</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, confirmer',
        cancelButtonText: 'Non, annuler',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('new_role', newRole);

            fetch('update_role.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // تحديث الـ badge
                    const badge = document.getElementById('role-badge-' + userId);
                    badge.textContent = newRole.charAt(0).toUpperCase() + newRole.slice(1);
                    badge.className = 'role-badge role-' + newRole;

                    // تحديث الزر
                    const btn = document.getElementById('role-btn-' + userId);
                    const next = newRole === 'admin' ? 'client' : 'admin';
                    const nextLabel = next === 'admin' ? 'Admin' : 'Client';
                    btn.textContent = '→ ' + nextLabel;
                    btn.setAttribute('onclick',
                        `confirmRoleChange(${userId}, '${userName}', '${newRole}', '${next}', '${nextLabel}')`
                    );

                    Swal.fire({ title: 'Succès !', text: 'Rôle mis à jour.', icon: 'success', timer: 1800, showConfirmButton: false });
                } else {
                    Swal.fire('Erreur', data.message, 'error');
                }
            });
        }
    });
}

// ===== Suspendre / Activer =====
function confirmStatusUpdate(userId, currentStatus, userName) {
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
            window.location.href = `users.php?toggle_status=${currentStatus}&id=${userId}`;
        }
    });
}
</script>
</body>
</html>
