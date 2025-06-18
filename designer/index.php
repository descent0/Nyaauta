<?php
require_once '../includes/header.php';
requireRole('employee');
if (!isset($_GET['template_id'])) {
    if (!isset($_SESSION['size_selected']) && !isset($_GET['skip_size'])) {
        header("Location: select-size.php");
        exit;
    }
}

$imagediv_width = isset($_SESSION['imagediv_width']) ? $_SESSION['imagediv_width'] : 0;
$imagediv_height = isset($_SESSION['imagediv_height']) ? $_SESSION['imagediv_height'] : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Image & Text Editor</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="styles/fonts.css">
    <link rel="stylesheet" href="texbox.css" />

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 20px;
        }

        .control-panel {
            background: #f8f9fa;
            border-right: 1px solid #e9ecef;
            border-radius: 20px 0 0 20px;
            padding: 30px;
            height: calc(100vh - 40px);
            overflow-y: auto;
        }

        .work-panel {
            padding: 30px;
            border-radius: 0 20px 20px 0;

            overflow-y: auto;
            /* Added overflow property */
            max-height: 100vh;
            /* Optional: set a max-height */
        }

        .section-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .zoom-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .zoom-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .zoom-btn:hover {
            background: #667eea;
            color: white;
            transform: scale(1.1);
        }

        .coordinate-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .coordinate-input {
            width: 80px;
        }

        .position-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .preview-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
        }

        .preview-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
            text-decoration: none;
        }

        #imagediv {
            background: #f8f9fa;
            border: 3px dashed #dee2e6;
            border-radius: 15px;
            min-height: 400px;
            position: relative;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        #imagediv:hover {
            border-color: #667eea;
            background: #f0f2ff;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: linear-gradient(45deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            transition: all 0.3s ease;
        }

        .file-input-wrapper:hover {
            background: linear-gradient(45deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
            transform: translateY(-2px);
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .upload-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .card-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
            border: none;
        }

        .input-group-text {
            background: #e9ecef;
            border: 2px solid #e9ecef;
            border-radius: 10px 0 0 10px;
            color: #495057;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="main-container">
            <div class="row g-0">
                <!-- Control Panel -->
                <div class="col-lg-4 col-md-5">
                    <div class="control-panel">
                        <!-- Image Selection Section -->
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-image"></i> Image Selection
                            </div>
                            <div class="card-body">
                                <div class="file-input-wrapper">
                                    <i class="bi bi-cloud-upload upload-icon"></i>
                                    <h5>Click to upload images</h5>
                                    <p class="text-muted mb-0">Supports multiple image files</p>
                                    <input type="file" multiple id="image" name="images" accept="image/*" />
                                </div>

                                <div class="mt-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-zoom-in"></i> Zoom Controls
                                    </label>
                                    <div class="zoom-controls">
                                        <button class="btn zoom-btn" id="zoomIn" title="Zoom In">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                        <span class="mx-2 text-muted">Zoom</span>
                                        <button class="btn zoom-btn" id="zoomOut" title="Zoom Out">
                                            <i class="bi bi-dash-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Text Formatting Section -->
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-fonts"></i> Text Formatting
                            </div>
                            <div class="card-body">
                                <form id="form">
                                    <div class="mb-3">
                                        <label for="size" class="form-label fw-semibold">
                                            <i class="bi bi-type"></i> Font Size
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">px</span>
                                            <input type="number" class="form-control" id="size" name="name"
                                                placeholder="16" />
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="fontList" class="form-label fw-semibold">
                                            <i class="bi bi-typography"></i> Font Family
                                        </label>
                                        <select id="fontList" class="form-select"></select>
                                        <input type="file" id="fontFile" name="fontFile" class="form-control mt-2"
                                            accept=".ttf,.otf,.woff,.woff2">
                                        <datalist id="fonts"></datalist>
                                    </div>

                                    <div class="mb-3">
                                        <label for="color" class="form-label fw-semibold">
                                            <i class="bi bi-palette"></i> Font Color
                                        </label>
                                        <input type="color" class="form-control form-control-color" id="color"
                                            name="color" />
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Position Section -->
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-arrows-move"></i> Position & Alignment
                            </div>
                            <div class="card-body">
                                <div class="position-grid mb-3">
                                    <div>
                                        <label for="height" class="form-label fw-semibold">Height</label>
                                        <input type="number" class="form-control" id="height" value="50" />
                                    </div>
                                    <div>
                                        <label for="width" class="form-label fw-semibold">Width</label>
                                        <input type="number" class="form-control" id="width" value="50" />
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-crosshair"></i> Coordinates
                                    </label>
                                    <div class="coordinate-group">
                                        <div class="input-group coordinate-input">
                                            <span class="input-group-text">X</span>
                                            <input type="number" class="form-control coordinates" id="x" />
                                        </div>
                                        <div class="input-group coordinate-input">
                                            <span class="input-group-text">Y</span>
                                            <input type="number" class="form-control coordinates" id="y" />
                                        </div>
                                        <div class="input-group coordinate-input">
                                            <span class="input-group-text">Z</span>
                                            <input type="number" class="form-control coordinates" id="z" min="0" />
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="alignment_type" class="form-label fw-semibold">
                                        <i class="bi bi-text-center"></i> Text Alignment
                                    </label>
                                    <select name="alignment_type" id="alignment_type" class="form-select">
                                        <option value="left">
                                            <i class="bi bi-text-left"></i> Left
                                        </option>
                                        <option value="right">
                                            <i class="bi bi-text-right"></i> Right
                                        </option>
                                        <option value="middle">
                                            <i class="bi bi-text-center"></i> Center
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Work Panel -->
                <div class="col-lg-8 col-md-7">
                    <div class="work-panel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-easel text-primary"></i> Canvas

                            </h3>

                            <div class="d-flex gap-2">
                                <!-- #region -->
                                <select name="category" id="category_id" class="form-select">
                                    <option value="0">Select Category</option>

                                    <?php
                                    $query = "SELECT * FROM categories";
                                    $result = mysqli_query($conn, $query);

                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
                                    }
                                    ?>
                                </select>


                                <a id="save_template" target="_blank" href="save_template.php" class="preview-btn">
                                    <i class="bi bi-eye"></i> Save Template
                                </a>





                            </div>
                        </div>

                        <div id="workpanel" class="position-relative">
                                                     <?php
                            if (isset($_GET['template_id'])) {
                                $template_id = intval($_GET['template_id']);
                                
                                // Use prepared statement for security
                                $stmt = $conn->prepare("SELECT * FROM templates WHERE id = ?");
                                $stmt->bind_param("i", $template_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result && $result->num_rows > 0) {
                                    $template = $result->fetch_assoc();
                                    // Check if template_data column exists, if not use design_data
                                    $template_data = isset($template['template_data']) ? $template['template_data'] : $template['design_data'];
                                    echo $template_data;
                                } else {
                                    echo '<p class="text-muted text-center">Template not found or you don\'t have permission to view it.</p>';
                                }
                                $stmt->close();
                            } 
                           ?>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>

        const imagedivWidth = <?php echo json_encode($imagediv_width); ?>;
        const imagedivHeight = <?php echo json_encode($imagediv_height); ?>;
        const width = imagedivWidth;
        const height = imagedivHeight;

        document.querySelector('.file-input-wrapper').addEventListener('click', function () {
            document.getElementById('image').click();
        });

        const fileWrapper = document.querySelector('.file-input-wrapper');

        fileWrapper.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.style.background = 'linear-gradient(45deg, rgba(102, 126, 234, 0.3), rgba(118, 75, 162, 0.3))';
        });

        fileWrapper.addEventListener('dragleave', function (e) {
            e.preventDefault();
            this.style.background = 'linear-gradient(45deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1))';
        });

        fileWrapper.addEventListener('drop', function (e) {
            e.preventDefault();
            this.style.background = 'linear-gradient(45deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1))';

            const files = e.dataTransfer.files;
            document.getElementById('image').files = files;
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.9.0/html-to-image.min.js"></script>
    <script type="module" src="workplace.js"></script>
    <script type="module" src="images.js"></script>
    <script type="module" src="textBox.js"></script>
    <script src="font.js"></script>
</body>

</html>