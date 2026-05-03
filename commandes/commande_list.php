<?php
// Inclusion du header qui contient déjà la session et la connexion DB
include_once __DIR__ . "/../includes/header.php"; 

// 1. Vérification de sécurité
if (!$is_logged_in) {
    header("Location: " . $base . "/auth/login.php");
    exit;
}

// 2. Récupération des commandes
$query = "SELECT * FROM commande WHERE id_user = $id_user ORDER BY id_commande DESC";
$commandes = $conn->query($query);
?>

<style>
    /* Design spécifique pour la page Achats - كود التنسيق الخاص بك كما هو */
    .achats-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        color: var(--taupe);
        margin-bottom: 30px;
        font-size: 2rem;
        border-bottom: 2px solid var(--gold);
        display: inline-block;
        padding-bottom: 10px;
    }

    .order-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid rgba(196, 164, 107, 0.1);
        transition: transform 0.3s ease;
    }

    .order-card:hover {
        transform: translateY(-5px);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px dashed #eee;
    }

    .order-id {
        font-weight: 700;
        color: var(--taupe);
        font-size: 1.1rem;
    }

    .status-badge {
        padding: 6px 15px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-payee { background: #e3f9e5; color: #1f8b24; }
    .status-attente { background: #fff4e5; color: #d48806; }

    .total-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
    }

    .price-total {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--taupe);
    }

    .price-total span { color: var(--gold); }

    .btn-pay {
        background: var(--taupe);
        color: var(--gold);
        border: 2px solid var(--gold);
        padding: 10px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        transition: 0.3s;
    }

    .btn-pay:hover {
        background: var(--gold);
        color: var(--taupe);
    }
</style>

<div class="main-content">
    <div class="achats-container">
    <h1 class="page-title">Mes Achats</h1>

    <?php 
    $i = 1; 
    while($commande = $commandes->fetch_assoc()): 
        $id_cmd = $commande['id_commande'];
        $statut = $commande['statut'];
        
        // --- الإصلاح هنا: جلب القيمة من العمود 'total' الموجود في جدولك ---
        $total_val = $commande['total'];
    ?>
    
    <div class="order-card">
        <div class="order-header">
            <div>
                <span class="order-id">Commande n°<?= $i++ ?></span>
                <small>(Réf: #<?= $id_cmd ?>)</small>
            </div>
            <span class="status-badge <?= ($statut == 'payée') ? 'status-payee' : 'status-attente' ?>">
                <?= ($statut == 'payée') ? 'Payée' : 'En attente' ?>
            </span>
        </div>

        <div class="total-section">
            <div class="price-total">Total: <span><?= number_format($total_val, 2) ?> DA</span></div>
            
            <div class="actions-gap" style="display: flex; gap: 10px;">
                <?php if($statut != 'payée'): ?>
                    <a href="annuler_commande.php?id=<?= $id_cmd ?>" 
                       onclick="return confirm('Voulez-vous vraiment annuler cette commande ?')"
                       style="color: #e74c3c; text-decoration: none; font-size: 0.9rem; padding: 10px;">
                       Annuler la commande
                    </a>

                    <a href="paiement.php?id=<?= $id_cmd ?>&total=<?= $total_val ?>" class="btn-pay">
                         Régler la commande
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
</div>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>