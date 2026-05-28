<?php
/**
 * ShopVerse Admin Dashboard
 * Displays high-level analytics, statistics, and recent activity.
 */

$page_title = 'Dashboard';
require_once __DIR__ . '/admin_header.php';

// Fetch Statistics

// 1. Total Revenue
try {
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'");
    $total_revenue = $stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $total_revenue = 0;
}

// 2. Total Orders
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $total_orders_stat = $stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $total_orders_stat = 0;
}

// 3. Total Users (Customers)
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    $total_customers = $stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $total_customers = 0;
}

// 4. Total Products
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $total_products = 0;
}

// Fetch Recent Orders
try {
    $stmt = $pdo->query("
        SELECT o.id, o.total_amount, o.status, o.created_at, u.full_name, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_orders = [];
}
?>

<div class="page-header">
    <div>
        <h1>Dashboard Overview</h1>
        <div class="breadcrumb">
            <a href="index.php">Admin</a> / Dashboard
        </div>
    </div>
    <div class="header-actions">
        <a href="orders.php" class="btn btn-primary">
            <i class="fas fa-file-invoice"></i> View All Orders
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="stat-cards">
    <div class="stat-card">
        <div class="stat-icon revenue">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <h3>$<?php echo number_format($total_revenue, 2); ?></h3>
            <p>Total Revenue</p>
        </div>
        <div class="stat-trend up">
            <i class="fas fa-arrow-up"></i> 8.5%
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orders">
            <i class="fas fa-shopping-bag"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($total_orders_stat); ?></h3>
            <p>Total Orders</p>
        </div>
        <div class="stat-trend up">
            <i class="fas fa-arrow-up"></i> 12.2%
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon users">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($total_customers); ?></h3>
            <p>Active Customers</p>
        </div>
        <div class="stat-trend up">
            <i class="fas fa-arrow-up"></i> 3.1%
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon products">
            <i class="fas fa-box-open"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo number_format($total_products); ?></h3>
            <p>Total Products</p>
        </div>
        <div class="stat-trend down">
            <i class="fas fa-arrow-down"></i> 1.4%
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="chart-grid">
    <div class="admin-card">
        <div class="card-header">
            <h3>Sales Overview</h3>
            <select class="form-control" style="width: auto; padding: 4px 10px; font-size: 0.8rem;">
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
                <option>This Year</option>
            </select>
        </div>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
    
    <div class="admin-card">
        <div class="card-header">
            <h3>Revenue by Category</h3>
        </div>
        <div class="chart-container">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="admin-card">
    <div class="card-header">
        <h3>Recent Orders</h3>
        <a href="orders.php" class="btn btn-sm btn-primary" style="background: transparent; border: 1px solid #818cf8; color: #818cf8;">View All</a>
    </div>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recent_orders) > 0): ?>
                    <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#ORD-<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <div><strong><?php echo htmlspecialchars($order['full_name']); ?></strong></div>
                                <div style="font-size: 0.75rem; color: #9ca3af;"><?php echo htmlspecialchars($order['email']); ?></div>
                            </td>
                            <td><?php echo date('M d, Y - h:i A', strtotime($order['created_at'])); ?></td>
                            <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            <td>
                                <span class="status-badge <?php echo htmlspecialchars($order['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-icon btn-primary" title="View Order">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 30px;">
                            <div class="empty-state" style="padding: 10px;">
                                <i class="fas fa-inbox"></i>
                                <p>No recent orders found.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Inline script for charts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart is available
    if (typeof Chart !== 'undefined') {
        // Sales Chart
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Sales ($)',
                        data: [1200, 1900, 1500, 2200, 1800, 2800, 2400],
                        borderColor: '#818cf8',
                        backgroundColor: 'rgba(129, 140, 248, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(45, 55, 72, 0.5)' },
                            ticks: { color: '#9ca3af' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#9ca3af' }
                        }
                    }
                }
            });
        }

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Electronics', 'Clothing', 'Home & Garden', 'Sports'],
                    datasets: [{
                        data: [45, 25, 20, 10],
                        backgroundColor: ['#818cf8', '#34d399', '#fbbf24', '#f87171'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { color: '#e4e6eb', padding: 20 }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
