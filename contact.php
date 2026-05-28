<?php
/**
 * ShopVerse - Interactive Contact Page
 */

require_once 'includes/functions.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    $csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf($csrf)) {
        flash('contact', 'Security token expired. Please try again.', 'danger');
        redirect('contact.php');
    }

    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        flash('contact', 'All form fields are required.', 'danger');
        redirect('contact.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('contact', 'Please provide a valid email address.', 'danger');
        redirect('contact.php');
    }

    // Insert Message into database
    try {
        $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        
        flash('contact', '🎉 Your message has been sent successfully! Our administrative team will reach out shortly.', 'success');
    } catch (PDOException $e) {
        flash('contact', 'Database error: Could not record message. Please try again.', 'danger');
    }

    redirect('contact.php');
}

$page_title = "Contact Support";
require_once 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════
     Page Breadcrumbs and Header
     ══════════════════════════════════════════════════════ -->
<div class="py-4 mb-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.03) 0%, rgba(99, 102, 241, 0.03) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 class="fw-bold mb-2">Get In Touch</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contact</li>
            </ol>
        </nav>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Contact Grid
     ══════════════════════════════════════════════════════ -->
<div class="container py-4">
    <!-- Alerts -->
    <?php display_flash('contact'); ?>

    <div class="row g-5">
        <!-- Contact Form Card -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4 p-md-5" style="border-radius:24px; background:var(--bg-secondary); border: 1px solid var(--border-color);">
                <h3 class="fw-bold mb-2">Send Us a Message</h3>
                <p class="text-secondary mb-4">Have questions about products, returns, or site issues? Write us a line.</p>

                <form action="contact.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6 form-group">
                            <label for="contact-name">Your Full Name</label>
                            <input type="text" class="form-control" id="contact-name" name="name" required placeholder="John Doe">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="contact-email">Email Address</label>
                            <input type="email" class="form-control" id="contact-email" name="email" required placeholder="john@example.com">
                        </div>
                        <div class="col-12 form-group">
                            <label for="contact-subject">Message Subject</label>
                            <input type="text" class="form-control" id="contact-subject" name="subject" required placeholder="e.g., Shipping Inquiry">
                        </div>
                        <div class="col-12 form-group">
                            <label for="contact-message">Detailed Message</label>
                            <textarea class="form-control" id="contact-message" name="message" rows="5" required placeholder="Write your message details here..."></textarea>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100" style="border-radius:50px;">
                                <i class="fas fa-paper-plane me-2"></i> Submit Inquiry
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Information Panels -->
        <div class="col-lg-5">
            <div class="d-flex flex-column gap-4">
                <!-- Info Widget 1: Phone -->
                <div class="card border-0 shadow-sm p-4 d-flex flex-row align-items-center gap-4" style="border-radius:20px; background:var(--bg-secondary); border:1px solid var(--border-color);">
                    <div class="d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; background: rgba(34, 197, 94, 0.1); color: #22c55e; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Call Customer Service</h6>
                        <span class="text-secondary small"><?php echo htmlspecialchars(get_setting($conn, 'contact_phone') ?? '+1 (555) 123-4567'); ?></span>
                        <br><small class="text-muted">Mon - Fri, 9:00 AM - 6:00 PM</small>
                    </div>
                </div>

                <!-- Info Widget 2: Mail -->
                <div class="card border-0 shadow-sm p-4 d-flex flex-row align-items-center gap-4" style="border-radius:20px; background:var(--bg-secondary); border:1px solid var(--border-color);">
                    <div class="d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; background: rgba(99, 102, 241, 0.1); color: #6366f1; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Email Support Channel</h6>
                        <span class="text-secondary small"><?php echo htmlspecialchars(get_setting($conn, 'contact_email') ?? 'support@shopverse.com'); ?></span>
                        <br><small class="text-muted">Avg response time: Under 4 hours</small>
                    </div>
                </div>

                <!-- Info Widget 3: Head office -->
                <div class="card border-0 shadow-sm p-4 d-flex flex-row align-items-center gap-4" style="border-radius:20px; background:var(--bg-secondary); border:1px solid var(--border-color);">
                    <div class="d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; background: rgba(124, 58, 237, 0.1); color: #7c3aed; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Corporate Headquarters</h6>
                        <span class="text-secondary small">123 Commerce Street, Tech City, TC 10001</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
