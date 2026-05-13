<?php
require_once __DIR__ . "/../includes/db.php";
require_once '../includes/head.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$current_admin_id = intval($_SESSION['id_user']);

if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $user_id    = intval($_GET['id']);
    $new_status = ($_GET['toggle_status'] == 'active') ? 'suspended' : 'active';
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'utilisateur'");
    $stmt->bind_param("si", $new_status, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: users.php");
    exit;
}

$query  = "SELECT id, firstname, lastname, email, status, role FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <?php include '../includes/dark_init.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Lecteurs - AuraLib</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary:   #C4A46B;
            --dark:      #3D2E25;
            --bg:        #f4f1ea;
            --surface:   #FFFDF9;
            --text:      #2A1F14;
            --muted:     #7a6e64;
            --border:    #eeebe6;
        }
        html.dark {
            --bg:      #1C1610;
            --surface: #2C2418;
            --text:    #F0E8D8;
            --muted:   #A89880;
            --border:  #3E3228;
            --dark:    #C4A46B;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Lato', sans-serif;
            background: var(--bg);
            color: var(--text);
            padding: 40px;
            transition: background .3s, color .3s;
        }

        .container {
            max-width: 1150px;
            margin: auto;
            background: var(--surface);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,.08);
            border: 1px solid var(--border);
            transition: background .3s, border-color .3s;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header h1 { color: var(--dark); font-size: 24px; transition: color .3s; }

        .btn-retour {
            text-decoration: none;
            color: var(--muted);
            padding: 8px 16px;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            transition: all .2s;
        }
        .btn-retour:hover { background: var(--border); color: var(--text); }

        table { width: 100%; border-collapse: collapse; }

        th {
            text-align: left;
            padding: 15px;
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 2px solid var(--border);
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            vertical-align: middle;
            color: var(--text);
        }

        tbody tr:hover td { background: rgba(196,164,107,.06); }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .active    { background: #f6ffed; color: #52c41a; border: 1px solid #b7eb8f; }
        .suspended { background: #fff1f0; color: #d85c5c; border: 1px solid #ffccc7; }
        html.dark .active    { background: #0E2218 !important; color: #4ade80 !important; border-color: #1A4030 !important; }
        html.dark .suspended { background: #220E0E !important; color: #f87171 !important; border-color: #401818 !important; }

        .role-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
        }
        /* تعديل: جعل دور المستخدم والآدمين بنفس اللون الأزرق كما طلبت */
        .role-admin, .role-utilisateur { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        
        html.dark .role-admin, html.dark .role-utilisateur { background: #0F1E30 !important; color: #7EB3E8 !important; border-color: #1A3550 !important; }

        .id-tag {
            background: var(--bg);
            color: var(--primary);
            padding: 2px 8px;
            border-radius: 6px;
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 12px;
            border: 1px solid var(--border);
            font-weight: 600;
        }

        .actions-cell { display: flex; gap: 8px; flex-wrap: wrap; }

        .btn-action {
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            transition: .2s;
            cursor: pointer;
            border: none;
        }
        .btn-toggle { background: var(--dark); color: #fff; }
        .btn-toggle:hover { opacity: .85; }
        html.dark .btn-toggle { background: #3E3228 !important; color: #F0E8D8 !important; }

        .btn-role { background: #1d4ed8; color: white; }
        .btn-role:hover { background: #1e40af; }
        html.dark .btn-role { background: #1A3550 !important; color: #7EB3E8 !important; }
        html.dark .btn-role:hover { background: #1d4ed8 !important; color: #fff !important; }

        .btn-disabled {
            background: #e5e7eb;
            color: #9ca3af;
            cursor: not-allowed;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            border: none;
        }
        html.dark .btn-disabled { background: #2A2418 !important; color: #5A4C3C !important; }

        .row-me td { background: rgba(196,164,107,.06) !important; }

        #dm-fab {
            position: fixed;
            bottom: 24px; right: 24px;
            z-index: 9999;
            width: 44px; height: 44px;
            border-radius: 50%;
            border: 1.5px solid rgba(196,164,107,.35);
            background: var(--surface);
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0,0,0,.2);
            transition: background .3s, border-color .3s;
        }
        #dm-fab:hover { border-color: #C4A46B; }

        .swal2-styled.swal2-confirm { background-color: #C4A46B !important; }
        .swal2-styled.swal2-cancel  { background-color: #3D2E25 !important; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Gestion des Lecteurs</h1>
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
                $uid          = intval($row['id']);
                $fullname     = htmlspecialchars($row['firstname'] . ' ' . $row['lastname']);
                $firstname    = htmlspecialchars($row['firstname']);
                $email        = htmlspecialchars($row['email']);
                $status       = $row['status'];
                
                // تصحيح: التأكد من أن الرول ليس فارغاً ليعرض النص في الجدول
                $role         = (!empty($row['role'])) ? $row['role'] : 'utilisateur';
                
                $newRole      = ($role === 'admin') ? 'utilisateur' : 'admin';
                $newRoleLabel = ($newRole === 'admin') ? 'Admin' : 'utilisateur';
                $isMe         = ($uid === $current_admin_id);
            ?>
            <tr id="row-<?= $uid ?>" class="<?= $isMe ? 'row-me' : '' ?>">
                <td><span class="id-tag"><?= sprintf("%02d", $uid) ?></span></td>
                <td><strong><?= $fullname ?></strong></td>
                <td><?= $email ?></td>
                <td>
                    <span class="status-badge <?= $status ?>">
                        <?= ($status === 'active') ? 'Actif' : 'Suspendu' ?>
                    </span>
                </td>
                <td>
                    <span class="role-badge role-<?= $role ?>" id="role-badge-<?= $uid ?>">
                        <?= ucfirst($role) ?>
                    </span>
                </td>
                <td>
                    <div class="actions-cell">
                        <?php if (!$isMe): ?>
                            <button class="btn-action btn-toggle"
                                onclick="confirmStatusUpdate('<?= $uid ?>', '<?= $status ?>', '<?= $firstname ?>')">
                                <?= ($status === 'active') ? 'Suspendre' : 'Activer' ?>
                            </button>
                            <button class="btn-action btn-role"
                                id="role-btn-<?= $uid ?>"
                                onclick="confirmRoleChange(<?= $uid ?>, '<?= $firstname ?>', '<?= $role ?>', '<?= $newRole ?>', '<?= $newRoleLabel ?>')">
                                → <?= $newRoleLabel ?>
                            </button>
                        <?php else: ?>
                            <button class="btn-disabled" disabled>Mon compte</button>
                            <button class="btn-disabled" disabled>Mon rôle</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<button id="dm-fab" title="Toggle dark mode">🌙</button>

<script>
(function(){
    var fab = document.getElementById('dm-fab');
    if (localStorage.getItem('auralib_theme') === 'dark') fab.innerHTML = '☀️';
    fab.addEventListener('click', function(){
        var dark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('auralib_theme', dark ? 'dark' : 'light');
        fab.innerHTML = dark ? '☀️' : '🌙';
    });
})();

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
                    const badge = document.getElementById('role-badge-' + userId);
                    badge.textContent = newRole.charAt(0).toUpperCase() + newRole.slice(1);
                    badge.className = 'role-badge role-' + newRole;
                    const btn = document.getElementById('role-btn-' + userId);
                    const next = newRole === 'admin' ? 'utilisateur' : 'admin';
                    const nextLabel = next === 'admin' ? 'Admin' : 'utilisateur';
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