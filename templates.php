<?php require_once 'includes/header.php';

$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT t.*, c.name as category_name FROM templates t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.status = 'published'";

if ($category_filter) {
    $sql .= " AND t.category_id = " . intval($category_filter);
}

if ($search) {
    $sql .= " AND t.name LIKE '%" . $conn->real_escape_string($search) . "%'";
}

$sql .= " ORDER BY t.created_at DESC";
$templates = $conn->query($sql);

$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<style>
.template-preview {
    height: 200px;
    overflow: hidden;
    position: relative;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem 0.375rem 0 0;
}

.template-preview-content {
    transform-origin: top left;
    width: 100%;
    height: 100%;
    pointer-events: none;
    position: absolute;
    top: 0;
    left: 0;
}

.template-preview-content > * {
    max-width: 100% !important;
    max-height: 100% !important;
    box-sizing: border-box !important;
}

.template-card {
    transition: transform 0.2s ease-in-out;
    cursor: pointer;
}

.template-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.template-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
}

.template-card:hover .template-overlay {
    opacity: 1;
}

.preview-btn {
    background: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
    text-decoration: none;
    color: #333;
}
</style>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2>Invitation Templates</h2>
            <p class="text-muted">Choose from our collection of professional templates</p>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form method="GET" class="d-flex">
                <input type="text" class="form-control me-2" name="search" placeholder="Search templates..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <div class="col-md-6">
            <form method="GET">
                <select class="form-select" name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>
    </div>
    
    <!-- Templates Grid -->
    <div class="row">
        <?php if ($templates->num_rows > 0): ?>
            <?php while ($template = $templates->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 template-card" onclick="selectTemplate(<?php echo $template['id']; ?>)">
                    <div class="template-preview">
                        <?php if ($template['design_data']): ?>
                            <div class="template-preview-content">
                                <?php 
                                // Scale down the template content to fit the preview
                                $scaled_content = '<div style="transform: scale(0.3); transform-origin: top left; width: 333.33%; height: 333.33%;">' . $template['design_data'] . '</div>';
                                echo $scaled_content;
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="template-overlay">
                            <a href="customize.php?template=<?php echo $template['id']; ?>" class="preview-btn">
                                <i class="fas fa-edit me-1"></i> Use Template
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($template['name']); ?></h5>
                        <p class="card-text text-muted mb-2"><?php echo htmlspecialchars($template['category_name']); ?></p>
                        
                        <?php if (!empty($template['description'])): ?>
                            <p class="card-text small text-muted"><?php echo htmlspecialchars(substr($template['description'], 0, 100)); ?>...</p>
                        <?php endif; ?>
                        
                        <div class="mt-auto">
                            <a href="customize.php?template=<?php echo $template['id']; ?>" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-edit me-1"></i> Customize
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <h4>No templates found</h4>
                    <p>Try adjusting your search criteria or browse all categories.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function selectTemplate(templateId) {
    window.location.href = 'customize.php?template=' + templateId;
}
</script>

<?php include 'includes/footer.php'; ?>