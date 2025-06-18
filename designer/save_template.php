<?php
include_once '../includes/auth.php';
require_once "../config/database.php";
requireRole('employee'); 
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $raw_input = file_get_contents('php://input');
    $input = json_decode($raw_input, true);

    if (!$input) {
        throw new Exception('Invalid JSON data');
    }

    // Validate fields
    if (empty($input['category_id']) || !is_numeric($input['category_id'])) {
        throw new Exception('Valid category ID is required');
    }

    if (empty($input['design_data'])) {
        throw new Exception('Design data is required');
    }

    $template_id = isset($input['template_id']) ? intval($input['template_id']) : null;
    $name = isset($input['name']) ? trim($input['name']) : 'Untitled';
    $category_id = (int)$input['category_id'];
    $design_data = $input['design_data'];
    $user_id = $_SESSION['user_id'];

    // Sanitize design data
    if (is_string($design_data)) {
        $design_data = str_replace(['\0', '\x00'], '', $design_data);
        $design_data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $design_data);
    }

    // Check if category exists
    $category_check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    if (!$category_check) {
        throw new Exception("Category check prepare failed: " . $conn->error);
    }
    $category_check->bind_param("i", $category_id);
    $category_check->execute();
    $category_result = $category_check->get_result();
    $category_check->close();

    if ($category_result->num_rows == 0) {
        throw new Exception('Category does not exist');
    }

    if ($template_id) {
        // Update existing template
        $stmt = $conn->prepare("UPDATE templates SET name = ?, category_id = ?, design_data = ? WHERE id = ? AND created_by = ?");
        if (!$stmt) {
            throw new Exception("Update prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sisii", $name, $category_id, $design_data, $template_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Template updated successfully',
                'template_id' => $template_id,
            ]);
        } else {
            throw new Exception('Update failed: ' . $stmt->error);
        }
        $stmt->close();
    } else {
        // Insert new template
        $stmt = $conn->prepare("INSERT INTO templates (name, category_id, design_data, created_by, status) VALUES (?, ?, ?, ?, 'draft')");
        if (!$stmt) {
            throw new Exception("Insert prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sisi", $name, $category_id, $design_data, $user_id);
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Template created successfully',
                'template_id' => $conn->insert_id,
            ]);
        } else {
            throw new Exception('Insert failed: ' . $stmt->error);
        }
        $stmt->close();
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'mysql_error' => isset($conn) ? $conn->error : 'No connection',
            'mysql_errno' => isset($conn) ? $conn->errno : 'No connection'
        ]
    ]);
}
?>
