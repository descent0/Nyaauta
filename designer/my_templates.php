<?php
require_once '../includes/header.php';
requireRole('employee');

$message = '';

// Fetch all templates created by designers
$templates = $conn->query("
    SELECT t.*, u.username as created_by_name 
    FROM templates t 
    LEFT JOIN users u ON t.created_by = u.id 
    ORDER BY t.created_at DESC
");
?>

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
                        <table class="table table-striped">
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
                                    <td>
                                        <?php echo htmlspecialchars($template['id']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($template['name']); ?></td>
                                 
                                    <td><?php echo htmlspecialchars($template['created_by_name']); ?></td>
                                    <td> <?php echo htmlspecialchars($template['status'])?></td>
                                    <td><?php echo htmlspecialchars($template['created_at']); ?></td>
                                    <td>
                                        <a href="<?php echo SITE_URL?>designer/index.php?template_id=<?php echo $template['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fas fa-eye"></i> Open
                                        </a>
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

<?php include '../includes/footer.php'; ?>
