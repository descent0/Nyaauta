import { dragging } from "./functions/dragging.mjs";
import { heightAndWidthUpdate } from "./functions/heightAndWidth.mjs";
import { selection } from "./functions/selection.mjs";


document.addEventListener("DOMContentLoaded", () => {
    document.getElementById('image').addEventListener("change", (event) => {
        const files = event.target.files;
        if (!files || files.length === 0) return;

        Array.from(files).forEach(file => {
            if (!file.type.startsWith('image/')) return;
            uploadImage(file).then(imageUrl => {
                console.log("Image URL:", imageUrl);
                if (imageUrl) renderImage(imageUrl);
            });
        });
    });
    heightAndWidthUpdate();     
});

function renderImage(imageUrl) {
    console.log("image rendered");
    const imgContainer = document.createElement('div');
    imgContainer.classList.add('image-container');
    imgContainer.style.position = 'absolute';
    imgContainer.style.left = '0';
    imgContainer.style.top = '0';
  
    const imgElement = document.createElement('img');
    imgElement.src = imageUrl;
    imgElement.style.position = "relative";
    imgElement.style.width = '100%';      // <-- Add this
    imgElement.style.height = '100%';     // <-- Add this
    imgElement.style.objectFit = 'contain'; // or 'cover' as you prefer
    imgElement.style.zIndex = 'inherit';
    
    // Wait for the image to load before setting the container dimensions
    imgElement.addEventListener('load', () => {
      imgContainer.style.width = `${imgElement.naturalWidth}px`;
      imgContainer.style.height = `${imgElement.naturalHeight}px`;
    });
  
    imgContainer.appendChild(imgElement);
    imgContainer.addEventListener("click", selection);
    document.getElementById('imagediv').appendChild(imgContainer);
    dragging(imgContainer);
  }

  function uploadImage(file) {
    const formData = new FormData();
    formData.append('file', file);
    return fetch('upload.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.ok ? res.text() : Promise.reject('Upload failed'))
    .then(imageUrl => {
        console.log('Server image URL:', imageUrl);
        return imageUrl;
    })
    .catch(err => {
        console.error('Upload error:', err);
        return null;
    });
}
