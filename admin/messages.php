<?php
/**
 * Online shopping registration system Admin Messages Viewer
 * Read and manage customer contact messages.
 */

$page_title = 'Messages';
require_once __DIR__ . '/admin_header.php';

$action = $_GET['action'] ?? 'list';
$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle Delete
if ($action === 'delete' && $message_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $_SESSION['flash_message'] = "Message deleted successfully.";
        $_SESSION['flash_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Failed to delete message.";
        $_SESSION['flash_type'] = "error";
    }
    redirect('messages.php');
    exit;
}

// Handle Read/Unread Status Toggle
if ($action === 'toggle_read' && $message_id > 0) {
    try {
        $stmt = $pdo->prepare("UPDATE messages SET is_read = NOT is_read WHERE id = ?");
        $stmt->execute([$message_id]);
    } catch (PDOException $e) {}
    redirect('messages.php');
    exit;
}

// Handle View details (and auto-mark as read)
if ($action === 'view' && $message_id > 0) {
    try {
        // Auto mark as read
        $stmt_update = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND is_read = 0");
        $stmt_update->execute([$message_id]);
        
        $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $msg = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$msg) {
            $_SESSION['flash_message'] = "Message not found.";
            $_SESSION['flash_type'] = "error";
            redirect('messages.php');
            exit;
        }
    } catch (PDOException $e) {
        $msg = null;
    }
} else {
    // Fetch messages list
    try {
        $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $messages = [];
    }
}
?>

<div class="page-header">
    <div>
        <h1>Messages</h1>
        <div class="breadcrumb">
            <a href="index.php">Admin</a> / <?php echo $action === 'view' ? '<a href="messages.php">Messages</a> / View' : 'Messages'; ?>
        </div>
    </div>
    <?php if ($action === 'view'): ?>
    <div class="header-actions">
        <a href="messages.php" class="btn" style="background: #2d3748; color: #e4e6eb;">
            <i class="fas fa-arrow-left"></i> Back to Messages
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if ($action === 'view' && $msg): ?>
    <div class="admin-card" style="max-width: 800px;">
        <div class="card-header" style="align-items: flex-start; flex-direction: column; gap: 10px;">
            <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                <h3 style="font-size: 1.3rem; color: #e4e6eb;"><?php echo htmlspecialchars($msg['subject']); ?></h3>
                <span class="status-badge <?php echo $msg['is_read'] ? 'read' : 'unread'; ?>">
                    <?php echo $msg['is_read'] ? 'Read' : 'Unread'; ?>
                </span>
            </div>
            <div style="display: flex; gap: 20px; font-size: 0.85rem; color: #9ca3af;">
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($msg['name']); ?></span>
                <span><i class="fas fa-envelope"></i> <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="color: #818cf8;"><?php echo htmlspecialchars($msg['email']); ?></a></span>
                <span><i class="fas fa-clock"></i> <?php echo date('M d, Y - h:i A', strtotime($msg['created_at'])); ?></span>
            </div>
        </div>
        <div style="padding: 20px; background: #0f1117; border-radius: 8px; border: 1px solid #2d3748; line-height: 1.6; color: #d1d5db; white-space: pre-wrap; font-size: 0.95rem; margin-bottom: 20px;">
<?php echo htmlspecialchars($msg['message']); ?>
        </div>
        
        <div class="form-actions" style="margin-top: 0; padding-top: 16px; border-top: 1px solid #2d3748;">
            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" class="btn btn-primary">
                <i class="fas fa-reply"></i> Reply via Email
            </a>
            <a href="?action=delete&id=<?php echo $msg['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this message?');">
                <i class="fas fa-trash"></i> Delete
            </a>
        </div>
    </div>
<?php elseif ($action === 'list'): ?>
    <div class="admin-card">
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="40">Status</th>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th width="140" style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($messages) > 0): ?>
                        <?php foreach ($messages as $m): ?>
                            <tr style="<?php echo !$m['is_read'] ? 'background: rgba(129,140,248,0.05);' : ''; ?>">
                                <td style="text-align: center;">
                                    <?php if ($m['is_read']): ?>
                                        <i class="fas fa-envelope-open" style="color: #9ca3af;" title="Read"></i>
                                    <?php else: ?>
                                        <i class="fas fa-envelope" style="color: #818cf8;" title="Unread"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($m['name']); ?></strong><br>
                                    <small style="color: #6b7280;"><?php echo htmlspecialchars($m['email']); ?></small>
                                </td>
                                <td>
                                    <div style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; <?php echo !$m['is_read'] ? 'font-weight: 600; color: #e4e6eb;' : ''; ?>">
                                        <?php echo htmlspecialchars($m['subject']); ?>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($m['created_at'])); ?></td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <a href="?action=view&id=<?php echo $m['id']; ?>" class="btn btn-icon btn-primary" style="background: rgba(129,140,248,0.15); color: #818cf8;" title="View Message">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?action=toggle_read&id=<?php echo $m['id']; ?>" class="btn btn-icon" style="background: #2d3748; color: #9ca3af;" title="<?php echo $m['is_read'] ? 'Mark Unread' : 'Mark Read'; ?>">
                                            <i class="fas <?php echo $m['is_read'] ? 'fa-envelope' : 'fa-envelope-open'; ?>"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $m['id']; ?>" class="btn btn-icon btn-danger" title="Delete" onclick="return confirm('Delete this message?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>No messages found.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
