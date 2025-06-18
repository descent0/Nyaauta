<?php
// Turn off error reporting to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

require_once 'includes/auth.php';
require_once 'config/database.php'; 
requireLogin('user');

// Set JSON header at the very beginning
header('Content-Type: application/json');

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $data = []) {
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $data);
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Invalid request method');
}

if (!isset($_SESSION['user_id'])) {
    sendJsonResponse(false, 'Unauthorized');
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Invalid JSON input');
    }

    if (!isset($data['template_id']) || !is_numeric($data['template_id'])) {
        throw new Exception('Valid template_id is required');
    }

    if (empty($data['design_data'])) {
        throw new Exception('Design data is required');
    }

    $template_id = intval($data['template_id']);
    $design_data = $data['design_data'];
    $user_id = $_SESSION['user_id'];
    $invitation_id = isset($data['invitation_id']) ? intval($data['invitation_id']) : null;

    // Check if template exists and is published
    $template_check = $conn->prepare("SELECT id FROM templates WHERE id = ? AND status = 'published'");
    if (!$template_check) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $template_check->bind_param("i", $template_id);
    $template_check->execute();
    $template_result = $template_check->get_result();
    $template_check->close();

    if ($template_result->num_rows === 0) {
        throw new Exception('Template not found or not published');
    }

    if ($invitation_id) {
        // Update existing invitation
        $verify_stmt = $conn->prepare("SELECT id FROM invitations WHERE id = ? AND user_id = ?");
        if (!$verify_stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }
        
        $verify_stmt->bind_param("ii", $invitation_id, $user_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        $verify_stmt->close();

        if ($verify_result->num_rows === 0) {
            throw new Exception('Invitation not found or access denied');
        }

        // Update the invitation
        $stmt = $conn->prepare("UPDATE invitations SET design_data = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }
        
        $stmt->bind_param("sii", $design_data, $invitation_id, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Database execute error: ' . $stmt->error);
        }
        $stmt->close();

        $message = 'Invitation updated successfully';
    } else {
        // Insert new invitation
        $stmt = $conn->prepare("INSERT INTO invitations (user_id, template_id, design_data, created_at) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }
        
        $stmt->bind_param("iis", $user_id, $template_id, $design_data);

        if (!$stmt->execute()) {
            throw new Exception('Database execute error: ' . $stmt->error);
        }

        $invitation_id = $stmt->insert_id;
        $stmt->close();
        
        $message = 'Invitation saved successfully';
    }

    // Return success response
    sendJsonResponse(true, $message, [
        'invitation_id' => $invitation_id,
        'redirect_url' => "customize.php?invitation_id=" . $invitation_id
    ]);

} catch (Exception $e) {
    sendJsonResponse(false, $e->getMessage(), [
        'debug' => [
            'mysql_error' => isset($conn) ? $conn->error : 'No connection',
            'mysql_errno' => isset($conn) ? $conn->errno : 'No connection'
        ]
    ]);
}
?>