<?php
require_once 'includes/header.php';
require_once 'config/database.php'; // Adjust path as needed

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM invitations WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
.invitation-preview {
    height: 200px;
    overflow: hidden;
    position: relative;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem 0.375rem 0 0;
}

.invitation-preview-content {
    transform-origin: top left;
    width: 100%;
    height: 100%;
    pointer-events: none;
    position: absolute;
    top: 0;
    left: 0;
}

.invitation-card {
    transition: transform 0.2s ease-in-out;
    cursor: pointer;
}

.invitation-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.invitation-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
}

.invitation-card:hover .invitation-overlay {
    opacity: 1;
}

.edit-btn {
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
            <h2>My Invitations</h2>
            <p class="text-muted">Manage and edit your invitation designs</p>
        </div>
    </div>

    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($inv = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 invitation-card" onclick="editInvitation(<?php echo $inv['id']; ?>)">
                        <div class="invitation-preview">
                            <?php if ($inv['design_data']): ?>
                                <div class="invitation-preview-content">
                                    <?php 
                                    echo '<div style="transform: scale(0.3); transform-origin: top left; width: 333.33%; height: 333.33%;">' . $inv['design_data'] . '</div>';
                                    ?>
                                </div>
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="invitation-overlay">
                                <a href="edit_invitation.php?invitation_id=<?php echo $inv['id']; ?>" class="edit-btn">
                                    <i class="fas fa-pen me-1"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            
                            <div class="mt-auto">
                                <a href="edit_invitation.php?invitation_id=<?php echo $inv['id']; ?>" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-pen me-1"></i> Edit Invitation
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <h4>No invitations found</h4>
                    <p>You haven't created any invitations yet.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function editInvitation(invitationId) {
    window.location.href = 'customize.php?invitation_id=' + invitationId;
}
</script>

<?php include 'includes/footer.php'; ?>
