<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/languages.php';

// ─── Fetch 5 most recent documents for Hero Slider ───────────────────────────────
$hero_sql = "SELECT b.*, c.libelle_type 
             FROM documents b 
             LEFT JOIN types_documents c ON b.id_type = c.id_type 
             ORDER BY b.created_at DESC 
             LIMIT 5";
$hero_result = mysqli_query($conn, $hero_sql);
$hero_documents = [];
while ($row = mysqli_fetch_assoc($hero_result)) {
    $hero_documents[] = $row;
}

// ─── Fetch all types_documents that have documents ────────────────────────────────────
$cat_sql = "SELECT DISTINCT c.id_type, c.libelle_type 
            FROM types_documents c 
            INNER JOIN documents b ON b.id_type = c.id_type 
            ORDER BY c.libelle_type ASC";
$cat_result = mysqli_query($conn, $cat_sql);
$types_documents = [];
while ($row = mysqli_fetch_assoc($cat_result)) {
    $types_documents[] = $row;
}

// ─── Fetch documents per category (up to 10 per row) ─────────────────────────────
$documents_by_category = [];
foreach ($types_documents as $cat) {
    $cid = (int)$cat['id_type'];
    $documents_sql = "SELECT b.*, c.libelle_type 
                  FROM documents b 
                  LEFT JOIN types_documents c ON b.id_type = c.id_type 
                  WHERE b.id_type = $cid 
                  ORDER BY b.created_at DESC 
                  LIMIT 10";
    $documents_result = mysqli_query($conn, $documents_sql);
    $documents = [];
   $documents_list = []; // مصفوفة جديدة لتخزين النتائج
while ($doc_row = mysqli_fetch_assoc($documents_result)) {
    $documents_list[] = $doc_row;
}
if (!empty($documents_list)) {
    $documents_by_category[$cat['libelle_type']] = $documents_list;
}
    if (!empty($documents)) {
        $documents_by_category[$cat['libelle_type']] = $documents;
    }
}

// ─── Wishlist IDs for logged-in user ─────────────────────────────────────────
$wishlist_ids = [];
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $wl_sql = "SELECT id_doc FROM wishlist WHERE id_user = $uid";
    $wl_result = mysqli_query($conn, $wl_sql);
    while ($wl = mysqli_fetch_assoc($wl_result)) {
        $wishlist_ids[] = $wl['id_doc'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuraLib — Your Digital Library</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Premium CSS -->
    <link rel="stylesheet" href="../css/auralib-premium.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<!-- ═══════════════════════════════════════════════════════════════════════════
     HERO SLIDER SECTION
════════════════════════════════════════════════════════════════════════════ -->
<section class="hero-slider-section">
    <?php if (!empty($hero_documents)): ?>
    <div id="heroSlider" class="hero-slider">

        <!-- Slides -->
        <?php foreach ($hero_documents as $i => $documents): ?>
        <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>" 
     data-index="<?= $i ?>"
     style="background-image: url('../uploads/<?= $documents['id_doc'] ?>.jpg');">
    
    <img src="../uploads/<?= $documents['id_doc'] ?>.jpg" 
         alt="<?= htmlspecialchars($documents['titre']) ?>"
         class="hero-documents-cover"
         onerror="this.src='../uploads/default.jpg'">
            <div class="hero-slide-overlay"></div>

            <div class="hero-slide-content container">
                <div class="row align-items-end" style="min-height:520px;">
                    <div class="col-lg-6 col-md-8 pb-5">
                        <div class="hero-badge">
                            <i class="bi bi-documentsmark-star-fill me-1"></i>
                            <?= htmlspecialchars($documents['libelle_type'] ?? 'New Arrival') ?>
                        </div>
                        <h1 class="hero-title"><?= htmlspecialchars($documents['titre']) ?></h1>
                        <p class="hero-author">
                            <i class="bi bi-person-fill me-2"></i>
                            <?= htmlspecialchars($document['auteur'] ?? 'Unknown Author') ?>
                        </p>
                        <p class="hero-desc">
                            <?= htmlspecialchars(mb_substr($document['description_longue'] ?? 'Explore this remarkable documents from our curated collection.', 0, 160)) ?>…
                        </p>
                        <div class="hero-actions d-flex gap-3 flex-wrap">
                            <a href="doc_details.php?id=<?= $documents['id_doc'] ?>" class="btn btn-hero-primary">
                                <i class="bi bi-eye-fill me-2"></i>View Details
                            </a>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php $in_wl = in_array($documents['id_doc'], $wishlist_ids); ?>
                                <a href="../client/toggle_wishlist.php?id=<?= $documents['id_doc'] ?>" 
                                   class="btn btn-hero-secondary <?= $in_wl ? 'wishlisted' : '' ?>">
                                    <i class="bi <?= $in_wl ? 'bi-heart-fill' : 'bi-heart' ?> me-2"></i>
                                    <?= $in_wl ? 'Wishlisted' : 'Wishlist' ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Floating documents Cover -->
                    <div class="col-lg-4 offset-lg-2 col-md-4 d-none d-md-flex justify-content-center align-items-end pb-5">
                        <div class="hero-documents-cover-wrapper">
                            <img src="../uploads/<?= $document['id_doc'] ?>.jpg"
     alt="<?= htmlspecialchars($documents['titre']) ?>"
     class="documents-cover"
     loading="lazy"
     onerror="this.src='../uploads/default.jpg'">
                                
                                 
                               
                            <div class="hero-document-glow"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Slider Controls -->
        <button class="hero-ctrl hero-prev" id="heroPrev" aria-label="Previous">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button class="hero-ctrl hero-next" id="heroNext" aria-label="Next">
            <i class="bi bi-chevron-right"></i>
        </button>

        <!-- Dots -->
        <div class="hero-dots" id="heroDots">
            <?php foreach ($hero_documents as $i => $documents): ?>
            <button class="hero-dot <?= $i === 0 ? 'active' : '' ?>" data-slide="<?= $i ?>" aria-label="Slide <?= $i+1 ?>"></button>
            <?php endforeach; ?>
        </div>

        <!-- Progress Bar -->
        <div class="hero-progress-bar">
            <div class="hero-progress-fill" id="heroProgress"></div>
        </div>
    </div>
    <?php else: ?>
    <!-- Fallback if no documents yet -->
    <div class="hero-empty d-flex align-items-center justify-content-center" style="min-height:520px;">
        <div class="text-center text-white">
            <i class="bi bi-documents display-1 mb-3 d-block opacity-50"></i>
            <h3>Your library is empty — add some documents!</h3>
        </div>
    </div>
    <?php endif; ?>
</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     QUICK STATS BAR
════════════════════════════════════════════════════════════════════════════ -->
<section class="stats-bar py-4">
    <div class="container">
        <div class="row g-3 text-center">
            <?php
            $total_documents   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM documents"))[0] ?? 0;
            $total_cats    = count($types_documents);
            $total_members = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='client'"))[0] ?? 0;
            $total_borrows = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM emprunt"))[0] ?? 0;
            ?>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <span class="stat-number"><?= number_format($total_documents) ?></span>
                    <span class="stat-label"><i class="bi bi-documents me-1"></i>documents</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <span class="stat-number"><?= number_format($total_cats) ?></span>
                    <span class="stat-label"><i class="bi bi-grid me-1"></i>types_documents</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <span class="stat-number"><?= number_format($total_members) ?></span>
                    <span class="stat-label"><i class="bi bi-people me-1"></i>Members</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <span class="stat-number"><?= number_format($total_borrows) ?></span>
                    <span class="stat-label"><i class="bi bi-arrow-left-right me-1"></i>Borrows</span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════════════════
     documents BY CATEGORY — HORIZONTAL ROWS
════════════════════════════════════════════════════════════════════════════ -->
<main class="library-main py-5">
    <div class="container-fluid px-4">
        
        <?php foreach ($documents_by_category as $category_name => $docs_list): ?>
            <div class="category-section mb-5">
                <div class="row-header d-flex justify-content-between align-items-center mb-3">
                    <h2 class="row-title">
                        <span class="row-title-accent"></span> 
                        <?= htmlspecialchars($category_name) ?>s
                    </h2>
                </div>

                <div class="shelf-grid">
                    <?php foreach ($docs_list as $doc): ?>
                        <div class="lib-card">
                            <div class="lib-cover-area">
                                <img src="../uploads/<?= $doc['id_doc'] ?>.jpg" class="lib-img" onerror="this.src='../uploads/default.jpg'">
                                <div class="lib-actions-overlay">
    <div class="action-group">
        <?php if (in_array($doc['disponible_pour'], ['emprunt', 'both'])): ?>
            <button class="lib-btn btn-borrow" title="Request Borrowing">
                <i class="bi bi-feather"></i>
            </button>
        <?php endif; ?>

        <?php if (in_array($doc['disponible_pour'], ['achat', 'both'])): ?>
            <button class="lib-btn btn-purchase" title="Purchase Copy">
                <i class="bi bi-bag-check"></i>
            </button>
        <?php endif; ?>

        <a href="doc_details.php?id=<?= $doc['id_doc'] ?>" class="lib-btn btn-details" title="View Collection Details">
            <i class="bi bi-plus-lg"></i>
        </a>
    </div>
</div>
                            </div>
                            <div class="lib-metadata">
                                <h3 class="lib-title"><?= htmlspecialchars($doc['titre']) ?></h3>
                                <p class="lib-author"><?= htmlspecialchars($doc['auteur'] ?? 'Unknown') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</main>


<!-- ═══════════════════════════════════════════════════════════════════════════
     SCROLL-TO-TOP BUTTON
════════════════════════════════════════════════════════════════════════════ -->
<button id="scrollTopBtn" class="scroll-top-btn" aria-label="Back to top">
    <i class="bi bi-chevron-up"></i>
</button>


<?php include '../includes/footer.php'; ?>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* ═══════════════════════════════════════════════
   HERO SLIDER LOGIC
═══════════════════════════════════════════════ */
(function () {
    const slides    = document.querySelectorAll('.hero-slide');
    const dots      = document.querySelectorAll('.hero-dot');
    const progress  = document.getElementById('heroProgress');
    const prevBtn   = document.getElementById('heroPrev');
    const nextBtn   = document.getElementById('heroNext');

    if (!slides.length) return;

    let current   = 0;
    let timer     = null;
    const DURATION = 5000; // ms per slide

    function goTo(idx) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');

        current = (idx + slides.length) % slides.length;

        slides[current].classList.add('active');
        dots[current].classList.add('active');

        // Reset progress bar animation
        if (progress) {
            progress.style.transition = 'none';
            progress.style.width = '0%';
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    progress.style.transition = `width ${DURATION}ms linear`;
                    progress.style.width = '100%';
                });
            });
        }
    }

    function startAutoplay() {
        clearInterval(timer);
        timer = setInterval(() => goTo(current + 1), DURATION);
    }

    // Init
    goTo(0);
    startAutoplay();

    // Controls
    if (prevBtn) prevBtn.addEventListener('click', () => { goTo(current - 1); startAutoplay(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { goTo(current + 1); startAutoplay(); });

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            goTo(parseInt(dot.dataset.slide));
            startAutoplay();
        });
    });

    // Touch/swipe support
    let touchStartX = 0;
    const sliderEl = document.getElementById('heroSlider');
    if (sliderEl) {
        sliderEl.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
        sliderEl.addEventListener('touchend', e => {
            const diff = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) {
                diff > 0 ? goTo(current + 1) : goTo(current - 1);
                startAutoplay();
            }
        }, { passive: true });
    }
})();


/* ═══════════════════════════════════════════════
   HORIZONTAL STRIP SCROLL BUTTONS
═══════════════════════════════════════════════ */
document.querySelectorAll('.category-row').forEach(row => {
    const strip = row.querySelector('.documents-strip');
    const prev  = row.querySelector('.strip-prev');
    const next  = row.querySelector('.strip-next');
    if (!strip) return;

    const SCROLL_AMT = 320;

    if (prev) prev.addEventListener('click', () => strip.scrollBy({ left: -SCROLL_AMT, behavior: 'smooth' }));
    if (next) next.addEventListener('click', () => strip.scrollBy({ left: SCROLL_AMT, behavior: 'smooth' }));

    // Show/hide strip controls based on scroll position
    function updateCtrls() {
        if (prev) prev.style.opacity = strip.scrollLeft > 10 ? '1' : '0';
        if (next) {
            const atEnd = strip.scrollLeft + strip.clientWidth >= strip.scrollWidth - 10;
            next.style.opacity = atEnd ? '0' : '1';
        }
    }
    strip.addEventListener('scroll', updateCtrls, { passive: true });
    updateCtrls();
});


/* ═══════════════════════════════════════════════
   SCROLL-TO-TOP BUTTON
═══════════════════════════════════════════════ */
const scrollTopBtn = document.getElementById('scrollTopBtn');
if (scrollTopBtn) {
    window.addEventListener('scroll', () => {
        scrollTopBtn.classList.toggle('visible', window.scrollY > 400);
    });
    scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}
</script>

</body>
</html>
