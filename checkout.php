<?php
/**
 * Online shopping registration system - Checkout Billing Page
 */

require_once 'includes/auth_check.php';

$user_id = $_SESSION['user_id'];

// Fetch Cart items to check if empty
$cart_items = [];
try {
    $stmt = $pdo->prepare("SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.sale_price, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
} catch (PDOException $e) {
    $cart_items = [];
}
    // Fetch cities for dropdown
    $city_stmt = $pdo->prepare("SELECT id, name FROM cities ORDER BY name");
    $city_stmt->execute();
    $cities = $city_stmt->fetchAll();

if (empty($cart_items)) {
    flash('global', 'Your shopping cart is empty. Please add items before checking out.', 'warning');
    redirect('shop.php');
}

// Calculate Totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $unit_price = (!empty($item['sale_price']) && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
    $subtotal += $unit_price * $item['quantity'];
}

$TAX_RATE = 0.10;
$FREE_SHIPPING_THRESHOLD = 50;
$SHIPPING_COST = 5.99;

$tax = $subtotal * $TAX_RATE;
$shipping = $subtotal >= $FREE_SHIPPING_THRESHOLD ? 0 : $SHIPPING_COST;
$discount = 0.00; // Simulated coupon discount could be fetched from session if stored
$total = max(0, $subtotal + $tax + $shipping - $discount);

// Handle Order Placement Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification
    $csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf($csrf)) {
        flash('checkout', 'Security token expired. Please try again.', 'danger');
        redirect('checkout.php');
    }

    $fullname = sanitize_input($_POST['fullname'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $payment_method = sanitize_input($_POST['payment_method'] ?? 'COD');
$city_id = sanitize_input($_POST['city_id'] ?? '');

    if (empty($fullname) || empty($email) || empty($phone) || empty($address) || empty($city_id)) {
    flash('checkout', 'All billing form fields are required.', 'danger');
    redirect('checkout.php');
}


    try {
        $pdo->beginTransaction();

        // ── 1. Create Order ──────────────────────────────────
        $order_stmt = $pdo->prepare("INSERT INTO orders (user_id, city_id, total, subtotal, tax, shipping, discount, status, full_name, email, phone, address, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?)");
        $order_stmt->execute([
            $user_id,
            $city_id,
            $total,
            $subtotal,
            $tax,
            $shipping,
            $discount,
            $fullname,
            $email,
            $phone,
            $address,
            $payment_method
        ]);
        
        $order_id = $pdo->lastInsertId();

        // ── 2. Create Order Items and update stock ───────────
        $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, price, quantity) VALUES (?, ?, ?, ?)");
        $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($cart_items as $item) {
            $unit_price = (!empty($item['sale_price']) && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
            
            // Insert order line item
            $item_stmt->execute([$order_id, $item['product_id'], $unit_price, $item['quantity']]);
            
            // Adjust stock count
            $stock_stmt->execute([$item['quantity'], $item['product_id']]);
        }

        // ── 3. Clear Customer Cart ───────────────────────────
        $clear_stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_stmt->execute([$user_id]);

        // ── 4. Add User Activity Notification ────────────────
        $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, icon, message) VALUES (?, 'fa-box', ?)");
        $msg = "🎉 Order #{$order_id} containing " . count($cart_items) . " item(s) has been successfully placed. We are processing your delivery details.";
        $notif_stmt->execute([$user_id, $msg]);

        $pdo->commit();

        flash('dashboard', "🎉 Order placed successfully! Thank you for choosing Online shopping registration system. (Order #{$order_id})", 'success');
        redirect('dashboard.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        flash('checkout', 'Transaction failed. Could not write order: ' . htmlspecialchars($e->getMessage()), 'danger');
        redirect('checkout.php');
    }
}

$page_title = "Checkout Billing Info";
require_once 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════
     Breadcrumbs
     ══════════════════════════════════════════════════════ -->
<div class="py-4 mb-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.03) 0%, rgba(99, 102, 241, 0.03) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 class="fw-bold mb-2">Checkout Billing</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="cart.php">Cart</a></li>
                <li class="breadcrumb-item active" aria-current="page">Checkout</li>
            </ol>
        </nav>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Checkout Panel Layout
     ══════════════════════════════════════════════════════ -->
<div class="container py-4">
    <!-- Alerts -->
    <?php display_flash('checkout'); ?>

    <div class="row g-4">
        <!-- Billing Address Form Column (col-lg-7) -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4 p-md-5" style="border-radius:24px; background:var(--bg-secondary); border: 1px solid var(--border-color);">
                <h4 class="fw-bold mb-4"><i class="fas fa-file-invoice text-primary me-2"></i>Billing & Delivery Address</h4>

                <form action="checkout.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6 form-group">
                            <label for="fullname">Receiver's Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" placeholder="Victoria Vance">
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label for="email">Contact Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" placeholder="vance@onlineshoppingregistrationsystem.com">
                        </div>
                        
                        <div class="col-12 form-group">
                            <label for="phone">Phone / Mobile Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" required placeholder="+1 (555) 123-4567">
                        </div>

                        <div class="col-12 form-group">
                            <label for="address">Fulfillment Home Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required placeholder="Street address, Apartment, Suite, City, State, ZIP"></textarea>
                        </div>
                        
                        <div class="col-12 form-group">
    <label for="city_id">City</label>
    <select class="form-select" id="city_id" name="city_id" required>
        <option value="">Select a city</option>
        <?php foreach ($cities as $city): ?>
            <option value="<?php echo $city['id']; ?>"><?php echo htmlspecialchars($city['name']); ?></option>
        <?php endforeach; ?>
    </select>
</div>
<hr class="my-4" style="border-color:var(--border-color);">
                        
                        <h5 class="fw-bold mb-3"><i class="fas fa-wallet text-primary me-2"></i>Payment Method</h5>
                        
                        <div class="col-12">
                            <div class="form-check p-3 mb-3 border d-flex align-items-center gap-3" style="border-radius:12px; cursor:pointer;">
                                <input class="form-check-input ms-0" type="radio" name="payment_method" id="pay-cod" value="COD" checked>
                                <label class="form-check-label fw-bold" for="pay-cod">
                                    💵 Cash on Delivery (COD)
                                    <span class="d-block text-muted small fw-normal">Pay directly in cash when our courier delivers the package to your door.</span>
                                </label>
                            </div>

                            <div class="form-check p-3 border d-flex align-items-center gap-3" style="border-radius:12px; cursor:pointer;">
                                <input class="form-check-input ms-0" type="radio" name="payment_method" id="pay-card" value="Credit Card">
                                <label class="form-check-label fw-bold" for="pay-card">
                                    💳 Credit Card / Online Gateway
                                    <span class="d-block text-muted small fw-normal">Secure payment via credit/debit card (Visa, Mastercard, Amex).</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 py-3" style="border-radius:50px;">
                                <i class="fas fa-check-circle me-1"></i> Place Secure Order (<?php echo format_price($total); ?>)
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Totals Column (col-lg-5) -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4" style="border-radius:20px; background:var(--bg-secondary); border: 1px solid var(--border-color);">
                <h5 class="fw-bold mb-4">Your Order Items</h5>
                
                <div class="d-flex flex-column gap-3 mb-4">
                    <?php 
                    // Fetch products again for printing details
                    foreach ($cart_items as $item): 
                        $unit_price = (!empty($item['sale_price']) && $item['sale_price'] > 0) ? $item['sale_price'] : $item['price'];
                        $line_total = $unit_price * $item['quantity'];
                    ?>
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div style="flex:1;">
                                <h6 class="fw-bold mb-0" style="font-size:0.92rem;"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <span class="text-muted small">Qty: <?php echo $item['quantity']; ?> @ <?php echo format_price($unit_price); ?></span>
                            </div>
                            <span class="fw-bold text-dark" style="font-size:0.92rem;"><?php echo format_price($line_total); ?></span>
                        </div>
                    <?php endforeach; ?>

                    <hr class="my-2" style="border-color:var(--border-color);">
                    
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-secondary small">Subtotal</span>
                        <span class="fw-bold" style="font-size:0.92rem;"><?php echo format_price($subtotal); ?></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-secondary small">VAT Tax (10%)</span>
                        <span class="fw-bold" style="font-size:0.92rem;"><?php echo format_price($tax); ?></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-secondary small">Shipping Cost</span>
                        <span class="fw-bold" style="font-size:0.92rem;"><?php echo $shipping === 0 ? 'FREE' : format_price($shipping); ?></span>
                    </div>
                    
                    <hr class="my-2" style="border-color:var(--border-color);">
                    
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="fs-5 fw-bold">Order Grand Total</span>
                        <span class="fs-4 fw-bold text-primary"><?php echo format_price($total); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
