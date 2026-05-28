<?php
/**
 * Online shopping registration system - Customer Shopping Cart Page
 */

require_once 'includes/auth_check.php';

// Fetch Customer Cart Items
$user_id = $_SESSION['user_id'];
$cart_items = [];

try {
    $stmt = $pdo->prepare("SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.slug, p.price, p.sale_price, p.image, p.stock, cat.name AS category_name FROM cart c JOIN products p ON c.product_id = p.id LEFT JOIN categories cat ON p.category_id = cat.id WHERE c.user_id = ? ORDER BY c.created_at DESC");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
} catch (PDOException $e) {
    $cart_items = [];
}

$page_title = "Your Shopping Cart";
require_once 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════
     Cart Header Breadcrumbs
     ══════════════════════════════════════════════════════ -->
<div class="py-4 mb-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.03) 0%, rgba(99, 102, 241, 0.03) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 class="fw-bold mb-2">Shopping Basket</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
                <li class="breadcrumb-item active" aria-current="page">Cart</li>
            </ol>
        </nav>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Interactive Cart Page Layout
     ══════════════════════════════════════════════════════ -->
<div class="container py-4 cart-page" data-cart-page>
    <?php if (empty($cart_items)): ?>
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius:18px; background:var(--bg-secondary); border:1px solid var(--border-color);">
            <div class="p-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-4 opacity-30"></i>
                <h3 class="fw-bold">Your cart is empty</h3>
                <p class="text-secondary mb-4">Looks like you haven't added any premium items to your basket yet.</p>
                <a href="shop.php" class="btn btn-primary" style="border-radius:50px;">Continue Shopping</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            
            <!-- Cart Items List (col-lg-8) -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius:20px; background: var(--bg-secondary); border: 1px solid var(--border-color);">
                    <h5 class="fw-bold mb-4"><i class="fas fa-shopping-cart text-primary me-2"></i>Cart Items</h5>
                    
                    <div class="table-responsive">
                        <table class="table align-middle cart-table" data-cart-items>
                            <thead>
                                <tr class="text-muted" style="font-size:0.85rem; text-transform:uppercase;">
                                    <th scope="col" style="border-bottom:none;">Product</th>
                                    <th scope="col" style="border-bottom:none;">Price</th>
                                    <th scope="col" style="border-bottom:none;">Quantity</th>
                                    <th scope="col" style="border-bottom:none;">Total</th>
                                    <th scope="col" style="border-bottom:none;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <?php 
                                    $has_sale = !empty($item['sale_price']) && $item['sale_price'] > 0;
                                    $unit_price = $has_sale ? $item['sale_price'] : $item['price'];
                                    $line_total = $unit_price * $item['quantity'];
                                    $image_url = !empty($item['image']) ? htmlspecialchars($item['image']) : 'https://via.placeholder.com/100';
                                    ?>
                                    <tr data-cart-id="<?php echo $item['cart_id']; ?>" data-price="<?php echo $unit_price; ?>" style="border-bottom: 1px solid var(--border-color);">
                                        <!-- Product info -->
                                        <td style="padding: 16px 0;">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid rounded-3" style="width:70px; height:70px; object-fit:cover; border:1px solid var(--border-color);">
                                                <div>
                                                    <h6 class="fw-bold mb-1">
                                                        <a href="product.php?id=<?php echo $item['product_id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($item['name']); ?></a>
                                                    </h6>
                                                    <span class="text-muted small"><?php echo htmlspecialchars($item['category_name'] ?? 'General'); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Unit Price -->
                                        <td>
                                            <span class="fw-bold"><?php echo format_price($unit_price); ?></span>
                                        </td>
                                        
                                        <!-- Quantity selection -->
                                        <td>
                                            <div class="quantity-selector input-group" style="width: 120px; border-radius:8px; overflow:hidden;">
                                                <button class="btn btn-outline-secondary qty-btn-minus" type="button" style="padding:6px 12px;"><i class="fas fa-minus" style="font-size:0.75rem;"></i></button>
                                                <input type="number" class="form-control text-center border-start-0 border-end-0 qty-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo (int)($item['stock'] ?? 99); ?>" style="font-size:0.9rem; font-weight:600;">
                                                <button class="btn btn-outline-secondary qty-btn-plus" type="button" style="padding:6px 12px;"><i class="fas fa-plus" style="font-size:0.75rem;"></i></button>
                                            </div>
                                        </td>
                                        
                                        <!-- Line Total -->
                                        <td>
                                            <span class="fw-bold text-primary line-total"><?php echo format_price($line_total); ?></span>
                                        </td>
                                        
                                        <!-- Actions -->
                                        <td>
                                            <button class="btn btn-link text-danger p-0 cart-remove-btn" title="Remove Item" style="font-size:1.1rem; border:none; background:transparent;">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Promo Code Widget -->
                <div class="card border-0 shadow-sm p-4" style="border-radius:18px; background:var(--bg-secondary); border:1px solid var(--border-color);">
                    <h6 class="fw-bold mb-3"><i class="fas fa-ticket-alt text-primary me-2"></i>Apply Coupon Code</h6>
                    <form class="coupon-form" data-coupon-form style="max-width:400px;">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="e.g. SAVE20" style="text-transform:uppercase;">
                            <button class="btn btn-primary" type="submit">Apply Code</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Cart Totals Column (col-lg-4) -->
            <div class="col-lg-4" data-cart-totals>
                <div class="card border-0 shadow-sm p-4 position-sticky" style="top: 100px; border-radius:20px; background: var(--bg-secondary); border: 1px solid var(--border-color);">
                    <h5 class="fw-bold mb-4">Summary Totals</h5>
                    
                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-secondary">Subtotal</span>
                            <span class="fw-bold cart-subtotal" data-subtotal>$0.00</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-secondary">VAT Tax (10%)</span>
                            <span class="fw-bold cart-tax" data-tax>$0.00</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-secondary">Shipping</span>
                            <span class="fw-bold cart-shipping" data-shipping>$0.00</span>
                        </div>
                        <!-- Coupon Discount -->
                        <div class="d-flex align-items-center justify-content-between text-success">
                            <span>Coupon Discount</span>
                            <span class="fw-bold cart-discount" data-discount="0" data-discount-display>$0.00</span>
                        </div>
                        
                        <hr class="my-2" style="border-color:var(--border-color);">
                        
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fs-5 fw-bold">Grand Total</span>
                            <span class="fs-4 fw-bold text-primary cart-total" data-total>$0.00</span>
                        </div>
                    </div>

                    <a href="checkout.php" class="btn btn-primary btn-lg w-100 py-3" style="border-radius:50px;">
                        Proceed to Checkout <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Click Plus/Minus Event Dispatchers Helper -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.qty-btn-minus, .qty-btn-plus').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('input');
            const isPlus = btn.classList.contains('qty-btn-plus');
            let val = parseInt(input.value, 10) || 1;
            if (isPlus) {
                val = Math.min(parseInt(input.max, 10) || 99, val + 1);
            } else {
                val = Math.max(1, val - 1);
            }
            input.value = val;
            // Dispatch a change event so cart.js picks it up and updates DB + recalculates totals
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
