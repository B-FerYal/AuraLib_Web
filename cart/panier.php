<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . "/../includes/db.php";
include_once '../includes/languages.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

include_once "../includes/header.php";
$id_user = (int)$_SESSION['id_user'];

// ── Traductions ──────────────────────────────────────────
$pg = [
    'fr' => [
        'page_title'    => 'Mon Panier — AuraLib',
        'h1_pre'        => 'Mon',
        'h1_em'         => 'Panier',
        'h1_sub'        => 'article',
        'h1_sub_pl'     => 'articles',
        'page_sub'      => 'Vérifiez vos sélections avant de confirmer la commande',
        'th_articles'   => 'Articles',
        'th_zero'       => '0 livre',
        'empty_h'       => 'Votre panier est vide',
        'empty_p'       => "Vous n'avez ajouté aucun livre pour le moment.",
        'btn_browse'    => 'Explorer le catalogue',
        'th_selected'   => 'Articles sélectionnés',
        'livre'         => 'livre',
        'livres'        => 'livres',
        'price_lbl'     => 'Prix :',
        'th_summary'    => 'Récapitulatif',
        'total_lbl'     => 'Total',
        'btn_confirm'   => 'Confirmer',
        'secure_note'   => 'Paiement sécurisé — AuraLib',
        'swal_remove_title' => 'Retirer ce livre ?',
        'swal_remove_text'  => 'Il sera supprimé de votre panier.',
        'swal_remove_yes'   => 'Oui, retirer',
        'swal_remove_no'    => 'Annuler',
        'swal_warn_title'   => 'Attention',
        'swal_err_title'    => 'Erreur',
        'swal_err_del'      => 'Impossible de supprimer.',
    ],
    'en' => [
        'page_title'    => 'My Cart — AuraLib',
        'h1_pre'        => 'My',
        'h1_em'         => 'Cart',
        'h1_sub'        => 'item',
        'h1_sub_pl'     => 'items',
        'page_sub'      => 'Review your selections before confirming the order',
        'th_articles'   => 'Items',
        'th_zero'       => '0 book',
        'empty_h'       => 'Your cart is empty',
        'empty_p'       => "You haven't added any books yet.",
        'btn_browse'    => 'Explore the catalogue',
        'th_selected'   => 'Selected items',
        'livre'         => 'book',
        'livres'        => 'books',
        'price_lbl'     => 'Price:',
        'th_summary'    => 'Summary',
        'total_lbl'     => 'Total',
        'btn_confirm'   => 'Confirm',
        'secure_note'   => 'Secure payment — AuraLib',
        'swal_remove_title' => 'Remove this book?',
        'swal_remove_text'  => 'It will be removed from your cart.',
        'swal_remove_yes'   => 'Yes, remove',
        'swal_remove_no'    => 'Cancel',
        'swal_warn_title'   => 'Warning',
        'swal_err_title'    => 'Error',
        'swal_err_del'      => 'Unable to remove.',
    ],
    'ar' => [
        'page_title'    => 'سلتي — AuraLib',
        'h1_pre'        => '',
        'h1_em'         => 'سلتي',
        'h1_sub'        => 'عنصر',
        'h1_sub_pl'     => 'عناصر',
        'page_sub'      => 'راجع اختياراتك قبل تأكيد الطلب',
        'th_articles'   => 'العناصر',
        'th_zero'       => '0 كتاب',
        'empty_h'       => 'سلتك فارغة',
        'empty_p'       => 'لم تضف أي كتاب بعد.',
        'btn_browse'    => 'استكشف الكتالوج',
        'th_selected'   => 'العناصر المختارة',
        'livre'         => 'كتاب',
        'livres'        => 'كتب',
        'price_lbl'     => 'السعر:',
        'th_summary'    => 'ملخص الطلب',
        'total_lbl'     => 'الإجمالي',
        'btn_confirm'   => 'تأكيد',
        'secure_note'   => 'دفع آمن — AuraLib',
        'swal_remove_title' => 'إزالة هذا الكتاب؟',
        'swal_remove_text'  => 'سيتم حذفه من سلتك.',
        'swal_remove_yes'   => 'نعم، إزالة',
        'swal_remove_no'    => 'إلغاء',
        'swal_warn_title'   => 'تنبيه',
        'swal_err_title'    => 'خطأ',
        'swal_err_del'      => 'تعذّر الحذف.',
    ],
];
$p     = $pg[$lang] ?? $pg['fr'];
$isRtl = ($lang === 'ar');

$q = $conn->prepare("
    SELECT d.id_doc, d.titre, d.prix,
           i.id_item, i.quantite, i.type_transaction
    FROM panier p
    JOIN panier_item i ON p.id_panier = i.id_panier
    JOIN documents d   ON d.id_doc   = i.id_doc
    WHERE p.id_user = ? AND i.type_transaction = 'achat'
");
$q->bind_param("i", $id_user);
$q->execute();
$result = $q->get_result();

$items_achat = [];
$total_final  = 0;
while ($row = $result->fetch_assoc()) {
    $row['sous_total'] = (float)$row['quantite'] * (float)$row['prix'];
    $total_final      += $row['sous_total'];
    $items_achat[]     = $row;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="UTF-8">
<?php include '../includes/dark_init.php'; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $p['page_title'] ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@300;400;500;600&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="/MEMOIR/css/dark-mode.css">
<script>(function(){ if(localStorage.getItem('auralib_theme')==='dark') document.documentElement.classList.add('dark'); })();</script>
<style>
:root {
    --bg:#EDE8DC;--card:#FFFFFF;--navbar:#2C1F0F;
    --gold:#C8A96E;--gold-deep:#A8893E;--gold-pale:#F5EDD8;
    --text:#2C1F0F;--muted:#8A7A65;--border:#D9D0BF;
    --bdgold:rgba(200,169,110,.3);--danger:#B83232;--dpale:#FDECEA;--r:12px;
    --font-ui:<?= $isRtl ? "'Tajawal',sans-serif" : "'Inter',sans-serif" ?>;
}
html.dark{--bg:#100C07;--card:#1E1610;--navbar:#2C1F0E;--text:#EDE5D4;--muted:#9A8C7E;--border:#3A2E1E;--gold-pale:rgba(196,164,107,.08)}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);font-family:var(--font-ui);color:var(--text);min-height:100vh;direction:<?= $isRtl ? 'rtl' : 'ltr' ?>}
.pw{max-width:1080px;margin:0 auto;padding:108px 20px 80px}
.ptitle{margin-bottom:28px;opacity:0;animation:up .4s ease .1s forwards}
.ptitle h1{font-family:<?= $isRtl ? "'Tajawal',sans-serif" : "'Playfair Display',serif" ?>;font-size:clamp(26px,3.5vw,38px);font-weight:700;line-height:1.1;color:var(--navbar)}
.ptitle h1 em{font-style:italic;color:var(--gold-deep)}
.ptitle p{margin-top:5px;font-size:14px;color:var(--muted)}
.pill{display:inline-flex;align-items:center;justify-content:center;background:var(--gold-pale);color:var(--gold-deep);border:1px solid var(--bdgold);font-size:11px;font-weight:600;padding:1px 9px;border-radius:20px;margin-<?= $isRtl?'right':'left' ?>:10px;vertical-align:middle}
.pgrid{display:grid;grid-template-columns:1fr 296px;gap:20px;align-items:start}
@media(max-width:768px){.pgrid{grid-template-columns:1fr}}
.panel{background:var(--card);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:0 2px 14px rgba(44,31,15,.06)}
.panel-hd{padding:13px 20px;border-bottom:1px solid var(--border);background:#FAFAF7;display:flex;justify-content:space-between;align-items:center;flex-direction:<?= $isRtl?'row-reverse':'row' ?>}
html.dark .panel-hd{background:#1A1308}
.panel-hd span{font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--muted)}
.ip{opacity:0;animation:up .45s ease .15s forwards}
.empty{padding:60px 24px;text-align:center}
.empty-ic{width:60px;height:60px;border-radius:50%;background:var(--gold-pale);color:var(--gold-deep);display:flex;align-items:center;justify-content:center;font-size:22px;margin:0 auto 18px}
.empty h3{font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Playfair Display',serif" ?>;font-size:21px;margin-bottom:7px}
.empty p{color:var(--muted);font-size:14px;margin-bottom:22px}
.btn-browse{display:inline-flex;align-items:center;gap:8px;background:var(--navbar);color:#fff;padding:10px 22px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;transition:background .18s,transform .15s;flex-direction:<?= $isRtl?'row-reverse':'row' ?>}
.btn-browse:hover{background:var(--gold-deep);transform:translateY(-1px)}
.ci{display:grid;grid-template-columns:70px 1fr auto;gap:16px;align-items:center;padding:17px 20px;border-bottom:1px solid var(--border);transition:background .15s}
.ci:last-child{border-bottom:none}.ci:hover{background:#FDFCF7}
html.dark .ci:hover{background:rgba(196,164,107,.04)}
.bthumb{width:70px;height:100px;border-radius:7px;overflow:hidden;flex-shrink:0;background:var(--bg);box-shadow:2px 4px 12px rgba(44,31,15,.17),-1px 0 0 rgba(44,31,15,.07);position:relative}
.bthumb img{width:100%;height:100%;object-fit:cover;display:block}
.bthumb::after{content:'';position:absolute;top:0;<?= $isRtl?'right':'left' ?>:0;bottom:0;width:5px;background:linear-gradient(to <?= $isRtl?'left':'right' ?>,rgba(0,0,0,.13),transparent)}
.ci-info{min-width:0}
.ci-name{font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Playfair Display',serif" ?>;font-size:15.5px;font-weight:600;color:var(--navbar);margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ci-uprice{font-size:12px;color:var(--muted);margin-bottom:13px}
.ci-uprice strong{color:var(--gold-deep);font-weight:600}
.stepper{display:inline-flex;align-items:center;border:1px solid var(--border);border-radius:7px;overflow:hidden;flex-direction:<?= $isRtl?'row-reverse':'row' ?>}
.sbtn{width:30px;height:30px;background:#F4F1EA;border:none;cursor:pointer;font-size:15px;font-weight:700;color:var(--navbar);display:flex;align-items:center;justify-content:center;transition:background .14s,color .14s;flex-shrink:0}
html.dark .sbtn{background:#2A1F10}
.sval{min-width:36px;height:30px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;border-left:1px solid var(--border);border-right:1px solid var(--border);background:#fff;color:var(--navbar)}
html.dark .sval{background:#1E1610}
.sBtn:hover:not(:disabled){background:var(--gold);color:#fff}
.sBtn:disabled{opacity:.35;cursor:not-allowed}
.ci-right{text-align:<?= $isRtl?'left':'right' ?>;display:flex;flex-direction:column;align-items:<?= $isRtl?'flex-start':'flex-end' ?>;gap:9px;min-width:110px}
.ci-sub{font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Playfair Display',serif" ?>;font-size:19px;font-weight:700;color:var(--navbar)}
.ci-sub small{font-family:var(--font-ui);font-size:11px;font-weight:400;color:var(--muted);margin-<?= $isRtl?'right':'left' ?>:2px}
.btn-rm{display:inline-flex;align-items:center;gap:5px;background:none;border:1px solid #E5C0C0;color:var(--danger);cursor:pointer;padding:4px 11px;border-radius:6px;font-size:11px;font-weight:600;font-family:var(--font-ui);letter-spacing:.04em;text-transform:uppercase;transition:all .15s;flex-direction:<?= $isRtl?'row-reverse':'row' ?>}
.btn-rm:hover{background:var(--dpale);border-color:var(--danger)}
.sp{opacity:0;animation:up .45s ease .2s forwards;position:sticky;top:108px}
.srow{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--border);font-size:13.5px;flex-direction:<?= $isRtl?'row-reverse':'row' ?>}
.srow:last-of-type{border-bottom:none}
.srow .sl{color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px}
.srow .sv{font-weight:600;color:var(--navbar);white-space:nowrap}
.stotal{display:flex;justify-content:space-between;align-items:baseline;margin:18px 0;padding-top:14px;border-top:2px solid var(--border);flex-direction:<?= $isRtl?'row-reverse':'row' ?>}
.stotal .tl{font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.09em;color:var(--muted)}
.tamt{font-family:<?= $isRtl?"'Tajawal',sans-serif":"'Playfair Display',serif" ?>;font-size:26px;font-weight:700;color:var(--navbar)}
.tamt span{font-family:var(--font-ui);font-size:12px;font-weight:400;color:var(--muted);margin-<?= $isRtl?'right':'left' ?>:2px}
.btn-confirm{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;background:var(--navbar);color:#fff;border:none;cursor:pointer;padding:13px 18px;border-radius:9px;font-family:var(--font-ui);font-size:14px;font-weight:600;transition:background .18s,transform .14s,box-shadow .2s;flex-direction:<?= $isRtl?'row-reverse':'row' ?>}
.btn-confirm:hover{background:var(--gold-deep);transform:translateY(-1px);box-shadow:0 8px 20px rgba(168,137,62,.26)}
.snote{text-align:center;font-size:11px;color:var(--muted);margin-top:11px;line-height:1.6}
.snote i{color:var(--gold)}
@keyframes up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:520px){.ci{grid-template-columns:60px 1fr}.ci-right{grid-column:1/-1;flex-direction:row;justify-content:space-between;min-width:0}}
</style>
</head>
<body>

<div class="pw">

    <div class="ptitle">
        <h1>
            <?= $p['h1_pre'] ? $p['h1_pre'].' ' : '' ?><em><?= $p['h1_em'] ?></em>
            <?php if (!empty($items_achat)): ?>
                <span class="pill"><?= count($items_achat) ?> <?= count($items_achat)>1 ? $p['h1_sub_pl'] : $p['h1_sub'] ?></span>
            <?php endif; ?>
        </h1>
        <p><?= $p['page_sub'] ?></p>
    </div>

    <?php if (empty($items_achat)): ?>
        <div class="panel">
            <div class="panel-hd"><span><?= $p['th_articles'] ?></span><span><?= $p['th_zero'] ?></span></div>
            <div class="empty">
                <div class="empty-ic"><i class="fa fa-shopping-cart"></i></div>
                <h3><?= $p['empty_h'] ?></h3>
                <p><?= $p['empty_p'] ?></p>
                <a href="../client/library.php" class="btn-browse">
                    <i class="fa fa-book-open"></i> <?= $p['btn_browse'] ?>
                </a>
            </div>
        </div>

    <?php else: ?>
        <div class="pgrid">

            <!-- Items -->
            <div class="panel ip">
                <div class="panel-hd">
                    <span><?= $p['th_selected'] ?></span>
                    <span><?= count($items_achat) ?> <?= count($items_achat)>1 ? $p['livres'] : $p['livre'] ?></span>
                </div>

                <?php foreach ($items_achat as $item):
                    $cover = file_exists("../uploads/{$item['id_doc']}.jpg")
                             ? "../uploads/{$item['id_doc']}.jpg"
                             : "../uploads/default.jpg";
                ?>
                <div class="ci" id="row-<?= $item['id_item'] ?>" data-prix="<?= (float)$item['prix'] ?>">
                    <div class="bthumb"><img src="<?= $cover ?>" alt="Couverture"></div>
                    <div class="ci-info">
                        <div class="ci-name"><?= htmlspecialchars($item['titre']) ?></div>
                        <div class="ci-uprice"><?= $p['price_lbl'] ?> <strong><?= number_format((float)$item['prix'],0,'',' ') ?> DA</strong></div>
                        <div class="stepper">
                            <button class="sBtn sbtn" onclick="updateQuantity(<?= $item['id_item'] ?>, -1)">−</button>
                            <div class="sval" id="qty-<?= $item['id_item'] ?>"><?= (int)$item['quantite'] ?></div>
                            <button class="sBtn sbtn" onclick="updateQuantity(<?= $item['id_item'] ?>, 1)">+</button>
                        </div>
                    </div>
                    <div class="ci-right">
                        <div class="ci-sub">
                            <span id="sub-<?= $item['id_item'] ?>"><?= number_format($item['sous_total'],0,'',' ') ?></span><small>DA</small>
                        </div>
                        <button class="btn-rm" onclick="confirmRemove(<?= $item['id_item'] ?>)">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary -->
            <div class="panel sp">
                <div class="panel-hd"><span><?= $p['th_summary'] ?></span></div>
                <div style="padding:18px 20px">
                    <?php foreach ($items_achat as $item): ?>
                    <div class="srow" id="srow-wrap-<?= $item['id_item'] ?>">
                        <span class="sl"><?= htmlspecialchars(mb_strimwidth($item['titre'],0,20,'…')) ?></span>
                        <span class="sv" id="srow-<?= $item['id_item'] ?>"><?= number_format($item['sous_total'],0,'',' ') ?> DA</span>
                    </div>
                    <?php endforeach; ?>

                    <div class="stotal">
                        <span class="tl"><?= $p['total_lbl'] ?></span>
                        <div class="tamt">
                            <span id="grand-total"><?= number_format($total_final,0,'',' ') ?></span><span>DA</span>
                        </div>
                    </div>

                    <form action="checkout.php" method="POST">
                        <input type="hidden" name="total_global" id="input-total-global" value="<?= $total_final ?>">
                        <button type="submit" class="btn-confirm">
                            <i class="fa fa-lock"></i> <?= $p['btn_confirm'] ?>
                        </button>
                    </form>
                    <p class="snote"><i class="fa fa-shield-halved"></i> <?= $p['secure_note'] ?></p>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<script>
const SWAL = {
    removeTitle : <?= json_encode($p['swal_remove_title']) ?>,
    removeText  : <?= json_encode($p['swal_remove_text']) ?>,
    removeYes   : <?= json_encode($p['swal_remove_yes']) ?>,
    removeNo    : <?= json_encode($p['swal_remove_no']) ?>,
    warnTitle   : <?= json_encode($p['swal_warn_title']) ?>,
    errTitle    : <?= json_encode($p['swal_err_title']) ?>,
    errDel      : <?= json_encode($p['swal_err_del']) ?>,
};

function fmt(n){
    return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g,' ');
}

function recalcGrandTotal(){
    let t=0;
    document.querySelectorAll('.ci').forEach(r=>{
        const id=r.id.replace('row-','');
        t+=parseInt(document.getElementById('qty-'+id)?.innerText||0)*parseFloat(r.dataset.prix||0);
    });
    const g=document.getElementById('grand-total');
    const i=document.getElementById('input-total-global');
    if(!g)return;
    g.style.opacity='0';
    setTimeout(()=>{ g.innerText=fmt(t); g.style.transition='opacity .12s'; g.style.opacity='1'; if(i)i.value=t; },120);
}

function updateQuantity(idItem, delta){
    const qEl  = document.getElementById('qty-'+idItem);
    const subEl= document.getElementById('sub-'+idItem);
    const sEl  = document.getElementById('srow-'+idItem);
    const row  = document.getElementById('row-'+idItem);
    const prix = parseFloat(row?.dataset.prix||0);
    const cur  = parseInt(qEl.innerText);
    const nq   = cur+delta;
    if(nq<1)return;

    const btns=row.querySelectorAll('.sBtn');
    btns.forEach(b=>b.disabled=true);

    qEl.innerText=nq;
    const ns=fmt(nq*prix);
    [subEl,sEl].forEach((el,i)=>{
        if(!el)return;
        el.style.opacity='0';
        setTimeout(()=>{ el.innerText=ns+(i===1?' DA':''); el.style.transition='opacity .1s'; el.style.opacity='1'; },100);
    });
    recalcGrandTotal();

    fetch('./update_quantite_ajax.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`id_item=${idItem}&qty=${nq}`
    })
    .then(async r=>{const t=await r.text();try{return JSON.parse(t);}catch{throw new Error(SWAL.errTitle);}})
    .then(d=>{
        if(!d.success){
            qEl.innerText=cur;
            const rs=fmt(cur*prix);
            if(subEl)subEl.innerText=rs; if(sEl)sEl.innerText=rs+' DA';
            recalcGrandTotal();
            Swal.fire({icon:'warning',title:SWAL.warnTitle,text:d.message,timer:2400,showConfirmButton:false});
        }
    })
    .catch(e=>{
        qEl.innerText=cur;
        const rs=fmt(cur*prix);
        if(subEl)subEl.innerText=rs; if(sEl)sEl.innerText=rs+' DA';
        recalcGrandTotal();
        Swal.fire({icon:'error',title:SWAL.errTitle,text:e.message,timer:2400,showConfirmButton:false});
    })
    .finally(()=>btns.forEach(b=>b.disabled=false));
}

function confirmRemove(idItem){
    Swal.fire({
        title: SWAL.removeTitle,
        text:  SWAL.removeText,
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#2C1F0F', cancelButtonColor: '#8A7A65',
        confirmButtonText: SWAL.removeYes, cancelButtonText: SWAL.removeNo
    }).then(r=>{
        if(!r.isConfirmed)return;
        const row =document.getElementById('row-'+idItem);
        const wrap=document.getElementById('srow-wrap-'+idItem);
        fetch(`delete_from_cart.php?id=${idItem}&ajax=1`)
        .then(async res=>{const t=await res.text();try{return JSON.parse(t);}catch{return{success:true};}})
        .then(d=>{
            if(d.success!==false){
                row.style.transition='opacity .28s,max-height .36s,padding .36s';
                row.style.overflow='hidden'; row.style.maxHeight=row.offsetHeight+'px';
                requestAnimationFrame(()=>{ row.style.opacity='0'; row.style.maxHeight='0'; row.style.padding='0'; });
                if(wrap)wrap.style.display='none';
                setTimeout(()=>{ row.remove(); recalcGrandTotal(); if(!document.querySelector('.ci'))location.reload(); },380);
            } else {
                Swal.fire({icon:'error',title:SWAL.errTitle,text:d.message||SWAL.errDel});
            }
        })
        .catch(()=>{ window.location.href=`delete_from_cart.php?id=${idItem}`; });
    });
}
</script>

<?php include_once "../includes/footer.php"; ?>
</body>
</html>