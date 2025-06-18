<?php
require_once '../includes/header.php';
requireRole('admin');

$message = '';
$designDataToShow = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $template_id = $_POST['template_id'];
        $new_status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE templates SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $template_id);
        $message = $stmt->execute()
            ? '<div class="alert alert-success">Template status updated successfully!</div>'
            : '<div class="alert alert-danger">Failed to update template status.</div>';
    }

    // Handle modal preview request
    if (isset($_POST['preview_template_id'])) {
        $preview_id = $_POST['preview_template_id'];
        $stmt = $conn->prepare("SELECT design_data FROM templates WHERE id = ?");
        $stmt->bind_param("i", $preview_id);
        $stmt->execute();
        $stmt->bind_result($designDataToShow);
        $stmt->fetch();
        $stmt->close();
    }
}

// Fetch templates
$templates = $conn->query("
    SELECT t.*, u.username AS created_by_name 
    FROM templates t 
    LEFT JOIN users u ON t.created_by = u.id 
    ORDER BY t.created_at DESC
");
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Manage Templates</h2>
            <?php echo $message; ?>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Existing Templates</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($template = $templates->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $template['id']; ?></td>
                                        <td><?php echo htmlspecialchars($template['name']); ?></td>
                                        <td><?php echo htmlspecialchars($template['created_by_name']); ?></td>
                                        <td>
                                            <form method="POST" class="d-flex align-items-center">
                                                <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                                <select name="status" class="form-select form-select-sm me-2" required>
                                                    <option value="draft" <?php if ($template['status'] === 'draft') echo 'selected'; ?>>Draft</option>
                                                    <option value="published" <?php if ($template['status'] === 'published') echo 'selected'; ?>>Published</option>
                                                    <option value="archived" <?php if ($template['status'] === 'archived') echo 'selected'; ?>>Archived</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td><?php echo htmlspecialchars($template['created_at']); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="preview_template_id" value="<?php echo $template['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> Open
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if ($templates->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No templates found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview (triggered if $designDataToShow is filled) -->
<?php if (!empty($designDataToShow)): ?>
<div class="modal fade show" id="templateModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-xl ">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Template Preview</h5>
        <button type="button" class="btn-close" aria-label="Close" onclick="closeModal()"></button>
      </div>
      <div class="modal-body">
        <?php echo $designDataToShow; ?>
      </div>
    </div>
  </div>
</div>

<script>
function closeModal() {
    const modal = document.getElementById('templateModal');
    if (modal) {
        modal.remove(); // Remove modal from DOM
        window.history.pushState({}, document.title, window.location.pathname); // Clean URL
    }
}
</script>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include '../includes/footer.php'; ?>
