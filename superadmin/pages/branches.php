<?php
require_once dirname(dirname(__DIR__)) . '/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/rbac.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'super_admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $code = $_POST['code'];
            $name = $_POST['name'];
            $address = $_POST['address'];
            $contact = $_POST['contact_number'];
            $is_main = isset($_POST['is_main']) ? 1 : 0;
            
            if ($is_main) {
                $conn->query("UPDATE branches SET is_main = 0");
            }
            
            $stmt = $conn->prepare("INSERT INTO branches (code, name, address, contact_number, is_main) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $code, $name, $address, $contact, $is_main);
            
            if ($stmt->execute()) {
                $message = "Branch added successfully!";
            } else {
                $error = "Error adding branch: " . $conn->error;
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            if ($conn->query("DELETE FROM branches WHERE id = $id AND is_main = 0")) {
                $message = "Branch deleted successfully!";
            } else {
                $error = "Cannot delete main branch!";
            }
        }
    }
}

$branches = $conn->query("SELECT * FROM branches ORDER BY is_main DESC, name ASC");
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-building"></i> Manage Branches</h1>
        <p>Add and manage campus branches</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-plus me-2"></i>Add New Branch</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="form-row">
    <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label class="form-label">Branch Code</label>
                <input type="text" name="code" class="form-control" required placeholder="e.g., MAIN">
            </div>
            <div class="form-group">
                <label class="form-label">Branch Name</label>
                <input type="text" name="name" class="form-control" required placeholder="Branch Name">
            </div>
            <div class="form-group">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" placeholder="Full Address">
            </div>
            <div class="form-group">
                <label class="form-label">Contact</label>
                <input type="text" name="contact_number" class="form-control" placeholder="Phone Number">
            </div>
            <div class="form-group" style="flex: 0 0 auto; align-self: flex-end;">
                <div class="form-check">
                    <input type="checkbox" name="is_main" class="form-check-input" id="is_main">
                    <label class="form-check-label" for="is_main">Main Branch</label>
                </div>
            </div>
            <div class="form-group" style="flex: 0 0 auto; align-self: flex-end;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Branch</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-header">
        <h5 class="table-title"><i class="fas fa-list me-2"></i>Existing Branches</h5>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($branch = $branches->fetch_assoc()): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($branch['code']); ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($branch['name']); ?>
                        <?php if ($branch['is_main']): ?>
                            <span class="badge badge-danger">MAIN</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($branch['address'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($branch['contact_number'] ?? '-'); ?></td>
                    <td>
                        <span class="status <?php echo $branch['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $branch['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!$branch['is_main']): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this branch?');">
    <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $branch['id']; ?>">
                                <button type="submit" class="btn btn-icon btn-ghost text-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-icon btn-ghost text-muted" disabled title="Cannot delete main branch">
                                <i class="fas fa-lock"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
