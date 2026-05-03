<?php
include "../includes/header.php";
?>
<title><?= t('about') ?> — AuraLibre</title>

<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Lato:wght@300;400;700&family=Tajawal:wght@400;700&display=swap" rel="stylesheet">

<style>

/* ═══════════════════════════════════════════════════════
   GLOBAL PAGE RESET — no gap after header
═══════════════════════════════════════════════════════ */
.about-root {
    width: 100%;
    margin: 0;
    padding: 0;
}

/* All inner sections share the same centered width */
.about-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 60px;
}

/* ═══════════════════════════════════════════════════════
   HERO — full viewport, photo background
═══════════════════════════════════════════════════════ */
.about-hero-section {
    position: relative;
    width: 100%;
    height: 100vh;
    min-height: 640px;
    max-height: 900px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    isolation: isolate;
    margin: 0;
}

/* Photo layer */
.about-hero-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url('../assets/images/hero-librarian.jpg');
    background-size: cover;
    background-position: center center;
    filter: blur(3px) brightness(0.48) saturate(0.7);
    transform: scale(1.05);
    z-index: 0;
}

/* Dark vignette on top */
.about-hero-section::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom,
        rgba(10,6,2,0.35) 0%,
        rgba(20,12,4,0.58) 70%,
        rgba(26,16,8,0.75) 100%
    );
    z-index: 1;
}

.about-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    padding: 0 40px;
    max-width: 860px;
}

.about-hero-content .badge-pill {
    display: inline-block;
    background: rgba(196,164,107,0.14);
    border: 1px solid rgba(196,164,107,0.5);
    color: #E2C07A;
    font-family: 'Lato', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 3.5px;
    text-transform: uppercase;
    padding: 8px 24px;
    border-radius: 30px;
    margin-bottom: 32px;
    backdrop-filter: blur(10px);
}

.about-hero-content h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 76px;
    font-weight: 700;
    line-height: 1.08;
    color: #FFFFFF;
    margin: 0 0 26px;
    letter-spacing: -1.5px;
    text-shadow: 0 3px 30px rgba(0,0,0,0.65);
}

.about-hero-content h1 em {
    color: #D4A853;
    font-style: italic;
}

.about-hero-content p {
    font-family: 'Cormorant Garamond', serif;
    font-size: 24px;
    font-weight: 400;
    font-style: italic;
    color: #F0E6CC;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.7;
    text-shadow: 0 2px 14px rgba(0,0,0,0.65);
}

/* Scroll indicator */
.hero-scroll-hint {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.hero-scroll-hint span {
    font-family: 'Lato', sans-serif;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: rgba(255,255,255,0.35);
}
.hero-scroll-line {
    width: 1px;
    height: 44px;
    background: linear-gradient(to bottom, rgba(196,164,107,0.85), transparent);
    animation: pulse 2.2s ease-in-out infinite;
}
@keyframes pulse {
    0%,100% { opacity: 0.4; }
    50%      { opacity: 1; }
}

/* ═══════════════════════════════════════════════════════
   CONTENT SECTIONS — same wide layout as hero
═══════════════════════════════════════════════════════ */
.about-body {
    background: var(--page-bg, #F5F0E8);
    padding: 80px 0;
}
/* ── Stats band ── */
.stats-band {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1px;
    background: #DDD5C8;
    border: 1px solid #DDD5C8;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 80px;
}
.stat-item {
    background: #FFFDF9;
    padding: 36px 24px;
    text-align: center;
}
.stat-item .num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 52px;
    font-weight: 600;
    color: #B8924A;
    line-height: 1;
    margin-bottom: 8px;
    letter-spacing: -1px;
}
.stat-item .lbl {
    font-family: 'Lato', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #7A6A55;
}

/* ── Section ── */
.section { margin-bottom: 80px; }

.section-tag {
    font-family: 'Lato', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #B8924A;
    margin-bottom: 14px;
}
.section h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 46px;
    font-weight: 700;
    color: #1A1008;
    margin: 0 0 20px;
    letter-spacing: -0.5px;
    line-height: 1.1;
}
.section p {
    font-family: 'Lato', sans-serif;
    font-size: 16px;
    color: #5C4A30;
    line-height: 1.9;
    margin-bottom: 16px;
    max-width: 760px;
}

/* ── Divider ── */
.divider {
    display: flex;
    align-items: center;
    gap: 18px;
    margin: 0 0 50px;
    color: #9A8470;
    font-family: 'Lato', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
}
.divider::before, .divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #DDD5C8;
}

/* ── Feature cards — icon left, text right ── */
.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
    margin-bottom: 80px;
}

.feat-card {
    background: #FFFDF9;
    border: 1px solid #DDD5C8;
    border-radius: 16px;
    padding: 32px 28px;
    border-top: 3px solid transparent;
    transition: border-color .2s, transform .2s;
    display: flex;
    align-items: flex-start;
    gap: 22px;
}
.feat-card:hover {
    border-top-color: #C4A46B;
    transform: translateY(-4px);
}
.feat-icon {
    width: 62px;
    height: 62px;
    min-width: 62px;
    border-radius: 14px;
    background: rgba(196,164,107,0.10);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    flex-shrink: 0;
}
.feat-text { flex: 1; }
.feat-card h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 700;
    color: #1A1008;
    margin: 0 0 8px;
    letter-spacing: -0.2px;
}
.feat-card p {
    font-family: 'Lato', sans-serif;
    font-size: 14px;
    color: #6A5840;
    line-height: 1.75;
    margin: 0;
}

/* ── Values grid — icon + title inline, desc below ── */
.values-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 80px;
}
.value-card {
    background: #FFFDF9;
    border: 1px solid #DDD5C8;
    border-radius: 14px;
    padding: 28px 24px;
    transition: transform .2s, border-color .2s;
}
.value-card:hover {
    transform: translateY(-3px);
    border-color: rgba(196,164,107,0.5);
}
.value-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 12px;
}
.value-card .vi {
    font-size: 36px;
    line-height: 1;
    flex-shrink: 0;
}
.value-card h4 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 700;
    color: #1A1008;
    margin: 0;
    letter-spacing: -0.2px;
}
.value-card p {
    font-family: 'Lato', sans-serif;
    font-size: 14px;
    color: #6A5840;
    line-height: 1.7;
    margin: 0;
}
/* ── How it works ── */
.steps {
    display: flex;
    gap: 0;
    position: relative;
    margin-bottom: 80px;
}
.steps::before {
    content: '';
    position: absolute;
    top: 30px;
    left: 60px;
    right: 60px;
    height: 1px;
    background: #DDD5C8;
    z-index: 0;
}
.step {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 1;
    padding: 0 16px;
}
.step-num {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #FFFDF9;
    border: 2px solid #C4A46B;
    color: #B8924A;
    font-family: 'Cormorant Garamond', serif;
    font-size: 24px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
}
.step h4 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px;
    font-weight: 700;
    color: #1A1008;
    margin: 0 0 8px;
}
.step p {
    font-family: 'Lato', sans-serif;
    font-size: 13px;
    color: #6A5840;
    line-height: 1.7;
    margin: 0;
}

/* ── CTA Banner ── */
.cta-banner {
    background: #1A1008;
    border-radius: 20px;
    padding: 60px 50px;
    text-align: center;
    border: 1px solid rgba(196,164,107,0.2);
}
.cta-banner h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 42px;
    font-weight: 700;
    color: #FFFFFF;
    margin: 0 0 14px;
    letter-spacing: -0.5px;
}
.cta-banner p {
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-style: italic;
    font-weight: 400;
    color: rgba(255,255,255,0.65);
    margin: 0 0 32px;
    line-height: 1.7;
}
.cta-btns {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
}
.btn-gold {
    background: #C4A46B;
    color: #1A1008;
    padding: 14px 32px;
    border-radius: 10px;
    text-decoration: none;
    font-family: 'Lato', sans-serif;
    font-weight: 700;
    font-size: 15px;
    transition: background .2s;
}
.btn-gold:hover { background: #D4B47B; }
.btn-outline-w {
    background: transparent;
    color: rgba(255,255,255,0.7);
    border: 1px solid rgba(255,255,255,0.2);
    padding: 14px 32px;
    border-radius: 10px;
    text-decoration: none;
    font-family: 'Lato', sans-serif;
    font-size: 15px;
    transition: border-color .2s, color .2s;
}
.btn-outline-w:hover { border-color: #C4A46B; color: #C4A46B; }

/* ── Responsive ── */
@media (max-width: 900px) {
    .about-inner        { padding: 0 30px; }
    .two-col            { grid-template-columns: 1fr; }
    .values-grid        { grid-template-columns: 1fr 1fr; }
    .stats-band         { grid-template-columns: repeat(2, 1fr); }
    .about-hero-content h1 { font-size: 50px; }
    .about-hero-content p  { font-size: 20px; }
}
@media (max-width: 600px) {
    .about-inner        { padding: 0 20px; }
    .values-grid        { grid-template-columns: 1fr; }
    .steps              { flex-direction: column; gap: 24px; }
    .steps::before      { display: none; }
    .about-hero-content h1 { font-size: 38px; }
    .feat-card          { flex-direction: column; }
    .cta-banner         { padding: 40px 24px; }
}
</style>

<div class="about-root">

    <!-- ══════════════════════════════════════════════
         HERO — full screen photo
    ══════════════════════════════════════════════ -->
    <section class="about-hero-section">
        <div class="about-hero-content">
            <div class="badge-pill">✦ À propos de nous</div>
            <h1>La bibliothèque<br>de <em>demain</em>, aujourd'hui</h1>
            <p>AuraLibre est une plateforme numérique dédiée à rendre la lecture accessible — emprunter gratuitement ou acheter en quelques clics, depuis n'importe où.</p>
        </div>
        <div class="hero-scroll-hint">
            <div class="hero-scroll-line"></div>
            <span>Découvrir</span>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════
         BODY — same width as hero
    ══════════════════════════════════════════════ -->
    <div class="about-body">
        <div class="about-inner">
<!-- Stats -->
            <div class="stats-band">
                <?php
                $nb_docs     = $conn->query("SELECT COUNT(*) c FROM documents")->fetch_assoc()['c'] ?? 0;
                $nb_users    = $conn->query("SELECT COUNT(*) c FROM users WHERE role='client'")->fetch_assoc()['c'] ?? 0;
                $nb_emprunts = $conn->query("SELECT COUNT(*) c FROM emprunt")->fetch_assoc()['c'] ?? 0;
                $nb_types    = $conn->query("SELECT COUNT(*) c FROM types_documents")->fetch_assoc()['c'] ?? 0;
                ?>
                <div class="stat-item"><div class="num"><?= number_format($nb_docs) ?>+</div><div class="lbl">Documents</div></div>
                <div class="stat-item"><div class="num"><?= number_format($nb_users) ?>+</div><div class="lbl">Lecteurs</div></div>
                <div class="stat-item"><div class="num"><?= number_format($nb_emprunts) ?>+</div><div class="lbl">Emprunts réalisés</div></div>
                <div class="stat-item"><div class="num"><?= $nb_types ?></div><div class="lbl">Types de documents</div></div>
            </div>

            <!-- Mission -->
            <div class="section">
                <div class="section-tag">Notre mission</div>
                <h2>Démocratiser l'accès au savoir</h2>
                <p>AuraLibre est né d'une conviction simple : chaque personne mérite un accès facile, rapide et élégant aux livres, thèses, articles et documents académiques.</p>
                <p>En combinant emprunt gratuit et achat en ligne, nous offrons une expérience complète qui s'adapte à tous les besoins — étudiant, chercheur, ou simple passionné de lecture.</p>
            </div>

            <div class="divider">Ce que nous proposons</div>

            <!-- Features -->
            <div class="two-col">
                <div class="feat-card">
                    <div class="feat-icon">📖</div>
                    <div class="feat-text">
                        <h3>Emprunt gratuit &amp; simple</h3>
                        <p>Empruntez n'importe quel document disponible pour 14 jours, gratuitement. Renouvelez en un clic si vous avez besoin de plus de temps.</p>
                    </div>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">🛒</div>
                    <div class="feat-text">
                        <h3>Achat en ligne sécurisé</h3>
                        <p>Achetez vos livres préférés directement depuis le catalogue. Ajoutez au panier, validez votre commande et suivez sa livraison.</p>
                    </div>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">🔍</div>
                    <div class="feat-text">
                        <h3>Recherche intelligente</h3>
                        <p>Trouvez n'importe quel document par titre, auteur, ISBN ou catégorie. Les résultats apparaissent instantanément.</p>
                    </div>
                </div>
                <div class="feat-card">
                    <div class="feat-icon">🌙</div>
                    <div class="feat-text">
                        <h3>Interface multilingue &amp; dark mode</h3>
                        <p>Disponible en français, anglais et arabe. Le mode sombre s'active en un clic et reste mémorisé entre vos visites.</p>
                    </div>
                </div>
            </div>

            <div class="divider">Nos valeurs</div>
<!-- Values -->
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-header"><div class="vi">🔓</div><h4>Accessibilité</h4></div>
                    <p>Le savoir ne doit pas avoir de barrière. Emprunt gratuit pour tous.</p>
                </div>
                <div class="value-card">
                    <div class="value-header"><div class="vi">✨</div><h4>Excellence</h4></div>
                    <p>Une interface soignée, rapide et pensée pour l'expérience utilisateur.</p>
                </div>
                <div class="value-card">
                    <div class="value-header"><div class="vi">🔒</div><h4>Sécurité</h4></div>
                    <p>Vos données personnelles sont protégées et ne sont jamais partagées.</p>
                </div>
                <div class="value-card">
                    <div class="value-header"><div class="vi">🌍</div><h4>Inclusion</h4></div>
                    <p>Disponible en 3 langues pour toucher le plus grand nombre.</p>
                </div>
                <div class="value-card">
                    <div class="value-header"><div class="vi">📚</div><h4>Diversité</h4></div>
                    <p>Livres, thèses, articles, journaux — tout type de document en un seul endroit.</p>
                </div>
                <div class="value-card">
                    <div class="value-header"><div class="vi">⚡️</div><h4>Rapidité</h4></div>
                    <p>Empruntez ou achetez en moins de 3 clics, sans file d'attente.</p>
                </div>
            </div>

            <div class="divider">Comment ça marche</div>

            <!-- Steps -->
            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <h4>Créez votre compte</h4>
                    <p>Inscription gratuite en moins d'une minute. Nom, email, mot de passe — c'est tout.</p>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <h4>Parcourez le catalogue</h4>
                    <p>Des milliers de documents filtrables par type, thème ou disponibilité.</p>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <h4>Empruntez ou achetez</h4>
                    <p>Choisissez votre mode selon le document. Emprunt gratuit ou achat sécurisé.</p>
                </div>
                <div class="step">
                    <div class="step-num">4</div>
                    <h4>Suivez &amp; gérez</h4>
                    <p>Votre tableau de bord affiche vos emprunts, retours et commandes en temps réel.</p>
                </div>
            </div>

            <!-- CTA -->
            <div class="cta-banner">
                <h2>Prêt à explorer notre catalogue ?</h2>
                <p>Rejoignez des centaines de lecteurs qui font confiance à AuraLibre chaque jour.</p>
                <div class="cta-btns">
                    <a href="/MEMOIR/client/library.php" class="btn-gold">📚 Explorer le catalogue</a>
                    <?php if (!isset($_SESSION['id_user'])): ?>
                    <a href="/MEMOIR/auth/signup.php" class="btn-outline-w">Créer un compte gratuit</a>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /about-inner -->
    </div><!-- /about-body -->

</div><!-- /about-root -->

<?php include "../includes/footer.php"; ?>
