/**
 * ShopVerse — Cart & Wishlist Module
 * AJAX cart actions, animated item removal, custom dialogs,
 * real-time total calculation, and wishlist toggling.
 * ──────────────────────────────────────────────────────────────────────────
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  initAddToCartButtons();
  initCartPage();
  initWishlistButtons();
  initCoupon();
});

/* ═══════════════════════════════════════════════════════════════════════════
   §1  ADD TO CART
   ═══════════════════════════════════════════════════════════════════════════ */

function initAddToCartButtons() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.add-to-cart-btn, [data-add-to-cart]');
    if (!btn) return;
    e.preventDefault();

    const productId = btn.dataset.productId || btn.dataset.addToCart;
    const qtyInput = btn.closest('.product-card, .product-details, .quickview-details')
                        ?.querySelector('.quantity-selector input, [data-quantity] input');
    const quantity = parseInt(qtyInput?.value, 10) || 1;

    addToCart(productId, quantity, btn);
  });
}

/**
 * Add a product to the cart via AJAX.
 * @param {string|number} productId
 * @param {number} quantity
 * @param {HTMLElement} [triggerBtn] — button that triggered the action
 */
async function addToCart(productId, quantity = 1, triggerBtn = null) {
  if (triggerBtn) {
    triggerBtn.disabled = true;
    triggerBtn.dataset.origHtml = triggerBtn.innerHTML;
    triggerBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding…';
  }

  try {
    const body = new FormData();
    body.append('action', 'add');
    body.append('product_id', productId);
    body.append('quantity', quantity);

    const res = await fetch('api/cart_actions.php', { method: 'POST', body });
    if (!res.ok) throw new Error('Network error');
    const data = await res.json();

    if (data.success) {
      showToastSafe('🛒 Added to cart!', 'success');
      updateCartBadge(data.cart_count);
      animateCartIcon();
    } else {
      showToastSafe(data.message || 'Could not add to cart.', 'error');
    }
  } catch {
    showToastSafe('Something went wrong. Please try again.', 'error');
  } finally {
    if (triggerBtn) {
      triggerBtn.disabled = false;
      triggerBtn.innerHTML = triggerBtn.dataset.origHtml;
    }
  }
}

/* ═══════════════════════════════════════════════════════════════════════════
   §2  UPDATE CART QUANTITY
   ═══════════════════════════════════════════════════════════════════════════ */

const debouncedUpdate = debounceCart(async (cartId, quantity) => {
  try {
    const body = new FormData();
    body.append('action', 'update');
    body.append('cart_id', cartId);
    body.append('quantity', quantity);

    const res = await fetch('api/cart_actions.php', { method: 'POST', body });
    if (!res.ok) throw new Error();
    const data = await res.json();

    if (data.success) {
      updateCartBadge(data.cart_count);
      recalculateTotals();
    } else {
      showToastSafe(data.message || 'Failed to update quantity.', 'error');
    }
  } catch {
    showToastSafe('Could not update quantity.', 'error');
  }
}, 400);

/**
 * Update the quantity of a cart line item.
 * @param {string|number} cartId
 * @param {number} quantity
 */
function updateQuantity(cartId, quantity) {
  /* Immediately update the line total in UI */
  const row = document.querySelector(`[data-cart-id="${cartId}"]`);
  if (row) {
    const price = parseFloat(row.dataset.price) || 0;
    const lineTotal = row.querySelector('.line-total, .cart-item-total');
    if (lineTotal) lineTotal.textContent = '$' + (price * quantity).toFixed(2);
  }
  recalculateTotals();
  debouncedUpdate(cartId, quantity);
}

/* ═══════════════════════════════════════════════════════════════════════════
   §3  REMOVE FROM CART — Custom Confirm Dialog
   ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Remove a line item from the cart.
 * @param {string|number} cartId
 */
async function removeFromCart(cartId) {
  const confirmed = await showConfirmDialog(
    'Remove Item',
    'Are you sure you want to remove this item from your cart?',
    { confirmText: 'Remove', confirmClass: 'btn-danger' }
  );
  if (!confirmed) return;

  const row = document.querySelector(`[data-cart-id="${cartId}"]`);

  /* Animate out */
  if (row) {
    row.style.transition = 'all 0.45s cubic-bezier(0.4, 0, 0.2, 1)';
    row.style.transform = 'translateX(60px)';
    row.style.opacity = '0';
    row.style.maxHeight = row.scrollHeight + 'px';
    requestAnimationFrame(() => (row.style.maxHeight = '0'));
  }

  try {
    const body = new FormData();
    body.append('action', 'remove');
    body.append('cart_id', cartId);

    const res = await fetch('api/cart_actions.php', { method: 'POST', body });
    if (!res.ok) throw new Error();
    const data = await res.json();

    if (data.success) {
      setTimeout(() => {
        row?.remove();
        updateCartBadge(data.cart_count);
        recalculateTotals();
        checkEmptyCart();
      }, 460);
      showToastSafe('Item removed from cart.', 'info');
    } else {
      /* Revert animation */
      if (row) {
        row.style.transform = '';
        row.style.opacity = '';
        row.style.maxHeight = '';
      }
      showToastSafe(data.message || 'Could not remove item.', 'error');
    }
  } catch {
    if (row) {
      row.style.transform = '';
      row.style.opacity = '';
      row.style.maxHeight = '';
    }
    showToastSafe('Something went wrong. Please try again.', 'error');
  }
}

/* ═══════════════════════════════════════════════════════════════════════════
   §4  CUSTOM CONFIRM DIALOG
   ═══════════════════════════════════════════════════════════════════════════ */

function showConfirmDialog(title, message, options = {}) {
  return new Promise(resolve => {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay confirm-overlay';
    overlay.innerHTML = `
      <div class="confirm-dialog">
        <div class="confirm-header">
          <h3>${title}</h3>
          <button class="confirm-close" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <div class="confirm-body">
          <p>${message}</p>
        </div>
        <div class="confirm-footer">
          <button class="btn btn-secondary confirm-cancel">
            ${options.cancelText || 'Cancel'}
          </button>
          <button class="btn ${options.confirmClass || 'btn-primary'} confirm-ok">
            ${options.confirmText || 'Confirm'}
          </button>
        </div>
      </div>`;

    document.body.appendChild(overlay);
    requestAnimationFrame(() => overlay.classList.add('active'));

    const close = (result) => {
      overlay.classList.remove('active');
      overlay.addEventListener('transitionend', () => overlay.remove(), { once: true });
      setTimeout(() => overlay.remove(), 400);
      resolve(result);
    };

    overlay.querySelector('.confirm-ok').addEventListener('click', () => close(true));
    overlay.querySelector('.confirm-cancel').addEventListener('click', () => close(false));
    overlay.querySelector('.confirm-close').addEventListener('click', () => close(false));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) close(false); });
    document.addEventListener('keydown', function esc(e) {
      if (e.key === 'Escape') { document.removeEventListener('keydown', esc); close(false); }
    });
  });
}

/* ═══════════════════════════════════════════════════════════════════════════
   §5  CART PAGE — Totals, Coupon, Checkout
   ═══════════════════════════════════════════════════════════════════════════ */

function initCartPage() {
  const cartPage = document.querySelector('.cart-page, [data-cart-page]');
  if (!cartPage) return;

  /* ── Quantity change listeners ──────────────────────────── */
  cartPage.addEventListener('change', (e) => {
    const input = e.target.closest('.quantity-selector input, [data-quantity] input');
    if (!input) return;
    const row = input.closest('[data-cart-id]');
    if (!row) return;
    updateQuantity(row.dataset.cartId, parseInt(input.value, 10) || 1);
  });

  /* ── Remove buttons ────────────────────────────────────── */
  cartPage.addEventListener('click', (e) => {
    const removeBtn = e.target.closest('.cart-remove-btn, [data-cart-remove]');
    if (!removeBtn) return;
    e.preventDefault();
    const row = removeBtn.closest('[data-cart-id]');
    if (row) removeFromCart(row.dataset.cartId);
  });

  recalculateTotals();
}

function recalculateTotals() {
  const items = document.querySelectorAll('[data-cart-id]');
  let subtotal = 0;

  items.forEach(row => {
    const price = parseFloat(row.dataset.price) || 0;
    const qty = parseInt(row.querySelector('.quantity-selector input, [data-quantity] input')?.value, 10) || 1;
    subtotal += price * qty;
  });

  const TAX_RATE = 0.10;
  const FREE_SHIPPING_THRESHOLD = 50;
  const SHIPPING_COST = 5.99;

  const tax = subtotal * TAX_RATE;
  const shipping = subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
  const discount = parseFloat(document.querySelector('[data-discount]')?.dataset.discount) || 0;
  const total = Math.max(0, subtotal + tax + shipping - discount);

  setTotal('.cart-subtotal, [data-subtotal]', subtotal);
  setTotal('.cart-tax, [data-tax]', tax);
  setTotal('.cart-shipping, [data-shipping]', shipping, shipping === 0 ? 'FREE' : null);
  setTotal('.cart-discount, [data-discount-display]', discount, discount > 0 ? '-$' + discount.toFixed(2) : '$0.00');
  setTotal('.cart-total, [data-total]', total);
}

function setTotal(selector, value, override = null) {
  const el = document.querySelector(selector);
  if (el) {
    const display = override ?? ('$' + value.toFixed(2));
    if (el.textContent !== display) {
      el.textContent = display;
      el.classList.add('total-updated');
      el.addEventListener('animationend', () => el.classList.remove('total-updated'), { once: true });
    }
  }
}

function checkEmptyCart() {
  const items = document.querySelectorAll('[data-cart-id]');
  if (items.length > 0) return;

  const container = document.querySelector('.cart-items, .cart-table tbody, [data-cart-items]');
  if (!container) return;

  container.innerHTML = `
    <div class="cart-empty" style="text-align:center;padding:3rem 1rem;">
      <div style="font-size:4rem;margin-bottom:1rem;opacity:0.3;">
        <i class="fas fa-shopping-cart"></i>
      </div>
      <h3 style="margin-bottom:0.5rem;">Your cart is empty</h3>
      <p style="color:var(--text-muted,#999);margin-bottom:1.5rem;">
        Looks like you haven't added any items yet.
      </p>
      <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
    </div>`;

  /* Hide totals section */
  const totals = document.querySelector('.cart-totals, .cart-summary, [data-cart-totals]');
  if (totals) totals.style.display = 'none';
}

/* ═══════════════════════════════════════════════════════════════════════════
   §6  COUPON CODE (UI Simulation)
   ═══════════════════════════════════════════════════════════════════════════ */

function initCoupon() {
  const form = document.querySelector('.coupon-form, [data-coupon-form]');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = form.querySelector('input');
    const btn = form.querySelector('button');
    const code = input?.value.trim().toUpperCase();

    if (!code) {
      showToastSafe('Please enter a coupon code.', 'warning');
      return;
    }

    btn.disabled = true;
    const origHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    /* Simulated coupon codes */
    const coupons = {
      SAVE10:  { discount: 10, label: '10% off' },
      SAVE20:  { discount: 20, label: '20% off' },
      WELCOME: { discount: 5,  label: '$5 off'  },
    };

    await new Promise(r => setTimeout(r, 800));

    if (coupons[code]) {
      const coupon = coupons[code];
      const discountEl = document.querySelector('[data-discount]');
      if (discountEl) discountEl.dataset.discount = coupon.discount;
      recalculateTotals();
      showToastSafe(`🎉 Coupon applied: ${coupon.label}!`, 'success');
      input.disabled = true;
      btn.textContent = '✓ Applied';
      btn.classList.add('btn-success');
    } else {
      showToastSafe('Invalid coupon code.', 'error');
      input.classList.add('shake');
      input.addEventListener('animationend', () => input.classList.remove('shake'), { once: true });
      btn.innerHTML = origHTML;
      btn.disabled = false;
    }
  });
}

/* ═══════════════════════════════════════════════════════════════════════════
   §7  WISHLIST TOGGLE
   ═══════════════════════════════════════════════════════════════════════════ */

function initWishlistButtons() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.wishlist-btn, [data-wishlist-toggle]');
    if (!btn) return;
    e.preventDefault();
    const productId = btn.dataset.productId || btn.dataset.wishlistToggle;
    toggleWishlist(productId, btn);
  });
}

/**
 * Toggle a product in the wishlist.
 * @param {string|number} productId
 * @param {HTMLElement} btn
 */
async function toggleWishlist(productId, btn) {
  const icon = btn.querySelector('i');
  const isWished = btn.classList.contains('wishlisted');

  /* Optimistic UI update */
  btn.classList.toggle('wishlisted');
  if (icon) {
    icon.classList.toggle('far', isWished);
    icon.classList.toggle('fas', !isWished);
  }

  /* Animate heart */
  btn.classList.add('wishlist-pop');
  btn.addEventListener('animationend', () => btn.classList.remove('wishlist-pop'), { once: true });

  try {
    const body = new FormData();
    body.append('action', isWished ? 'remove' : 'add');
    body.append('product_id', productId);

    const res = await fetch('api/wishlist.php', { method: 'POST', body });
    if (!res.ok) throw new Error();
    const data = await res.json();

    if (data.success) {
      showToastSafe(
        isWished ? 'Removed from wishlist' : '❤️ Added to wishlist',
        isWished ? 'info' : 'success'
      );
    } else {
      throw new Error(data.message);
    }
  } catch {
    /* Revert on failure */
    btn.classList.toggle('wishlisted');
    if (icon) {
      icon.classList.toggle('far', !isWished);
      icon.classList.toggle('fas', isWished);
    }
    showToastSafe('Could not update wishlist.', 'error');
  }
}

/* ═══════════════════════════════════════════════════════════════════════════
   §8  HELPERS
   ═══════════════════════════════════════════════════════════════════════════ */

function updateCartBadge(count) {
  document.querySelectorAll('.cart-badge, .cart-count, [data-cart-count]').forEach(el => {
    el.textContent = count;
    el.classList.toggle('hidden', count <= 0);
    /* Bounce */
    el.classList.add('badge-bounce');
    el.addEventListener('animationend', () => el.classList.remove('badge-bounce'), { once: true });
  });
}

function animateCartIcon() {
  const icon = document.querySelector('.cart-icon, [data-cart-icon]');
  if (!icon) return;
  icon.classList.add('cart-bounce');
  icon.addEventListener('animationend', () => icon.classList.remove('cart-bounce'), { once: true });
}

function debounceCart(fn, delay) {
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
    console.log(`[ShopVerse Cart] ${type}: ${message}`);
  }
}

/* ── Expose public API ───────────────────────────────────── */
window.ShopVerseCart = {
  addToCart,
  updateQuantity,
  removeFromCart,
  toggleWishlist,
};
