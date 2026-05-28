/**
 * ShopVerse — Dashboard Module
 * Admin sidebar, Chart.js analytics, data tables, user management,
 * profile editing, notifications & simulated real-time updates.
 * ──────────────────────────────────────────────────────────────────────────
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initCharts();
  initDataTables();
  initUserManagement();
  initProfileManagement();
  initNotifications();
  initRealtimeUpdates();
});

/* ═══════════════════════════════════════════════════════════════════════════
   §1  SIDEBAR TOGGLE
   ═══════════════════════════════════════════════════════════════════════════ */

function initSidebar() {
  const sidebar = document.querySelector('.sidebar, [data-sidebar]');
  const toggle = document.querySelector('.sidebar-toggle, [data-sidebar-toggle]');
  const overlay = document.querySelector('.sidebar-overlay, [data-sidebar-overlay]');
  if (!sidebar || !toggle) return;

  /* Restore state */
  const collapsed = localStorage.getItem('sv-sidebar') === 'collapsed';
  if (collapsed) sidebar.classList.add('collapsed');

  toggle.addEventListener('click', () => {
    const isMobile = window.innerWidth < 768;

    if (isMobile) {
      sidebar.classList.toggle('mobile-open');
      document.body.classList.toggle('sidebar-open');
    } else {
      sidebar.classList.toggle('collapsed');
      localStorage.setItem('sv-sidebar',
        sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded');
    }
  });

  /* Mobile overlay close */
  overlay?.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    document.body.classList.remove('sidebar-open');
  });

  /* Close sidebar on resize to desktop */
  window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) {
      sidebar.classList.remove('mobile-open');
      document.body.classList.remove('sidebar-open');
    }
  });
}

/* ═══════════════════════════════════════════════════════════════════════════
   §2  CHARTS (Chart.js)
   ═══════════════════════════════════════════════════════════════════════════ */

function initCharts() {
  if (typeof Chart === 'undefined') return;

  /* ── Design system accent colours ──────────────────────── */
  const colors = {
    primary:   '#6366f1',
    secondary: '#8b5cf6',
    accent:    '#ec4899',
    success:   '#22c55e',
    warning:   '#eab308',
    danger:    '#ef4444',
    info:      '#3b82f6',
  };

  const fontFamily = getComputedStyle(document.body).fontFamily || "'Inter', sans-serif";

  Chart.defaults.font.family = fontFamily;
  Chart.defaults.color = getComputedStyle(document.body).getPropertyValue('--text-primary')?.trim() || '#555';
  Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0,0,0,0.8)';
  Chart.defaults.plugins.tooltip.cornerRadius = 8;
  Chart.defaults.plugins.tooltip.padding = 12;

  /* ── Sales Line Chart ──────────────────────────────────── */
  const salesCtx = document.getElementById('sales-chart')?.getContext('2d');
  if (salesCtx) {
    const gradient = salesCtx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, hexToRgba(colors.primary, 0.25));
    gradient.addColorStop(1, hexToRgba(colors.primary, 0.01));

    new Chart(salesCtx, {
      type: 'line',
      data: {
        labels: getLast7Days(),
        datasets: [{
          label: 'Sales',
          data: [120, 190, 150, 220, 280, 250, 310],
          borderColor: colors.primary,
          backgroundColor: gradient,
          borderWidth: 2.5,
          pointBackgroundColor: '#fff',
          pointBorderColor: colors.primary,
          pointBorderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7,
          fill: true,
          tension: 0.4,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 1200, easing: 'easeInOutQuart' },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)' },
          },
          x: { grid: { display: false } },
        },
        plugins: { legend: { display: false } },
      },
    });
  }

  /* ── Revenue Bar Chart ─────────────────────────────────── */
  const revenueCtx = document.getElementById('revenue-chart')?.getContext('2d');
  if (revenueCtx) {
    new Chart(revenueCtx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
          label: 'Revenue ($)',
          data: [4200, 3800, 5100, 4800, 6200, 5800, 7100, 6800, 7500, 6900, 8200, 9100],
          backgroundColor: [
            colors.primary, colors.secondary, colors.accent, colors.success,
            colors.warning, colors.info, colors.primary, colors.secondary,
            colors.accent, colors.success, colors.warning, colors.danger,
          ].map(c => hexToRgba(c, 0.75)),
          borderColor: [
            colors.primary, colors.secondary, colors.accent, colors.success,
            colors.warning, colors.info, colors.primary, colors.secondary,
            colors.accent, colors.success, colors.warning, colors.danger,
          ],
          borderWidth: 1.5,
          borderRadius: 6,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 1000, easing: 'easeOutQuart' },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)' },
            ticks: { callback: v => '$' + v.toLocaleString() },
          },
          x: { grid: { display: false } },
        },
        plugins: { legend: { display: false } },
      },
    });
  }

  /* ── Category Doughnut Chart ───────────────────────────── */
  const catCtx = document.getElementById('category-chart')?.getContext('2d');
  if (catCtx) {
    new Chart(catCtx, {
      type: 'doughnut',
      data: {
        labels: ['Electronics', 'Fashion', 'Home', 'Sports', 'Books'],
        datasets: [{
          data: [35, 25, 20, 12, 8],
          backgroundColor: [
            colors.primary, colors.accent, colors.success, colors.warning, colors.info,
          ],
          borderWidth: 0,
          hoverOffset: 8,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { animateRotate: true, duration: 1200 },
        cutout: '65%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' },
          },
        },
      },
    });
  }
}

/* ═══════════════════════════════════════════════════════════════════════════
   §3  DATA TABLES — Sort, Search, Select, Pagination
   ═══════════════════════════════════════════════════════════════════════════ */

function initDataTables() {
  document.querySelectorAll('.data-table, [data-table]').forEach(table => {
    new ShopVerseTable(table);
  });
}

class ShopVerseTable {
  constructor(tableEl) {
    this.table = tableEl;
    this.headers = tableEl.querySelectorAll('thead th[data-sort]');
    this.tbody = tableEl.querySelector('tbody');
    this.rows = Array.from(this.tbody?.querySelectorAll('tr') || []);
    this.originalRows = [...this.rows];
    this.currentSort = { col: null, dir: 'asc' };
    this.perPage = parseInt(tableEl.dataset.perPage, 10) || 10;
    this.currentPage = 1;
    this.filteredRows = [...this.rows];

    this.wrapper = tableEl.closest('.table-wrapper, [data-table-wrapper]');
    this.searchInput = this.wrapper?.querySelector('.table-search, [data-table-search]');
    this.selectAllCheckbox = tableEl.querySelector('thead .select-all, thead [data-select-all]');
    this.bulkActions = this.wrapper?.querySelector('.bulk-actions, [data-bulk-actions]');
    this.paginationContainer = this.wrapper?.querySelector('.table-pagination, [data-table-pagination]');

    this.init();
  }

  init() {
    /* ── Sorting ─────────────────────────────────────────── */
    this.headers.forEach(th => {
      th.style.cursor = 'pointer';
      th.addEventListener('click', () => this.sort(th));
    });

    /* ── Search / filter ─────────────────────────────────── */
    if (this.searchInput) {
      this.searchInput.addEventListener('input', debounceTable(() => {
        const query = this.searchInput.value.toLowerCase().trim();
        this.filteredRows = this.originalRows.filter(row =>
          row.textContent.toLowerCase().includes(query)
        );
        this.currentPage = 1;
        this.render();
      }, 250));
    }

    /* ── Select all checkbox ─────────────────────────────── */
    if (this.selectAllCheckbox) {
      this.selectAllCheckbox.addEventListener('change', () => {
        const checked = this.selectAllCheckbox.checked;
        this.tbody.querySelectorAll('input[type="checkbox"].row-select').forEach(cb => {
          cb.checked = checked;
          cb.closest('tr')?.classList.toggle('selected', checked);
        });
        this.updateBulkActions();
      });
    }

    /* ── Row checkbox ────────────────────────────────────── */
    this.tbody?.addEventListener('change', (e) => {
      if (!e.target.classList.contains('row-select')) return;
      e.target.closest('tr')?.classList.toggle('selected', e.target.checked);
      this.updateBulkActions();
    });

    this.render();
  }

  sort(th) {
    const col = th.dataset.sort;
    const dir = (this.currentSort.col === col && this.currentSort.dir === 'asc') ? 'desc' : 'asc';
    this.currentSort = { col, dir };

    /* Update header indicators */
    this.headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
    th.classList.add(dir === 'asc' ? 'sort-asc' : 'sort-desc');

    const colIndex = Array.from(th.parentElement.children).indexOf(th);

    this.filteredRows.sort((a, b) => {
      const aVal = a.children[colIndex]?.textContent.trim() || '';
      const bVal = b.children[colIndex]?.textContent.trim() || '';
      const aNum = parseFloat(aVal.replace(/[$,]/g, ''));
      const bNum = parseFloat(bVal.replace(/[$,]/g, ''));

      let comparison = 0;
      if (!isNaN(aNum) && !isNaN(bNum)) {
        comparison = aNum - bNum;
      } else {
        comparison = aVal.localeCompare(bVal, undefined, { numeric: true, sensitivity: 'base' });
      }
      return dir === 'asc' ? comparison : -comparison;
    });

    this.currentPage = 1;
    this.render();
  }

  render() {
    if (!this.tbody) return;

    const start = (this.currentPage - 1) * this.perPage;
    const pageRows = this.filteredRows.slice(start, start + this.perPage);

    this.tbody.innerHTML = '';
    if (pageRows.length === 0) {
      this.tbody.innerHTML = `
        <tr><td colspan="100%" style="text-align:center;padding:2rem;color:#999;">
          No records found.
        </td></tr>`;
    } else {
      pageRows.forEach(row => this.tbody.appendChild(row));
    }

    this.renderPagination();
  }

  renderPagination() {
    if (!this.paginationContainer) return;
    const totalPages = Math.ceil(this.filteredRows.length / this.perPage);

    if (totalPages <= 1) {
      this.paginationContainer.innerHTML = '';
      return;
    }

    let html = '';
    html += `<button class="page-btn" data-page="prev" ${this.currentPage <= 1 ? 'disabled' : ''}>
               <i class="fas fa-chevron-left"></i></button>`;

    for (let i = 1; i <= totalPages; i++) {
      if (i === 1 || i === totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
        html += `<button class="page-btn ${i === this.currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
      } else if (i === this.currentPage - 2 || i === this.currentPage + 2) {
        html += '<span class="page-ellipsis">…</span>';
      }
    }

    html += `<button class="page-btn" data-page="next" ${this.currentPage >= totalPages ? 'disabled' : ''}>
               <i class="fas fa-chevron-right"></i></button>`;

    this.paginationContainer.innerHTML = html;

    this.paginationContainer.querySelectorAll('.page-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const p = btn.dataset.page;
        if (p === 'prev') this.currentPage = Math.max(1, this.currentPage - 1);
        else if (p === 'next') this.currentPage = Math.min(totalPages, this.currentPage + 1);
        else this.currentPage = parseInt(p, 10);
        this.render();
      });
    });
  }

  updateBulkActions() {
    const selected = this.tbody.querySelectorAll('input.row-select:checked').length;
    if (this.bulkActions) {
      this.bulkActions.classList.toggle('active', selected > 0);
      const count = this.bulkActions.querySelector('.selected-count');
      if (count) count.textContent = `${selected} selected`;
    }
  }
}

/* ═══════════════════════════════════════════════════════════════════════════
   §4  USER MANAGEMENT — Status Toggle, Delete, Role Change
   ═══════════════════════════════════════════════════════════════════════════ */

function initUserManagement() {
  const section = document.querySelector('.user-management, [data-user-management]');
  if (!section) return;

  /* ── Toggle user status ────────────────────────────────── */
  section.addEventListener('click', async (e) => {
    const toggle = e.target.closest('[data-toggle-status]');
    if (toggle) {
      e.preventDefault();
      const userId = toggle.dataset.toggleStatus;
      const currentStatus = toggle.dataset.currentStatus || 'active';
      const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

      toggle.disabled = true;
      try {
        const body = new FormData();
        body.append('action', 'toggle_status');
        body.append('user_id', userId);
        body.append('status', newStatus);

        const res = await fetch('api/users.php', { method: 'POST', body });
        if (!res.ok) throw new Error();
        const data = await res.json();

        if (data.success) {
          toggle.dataset.currentStatus = newStatus;
          toggle.classList.toggle('status-active', newStatus === 'active');
          toggle.classList.toggle('status-inactive', newStatus === 'inactive');
          toggle.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
          showToastSafe(`User ${newStatus === 'active' ? 'activated' : 'deactivated'}.`, 'success');
        } else {
          throw new Error(data.message);
        }
      } catch (err) {
        showToastSafe(err.message || 'Failed to update user status.', 'error');
      } finally {
        toggle.disabled = false;
      }
    }
  });

  /* ── Delete user ───────────────────────────────────────── */
  section.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-delete-user]');
    if (!btn) return;
    e.preventDefault();

    const userId = btn.dataset.deleteUser;
    const confirmed = await showConfirmDialogDash(
      'Delete User',
      'This action cannot be undone. Are you sure?',
      { confirmText: 'Delete', confirmClass: 'btn-danger' }
    );
    if (!confirmed) return;

    btn.disabled = true;
    try {
      const body = new FormData();
      body.append('action', 'delete');
      body.append('user_id', userId);

      const res = await fetch('api/users.php', { method: 'POST', body });
      if (!res.ok) throw new Error();
      const data = await res.json();

      if (data.success) {
        const row = btn.closest('tr, .user-card, [data-user-id]');
        if (row) {
          row.style.transition = 'all 0.4s ease';
          row.style.opacity = '0';
          row.style.transform = 'scale(0.95)';
          setTimeout(() => row.remove(), 420);
        }
        showToastSafe('User deleted successfully.', 'success');
      } else {
        throw new Error(data.message);
      }
    } catch (err) {
      showToastSafe(err.message || 'Failed to delete user.', 'error');
      btn.disabled = false;
    }
  });

  /* ── Change role ───────────────────────────────────────── */
  section.addEventListener('change', async (e) => {
    const select = e.target.closest('[data-change-role]');
    if (!select) return;

    const userId = select.dataset.changeRole;
    const newRole = select.value;

    try {
      const body = new FormData();
      body.append('action', 'change_role');
      body.append('user_id', userId);
      body.append('role', newRole);

      const res = await fetch('api/users.php', { method: 'POST', body });
      if (!res.ok) throw new Error();
      const data = await res.json();

      if (data.success) {
        showToastSafe(`Role updated to ${newRole}.`, 'success');
      } else {
        throw new Error(data.message);
      }
    } catch (err) {
      showToastSafe(err.message || 'Failed to change role.', 'error');
    }
  });
}

/* ═══════════════════════════════════════════════════════════════════════════
   §5  PROFILE MANAGEMENT — Avatar Preview, Validation, Password Change
   ═══════════════════════════════════════════════════════════════════════════ */

function initProfileManagement() {
  /* ── Avatar upload preview ─────────────────────────────── */
  const avatarInput = document.querySelector('#avatar-upload, [data-avatar-upload]');
  const avatarPreview = document.querySelector('.avatar-preview, [data-avatar-preview]');
  if (avatarInput && avatarPreview) {
    avatarInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;

      /* Validate */
      const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      if (!validTypes.includes(file.type)) {
        showToastSafe('Please select a valid image file (JPG, PNG, GIF, WebP).', 'error');
        avatarInput.value = '';
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        showToastSafe('Image must be smaller than 5 MB.', 'error');
        avatarInput.value = '';
        return;
      }

      const reader = new FileReader();
      reader.onload = (ev) => {
        const img = avatarPreview.querySelector('img') || avatarPreview;
        if (img.tagName === 'IMG') {
          img.src = ev.target.result;
        } else {
          img.style.backgroundImage = `url(${ev.target.result})`;
        }
        avatarPreview.classList.add('avatar-updated');
      };
      reader.readAsDataURL(file);
    });
  }

  /* ── Profile form validation ───────────────────────────── */
  const profileForm = document.querySelector('#profile-form, [data-profile-form]');
  if (profileForm) {
    profileForm.addEventListener('submit', (e) => {
      const fullname = profileForm.querySelector('[name="fullname"], [name="full_name"]');
      const email = profileForm.querySelector('[name="email"]');
      let hasError = false;

      if (fullname && fullname.value.trim().length < 2) {
        markInvalid(fullname, 'Name must be at least 2 characters');
        hasError = true;
      }
      if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        markInvalid(email, 'Enter a valid email');
        hasError = true;
      }

      if (hasError) {
        e.preventDefault();
        showToastSafe('Please fix the errors before saving.', 'error');
      }
    });
  }

  /* ── Password change validation ────────────────────────── */
  const pwForm = document.querySelector('#password-form, [data-password-form]');
  if (pwForm) {
    pwForm.addEventListener('submit', (e) => {
      const current = pwForm.querySelector('[name="current_password"]');
      const newPw = pwForm.querySelector('[name="new_password"]');
      const confirm = pwForm.querySelector('[name="confirm_password"]');
      let hasError = false;

      if (!current?.value) { markInvalid(current, 'Current password required'); hasError = true; }
      if (!newPw?.value || newPw.value.length < 8) { markInvalid(newPw, 'Min 8 characters'); hasError = true; }
      if (newPw?.value !== confirm?.value) { markInvalid(confirm, 'Passwords do not match'); hasError = true; }

      if (hasError) {
        e.preventDefault();
        showToastSafe('Please fix password errors.', 'error');
      }
    });
  }
}

function markInvalid(input, message) {
  if (!input) return;
  const wrapper = input.closest('.form-group, .input-group, .field-wrapper') || input.parentElement;
  wrapper.classList.add('field-invalid');
  let msg = wrapper.querySelector('.field-message');
  if (!msg) {
    msg = document.createElement('small');
    msg.className = 'field-message';
    wrapper.appendChild(msg);
  }
  msg.textContent = message;
  msg.style.color = '#ef4444';
  input.addEventListener('input', () => {
    wrapper.classList.remove('field-invalid');
    msg.textContent = '';
  }, { once: true });
}

/* ═══════════════════════════════════════════════════════════════════════════
   §6  NOTIFICATIONS — Mark Read, Delete, Load More
   ═══════════════════════════════════════════════════════════════════════════ */

function initNotifications() {
  const container = document.querySelector('.notifications-list, [data-notifications]');
  if (!container) return;

  /* ── Mark single as read ───────────────────────────────── */
  container.addEventListener('click', async (e) => {
    const item = e.target.closest('.notification-item, [data-notification-id]');
    if (!item || item.classList.contains('read')) return;
    /* Skip if clicking delete button */
    if (e.target.closest('[data-delete-notification]')) return;

    const notifId = item.dataset.notificationId;
    item.classList.add('read');

    try {
      const body = new FormData();
      body.append('action', 'mark_read');
      body.append('notification_id', notifId);
      await fetch('api/notifications.php', { method: 'POST', body });
      updateNotificationBadge(-1);
    } catch { /* Silently fail – already marked in UI */ }
  });

  /* ── Delete notification ───────────────────────────────── */
  container.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-delete-notification]');
    if (!btn) return;
    e.stopPropagation();
    const item = btn.closest('.notification-item, [data-notification-id]');
    if (!item) return;

    item.style.transition = 'all 0.35s ease';
    item.style.opacity = '0';
    item.style.maxHeight = item.scrollHeight + 'px';
    requestAnimationFrame(() => {
      item.style.maxHeight = '0';
      item.style.padding = '0';
      item.style.margin = '0';
    });

    setTimeout(() => item.remove(), 360);

    try {
      const body = new FormData();
      body.append('action', 'delete');
      body.append('notification_id', item.dataset.notificationId);
      await fetch('api/notifications.php', { method: 'POST', body });
    } catch { /* Silently fail */ }
  });

  /* ── Mark all as read ──────────────────────────────────── */
  const markAllBtn = document.querySelector('[data-mark-all-read]');
  markAllBtn?.addEventListener('click', async () => {
    container.querySelectorAll('.notification-item:not(.read)').forEach(item => {
      item.classList.add('read');
    });
    updateNotificationBadge(0, true);

    try {
      const body = new FormData();
      body.append('action', 'mark_all_read');
      await fetch('api/notifications.php', { method: 'POST', body });
      showToastSafe('All notifications marked as read.', 'info');
    } catch {
      showToastSafe('Failed to mark all as read.', 'error');
    }
  });

  /* ── Load more ─────────────────────────────────────────── */
  let notifPage = 1;
  const loadMoreBtn = document.querySelector('[data-load-more-notifications]');
  loadMoreBtn?.addEventListener('click', async () => {
    notifPage++;
    loadMoreBtn.disabled = true;
    loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading…';

    try {
      const res = await fetch(`api/notifications.php?page=${notifPage}`);
      if (!res.ok) throw new Error();
      const data = await res.json();

      if (data.notifications && data.notifications.length > 0) {
        data.notifications.forEach(n => {
          const item = document.createElement('div');
          item.className = `notification-item ${n.read ? 'read' : ''}`;
          item.dataset.notificationId = n.id;
          item.innerHTML = `
            <div class="notification-icon"><i class="fas ${n.icon || 'fa-bell'}"></i></div>
            <div class="notification-content">
              <p class="notification-text">${n.message}</p>
              <span class="notification-time">${n.time_ago || ''}</span>
            </div>
            <button class="notification-delete" data-delete-notification aria-label="Delete">
              <i class="fas fa-times"></i>
            </button>`;
          container.appendChild(item);
        });

        if (!data.has_more) {
          loadMoreBtn.style.display = 'none';
        } else {
          loadMoreBtn.disabled = false;
          loadMoreBtn.innerHTML = 'Load More';
        }
      } else {
        loadMoreBtn.style.display = 'none';
      }
    } catch {
      showToastSafe('Failed to load notifications.', 'error');
      loadMoreBtn.disabled = false;
      loadMoreBtn.innerHTML = 'Load More';
    }
  });
}

function updateNotificationBadge(delta, absolute = false) {
  const badge = document.querySelector('.notification-badge, [data-notif-count]');
  if (!badge) return;
  let count = absolute ? delta : Math.max(0, (parseInt(badge.textContent, 10) || 0) + delta);
  badge.textContent = count;
  badge.classList.toggle('hidden', count <= 0);
}

/* ═══════════════════════════════════════════════════════════════════════════
   §7  SIMULATED REAL-TIME UPDATES
   ═══════════════════════════════════════════════════════════════════════════ */

function initRealtimeUpdates() {
  /* Only run on dashboard page */
  const dashboardPage = document.querySelector('.dashboard, [data-dashboard]');
  if (!dashboardPage) return;

  const CHECK_INTERVAL = 30_000; // 30 seconds

  const checkNotifications = async () => {
    try {
      const res = await fetch('api/notifications.php?check_new=1');
      if (!res.ok) return;
      const data = await res.json();

      if (data.new_count && data.new_count > 0) {
        updateNotificationBadge(data.new_count);
        showToastSafe(`${data.new_count} new notification${data.new_count > 1 ? 's' : ''}`, 'info');
      }
    } catch { /* Silent */ }
  };

  /* Initial check after 5s, then every 30s */
  setTimeout(checkNotifications, 5000);
  setInterval(checkNotifications, CHECK_INTERVAL);
}

/* ═══════════════════════════════════════════════════════════════════════════
   §8  CONFIRM DIALOG (Dashboard-local copy to avoid dependency)
   ═══════════════════════════════════════════════════════════════════════════ */

function showConfirmDialogDash(title, message, options = {}) {
  /* Use cart.js version if available, otherwise inline */
  if (typeof showConfirmDialog === 'function') {
    return showConfirmDialog(title, message, options);
  }

  return new Promise(resolve => {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay confirm-overlay';
    overlay.innerHTML = `
      <div class="confirm-dialog">
        <div class="confirm-header">
          <h3>${title}</h3>
          <button class="confirm-close" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <div class="confirm-body"><p>${message}</p></div>
        <div class="confirm-footer">
          <button class="btn btn-secondary confirm-cancel">${options.cancelText || 'Cancel'}</button>
          <button class="btn ${options.confirmClass || 'btn-primary'} confirm-ok">${options.confirmText || 'Confirm'}</button>
        </div>
      </div>`;

    document.body.appendChild(overlay);
    requestAnimationFrame(() => overlay.classList.add('active'));

    const close = (result) => {
      overlay.classList.remove('active');
      setTimeout(() => overlay.remove(), 350);
      resolve(result);
    };

    overlay.querySelector('.confirm-ok').addEventListener('click', () => close(true));
    overlay.querySelector('.confirm-cancel').addEventListener('click', () => close(false));
    overlay.querySelector('.confirm-close').addEventListener('click', () => close(false));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) close(false); });
  });
}

/* ═══════════════════════════════════════════════════════════════════════════
   §9  UTILITY HELPERS
   ═══════════════════════════════════════════════════════════════════════════ */

function getLast7Days() {
  const days = [];
  const today = new Date();
  for (let i = 6; i >= 0; i--) {
    const d = new Date(today);
    d.setDate(d.getDate() - i);
    days.push(d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }));
  }
  return days;
}

function hexToRgba(hex, alpha = 1) {
  const r = parseInt(hex.slice(1, 3), 16);
  const g = parseInt(hex.slice(3, 5), 16);
  const b = parseInt(hex.slice(5, 7), 16);
  return `rgba(${r},${g},${b},${alpha})`;
}

function debounceTable(fn, delay) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };
}

function showToastSafe(message, type, duration) {
  if (typeof showToast === 'function') {
    showToast(message, type, duration);
  } else {
    console.log(`[ShopVerse Dashboard] ${type}: ${message}`);
  }
}
