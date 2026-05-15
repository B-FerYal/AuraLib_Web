/**
 * admin_search.js — AuraLib Admin Live Search
 * ─────────────────────────────────────────────────────────────────
 * يتحمل فقط إذا المستخدم admin
 * يتحقق من data-user-role="admin" في <body> أو <html>
 *
 * الإضافة في header.php تاع الـ admin:
 *   <body data-user-role="<?= $_SESSION['role'] ?? 'client' ?>">
 *   <script src="/MEMOIR/assets/js/admin_search.js" defer></script>
 */

(() => {
  'use strict';

  /* ── Guard: شغّل فقط إذا كان المستخدم admin ──────────────────
     يتحقق من data-user-role في body أو html أو meta[name=user-role]
     إذا ما لقى شيء يوقف مباشرة — ما يعمل أي شيء
  ─────────────────────────────────────────────────────────────── */
  const role =
    document.body?.dataset?.userRole ||
    document.documentElement?.dataset?.userRole ||
    document.querySelector('meta[name="user-role"]')?.content ||
    '';

  if (role !== 'admin') return; // مش admin → خروج فوري بدون أي تأثير

  /* ── Config ──────────────────────────────────────────────────── */
  const SEARCH_URL  = '/MEMOIR/client/search_engine.php';
  const MIN_CHARS   = 2;
  const DEBOUNCE_MS = 280;
  const MAX_PER_CAT = 5;

  const LABEL = {
    documents : 'Documents',
    users     : 'Utilisateurs',
    loans     : 'Emprunts',
  };

  const ICON = {
    documents : '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 4.5a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-11Zm9 0a1 1 0 0 0-1-1h-5a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-11Z"/></svg>',
    users     : '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm-7 7a7 7 0 1 1 14 0H3Z"/></svg>',
    loans     : '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v4.5c0 .199.079.39.22.53l2.25 2.25a.75.75 0 1 0 1.06-1.06l-2.03-2.03v-4.19Z" clip-rule="evenodd"/></svg>',
  };

  /* ── Inject CSS ──────────────────────────────────────────────── */
  const style = document.createElement('style');
  style.textContent = `
    .adm-search-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }
    .adm-search-form {
      display: flex; align-items: center; gap: 0;
      background: rgba(255,255,255,.08);
      border: 1.5px solid rgba(255,255,255,.15);
      border-radius: 10px;
      padding: 0 4px 0 12px;
      transition: border-color .2s, background .2s, box-shadow .2s;
    }
    .adm-search-form:focus-within {
      background: rgba(255,255,255,.14);
      border-color: var(--gold, #C9963A);
      box-shadow: 0 0 0 3px rgba(201,150,58,.18);
    }
    .adm-search-input {
      background: transparent; border: none; outline: none;
      color: #fff; font-size: 13.5px; width: 220px;
      padding: 8px 0; font-family: inherit;
    }
    .adm-search-input::placeholder { color: rgba(255,255,255,.45); }
    .adm-search-btn {
      background: none; border: none; cursor: pointer;
      padding: 6px 8px; color: rgba(255,255,255,.55);
      display: flex; align-items: center; transition: color .15s;
    }
    .adm-search-btn:hover { color: var(--gold, #C9963A); }
    .adm-search-btn svg  { width: 17px; height: 17px; }

    /* Dropdown */
    .adm-search-dropdown {
      position: absolute; top: calc(100% + 8px); left: 0;
      width: 440px; max-height: 520px; overflow-y: auto;
      background: #1a1208;
      border: 1.5px solid rgba(201,150,58,.3);
      border-radius: 14px;
      box-shadow: 0 20px 60px rgba(0,0,0,.55);
      z-index: 9999;
      scrollbar-width: thin;
      scrollbar-color: rgba(201,150,58,.3) transparent;
      animation: sdrop-in .18s ease;
    }
    @keyframes sdrop-in {
      from { opacity:0; transform:translateY(-6px); }
      to   { opacity:1; transform:translateY(0); }
    }
    .adm-search-dropdown::-webkit-scrollbar { width: 4px; }
    .adm-search-dropdown::-webkit-scrollbar-thumb { background: rgba(201,150,58,.35); border-radius: 4px; }

    .adm-sd-header {
      display: flex; align-items: center; gap: 8px;
      padding: 11px 16px 8px;
      font-size: 11px; font-weight: 700; letter-spacing: .08em;
      text-transform: uppercase; color: var(--gold, #C9963A);
      border-bottom: 1px solid rgba(255,255,255,.06);
    }
    .adm-sd-header svg { width: 14px; height: 14px; flex-shrink: 0; }

    .adm-sd-item {
      display: flex; align-items: center; gap: 12px;
      padding: 10px 16px; text-decoration: none;
      color: rgba(255,255,255,.85); font-size: 13px;
      transition: background .15s; cursor: pointer;
      border: none; width: 100%; background: none; text-align: left;
    }
    .adm-sd-item:hover { background: rgba(201,150,58,.1); }
    .adm-sd-item:last-of-type { border-bottom: 1px solid rgba(255,255,255,.06); }

    .adm-sd-avatar {
      width: 36px; height: 36px; border-radius: 8px;
      background: rgba(255,255,255,.07);
      display: flex; align-items: center; justify-content: center;
      font-size: 14px; font-weight: 700; color: var(--gold, #C9963A);
      flex-shrink: 0; overflow: hidden;
    }
    .adm-sd-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .adm-sd-main    { flex: 1; min-width: 0; }
    .adm-sd-title   { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .adm-sd-sub     { font-size: 11.5px; color: rgba(255,255,255,.45); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .adm-sd-badge {
      font-size: 10px; font-weight: 700;
      padding: 2px 7px; border-radius: 20px; flex-shrink: 0;
    }
    .badge-active    { background: rgba(34,197,94,.18);  color: #4ade80; }
    .badge-suspended { background: rgba(239,68,68,.18);  color: #f87171; }
    .badge-encours   { background: rgba(234,179,8,.18);  color: #facc15; }
    .badge-rendu     { background: rgba(34,197,94,.18);  color: #4ade80; }
    .badge-refused   { background: rgba(239,68,68,.18);  color: #f87171; }
    .badge-achat     { background: rgba(201,150,58,.18); color: var(--gold,#C9963A); }
    .badge-emprunt   { background: rgba(99,102,241,.18); color: #a5b4fc; }
    .badge-both      { background: rgba(255,255,255,.1); color: rgba(255,255,255,.7); }

    .adm-sd-actions { display: flex; gap: 6px; flex-shrink: 0; }
    .adm-sd-act-btn {
      display: flex; align-items: center; gap: 4px;
      padding: 4px 9px; border-radius: 6px;
      border: 1.5px solid rgba(255,255,255,.14);
      font-size: 11px; font-weight: 600;
      color: rgba(255,255,255,.8);
      background: rgba(255,255,255,.05);
      text-decoration: none;
      transition: background .15s, border-color .15s, color .15s;
      cursor: pointer;
    }
    .adm-sd-act-btn:hover { background: rgba(201,150,58,.2); border-color: var(--gold,#C9963A); color: #fff; }
    .adm-sd-act-btn svg   { width: 12px; height: 12px; }

    .adm-sd-empty {
      padding: 28px 20px; text-align: center;
      color: rgba(255,255,255,.35); font-size: 13px;
    }
    .adm-sd-empty svg { width: 28px; height: 28px; margin: 0 auto 8px; display: block; opacity: .35; }
    .adm-sd-footer {
      padding: 10px 16px; text-align: center;
      font-size: 12px; color: rgba(255,255,255,.3);
    }
    .adm-sd-spinner {
      display: flex; align-items: center; justify-content: center;
      gap: 8px; padding: 22px;
      color: rgba(255,255,255,.4); font-size: 13px;
    }
    .spin-ring {
      width: 18px; height: 18px;
      border: 2px solid rgba(201,150,58,.2);
      border-top-color: var(--gold,#C9963A);
      border-radius: 50%;
      animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 768px) {
      .adm-search-input { width: 150px; }
      .adm-search-dropdown { width: 96vw; left: 50%; transform: translateX(-50%); }
    }
  `;
  document.head.appendChild(style);

  /* ── Find navbar slot ────────────────────────────────────────── */
  function findNavbarSlot() {
    return (
      document.querySelector('.nav-right')            ||
      document.querySelector('.navbar-right')         ||
      document.querySelector('#admin-navbar .nav-actions') ||
      document.querySelector('nav .nav-links')?.parentElement ||
      document.querySelector('nav')                   ||
      document.body
    );
  }

  /* ── Build widget DOM ────────────────────────────────────────── */
  function buildWidget() {
    const wrap = document.createElement('div');
    wrap.className = 'adm-search-wrap';
    wrap.innerHTML = `
      <div class="adm-search-form" role="search">
        <input class="adm-search-input"
               id="adm-search-input"
               type="search"
               placeholder="Rechercher docs, utilisateurs, emprunts…"
               autocomplete="off"
               aria-label="Recherche globale admin"
               aria-controls="adm-search-dropdown"
               aria-expanded="false">
        <button class="adm-search-btn" type="button" aria-label="Lancer la recherche">
          <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="8.5" cy="8.5" r="5.5"/>
            <line x1="13" y1="13" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
      <div class="adm-search-dropdown" id="adm-search-dropdown" hidden role="listbox"></div>
    `;
    return wrap;
  }

  /* ── Helpers ─────────────────────────────────────────────────── */
  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g,
      c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }
  function highlight(text, q) {
    if (!q) return esc(text);
    const rx = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    return esc(text).replace(rx, '<mark style="background:rgba(201,150,58,.35);color:#fff;border-radius:2px;padding:0 1px">$1</mark>');
  }
  function statusBadge(statut) {
    const map = {
      'active'    : ['active',   'Actif'],
      'suspended' : ['suspended','Suspendu'],
      'en_cours'  : ['encours',  'En cours'],
      'rendu'     : ['rendu',    'Rendu'],
      'refusée'   : ['refused',  'Refusée'],
      'en attente': ['encours',  'Attente'],
    };
    const [cls, lbl] = map[statut] ?? ['encours', statut];
    return `<span class="adm-sd-badge badge-${cls}">${esc(lbl)}</span>`;
  }
  function dispoBadge(dispo) {
    const map = { achat:'badge-achat', emprunt:'badge-emprunt', both:'badge-both' };
    const lbl = { achat:'Achat', emprunt:'Emprunt', both:'Achat + Emprunt' };
    return `<span class="adm-sd-badge ${map[dispo] ?? 'badge-both'}">${lbl[dispo] ?? dispo}</span>`;
  }

  /* ── Render dropdown ─────────────────────────────────────────── */
  function renderDropdown(data, q) {
    const drop = document.getElementById('adm-search-dropdown');
    if (!drop) return;

    const { documents = [], users = [], loans = [] } = data.results ?? {};
    const total = data.total ?? 0;

    if (total === 0) {
      drop.innerHTML = `
        <div class="adm-sd-empty">
          <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM2 8a6 6 0 1 1 10.89 3.476l4.817 4.817a1 1 0 0 1-1.414 1.414l-4.816-4.816A6 6 0 0 1 2 8Z" clip-rule="evenodd"/>
          </svg>
          Aucun résultat pour « ${esc(q)} »
        </div>`;
      return;
    }

    let html = '';

    /* Documents */
    if (documents.length) {
      html += `<div class="adm-sd-header">${ICON.documents} ${LABEL.documents} <span style="margin-left:auto;opacity:.5">${documents.length}</span></div>`;
      documents.slice(0, MAX_PER_CAT).forEach(d => {
        const img = d.image ? `/MEMOIR/uploads/${esc(d.image)}` : '';
        html += `
          <div class="adm-sd-item">
            <div class="adm-sd-avatar">${img
              ? `<img src="${img}" onerror="this.style.display='none'">`
              : esc(d.titre.charAt(0).toUpperCase())}</div>
            <div class="adm-sd-main">
              <div class="adm-sd-title">${highlight(d.titre, q)}</div>
              <div class="adm-sd-sub">${esc(d.auteur)}${d.annee ? ' · ' + esc(d.annee) : ''} · ${esc(d.type)}</div>
            </div>
            ${dispoBadge(d.dispo)}
            <div class="adm-sd-actions">
              <a href="${esc(d.edit_url)}" class="adm-sd-act-btn">
                <svg viewBox="0 0 16 16" fill="currentColor"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg>
                Modifier
              </a>
              <a href="${esc(d.detail_url)}" class="adm-sd-act-btn">
                <svg viewBox="0 0 16 16" fill="currentColor"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/></svg>
                Voir
              </a>
            </div>
          </div>`;
      });
    }

    /* Users */
    if (users.length) {
      html += `<div class="adm-sd-header">${ICON.users} ${LABEL.users} <span style="margin-left:auto;opacity:.5">${users.length}</span></div>`;
      users.slice(0, MAX_PER_CAT).forEach(u => {
        html += `
          <div class="adm-sd-item">
            <div class="adm-sd-avatar" style="background:rgba(201,150,58,.15);">${esc((u.name||'?').charAt(0).toUpperCase())}</div>
            <div class="adm-sd-main">
              <div class="adm-sd-title">${highlight(u.name, q)}</div>
              <div class="adm-sd-sub">${highlight(u.email, q)}${u.phone ? ' · ' + esc(u.phone) : ''}</div>
            </div>
            ${statusBadge(u.status)}
            <div class="adm-sd-actions">
              <a href="${esc(u.profile_url)}" class="adm-sd-act-btn">
                <svg viewBox="0 0 16 16" fill="currentColor"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/></svg>
                Profil
              </a>
            </div>
          </div>`;
      });
    }

    /* Loans */
    if (loans.length) {
      html += `<div class="adm-sd-header">${ICON.loans} ${LABEL.loans} <span style="margin-left:auto;opacity:.5">${loans.length}</span></div>`;
      loans.slice(0, MAX_PER_CAT).forEach(l => {
        html += `
          <div class="adm-sd-item">
            <div class="adm-sd-avatar" style="background:rgba(99,102,241,.12);color:#a5b4fc">
              <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v4.5c0 .199.079.39.22.53l2.25 2.25a.75.75 0 1 0 1.06-1.06l-2.03-2.03v-4.19Z" clip-rule="evenodd"/></svg>
            </div>
            <div class="adm-sd-main">
              <div class="adm-sd-title">${highlight(l.book_title, q)}</div>
              <div class="adm-sd-sub">${highlight(l.user_name, q)} · ${esc(l.date_debut ?? '')} → ${esc(l.date_retour ?? '?')}</div>
            </div>
            ${statusBadge(l.statut)}
            <div class="adm-sd-actions">
              <a href="${esc(l.manage_url)}" class="adm-sd-act-btn">
                <svg viewBox="0 0 16 16" fill="currentColor"><path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/></svg>
                Gérer
              </a>
            </div>
          </div>`;
      });
    }

    html += `<div class="adm-sd-footer">Entrée = recherche complète · ${total} résultat${total > 1 ? 's' : ''}</div>`;
    drop.innerHTML = html;
  }

  /* ── Init ────────────────────────────────────────────────────── */
  function init() {
    const slot   = findNavbarSlot();
    const widget = buildWidget();
    slot.insertBefore(widget, slot.querySelector('.dark-toggle') || slot.firstChild);

    const input = document.getElementById('adm-search-input');
    const drop  = document.getElementById('adm-search-dropdown');
    if (!input || !drop) return;

    let timer = null;
    let ctrl  = null;

    const showDrop = () => { drop.hidden = false; input.setAttribute('aria-expanded','true'); };
    const hideDrop = () => { drop.hidden = true;  input.setAttribute('aria-expanded','false'); };

    function showSpinner() {
      drop.innerHTML = `<div class="adm-sd-spinner"><div class="spin-ring"></div> Recherche en cours…</div>`;
      showDrop();
    }

    async function doSearch(q) {
      if (ctrl) ctrl.abort();
      ctrl = new AbortController();
      showSpinner();
      try {
        const res  = await fetch(`${SEARCH_URL}?scope=admin&q=${encodeURIComponent(q)}`,
                                 { signal: ctrl.signal, credentials: 'same-origin' });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        renderDropdown(data, q);
        showDrop();
      } catch (e) {
        if (e.name === 'AbortError') return;
        drop.innerHTML = `<div class="adm-sd-empty">Erreur lors de la recherche (${e.message}).</div>`;
        showDrop();
      }
    }

    input.addEventListener('input', () => {
      clearTimeout(timer);
      const q = input.value.trim();
      if (q.length < MIN_CHARS) { hideDrop(); return; }
      timer = setTimeout(() => doSearch(q), DEBOUNCE_MS);
    });

    input.addEventListener('keydown', e => {
      if (e.key === 'Enter') {
        e.preventDefault();
        const q = input.value.trim();
        if (q.length >= MIN_CHARS)
          window.location.href = `/MEMOIR/client/library.php?search=${encodeURIComponent(q)}`;
      }
      if (e.key === 'Escape') hideDrop();
    });

    document.addEventListener('click', e => {
      if (!widget.contains(e.target)) hideDrop();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();