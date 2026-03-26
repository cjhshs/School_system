<?php
require_once dirname(dirname(__DIR__)) . '/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/rbac.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header('Location: ../login.php');
    exit;
}

$logs = $conn->query("SELECT ual.*, su.username, su.first_name, su.last_name 
                      FROM activity_logs ual 
                      LEFT JOIN system_users su ON ual.user_id = su.id 
                      ORDER BY ual.created_at DESC LIMIT 100");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-history"></i> Activity Logs</h1>
        <p>Monitor system activity</p>
    </div>
</div>

<div class="card">
    <div class="table-header">
        <h5 class="table-title"><i class="fas fa-list me-2"></i>Recent User Activity</h5>
    </div>
    <div class="card-body pt-0">
        <div class="search-container">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input table-search" data-table="logsTable" placeholder="Search logs by user, action, or description...">
                <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="logsTable">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs && $logs->num_rows > 0): ?>
                    <?php while ($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar"><?php echo strtoupper(substr($log['first_name'] ?? 'U', 0, 1)); ?></div>
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')); ?></div>
                                    <small class="text-muted">@<?php echo htmlspecialchars($log['username'] ?? 'Unknown'); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($log['action']); ?></span></td>
                        <td><?php echo htmlspecialchars($log['description'] ?? '-'); ?></td>
                        <td><code><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></code></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-history"></i></div>
                                <h4>No Activity Logs</h4>
                                <p>System activity will be recorded here.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelector('.table-search[data-table="logsTable"]')?.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#logsTable tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

function clearSearch(btn) {
    const input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    input.dispatchEvent(new Event('input'));
}
</script>
