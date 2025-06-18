<?php
require_once '../includes/header.php';
requireRole('employee');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $preset = $_POST['preset'];
    $_SESSION['size_selected'] = true;
    if ($preset === 'A5') {
        $_SESSION['imagediv_width'] = 148 * 3.78;
        $_SESSION['imagediv_height'] = 210 * 3.78;
    } elseif ($preset === 'A6') {
        $_SESSION['imagediv_width'] = 105 * 3.78;
        $_SESSION['imagediv_height'] = 148 * 3.78;
    } elseif ($preset === 'square') {
        $_SESSION['imagediv_width'] = 200 * 3.78;
        $_SESSION['imagediv_height'] = 200 * 3.78;
    } elseif ($preset === 'custom') {
        $_SESSION['imagediv_width'] = intval($_POST['customWidth']);
        $_SESSION['imagediv_height'] = intval($_POST['customHeight']);
    }
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Invitation Size</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="">
    <h2 class="mb-4">Select Invitation Size</h2>
    <form method="POST" class="border p-4 rounded shadow">
        <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="preset" value="A5" id="a5" checked>
            <label class="form-check-label" for="a5">A5 (148 x 210 mm)</label>
        </div>
        <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="preset" value="A6" id="a6">
            <label class="form-check-label" for="a6">A6 (105 x 148 mm)</label>
        </div>
        <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="preset" value="square" id="square">
            <label class="form-check-label" for="square">Square (200 x 200 mm)</label>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="radio" name="preset" value="custom" id="custom">
            <label class="form-check-label" for="custom">Custom Size</label>
        </div>

        <div id="customSize" class="row g-3 mb-3" style="display:none;">
            <div class="col">
                <label for="customWidth" class="form-label">Width (px)</label>
                <input type="number" class="form-control" name="customWidth" id="customWidth" min="50" max="2000" value="500">
            </div>
            <div class="col">
                <label for="customHeight" class="form-label">Height (px)</label>
                <input type="number" class="form-control" name="customHeight" id="customHeight" min="50" max="2000" value="700">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Continue</button>
    </form>

    <script>
        document.querySelectorAll('input[name="preset"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.getElementById('customSize').style.display = this.value === 'custom' ? 'flex' : 'none';
            });
        });
    </script>
</body>
</html>

<?php include '../includes/footer.php'; ?>
