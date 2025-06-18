<?php
require_once 'includes/header.php';
requireLogin();

$template_id = isset($_GET['template']) ? intval($_GET['template']) : 0;
$invitation_id = isset($_GET['invitation_id']) ? intval($_GET['invitation_id']) : 0;


if ($invitation_id) {
    // Get invitation data
    $stmt = $conn->prepare("SELECT i.*, t.name as template_name FROM invitations i JOIN templates t ON i.template_id = t.id WHERE i.id = ? AND i.user_id = ?");
    $stmt->bind_param("ii", $invitation_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $invitation = $result->fetch_assoc();
    $stmt->close();

    if (!$invitation) {
        header('Location: templates.php');
        exit();
    }

    $template = [
        'id' => $invitation['template_id'],
        'name' => $invitation['template_name'],
        'design_data' => $invitation['design_data']
    ];
    $template_id = $invitation['template_id'];
} else {
    if (!$template_id) {
        header('Location: templates.php');
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM templates WHERE id = ? AND status = 'published'");
    $stmt->bind_param("i", $template_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $template = $result->fetch_assoc();
    $stmt->close();

    if (!$template) {
        header('Location: templates.php');
        exit();
    }
}

$design_data = $template['design_data'] ? $template['design_data'] : "<div class='invitation-preview'>No design data available</div>";
?>

<style>
    :root {
        --primary-color: #6366f1;
        --secondary-color: #f8fafc;
        --accent-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --dark-color: #1f2937;
        --light-color: #f9fafb;
        --border-color: #e5e7eb;
    }

    body {
        background-color: var(--light-color);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .customization-container {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .top-bar {
        background: white;
        border-bottom: 1px solid var(--border-color);
        padding: 1rem 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .top-bar h4 {
        margin: 0;
        color: var(--dark-color);
        font-weight: 600;
    }

    .breadcrumb {
        background: none;
        padding: 0;
        margin: 0;
    }

    .breadcrumb-item a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .main-content {
        flex: 1;
        display: flex;
        height: calc(100vh - 80px);
    }

    .sidebar {
        width: 320px;
        background: white;
        border-right: 1px solid var(--border-color);
        padding: 1.5rem;
        overflow-y: auto;
    }

    .preview-area {
        flex: 1;
        padding: 2rem;
        display: flex;
        justify-content: center;
        align-items: center;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }

    .template-info {
        background: var(--secondary-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
    }

    .template-info h5 {
        color: var(--dark-color);
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .template-info p {
        color: #6b7280;
        margin: 0;
        font-size: 0.9rem;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 2rem;
    }

    .btn-modern {
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-weight: 500;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-primary-modern {
        background: var(--primary-color);
        color: white;
    }

    .btn-primary-modern:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
    }

    .btn-success-modern {
        background: var(--accent-color);
        color: white;
    }

    .btn-success-modern:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .btn-warning-modern {
        background: var(--warning-color);
        color: white;
    }

    .btn-warning-modern:hover {
        background: #d97706;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    }

    .btn-outline-modern {
        background: white;
        color: var(--dark-color);
        border: 1px solid var(--border-color);
    }

    .btn-outline-modern:hover {
        background: var(--secondary-color);
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    .invitation-preview {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        padding: 2rem;
        max-width: 600px;
        min-height: 400px;
        position: relative;
        overflow: hidden;
    }

    .textbox {
        cursor: text;
        min-height: 1.5em;
        border: 2px dashed transparent;
        border-radius: 4px;
        padding: 4px 8px;
        transition: all 0.2s ease;
        outline: none;
    }

    .textbox:hover {
        border-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.05);
    }

    .textbox:focus {
        border-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.1);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid var(--border-color);
        border-top: 4px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .success-message,
    .error-message {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: none;
    }

    .success-message {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .error-message {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .customization-tools {
        border-top: 1px solid var(--border-color);
        padding-top: 1.5rem;
    }

    .tool-section {
        margin-bottom: 1.5rem;
    }

    .tool-section h6 {
        color: var(--dark-color);
        font-weight: 600;
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Bulk Send Modal Styles */
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        border-bottom: 1px solid var(--border-color);
        padding: 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .upload-area {
        border: 2px dashed var(--border-color);
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        transition: all 0.2s ease;
        margin-bottom: 1rem;
    }

    .upload-area:hover {
        border-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.05);
    }

    .upload-area.dragover {
        border-color: var(--primary-color);
        background: rgba(99, 102, 241, 0.1);
    }

    .manual-entry-area {
        background: var(--secondary-color);
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1rem;
    }

    .recipient-row {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        align-items: center;
    }

    .recipient-row input {
        flex: 1;
        padding: 0.5rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .remove-recipient {
        background: var(--danger-color);
        color: white;
        border: none;
        border-radius: 4px;
        padding: 0.5rem;
        cursor: pointer;
        font-size: 0.8rem;
    }

    @media (max-width: 768px) {
        .main-content {
            flex-direction: column;
            height: auto;
        }

        .sidebar {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid var(--border-color);
        }

        .preview-area {
            padding: 1rem;
        }

        .recipient-row {
            flex-direction: column;
        }

        .recipient-row input {
            margin-bottom: 0.5rem;
        }
    }
</style>
</head>

<body>
    <div class="customization-container">
        <!-- Top Navigation Bar -->
        <div class="top-bar">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4>Template Editor</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-content">
            <!-- Sidebar with Controls -->
            <div class="sidebar">

                <div class="template-info">
                    <h5><?php echo htmlspecialchars($template['name']); ?></h5>
                    <p><?php echo $invitation_id ? 'Editing saved invitation' : 'Customize your invitation template'; ?></p>
                </div>

                <div class="success-message" id="successMessage">
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="successText"></span>
                </div>
                <div class="error-message" id="errorMessage">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="errorText"></span>
                </div>


                <div class="action-buttons">
                    <button class="btn btn-success-modern btn-modern" onclick="saveInvitation()">
                        <i class="fas fa-save"></i>
                        <?php echo $invitation_id ? 'Update Invitation' : 'Save Invitation'; ?>
                    </button>
                    <button class="btn btn-primary-modern btn-modern" onclick="downloadInvitation()">
                        <i class="fas fa-download"></i>
                        Download PNG
                    </button>
                    <button class="btn btn-warning-modern btn-modern" onclick="openBulkSendModal()">
                        <i class="fas fa-paper-plane"></i>
                        Bulk Send Invitations
                    </button>
                </div>

                <div class="customization-tools">
                    <div class="tool-section">
                        <h6>Instructions</h6>
                        <p class="text-muted small">
                            Click on any text in the preview to edit it. Changes can be saved to your account by Ctrl+S
                            or Click on save Invitation.
                        </p>
                    </div>
                </div>
            </div>

            <div class="preview-area">
                <div class="invitation-preview" id="invitationPreview" style="transform: scale(0.8); ">
                    <?php echo $template['design_data']; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bulkSendModal" tabindex="-1" aria-labelledby="bulkSendModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkSendModalLabel">
                        <i class="fas fa-paper-plane me-2"></i>
                        Bulk Send Invitations
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Upload Method Selection -->
                    <div class="mb-4">
                        <h6>Choose Input Method:</h6>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="inputMethod" id="excelMethod" value="excel" checked>
                            <label class="btn btn-outline-primary" for="excelMethod">
                                <i class="fas fa-file-excel me-2"></i>Upload Excel File
                            </label>
                            
                            <input type="radio" class="btn-check" name="inputMethod" id="manualMethod" value="manual">
                            <label class="btn btn-outline-primary" for="manualMethod">
                                <i class="fas fa-keyboard me-2"></i>Manual Entry
                            </label>
                        </div>
                    </div>

                    <div id="excelUploadArea">
                        <div class="upload-area" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <h6>Drop Excel file here or click to browse</h6>
                            <p class="text-muted small">Supported formats: .xlsx, .xls</p>
                            <p class="text-muted small">Required columns: Name, Email, WhatsApp, Address (optional)</p>
                            <input type="file" id="excelFile" accept=".xlsx,.xls" style="display: none;">
                        </div>
                        <div id="fileInfo" style="display: none;" class="mt-3">
                            <div class="alert alert-info">
                                <i class="fas fa-file-excel me-2"></i>
                                <span id="fileName"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearFile()">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="manualEntryArea" style="display: none;">
                        <div class="manual-entry-area">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Recipients</h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addRecipientRow()">
                                    <i class="fas fa-plus"></i> Add Recipient
                                </button>
                            </div>
                            <div id="recipientsList">
                            </div>
                        </div>
                    </div>

                    <div id="bulkProgress" style="display: none;" class="mt-3">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="mt-2">
                            <small id="progressText">Processing...</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="processBulkSend()" id="sendBulkBtn">
                        <i class="fas fa-paper-plane me-2"></i>Send Invitations
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        // Define template ID and invitation ID for JavaScript use
        const templateId = <?php echo $template_id; ?>;
        const invitationId = <?php echo $invitation_id ? $invitation_id : 'null'; ?>;
        let currentDesignData = '';
        let bulkSendModal;

        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('imagediv')) {
                let elements = document.getElementsByClassName("textBox");
                for (var i = 0; i < elements.length; i++) {
                    elements[i].setAttribute('contenteditable', 'true');
                }
            }
            bulkSendModal = new bootstrap.Modal(document.getElementById('bulkSendModal'));
            
            setupFileUpload();

            setupInputMethodToggle();
            
            addRecipientRow();
        });

        function updateDesignData() {
            const preview = document.getElementById('invitationPreview');
            currentDesignData = preview.innerHTML;
        }

        function saveInvitation() {
            showLoading(true);
            updateDesignData();

            const invitationData = {
                template_id: templateId,
                design_data: currentDesignData
            };

            // Add invitation_id if we're updating an existing invitation
            if (invitationId) {
                invitationData.invitation_id = invitationId;
            }

            fetch('save_invitation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(invitationData)
            })
                .then(response => response.json())
                .then(data => {
                    showLoading(false);
                    if (data.success) {
                        showMessage(data.message, 'success');
                        
                        // Redirect to customize page with invitation_id if it's a new invitation
                        if (data.redirect_url && !invitationId) {
                            setTimeout(() => {
                                window.location.href = data.redirect_url;
                            }, 1000);
                        }
                    } else {
                        showMessage('Error saving invitation: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    showLoading(false);
                    console.error('Error:', error);
                    showMessage('Network error. Please try again.', 'error');
                });
        }

        function downloadInvitation() {
            showLoading(true);
            const preview = document.getElementById('invitationPreview');
            preview.style.transform = 'scale(1)';

            if (typeof html2canvas !== 'undefined') {
                html2canvas(preview, {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true
                }).then(canvas => {
                    showLoading(false);
                    const link = document.createElement('a');
                    link.download = 'invitation-' + Date.now() + '.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                    showMessage('Invitation downloaded successfully!', 'success');
                    
                    // Reset the scale back
                    preview.style.transform = 'scale(0.8)';
                }).catch(error => {
                    showLoading(false);
                    showMessage('Error generating image. Please try again.', 'error');
                    preview.style.transform = 'scale(0.8)';
                });
            } else {
                showLoading(false);
                showMessage('Download feature is not available. Please refresh the page.', 'error');
            }
        }

        // Bulk Send Functions
        function openBulkSendModal() {
            updateDesignData();
            bulkSendModal.show();
        }

        function setupFileUpload() {
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('excelFile');

            uploadArea.addEventListener('click', () => fileInput.click());
            
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelect(files[0]);
                }
            });

            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFileSelect(e.target.files[0]);
                }
            });
        }

        function handleFileSelect(file) {
            const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
            
            if (!allowedTypes.includes(file.type)) {
                showMessage('Please select a valid Excel file (.xlsx or .xls)', 'error');
                return;
            }

            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileInfo').style.display = 'block';
            document.getElementById('uploadArea').style.display = 'none';
        }

        function clearFile() {
            document.getElementById('excelFile').value = '';
            document.getElementById('fileInfo').style.display = 'none';
            document.getElementById('uploadArea').style.display = 'block';
        }

        function setupInputMethodToggle() {
            const radioButtons = document.querySelectorAll('input[name="inputMethod"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'excel') {
                        document.getElementById('excelUploadArea').style.display = 'block';
                        document.getElementById('manualEntryArea').style.display = 'none';
                    } else {
                        document.getElementById('excelUploadArea').style.display = 'none';
                        document.getElementById('manualEntryArea').style.display = 'block';
                    }
                });
            });
        }

        function addRecipientRow() {
            const recipientsList = document.getElementById('recipientsList');
            const rowCount = recipientsList.children.length;
            
            const row = document.createElement('div');
            row.className = 'recipient-row';
            row.innerHTML = `
                <input type="text" placeholder="Name" name="recipients[${rowCount}][name]" required>
                <input type="email" placeholder="Email" name="recipients[${rowCount}][email]" required>
                <input type="text" placeholder="WhatsApp Number" name="recipients[${rowCount}][whatsapp]">
                <input type="text" placeholder="Address (Optional)" name="recipients[${rowCount}][address]">
                <button type="button" class="remove-recipient" onclick="removeRecipientRow(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            recipientsList.appendChild(row);
        }

        function removeRecipientRow(button) {
            const recipientsList = document.getElementById('recipientsList');
            if (recipientsList.children.length > 1) {
                button.parentElement.remove();
            }
        }

        function processBulkSend() {
            const inputMethod = document.querySelector('input[name="inputMethod"]:checked').value;
            
            if (inputMethod === 'excel') {
                processBulkSendExcel();
            } else {
                processBulkSendManual();
            }
        }

        function processBulkSendExcel() {
            const fileInput = document.getElementById('excelFile');
            
            if (!fileInput.files.length) {
                showMessage('Please select an Excel file', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('excel_file', fileInput.files[0]);
            formData.append('template_id', templateId);
            formData.append('design_data', currentDesignData);
            formData.append('method', 'excel');

            sendBulkRequest(formData);
        }

        function processBulkSendManual() {
            const recipients = [];
            const rows = document.querySelectorAll('.recipient-row');
            
            rows.forEach(row => {
                const inputs = row.querySelectorAll('input');
                const recipient = {
                    name: inputs[0].value.trim(),
                    email: inputs[1].value.trim(),
                    whatsapp: inputs[2].value.trim(),
                    address: inputs[3].value.trim()
                };
                
                if (recipient.name && recipient.email) {
                    recipients.push(recipient);
                }
            });

            if (recipients.length === 0) {
                showMessage('Please add at least one recipient with name and email', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('recipients', JSON.stringify(recipients));
            formData.append('template_id', templateId);
            formData.append('design_data', currentDesignData);
            formData.append('method', 'manual');

            sendBulkRequest(formData);
        }

      function sendBulkRequest(formData, currentDesignData) {
    const sendBtn = document.getElementById('sendBulkBtn');
    const progressArea = document.getElementById('bulkProgress');
    const progressBar = progressArea.querySelector('.progress-bar');
    const progressText = document.getElementById('progressText');

    sendBtn.disabled = true;
    progressArea.style.display = 'block';
    progressBar.style.width = '0%';
    progressText.textContent = 'Processing...';



    html2canvas(document.getElementById('imagediv')).then(canvas => {
        const dataURL = canvas.toDataURL('image/png');
        const imgTag = `<img src="${dataURL}" alt="Design Image">`;
        formData.set('design_data', imgTag); // Replace previous HTML with image tag
        console.log(dataURL); // For debugging

        // Now send the request
        fetch('excel-upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            sendBtn.disabled = false;
            progressArea.style.display = 'none';

            if (data.success) {
                showMessage(`Successfully sent ${data.sent_count} invitations!`, 'success');
                bulkSendModal.hide();

                // Reset form
                clearFile();
                document.getElementById('recipientsList').innerHTML = '';
                addRecipientRow();
            } else {
                showMessage('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            sendBtn.disabled = false;
            progressArea.style.display = 'none';
            console.error('Error:', error);
            showMessage('Network error. Please try again.', 'error');
        });
    });
}

        function showLoading(show) {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.style.display = show ? 'flex' : 'none';
            }
        }

        function showMessage(message, type) {
            const successEl = document.getElementById('successMessage');
            const errorEl = document.getElementById('errorMessage');

            // Hide both messages first
            successEl.style.display = 'none';
            errorEl.style.display = 'none';

            if (type === 'success') {
                document.getElementById('successText').textContent = message;
                successEl.style.display = 'block';
                setTimeout(() => {
                    successEl.style.display = 'none';
                }, 5000);
            } else if (type === 'error') {
                document.getElementById('errorText').textContent = message;
                errorEl.style.display = 'block';
                setTimeout(() => {
                    errorEl.style.display = 'none';
                }, 7000);
            }
        }

        function showStatus(message, type) {
            // You can implement a status bar or toast notification here
            console.log(`Status: ${message} (${type})`);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function (e) {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 's':
                        e.preventDefault();
                        saveInvitation();
                        break;
                    case 'd':
                        e.preventDefault();
                        downloadInvitation();
                        break;
                    default:
                        break;
                }
            }
        });
    </script>
</body>

</html>

<?php include 'includes/footer.php'; ?>