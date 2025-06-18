<?php
require_once 'includes/header.php';
requireLogin();

$user = getCurrentUser();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
            <p class="text-muted">Role: <?php echo ucfirst($user['role']); ?></p>
        </div>
    </div>
    
    <div class="row mt-4">
        <?php if (hasRole('user')): ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                        <h5>Create Invitation</h5>
                        <p>Start with a template or create from scratch</p>
                        <a href="templates.php" class="btn btn-primary">Get Started</a>
                    </div>
                </div>
            </div>
          
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-history fa-3x text-info mb-3"></i>
                        <h5>My Invitations</h5>
                        <p>View and manage your invitations</p>
                        <a href="invitations.php" class="btn btn-info">View All</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (hasRole('employee')): ?>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-paint-brush fa-3x text-primary mb-3"></i>
                        <h5>Design Studio</h5>
                        <p>Create and edit invitation templates</p>
                        <a href="designer/index.php" class="btn btn-primary">Open Designer</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-templates fa-3x text-success mb-3"></i>
                        <h5>My Templates</h5>
                        <p>Manage your created templates</p>
                        <a href="designer/my_templates.php" class="btn btn-success">View Templates</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (hasRole('admin')): ?>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h5>Users</h5>
                        <p>Manage system users</p>
                        <a href="admin/users.php" class="btn btn-primary">Manage</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-list fa-3x text-success mb-3"></i>
                        <h5>Categories</h5>
                        <p>Manage invitation categories</p>
                        <a href="admin/categories.php" class="btn btn-success">Manage</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-templates fa-3x text-info mb-3"></i>
                        <h5>Templates</h5>
                        <p>Manage all templates</p>
                        <a href="admin/all_templates.php" class="btn btn-info">Manage</a>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
