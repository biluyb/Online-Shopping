<?php
/**
 * =====================================================
 * Online shopping registration system - Footer Template
 * =====================================================
 * 
 * Shared footer included on every public-facing page.
 * Contains a 4-column info layout, newsletter form,
 * social links, payment icons, scripts, and back-to-top.
 * =====================================================
 */

// Fetch settings for footer content
$footer_site_name   = get_setting($conn, 'site_name') ?? 'Online shopping registration system';
$footer_description = get_setting($conn, 'site_description') ?? 'Premium Online Shopping Experience';
$footer_email       = get_setting($conn, 'contact_email') ?? 'support@onlineshoppingregistrationsystem.com';
$footer_phone       = get_setting($conn, 'contact_phone') ?? '+1 (555) 123-4567';

// Determine the current page for conditional script loading
$footer_current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

</main><!-- /.main-content (opened in header.php) -->

<!-- ══════════════════════════════════════════════════════
     Footer
     ══════════════════════════════════════════════════════ -->
<footer class="site-footer">

    <!-- ── Main Footer Content ───────────────────────── -->
    <div class="footer-main">
        <div class="container">
            <div class="row g-4">

                <!-- Column 1: About -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <a href="index.php" class="footer-logo d-flex align-items-center mb-3">
                            <i class="fas fa-shopping-bag me-2"></i>
                            <span><?php echo htmlspecialchars($footer_site_name); ?></span>
                        </a>
                        <p class="footer-about-text">
                            <?php echo htmlspecialchars($footer_description); ?>.
                            We bring you the finest products with exceptional service and unbeatable prices.
                        </p>
                        <!-- Social Media Links -->
                        <div class="footer-social">
                            <a href="#" class="social-link" aria-label="Facebook" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Twitter" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Instagram" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="YouTube" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h5 class="footer-widget-title">Quick Links</h5>
                        <ul class="footer-links">
                            <li><a href="index.php"><i class="fas fa-chevron-right me-2"></i>Home</a></li>
                            <li><a href="shop.php"><i class="fas fa-chevron-right me-2"></i>Shop</a></li>
                            <li><a href="categories.php"><i class="fas fa-chevron-right me-2"></i>Categories</a></li>
                            <li><a href="about.php"><i class="fas fa-chevron-right me-2"></i>About Us</a></li>
                            <li><a href="contact.php"><i class="fas fa-chevron-right me-2"></i>Contact</a></li>
                            <li><a href="faq.php"><i class="fas fa-chevron-right me-2"></i>FAQ</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Column 3: Customer Service -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h5 class="footer-widget-title">Customer Service</h5>
                        <ul class="footer-links">
                            <li><a href="profile.php"><i class="fas fa-chevron-right me-2"></i>My Account</a></li>
                            <li><a href="orders.php"><i class="fas fa-chevron-right me-2"></i>Order Tracking</a></li>
                            <li><a href="wishlist.php"><i class="fas fa-chevron-right me-2"></i>Wishlist</a></li>
                            <li><a href="returns.php"><i class="fas fa-chevron-right me-2"></i>Returns & Refunds</a></li>
                            <li><a href="shipping.php"><i class="fas fa-chevron-right me-2"></i>Shipping Policy</a></li>
                            <li><a href="privacy.php"><i class="fas fa-chevron-right me-2"></i>Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Column 4: Contact Info & Newsletter -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h5 class="footer-widget-title">Contact Info</h5>
                        <ul class="footer-contact-info">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>123 Commerce Street, Tech City, TC 10001</span>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span><?php echo htmlspecialchars($footer_phone); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($footer_email); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span>Mon – Fri: 9:00 AM – 6:00 PM</span>
                            </li>
                        </ul>

                        <!-- Newsletter Signup -->
                        <div class="footer-newsletter mt-4">
                            <h6 class="mb-2">Newsletter</h6>
                            <p class="small text-muted mb-2">Subscribe for exclusive deals and updates.</p>
                            <form class="newsletter-form" id="newsletterForm" action="newsletter_subscribe.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                                <div class="input-group">
                                    <input type="email" class="form-control" name="email"
                                           placeholder="Your email address" required
                                           aria-label="Email for newsletter">
                                    <button class="btn btn-primary" type="submit" aria-label="Subscribe">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div><!-- /.row -->
        </div><!-- /.container -->
    </div><!-- /.footer-main -->

    <!-- ── Payment Methods & Copyright ───────────────── -->
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">

                <!-- Copyright -->
                <div class="col-12 text-center">
                    <p class="mb-0">
                        &copy; <?php echo date('Y'); ?> <strong><?php echo htmlspecialchars($footer_site_name); ?></strong>.
                        All rights reserved.
                    </p>
                </div>

            </div>
        </div>
    </div><!-- /.footer-bottom -->

</footer>

<!-- ══════════════════════════════════════════════════════
     Back to Top Button
     ══════════════════════════════════════════════════════ -->
<button id="back-to-top" class="back-to-top-btn" aria-label="Back to top" title="Back to top">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- ══════════════════════════════════════════════════════
     JavaScript Dependencies
     ══════════════════════════════════════════════════════ -->

<!-- Bootstrap 5.3 Bundle (includes Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>

<!-- Chart.js (for dashboards & analytics) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<!-- Core Application Script -->
<script src="js/script.js"></script>

<?php
// ── Conditional Script Loading ────────────────────────
// Load page-specific JavaScript only where needed

// Auth pages (login, register, forgot password)
if (in_array($footer_current_page, ['login', 'register', 'forgot_password', 'reset_password'])): ?>
    <script src="js/auth.js"></script>
<?php endif; ?>

<?php // Cart & Shop pages
if (in_array($footer_current_page, ['cart', 'shop', 'product', 'checkout', 'category'])): ?>
    <script src="js/cart.js"></script>
<?php endif; ?>

<?php // Dashboard & Admin pages
if (in_array($footer_current_page, ['dashboard', 'profile', 'orders', 'order_detail', 'notifications'])
    || strpos($_SERVER['PHP_SELF'], '/admin/') !== false): ?>
    <script src="js/dashboard.js"></script>
<?php endif; ?>

</body>
</html>
