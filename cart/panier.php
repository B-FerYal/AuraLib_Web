<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . "/../includes/db.php";

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

include_once "../includes/header.php";
$id_user = (int)$_SESSION['id_user'];

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
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon Panier — AuraLib</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
/* ── AuraLib design tokens (matched from screenshots) ────────── */
:root {
    --bg:         #EDE8DC;
    --card:       #FFFFFF;
    --navbar:     #2C1F0F;
    --gold:       #C8A96E;
    --gold-deep:  #A8893E;
    --gold-pale:  #F5EDD8;
    --text:       #2C1F0F;
    --muted:      #8A7A65;
    --border:     #D9D0BF;
    --bdgold:     rgba(200,169,110,.3);
    --danger:     #B83232;
    --dpale:      #FDECEA;
    --r:          12px;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--bg); font-family: 'Inter', sans-serif; color: var(--text); min-height: 100vh; }

/* ── Wrapper ──────────────────────────────────────────────────── */
.pw { max-width: 1080px; margin: 0 auto; padding: 108px 20px 80px; }

/* ── Breadcrumb ───────────────────────────────────────────────── */
.bc {
    display: flex; align-items: center; gap: 7px;
    font-size: 13px; color: var(--muted); margin-bottom: 28px;
    opacity: 0; animation: up .35s ease .05s forwards;
}
.bc a { color: var(--muted); text-decoration: none; }
.bc a:hover { color: var(--gold-deep); }
.bc i.sep { font-size: 9px; color: var(--border); }

/* ── Page title ───────────────────────────────────────────────── */
.ptitle { margin-bottom: 28px; opacity: 0; animation: up .4s ease .1s forwards; }
.ptitle h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(26px, 3.5vw, 38px); font-weight: 700; line-height: 1.1;
    color: var(--navbar);
}
.ptitle h1 em { font-style: italic; color: var(--gold-deep); }
.ptitle p { margin-top: 5px; font-size: 14px; color: var(--muted); }
.pill {
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--gold-pale); color: var(--gold-deep);
    border: 1px solid var(--bdgold);
    font-size: 11px; font-weight: 600; padding: 1px 9px; border-radius: 20px;
    margin-left: 10px; vertical-align: middle;
}

/* ── Two-col grid ─────────────────────────────────────────────── */
.pgrid { display: grid; grid-template-columns: 1fr 296px; gap: 20px; align-items: start; }
@media(max-width:768px){ .pgrid{ grid-template-columns:1fr; } }

/* ── Shared panel base ────────────────────────────────────────── */
.panel {
    background: var(--card); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden;
    box-shadow: 0 2px 14px rgba(44,31,15,.06);
}
.panel-hd {
    padding: 13px 20px; border-bottom: 1px solid var(--border);
    background: #FAFAF7;
    display: flex; justify-content: space-between; align-items: center;
}
.panel-hd span {
    font-size: 11px; font-weight: 600; letter-spacing: .12em;
    text-transform: uppercase; color: var(--muted);
}

/* ── Items panel ──────────────────────────────────────────────── */
.ip { opacity: 0; animation: up .45s ease .15s forwards; }

/* ── Empty state ──────────────────────────────────────────────── */
.empty { padding: 60px 24px; text-align: center; }
.empty-ic {
    width: 60px; height: 60px; border-radius: 50%;
    background: var(--gold-pale); color: var(--gold-deep);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; margin: 0 auto 18px;
}
.empty h3 { font-family: 'Playfair Display', serif; font-size: 21px; margin-bottom: 7px; }
.empty p { color: var(--muted); font-size: 14px; margin-bottom: 22px; }
.btn-browse {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--navbar); color: #fff;
    padding: 10px 22px; border-radius: 8px; text-decoration: none;
    font-size: 13px; font-weight: 600; transition: background .18s, transform .15s;
}
.btn-browse:hover { background: var(--gold-deep); transform: translateY(-1px); }

/* ── Cart item row ────────────────────────────────────────────── */
.ci {
    display: grid; grid-template-columns: 70px 1fr auto;
    gap: 16px; align-items: center;
    padding: 17px 20px; border-bottom: 1px solid var(--border);
    transition: background .15s;
}
.ci:last-child { border-bottom: none; }
.ci:hover { background: #FDFCF7; }

/* Book cover */
.bthumb {
    width: 70px; height: 100px; border-radius: 7px;
    overflow: hidden; flex-shrink: 0;
    background: var(--bg);
    box-shadow: 2px 4px 12px rgba(44,31,15,.17), -1px 0 0 rgba(44,31,15,.07);
    position: relative;
}
.bthumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.bthumb::after {
    content: ''; position: absolute; top: 0; left: 0; bottom: 0; width: 5px;
    background: linear-gradient(to right, rgba(0,0,0,.13), transparent);
}

/* Info */
.ci-info { min-width: 0; }
.ci-name {
    font-family: 'Playfair Display', serif;
    font-size: 15.5px; font-weight: 600; color: var(--navbar);
    margin-bottom: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.ci-uprice { font-size: 12px; color: var(--muted); margin-bottom: 13px; }
.ci-uprice strong { color: var(--gold-deep); font-weight: 600; }

/* Quantity stepper */
.stepper {
    display: inline-flex; align-items: center;
    border: 1px solid var(--border); border-radius: 7px; overflow: hidden;
}
.sbtn {
    width: 30px; height: 30px; background: #F4F1EA; border: none;
    cursor: pointer; font-size: 15px; font-weight: 700; color: var(--navbar);
    display: flex; align-items: center; justify-content: center;
    transition: background .14s, color .14s; flex-shrink: 0;
}
.sBtn:first-child { border-right: 1px solid var(--border); }
.sBtn:last-child  { border-left:  1px solid var(--border); }
.sval {
    min-width: 36px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 600;
    border-left: 1px solid var(--border); border-right: 1px solid var(--border);
    background: #fff; color: var(--navbar);
}
.sBtn:hover:not(:disabled), .sBtn:hover:not(:disabled) { background: var(--gold); color: #fff; }
.sBtn:disabled { opacity: .35; cursor: not-allowed; }

/* Right side */
.ci-right {
    text-align: right; display: flex; flex-direction: column;
    align-items: flex-end; gap: 9px; min-width: 110px;
}
.ci-sub {
    font-family: 'Playfair Display', serif;
    font-size: 19px; font-weight: 700; color: var(--navbar);
}
.ci-sub small {
    font-family: 'Inter', sans-serif; font-size: 11px;
    font-weight: 400; color: var(--muted); margin-left: 2px;
}
.btn-rm {
    display: inline-flex; align-items: center; gap: 5px;
    background: none; border: 1px solid #E5C0C0; color: var(--danger);
    cursor: pointer; padding: 4px 11px; border-radius: 6px;
    font-size: 11px; font-weight: 600; font-family: 'Inter', sans-serif;
    letter-spacing: .04em; text-transform: uppercase;
    transition: all .15s;
}
.btn-rm:hover { background: var(--dpale); border-color: var(--danger); }

/* ── Summary panel ────────────────────────────────────────────── */
.sp { opacity: 0; animation: up .45s ease .2s forwards; position: sticky; top: 108px; }

.srow {
    display: flex; justify-content: space-between; align-items: center;
    padding: 9px 0; border-bottom: 1px solid var(--border); font-size: 13.5px;
}
.srow:last-of-type { border-bottom: none; }
.srow .sl { color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 160px; }
.srow .sv { font-weight: 600; color: var(--navbar); white-space: nowrap; }

.stotal {
    display: flex; justify-content: space-between; align-items: baseline;
    margin: 18px 0 18px; padding-top: 14px;
    border-top: 2px solid var(--border);
}
.stotal .tl { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .09em; color: var(--muted); }
.tamt {
    font-family: 'Playfair Display', serif;
    font-size: 26px; font-weight: 700; color: var(--navbar);
}
.tamt span { font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 400; color: var(--muted); margin-left: 2px; }

/* Confirm CTA — dark button matching site's style */
.btn-confirm {
    display: flex; align-items: center; justify-content: center; gap: 9px;
    width: 100%; background: var(--navbar); color: #fff;
    border: none; cursor: pointer; padding: 13px 18px; border-radius: 9px;
    font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 600;
    transition: background .18s, transform .14s, box-shadow .2s;
}
.btn-confirm:hover {
    background: var(--gold-deep); transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(168,137,62,.26);
}
.snote {
    text-align: center; font-size: 11px; color: var(--muted);
    margin-top: 11px; line-height: 1.6;
}
.snote i { color: var(--gold); }

/* ── Keyframes ────────────────────────────────────────────────── */
@keyframes up {
    from { opacity:0; transform: translateY(14px); }
    to   { opacity:1; transform: translateY(0); }
}

/* ── Mobile item ──────────────────────────────────────────────── */
@media(max-width:520px){
    .ci { grid-template-columns: 60px 1fr; }
    .ci-right { grid-column:1/-1; flex-direction:row; justify-content:space-between; min-width:0; }
}
</style>
</head>


<body>

<div class="pw">

   
    <div class="ptitle">
        <h1>
            Mon <em>Panier</em>
            <?php if (!empty($items_achat)): ?>
                <span class="pill"><?= count($items_achat) ?> article<?= count($items_achat)>1?'s':'' ?></span>
            <?php endif; ?>
        </h1>
        <p>Vérifiez vos sélections avant de confirmer la commande</p>
    </div>

    <?php if (empty($items_achat)): ?>
        <!-- Section l-Panier Khawi -->
        <div class="panel">
            <div class="panel-hd"><span>Articles</span><span>0 livre</span></div>
            <div class="empty">
                <div class="empty-ic"><i class="fa fa-shopping-cart"></i></div>
                <h3>Votre panier est vide</h3>
                <p>Vous n'avez ajouté aucun livre pour le moment.</p>
                <a href="../client/library.php" class="btn-browse">
                    <i class="fa fa-book-open"></i> Explorer le catalogue
                </a>
            </div>
        </div>

    <?php else: ?>
        <div class="pgrid">
            <!-- Items -->
            <div class="panel ip">
                <div class="panel-hd">
                    <span>Articles sélectionnés</span>
                    <span><?= count($items_achat) ?> livre<?= count($items_achat)>1?'s':'' ?></span>
                </div>

                <?php foreach ($items_achat as $idx => $item): 
                    $cover = file_exists("../uploads/{$item['id_doc']}.jpg") 
                             ? "../uploads/{$item['id_doc']}.jpg" 
                             : "../uploads/default.jpg";
                ?>
                <div class="ci" id="row-<?= $item['id_item'] ?>" data-prix="<?= (float)$item['prix'] ?>">
                    <div class="bthumb"><img src="<?= $cover ?>" alt="Couverture"></div>
                    <div class="ci-info">
                        <div class="ci-name"><?= htmlspecialchars($item['titre']) ?></div>
                        <div class="ci-uprice">Prix : <strong><?= number_format((float)$item['prix'],0,'', ' ') ?> DA</strong></div>
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
                <div class="panel-hd"><span>Récapitulatif</span></div>
                <div style="padding:18px 20px">
                    <?php foreach ($items_achat as $item): ?>
                    <div class="srow" id="srow-wrap-<?= $item['id_item'] ?>">
                        <span class="sl"><?= htmlspecialchars(mb_strimwidth($item['titre'],0,20,'…')) ?></span>
                        <span class="sv" id="srow-<?= $item['id_item'] ?>"><?= number_format($item['sous_total'],0,'',' ') ?> DA</span>
                    </div>
                    <?php endforeach; ?>

                    <div class="stotal">
                        <span class="tl">Total</span>
                        <div class="tamt">
                            <span id="grand-total"><?= number_format($total_final,0,'',' ') ?></span><span>DA</span>
                        </div>
                    </div>

                    <form action="checkout.php" method="POST">
                        <input type="hidden" name="total_global" id="input-total-global" value="<?= $total_final ?>">
                        <button type="submit" class="btn-confirm">
                            <i class="fa fa-lock"></i> Confirmer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>


<script>
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
    const sqEl = document.getElementById('sqty-'+idItem);
    const row  = document.getElementById('row-'+idItem);
    const prix = parseFloat(row?.dataset.prix||0);
    const cur  = parseInt(qEl.innerText);
    const nq   = cur+delta;
    if(nq<1)return;

    const btns=row.querySelectorAll('.sBtn');
    btns.forEach(b=>b.disabled=true);

    // Optimistic
    qEl.innerText=nq;
    if(sqEl)sqEl.innerText=nq;
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
    .then(async r=>{const t=await r.text();try{return JSON.parse(t);}catch{throw new Error('Erreur serveur');}})
    .then(d=>{
        if(!d.success){
            qEl.innerText=cur; if(sqEl)sqEl.innerText=cur;
            const rs=fmt(cur*prix);
            if(subEl)subEl.innerText=rs; if(sEl)sEl.innerText=rs+' DA';
            recalcGrandTotal();
            Swal.fire({icon:'warning',title:'Attention',text:d.message,timer:2400,showConfirmButton:false});
        }
    })
    .catch(e=>{
        qEl.innerText=cur; if(sqEl)sqEl.innerText=cur;
        const rs=fmt(cur*prix);
        if(subEl)subEl.innerText=rs; if(sEl)sEl.innerText=rs+' DA';
        recalcGrandTotal();
        Swal.fire({icon:'error',title:'Erreur',text:e.message,timer:2400,showConfirmButton:false});
    })
    .finally(()=>btns.forEach(b=>b.disabled=false));
}

function confirmRemove(idItem){
    Swal.fire({
        title:'Retirer ce livre ?',
        text:'Il sera supprimé de votre panier.',
        icon:'warning', showCancelButton:true,
        confirmButtonColor:'#2C1F0F', cancelButtonColor:'#8A7A65',
        confirmButtonText:'Oui, retirer', cancelButtonText:'Annuler'
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
                Swal.fire({icon:'error',title:'Erreur',text:d.message||'Impossible de supprimer.'});
            }
        })
        .catch(()=>{ window.location.href=`delete_from_cart.php?id=${idItem}`; });
    });
}
</script>

<?php include_once "../includes/footer.php"; ?>
</body>
</html>
