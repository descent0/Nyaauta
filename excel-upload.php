<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireLogin('user');

header('Content-Type: application/json');

define('SMTP_FROM_EMAIL', 'nowaitz84@gmail.com'); 
define('SMTP_FROM_NAME', 'Nyaauta'); 
define('EMAIL_SUBJECT', 'You\'re Invited! Special Invitation Just for You');

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
    $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
    $design_data = isset($_POST['design_data']) ? $_POST['design_data'] : '';
    $method = isset($_POST['method']) ? $_POST['method'] : '';
    $user_id = $_SESSION['user_id'];

    if (!$template_id || empty($design_data)) {
        throw new Exception('Template ID and design data are required');
    }

    // Check if template exists and is published
    $template_check = $conn->prepare("SELECT id, name FROM templates WHERE id = ? AND status = 'published'");
    if (!$template_check) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $template_check->bind_param("i", $template_id);
    $template_check->execute();
    $template_result = $template_check->get_result();
    $template_data = $template_result->fetch_assoc();
    $template_check->close();

    if (!$template_data) {
        throw new Exception('Template not found or not published');
    }

    $recipients = [];

    if ($method === 'excel') {
        // Handle Excel file upload
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Excel file upload failed');
        }

        $recipients = processExcelFile($_FILES['excel_file']);
    } elseif ($method === 'manual') {
        // Handle manual entry
        if (!isset($_POST['recipients'])) {
            throw new Exception('Recipients data is required');
        }

        $recipients_data = json_decode($_POST['recipients'], true);
        if (!$recipients_data) {
            throw new Exception('Invalid recipients data');
        }

        $recipients = $recipients_data;
    } else {
        throw new Exception('Invalid method specified');
    }

    if (empty($recipients)) {
        throw new Exception('No valid recipients found');
    }

    // Create bulk invitations table if it doesn't exist
    createBulkInvitationsTable($conn);

    // Process each recipient
    $sent_count = 0;
    $failed_count = 0;
    $email_sent_count = 0;
    $email_failed_count = 0;
    $errors = [];

    foreach ($recipients as $recipient) {
        try {
            // Validate recipient data
            if (empty($recipient['name']) || empty($recipient['email'])) {
                $failed_count++;
                $errors[] = "Missing name or email for recipient";
                continue;
            }

            // Validate email format
            if (!filter_var($recipient['email'], FILTER_VALIDATE_EMAIL)) {
                $failed_count++;
                $errors[] = "Invalid email format: " . $recipient['email'];
                continue;
            }

            // Personalize the design data
            $personalized_design = personalizeDesignData($design_data, $recipient);

            // Insert invitation
            $stmt = $conn->prepare("INSERT INTO invitations (user_id, template_id, design_data, created_at) VALUES (?, ?, ?, NOW())");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $conn->error);
            }
            
            $stmt->bind_param("iis", $user_id, $template_id, $personalized_design);
            
            if ($stmt->execute()) {
                $invitation_id = $stmt->insert_id;
                
                // Send email invitation
                $email_sent = sendInvitationEmail($recipient, $personalized_design, $template_data['name']);
                
                // Insert bulk invitation record with email status
                $bulk_stmt = $conn->prepare("INSERT INTO bulk_invitations (invitation_id, recipient_name, recipient_email, recipient_whatsapp, recipient_address, email_sent, sent_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                if ($bulk_stmt) {
                    $whatsapp = isset($recipient['whatsapp']) ? $recipient['whatsapp'] : '';
                    $address = isset($recipient['address']) ? $recipient['address'] : '';
                    $email_status = $email_sent ? 1 : 0;
                    
                    $bulk_stmt->bind_param("issssi", $invitation_id, $recipient['name'], $recipient['email'], $whatsapp, $address, $email_status);
                    $bulk_stmt->execute();
                    $bulk_stmt->close();
                }
                
                $sent_count++;
                
                if ($email_sent) {
                    $email_sent_count++;
                } else {
                    $email_failed_count++;
                    $errors[] = "Invitation created but email failed for: " . $recipient['email'];
                }
                
            } else {
                $failed_count++;
                $errors[] = "Failed to create invitation for: " . $recipient['email'];
            }
            
            $stmt->close();

        } catch (Exception $e) {
            $failed_count++;
            $errors[] = "Error processing " . $recipient['email'] . ": " . $e->getMessage();
        }
    }

    // Prepare response
    $response_data = [
        'sent_count' => $sent_count,
        'failed_count' => $failed_count,
        'email_sent_count' => $email_sent_count,
        'email_failed_count' => $email_failed_count
    ];

    if (!empty($errors)) {
        $response_data['errors'] = array_slice($errors, 0, 10); // Limit to first 10 errors
    }

    if ($sent_count > 0) {
        $message = "Bulk invitation process completed. ";
        $message .= "Invitations created: {$sent_count}, ";
        $message .= "Emails sent: {$email_sent_count}";
        if ($email_failed_count > 0) {
            $message .= ", Email failures: {$email_failed_count}";
        }
        sendJsonResponse(true, $message, $response_data);
    } else {
        sendJsonResponse(false, "No invitations were sent successfully", $response_data);
    }

} catch (Exception $e) {
    sendJsonResponse(false, $e->getMessage(), [
        'debug' => [
            'mysql_error' => isset($conn) ? $conn->error : 'No connection',
            'mysql_errno' => isset($conn) ? $conn->errno : 'No connection'
        ]
    ]);
}

function sendInvitationEmail($recipient, $personalized_design, $template_name) {
    try {
        $to = $recipient['email'];
        $subject = EMAIL_SUBJECT;
        
        // Create email body with the personalized invitation
        $email_body = createEmailTemplate($personalized_design, $recipient, $template_name);
        
        // Email headers
        $headers = array();
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>';
        $headers[] = 'Reply-To: ' . SMTP_FROM_EMAIL;
        $headers[] = 'X-Mailer: PHP/' . phpversion();
        
        // Send email
        $mail_sent = mail($to, $subject, $email_body, implode("\r\n", $headers));
        
        return $mail_sent;
        
    } catch (Exception $e) {
        error_log("Email sending failed for " . $recipient['email'] . ": " . $e->getMessage());
        return false;
    }
}

function createEmailTemplate($personalized_design, $recipient, $template_name) {
    $email_template = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Special Invitation for ' . htmlspecialchars($recipient['name']) . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f4f4f4;
            }
            .email-container {
                background-color: #ffffff;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #e0e0e0;
            }
            .header h1 {
                color: #2c3e50;
                margin: 0;
                font-size: 28px;
            }
            .greeting {
                font-size: 18px;
                margin-bottom: 20px;
                color: #2c3e50;
            }
            .invitation-content {
                background-color: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
                border-left: 4px solid #3498db;
            }
            .footer {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #e0e0e0;
                text-align: center;
                color: #7f8c8d;
                font-size: 14px;
            }
            .cta-button {
                display: inline-block;
                background-color: #3498db;
                color: white;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 5px;
                margin: 20px 0;
                font-weight: bold;
            }
            .contact-info {
                background-color: #ecf0f1;
                padding: 15px;
                border-radius: 5px;
                margin: 15px 0;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="header">
                <h1>🎉 Special Invitation</h1>
            </div>
            
            <div class="greeting">
                Dear ' . htmlspecialchars($recipient['name']) . ',
            </div>
            
            <p>We are delighted to invite you to our special event! This personalized invitation has been created especially for you.</p>
            
            <div class="invitation-content">
                ' . $personalized_design . '
            </div>
            
            <p>We hope you can join us for this special occasion. Your presence would make our event even more memorable!</p>';

    // Add contact information if available
    if (!empty($recipient['whatsapp']) || !empty($recipient['address'])) {
        $email_template .= '<div class="contact-info">
                <h4>Your Details:</h4>';
        
        if (!empty($recipient['whatsapp'])) {
            $email_template .= '<p><strong>WhatsApp:</strong> ' . htmlspecialchars($recipient['whatsapp']) . '</p>';
        }
        
        if (!empty($recipient['address'])) {
            $email_template .= '<p><strong>Address:</strong> ' . htmlspecialchars($recipient['address']) . '</p>';
        }
        
        $email_template .= '</div>';
    }

    $email_template .= '
            <div class="footer">
                <p>This invitation was sent from <strong>' . htmlspecialchars($template_name) . '</strong></p>
                <p>If you have any questions, please don\'t hesitate to contact us.</p>
                <p><small>This is an automated email. Please do not reply to this email address.</small></p>
            </div>
        </div>
    </body>
    </html>';

    return $email_template;
}

function processExcelFile($file) {
    // Simple CSV/Excel processing without external libraries
    $recipients = [];
    $file_path = $file['tmp_name'];
    
    // Check if it's a CSV file or try to read as text
    $file_content = file_get_contents($file_path);
    
    if ($file_content === false) {
        throw new Exception('Could not read the uploaded file');
    }

    // Try to detect if it's a simple CSV format
    $lines = explode("\n", $file_content);
    
    if (empty($lines)) {
        throw new Exception('File appears to be empty');
    }

    // Check if first line contains headers
    $headers = str_getcsv(trim($lines[0]));
    $header_map = [];
    
    // Map headers to expected fields (case insensitive)
    foreach ($headers as $index => $header) {
        $header_lower = strtolower(trim($header));
        if (in_array($header_lower, ['name', 'full name', 'fullname'])) {
            $header_map['name'] = $index;
        } elseif (in_array($header_lower, ['email', 'email address', 'e-mail'])) {
            $header_map['email'] = $index;
        } elseif (in_array($header_lower, ['whatsapp', 'whatsapp number', 'phone', 'mobile'])) {
            $header_map['whatsapp'] = $index;
        } elseif (in_array($header_lower, ['address', 'location', 'addr'])) {
            $header_map['address'] = $index;
        }
    }

    if (!isset($header_map['name']) || !isset($header_map['email'])) {
        throw new Exception('Excel file must contain Name and Email columns');
    }

    // Process data rows
    for ($i = 1; $i < count($lines); $i++) {
        $line = trim($lines[$i]);
        if (empty($line)) continue;
        
        $data = str_getcsv($line);
        
        if (count($data) < 2) continue; // Skip invalid rows
        
        $recipient = [
            'name' => isset($data[$header_map['name']]) ? trim($data[$header_map['name']]) : '',
            'email' => isset($data[$header_map['email']]) ? trim($data[$header_map['email']]) : '',
            'whatsapp' => isset($header_map['whatsapp']) && isset($data[$header_map['whatsapp']]) ? trim($data[$header_map['whatsapp']]) : '',
            'address' => isset($header_map['address']) && isset($data[$header_map['address']]) ? trim($data[$header_map['address']]) : ''
        ];
        
        if (!empty($recipient['name']) && !empty($recipient['email'])) {
            $recipients[] = $recipient;
        }
    }

    if (empty($recipients)) {
        throw new Exception('No valid recipients found in the Excel file');
    }

    return $recipients;
}

function personalizeDesignData($design_data, $recipient) {
    // Replace placeholders in the design data with recipient information
    $personalized = $design_data;
    
    // Common placeholders that might be in the template
    $placeholders = [
        '{{name}}' => $recipient['name'],
        '{{email}}' => $recipient['email'],
        '{{whatsapp}}' => isset($recipient['whatsapp']) ? $recipient['whatsapp'] : '',
        '{{address}}' => isset($recipient['address']) ? $recipient['address'] : '',
        '[NAME]' => $recipient['name'],
        '[EMAIL]' => $recipient['email'],
        '[WHATSAPP]' => isset($recipient['whatsapp']) ? $recipient['whatsapp'] : '',
        '[ADDRESS]' => isset($recipient['address']) ? $recipient['address'] : ''
    ];
    
    foreach ($placeholders as $placeholder => $value) {
        $personalized = str_replace($placeholder, $value, $personalized);
    }
    
    return $personalized;
}

function createBulkInvitationsTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS bulk_invitations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invitation_id INT NOT NULL,
        recipient_name VARCHAR(255) NOT NULL,
        recipient_email VARCHAR(255) NOT NULL,
        recipient_whatsapp VARCHAR(50),
        recipient_address TEXT,
        email_sent TINYINT(1) DEFAULT 0,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE CASCADE,
        INDEX idx_invitation_id (invitation_id),
        INDEX idx_recipient_email (recipient_email),
        INDEX idx_email_sent (email_sent)
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception('Failed to create bulk_invitations table: ' . $conn->error);
    }
}
?>