/**
 * ShopVerse — Core Client JS
 * Theme controller, Expandable search, Toast injector, Back-to-Top, Loading overlay.
 * ──────────────────────────────────────────────────────────────────────────
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  initLoadingOverlay();
  initThemeSystem();
  initExpandableSearch();
  initBackToTop();
});

/* ═══════════════════════════════════════════════════════════════════════════
   §1  LOADING OVERLAY
   ═══════════════════════════════════════════════════════════════════════════ */

function initLoadingOverlay() {
  const overlay = document.getElementById('loading-overlay');
  if (!overlay) return;
  
  // Fade out smoothly
  window.addEventListener('load', () => {
    overlay.classList.add('hidden');
  });
  
  // Safety timeout in case load event takes too long
  setTimeout(() => {
    overlay.classList.add('hidden');
  }, 1500);
}

/* ═══════════════════════════════════════════════════════════════════════════
   §2  THEME SYSTEM (Dark / Light Mode)
   ═══════════════════════════════════════════════════════════════════════════ */

function initThemeSystem() {
  const root = document.documentElement;
  const toggles = document.querySelectorAll('.theme-toggle');
  
  if (toggles.length === 0) return;
  
  // Determine initial theme
  const savedTheme = localStorage.getItem('sv-theme');
  const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const initialTheme = savedTheme || (systemPrefersDark ? 'dark' : 'light');
  
  applyTheme(initialTheme);
  
  toggles.forEach(toggle => {
    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      const currentTheme = root.getAttribute('data-bs-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      applyTheme(newTheme);
    });
  });
}

function applyTheme(theme) {
  const root = document.documentElement;
  root.setAttribute('data-bs-theme', theme);
  localStorage.setItem('sv-theme', theme);
  
  // Update icons and labels in all headers
  document.querySelectorAll('.theme-toggle').forEach(btn => {
    const moon = btn.querySelector('.theme-icon-dark');
    const sun = btn.querySelector('.theme-icon-light');
    const labelDark = btn.querySelector('.theme-label-dark');
    const labelLight = btn.querySelector('.theme-label-light');
    
    if (theme === 'dark') {
      moon?.classList.add('d-none');
      sun?.classList.remove('d-none');
      labelDark?.classList.add('d-none');
      labelLight?.classList.remove('d-none');
    } else {
      moon?.classList.remove('d-none');
      sun?.classList.add('d-none');
      labelDark?.classList.remove('d-none');
      labelLight?.classList.add('d-none');
    }
  });
}

/* ═══════════════════════════════════════════════════════════════════════════
   §3  EXPANDABLE NAVBAR SEARCH
   ═══════════════════════════════════════════════════════════════════════════ */

function initExpandableSearch() {
  const toggle = document.querySelector('.search-toggle');
  const form = document.querySelector('.nav-search-form');
  const input = form?.querySelector('input');
  
  if (!toggle || !form) return;
  
  toggle.addEventListener('click', (e) => {
    e.stopPropagation();
    form.classList.toggle('active');
    if (form.classList.contains('active')) {
      input?.focus();
    }
  });
  
  // Close search when clicking outside
  document.addEventListener('click', (e) => {
    if (!form.contains(e.target) && e.target !== toggle) {
      form.classList.remove('active');
    }
  });
  
  // Esc key closure
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      form.classList.remove('active');
    }
  });
}

/* ═══════════════════════════════════════════════════════════════════════════
   §4  BACK TO TOP BUTTON
   ═══════════════════════════════════════════════════════════════════════════ */

function initBackToTop() {
  const btn = document.getElementById('back-to-top');
  if (!btn) return;
  
  window.addEventListener('scroll', () => {
    if (window.scrollY > 400) {
      btn.classList.add('visible');
    } else {
      btn.classList.remove('visible');
    }
  });
  
  btn.addEventListener('click', () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
}

/* ═══════════════════════════════════════════════════════════════════════════
   §5  TOAST NOTIFICATIONS (Dynamic Injection)
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Display a dynamic premium toast alert.
 * @param {string} message 
 * @param {string} type — success, error, info, warning
 * @param {number} [duration=3500] 
 */
function showToast(message, type = 'info', duration = 3500) {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  
  const toast = document.createElement('div');
  toast.className = `sv-toast ${type}`;
  
  // Determine standard icons
  const icons = {
    success: 'fas fa-check-circle text-success',
    error: 'fas fa-exclamation-circle text-danger',
    warning: 'fas fa-exclamation-triangle text-warning',
    info: 'fas fa-info-circle text-primary'
  };
  const iconClass = icons[type] || icons.info;
  
  toast.innerHTML = `
    <i class="${iconClass}" style="font-size:1.25rem;"></i>
    <div class="toast-content" style="font-size:0.9rem;font-weight:500;">${message}</div>
  `;
  
  container.appendChild(toast);
  
  // Trigger dismiss slide-out
  setTimeout(() => {
    toast.style.transform = 'translateX(120%) scale(0.9)';
    toast.style.opacity = '0';
    toast.addEventListener('transitionend', () => toast.remove());
    // Backup clean
    setTimeout(() => toast.remove(), 400);
  }, duration);
}

// Export for global access
window.showToast = showToast;
