<?php include 'includes/header.php'; ?>

<div class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">Create Stunning Invitations</h1>
                <p class="lead">Design professional invitations with our easy-to-use platform. Upload Excel files for bulk generation or create custom designs.</p>
                <div class="mt-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="dashboard.php" class="btn btn-light btn-lg me-3">Go to Dashboard</a>
                    <?php else: ?>
                        <a href="auth/register.php" class="btn btn-light btn-lg me-3">Get Started</a>
                        <a href="templates.php" class="btn btn-outline-light btn-lg">Browse Templates</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/Bottom_Banner-Mput5L6PAvVOfi357AkqV2w33JV4el.avif" alt="Invitation Design" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h2>Popular Categories</h2>
            <p class="text-muted">Choose from our wide range of invitation categories</p>
        </div>
    </div>
    
    <div class="row">
        <?php
        $categories = $conn->query("SELECT * FROM categories ORDER BY created_at DESC LIMIT 6");
        while ($category = $categories->fetch_assoc()):
        ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <?php if ($category['image']): ?>
                    <img src="uploads/categories/<?php echo $category['image']; ?>" class="card-img-top" alt="<?php echo $category['name']; ?>" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($category['description']); ?></p>
                    <a href="templates.php?category=<?php echo $category['id']; ?>" class="btn btn-primary">View Templates</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2>Features</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <i class="fas fa-paint-brush fa-3x text-primary mb-3"></i>
                <h4>Design Studio</h4>
                <p>Professional design tools with drag-and-drop interface</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <i class="fas fa-file-excel fa-3x text-success mb-3"></i>
                <h4>Excel Integration</h4>
                <p>Upload Excel files to generate invitations automatically</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <i class="fas fa-download fa-3x text-info mb-3"></i>
                <h4>Easy Download</h4>
                <p>Download your invitations in high-quality formats</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
