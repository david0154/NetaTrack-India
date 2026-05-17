/**
 * NetaTrack India — Main JS
 * Pure vanilla JS, no jQuery needed
 */

'use strict';

// ---- Toast notifications ----------------------------------------
const Toast = {
  _container() {
    let el = document.getElementById('toast-container');
    if (!el) {
      el = document.createElement('div');
      el.id = 'toast-container';
      document.body.appendChild(el);
    }
    return el;
  },
  show(msg, type = 'info', duration = 3500) {
    const icons = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<span>${icons[type] || ''}</span><span>${msg}</span>`;
    this._container().appendChild(t);
    setTimeout(() => {
      t.style.opacity = '0';
      t.style.transform = 'translateX(20px)';
      t.style.transition = 'all .3s';
      setTimeout(() => t.remove(), 300);
    }, duration);
  },
  success(m) { this.show(m, 'success'); },
  error(m)   { this.show(m, 'error'); },
  info(m)    { this.show(m, 'info'); },
};
window.Toast = Toast;

// ---- Tabs -------------------------------------------------------
function initTabs() {
  document.querySelectorAll('.tab-list').forEach(list => {
    list.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = btn.dataset.tab;
        const parent = btn.closest('.tab-wrap') || document;
        parent.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        parent.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        const pane = parent.querySelector(`#${target}`);
        if (pane) pane.classList.add('active');
      });
    });
  });
}

// ---- Modals -----------------------------------------------------
function openModal(id) {
  const m = document.getElementById(id);
  if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
  const m = document.getElementById(id);
  if (m) { m.style.display = 'none'; document.body.style.overflow = ''; }
}
// Close on backdrop click
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-backdrop')) {
    e.target.style.display = 'none';
    document.body.style.overflow = '';
  }
});
// Close on Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-backdrop').forEach(m => {
      m.style.display = 'none';
    });
    document.body.style.overflow = '';
  }
});
window.openModal  = openModal;
window.closeModal = closeModal;

// ---- Confirm delete helper --------------------------------------
function confirmDelete(url, name) {
  if (confirm(`Delete "${name}"? This cannot be undone.`)) {
    window.location.href = url;
  }
}
window.confirmDelete = confirmDelete;

// ---- Fetch JSON helper ------------------------------------------
async function apiFetch(url, data = null) {
  try {
    const opts = data
      ? { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) }
      : { method:'GET' };
    const r = await fetch(url, opts);
    return await r.json();
  } catch(e) {
    Toast.error('Network error: ' + e.message);
    return null;
  }
}
window.apiFetch = apiFetch;

// ---- Score bar animate ------------------------------------------
function animateScoreBars() {
  document.querySelectorAll('.progress-bar[data-value]').forEach(bar => {
    const val = parseInt(bar.dataset.value) || 0;
    bar.style.width = '0%';
    setTimeout(() => { bar.style.width = val + '%'; }, 100);
    // Color coding
    if (val >= 70) bar.classList.add('green');
    else if (val >= 40) bar.classList.add('orange');
    else bar.classList.add('red');
  });
}

// ---- Active sidebar link ----------------------------------------
function markActiveSidebarLink() {
  const path = window.location.pathname;
  document.querySelectorAll('.sidebar-link').forEach(a => {
    if (a.href && a.href.includes(path) && path !== '/') {
      a.classList.add('active');
    }
  });
}

// ---- Inline search/filter table ---------------------------------
function initTableSearch() {
  document.querySelectorAll('[data-search-table]').forEach(input => {
    const tableId = input.dataset.searchTable;
    const table   = document.getElementById(tableId);
    if (!table) return;
    input.addEventListener('input', () => {
      const q = input.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });
}

// ---- Copy to clipboard ------------------------------------------
function copyText(text, label) {
  navigator.clipboard.writeText(text).then(() => {
    Toast.success((label || 'Text') + ' copied!');
  });
}
window.copyText = copyText;

// ---- Score ring color -------------------------------------------
function colorScoreRings() {
  document.querySelectorAll('.score-ring[data-score]').forEach(el => {
    const s = parseInt(el.dataset.score) || 0;
    if (s < 40) el.classList.add('red');
    else if (s < 70) el.classList.add('orange');
    el.textContent = s;
  });
}

// ---- AI Generate Bio button ------------------------------------
async function generateBio(leaderId) {
  const btn = document.querySelector(`[onclick*="generateBio(${leaderId})"]`);
  if (btn) { btn.textContent = '⏳ Generating...'; btn.disabled = true; }
  const d = await apiFetch('/api/ai/summarise', { leader_id: leaderId });
  if (d && d.summary) {
    Toast.success('AI Bio generated!');
    const bioEl = document.getElementById('leader-bio');
    if (bioEl) bioEl.value = d.summary;
  } else {
    Toast.error('AI unavailable. Add an API key in Settings.');
  }
  if (btn) { btn.textContent = '🤖 Generate AI Bio'; btn.disabled = false; }
}
window.generateBio = generateBio;

// ---- Push API test ----------------------------------------------
async function testPushConnection(url, token) {
  Toast.info('Testing connection...');
  const d = await apiFetch(`${url}/api/v1/ping`, null);
  if (d) Toast.success('✓ Connected to ' + url);
  else Toast.error('Connection failed. Check URL and token.');
}
window.testPushConnection = testPushConnection;

// ---- Init -------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
  initTabs();
  markActiveSidebarLink();
  animateScoreBars();
  initTableSearch();
  colorScoreRings();
});
