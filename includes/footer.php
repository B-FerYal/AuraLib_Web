<?php
// includes/footer.php
// Appelé en bas de chaque page : <?php include "../includes/footer.php"; ?>


<footer class="aura-footer">
    <div class="aura-footer-inner">

        <!-- ══ GRID : Brand + 3 colonnes ══ -->
        <div class="aura-footer-grid">

            <!-- ── Brand ── -->
            <div class="ft-brand-col">

                <div class="ft-brand-name">
                    <span class="ft-white">Aura</span>Lib
                </div>
                <span class="ft-brand-sub">Plateforme de gestion de livres en ligne</span>

                <p class="ft-brand-desc">
                    Découvrez, empruntez et achetez vos livres en ligne.
                    AuraLib connecte les lecteurs aux bibliothèques partout,
                    simplement et rapidement.
                </p>

                <div class="ft-socials">
                    <a href="mailto:contact@auralib.dz" class="ft-social-btn" title="Email">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                    </a>
                    <a href="#" class="ft-social-btn" title="Facebook">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                        </svg>
                    </a>
                    <a href="#" class="ft-social-btn" title="Instagram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5"/>
                            <circle cx="12" cy="12" r="4"/>
                            <circle cx="17.5" cy="6.5" r=".5" fill="currentColor"/>
                        </svg>
                    </a>
                </div>

                <div class="ft-badge">
                    <span class="ft-badge-dot"></span>
                    <span>Lecture · Emprunt · Achat</span>
                </div>

            </div>

            <!-- ── Navigation ── -->
            <div class="ft-col">
                <span class="ft-col-title">Navigation</span>
                <ul class="ft-links">
                    <li><a href="<?= $base ?>/client/library.php">Accueil</a></li>
                    <li><a href="<?= $base ?>/client/library.php">Catalogue</a></li>
                    <li><a href="<?= $base ?>/emprunts/mes_emprunts.php">Mes emprunts</a></li>
                    <li><a href="<?= $base ?>/commandes/commande_list.php">Mes achats</a></li>
                    <li><a href="<?= $base ?>/client/profile.php">Mon profil</a></li>
                </ul>
            </div>

            <!-- ── Services ── -->
            <div class="ft-col">
                <span class="ft-col-title">Services</span>
                <ul class="ft-links">
                    <li><a href="<?= $base ?>/client/library.php?filter=emprunt">Emprunter un livre</a></li>
                    <li><a href="<?= $base ?>/client/library.php?filter=achat">Acheter un livre</a></li>
                    <li><a href="<?= $base ?>/client/profile.php?tab=wishlist">Ma liste de souhaits</a></li>
                    <li><a href="<?= $base ?>/cart/panier.php">Mon panier</a></li>
                    <li><a href="<?= $base ?>/client/notifications.php">Notifications</a></li>
                </ul>
            </div>
<!-- ── Aide ── -->
            <div class="ft-col">
                <span class="ft-col-title">Aide</span>
                <ul class="ft-links">
                    <li><a href="#">À propos</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Politique d'emprunt</a></li>
                    <li><a href="#">Conditions d'utilisation</a></li>
                    <li><a href="<?= $base ?>/auth/login.php">Connexion</a></li>
                </ul>
            </div>

        </div>

        <hr class="ft-divider">

        <!-- ══ Barre du bas ══ -->
        <div class="ft-bottom">
            <p class="ft-copy">
                &copy; <?= date('Y') ?> <strong>AuraLib</strong>
                &nbsp;·&nbsp; Gestion, vente et emprunt de livres en ligne
            </p>
            <div class="ft-legal">
                <a href="#">Confidentialité</a>
                <a href="#">Mentions légales</a>
                <a href="#">Conditions</a>
            </div>
        </div>

    </div>
</footer>

<style>
/* ════════════════════════════════════════════
   AURALIB — Footer
   Thème : crème · taupe · doré
════════════════════════════════════════════ */
.aura-footer {
    background: #2C1F0E;
    padding: 52px 0 0;
    margin-top: 60px;
    border-top: 1px solid rgba(196,164,107,.2);
    font-family: 'Lato', sans-serif;
}

.aura-footer-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 40px;
}

/* ── Grid ── */
.aura-footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 40px;
    margin-bottom: 40px;
}

/* ── Brand ── */
.ft-brand-name {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    color: #C4A46B;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 4px;
}
.ft-white { color: #ffffff; }

.ft-brand-sub {
    display: block;
    font-size: 9px;
    color: rgba(196,164,107,.32);
    letter-spacing: 2.5px;
    text-transform: uppercase;
    margin-bottom: 14px;
}

.ft-brand-desc {
    font-size: 12px;
    color: rgba(237,229,212,.4);
    line-height: 1.85;
    margin-bottom: 20px;
    max-width: 230px;
}

/* ── Socials ── */
.ft-socials {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}
.ft-social-btn {
    width: 32px; height: 32px;
    border-radius: 7px;
    background: rgba(196,164,107,.1);
    border: 1px solid rgba(196,164,107,.22);
    display: flex; align-items: center; justify-content: center;
    color: #C4A46B;
    text-decoration: none;
    transition: background .15s, border-color .15s;
    flex-shrink: 0;
}
.ft-social-btn:hover {
    background: rgba(196,164,107,.22);
    border-color: rgba(196,164,107,.5);
}
.ft-social-btn svg { width: 14px; height: 14px; }

/* ── Badge ── */
.ft-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(196,164,107,.07);
    border: 1px solid rgba(196,164,107,.15);
    border-radius: 6px;
    padding: 6px 12px;
}
.ft-badge-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #C4A46B;
    flex-shrink: 0;
}
.ft-badge span {
    font-size: 10px;
    color: rgba(196,164,107,.55);
    letter-spacing: .5px;
}

/* ── Colonnes nav ── */
.ft-col-title {
    display: block;
    font-size: 10px;
    font-weight: 700;
    color: #C4A46B;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    margin-bottom: 14px;
}

.ft-links {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 9px;
}
.ft-links a {
    font-size: 12px;
    color: rgba(237,229,212,.44);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: color .15s;
}
.ft-links a::before {
    content: '';
    width: 4px; height: 4px;
    border-radius: 50%;
    background: rgba(196,164,107,.28);
    flex-shrink: 0;
    transition: background .15s;
}
.ft-links a:hover { color: #C4A46B; }
.ft-links a:hover::before { background: #C4A46B; }

/* ── Divider ── */
.ft-divider {
    border: none;
    border-top: 1px solid rgba(196,164,107,.12);
    margin: 0;
}
/* ── Bottom bar ── */
.ft-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
    flex-wrap: wrap;
    gap: 10px;
}

.ft-copy {
    font-size: 11px;
    color: rgba(237,229,212,.24);
    letter-spacing: .2px;
}
.ft-copy strong {
    color: rgba(196,164,107,.45);
    font-weight: 600;
}

.ft-legal { display: flex; gap: 16px; }
.ft-legal a {
    font-size: 11px;
    color: rgba(237,229,212,.24);
    text-decoration: none;
    transition: color .15s;
}
.ft-legal a:hover { color: #C4A46B; }

/* ── Responsive ── */
@media (max-width: 768px) {
    .aura-footer-grid {
        grid-template-columns: 1fr 1fr;
    }
    .ft-brand-col {
        grid-column: 1 / -1;
    }
    .ft-brand-desc { max-width: 100%; }
}

@media (max-width: 480px) {
    .aura-footer-inner { padding: 0 20px; }
    .aura-footer-grid  { grid-template-columns: 1fr; }
    .ft-bottom {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>
