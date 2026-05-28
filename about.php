<?php
/**
 * Online shopping registration system - About Brand Page
 */

require_once 'includes/functions.php';
$page_title = "About Our Brand";
require_once 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════
     Page Breadcrumbs and Header
     ══════════════════════════════════════════════════════ -->
<div class="py-4 mb-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.03) 0%, rgba(99, 102, 241, 0.03) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 class="fw-bold mb-2">About Online shopping registration system</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">About Us</li>
            </ol>
        </nav>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Brand Introduction
     ══════════════════════════════════════════════════════ -->
<div class="container py-5">
    <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6">
            <span class="badge bg-primary-subtle text-primary mb-3" style="border-radius: 50px; padding: 8px 20px; font-weight:600;">OUR VISION</span>
            <h2 class="fw-bold display-5 mb-4" style="letter-spacing:-1px;">Revolutionizing the E-Commerce Experience</h2>
            <p class="text-secondary mb-4 fs-5" style="line-height:1.7;">
                Online shopping registration system was born out of a desire to create a cohesive e-commerce environment that values modern aesthetics and robust, secure technology. 
            </p>
            <p class="text-secondary mb-4" style="line-height:1.7;">
                We work relentlessly to source top-tier products, integrating premium visual guidelines with responsive, interactive features that make shopping a true pleasure. Whether you are seeking state-of-the-art gaming setups, high-fashion apparel, or ergonomic work essentials, Online shopping registration system delivers an unmatched standard.
            </p>
            <a href="shop.php" class="btn btn-primary" style="border-radius:50px; padding:12px 30px;"><i class="fas fa-shopping-bag me-2"></i> Visit Storefront</a>
        </div>
        <div class="col-lg-6">
            <img src="https://images.unsplash.com/photo-1556740758-90de374c12ad?w=800&auto=format&fit=crop&q=60" alt="E-Commerce Fulfillment" class="img-fluid shadow-md" style="border-radius:24px; border: 1px solid var(--border-color);">
        </div>
    </div>

    <!-- ── Core Values ────────────────────────────────────── -->
    <div class="row g-4 mt-5 text-center">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-5 h-100" style="border-radius:20px; background:var(--bg-secondary); border: 1px solid var(--border-color);">
                <div class="mb-4 d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 50%; background: rgba(34, 197, 94, 0.1); color: #22c55e; font-size: 1.8rem; margin: 0 auto;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h4 class="fw-bold mb-3">Absolute Security</h4>
                <p class="text-muted mb-0 small" style="line-height:1.6;">Your transactional data is highly secure. We implement secure session architectures, strict validation, and hashed user credentials.</p>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-5 h-100" style="border-radius:20px; background:var(--bg-secondary); border: 1px solid var(--border-color);">
                <div class="mb-4 d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 50%; background: rgba(99, 102, 241, 0.1); color: #6366f1; font-size: 1.8rem; margin: 0 auto;">
                    <i class="fas fa-gem"></i>
                </div>
                <h4 class="fw-bold mb-3">Premium Quality</h4>
                <p class="text-muted mb-0 small" style="line-height:1.6;">We selectively curate high-end products across electronics, fashion, and home supplies. Only verified quality materials hit our shelves.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-5 h-100" style="border-radius:20px; background:var(--bg-secondary); border: 1px solid var(--border-color);">
                <div class="mb-4 d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 50%; background: rgba(124, 58, 237, 0.1); color: #7c3aed; font-size: 1.8rem; margin: 0 auto;">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <h4 class="fw-bold mb-3">Fast Shipping</h4>
                <p class="text-muted mb-0 small" style="line-height:1.6;">Our robust warehouse tracking guarantees your orders are processed in real-time and shipped safely with reliable tracking identifiers.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
