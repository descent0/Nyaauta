document.addEventListener("DOMContentLoaded", () => {
    const fontInput = document.getElementById('fontFile');
    const fontSelect = document.getElementById('fontList');
    const systemFonts = [
        "Arial", "Verdana", "Helvetica", "Times New Roman", "Courier New",
        "Georgia", "Palatino", "Garamond", "Bookman", "Comic Sans MS",
        "Trebuchet MS", "Arial Black", "Impact"
    ];

    // Add system fonts to select
    systemFonts.forEach(font => {
        const option = document.createElement('option');
        option.value = font;
        option.textContent = font;
        option.style.fontFamily = font;
        fontSelect.appendChild(option);
    });

    // Fetch custom fonts from fonts.css
    fetch('styles/fonts.css')
        .then(res => res.text())
        .then(css => {
            // Regex to extract font-family names from @font-face
            const regex = /font-family:\s*["']?([^;"']+)["']?;/g;
            let match;
            const customFonts = new Set();
            while ((match = regex.exec(css)) !== null) {
                customFonts.add(match[1]);
            }
            customFonts.forEach(font => {
                if (!systemFonts.includes(font)) {
                    const option = document.createElement('option');
                    option.value = font;
                    option.textContent = font;
                    option.style.fontFamily = font;
                    fontSelect.appendChild(option);
                }
            });
        });

    // Font upload handler (already present)
    if (fontInput) {
        fontInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);

            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(msg => {
                alert(msg);
                // Reload fonts after upload
                window.location.reload();
            })
            .catch(err => alert('Font upload error: ' + err));
        });
    }
});