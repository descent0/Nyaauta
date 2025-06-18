<?php
$allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$allowedFontExtensions = ['ttf', 'otf', 'woff', 'woff2'];

if (isset($_FILES['file'])) {
    $fileType = $_FILES['file']['type'];
    $fileName = basename($_FILES['file']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // IMAGE UPLOAD: always use unique name
    if (in_array($fileType, $allowedImageTypes)) {
        $targetDir = "images/";
        $uniqueName = date('Ymd_His') . '_' . uniqid() . '.' . $fileExt;
        $targetPath = $targetDir . $uniqueName;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            echo "/invitation-platform/designer/images/$uniqueName";
        } else {
            http_response_code(500);
            echo "Error uploading image.";
        }
        exit;
    }

    // FONT UPLOAD: check extension only, reject duplicates
    if (in_array($fileExt, $allowedFontExtensions)) {
        $targetDir = "fontFile/";
        $targetPath = $targetDir . $fileName;
        if (file_exists($targetPath)) {
            http_response_code(409);
            echo "Font already exists.";
            exit;
        }
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            echo "Font uploaded successfully: $fileName";
             $cssPath = __DIR__ . '/styles/fonts.css';
            $fontFamily = pathinfo($fileName, PATHINFO_FILENAME);
            // Detect format for @font-face
            switch ($fileExt) {
                case 'ttf':
                    $fontFormat = 'truetype';
                    break;
                case 'otf':
                    $fontFormat = 'opentype';
                    break;
                case 'woff':
                    $fontFormat = 'woff';
                    break;
                case 'woff2':
                    $fontFormat = 'woff2';
                    break;
                default:
                    $fontFormat = 'truetype';
            }
            $fontFace = "\n@font-face {\n"
                . "  font-family: \"" . addslashes($fontFamily) . "\";\n"
                . "  src: url(\"/fontFile/" . addslashes($fileName) . "\") format(\"$fontFormat\");\n"
                . "  font-weight: normal;\n"
                . "  font-style: normal;\n"
                . "}\n";
            file_put_contents($cssPath, $fontFace, FILE_APPEND);
        } else {
            http_response_code(500);
            echo "Error uploading font.";
        }
        exit;
    }

    http_response_code(400);
    echo "Invalid file type.";
} else {
    http_response_code(400);
    echo "No file uploaded.";
}
?>