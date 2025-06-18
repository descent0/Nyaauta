import { previous } from "../Update.mjs";

export let scale = 1.0;

const ZOOM_STEP = 0.1;
const MIN_SCALE = 0.1;

function getScaleFromTransform(transform) {
  const match = transform.match(/scale\(([^)]+)\)/);
  return match ? parseFloat(match[1]) : 1.0;
}

function updateScale() {
  const imgElement = document.querySelector("#imagediv");
  if (imgElement) {
    imgElement.style.transform = `scale(${scale})`;
    previous(document.getElementById("imagediv").outerHTML);
  }
}

 export function zoomIn() {
  const imgElement = document.getElementById("imagediv");
  if (imgElement) {
    scale = getScaleFromTransform(imgElement.style.transform);
    scale += ZOOM_STEP;
    updateScale();
  }
}

export function zoomOut() {
  const imgElement = document.getElementById("imagediv");
  if (imgElement) {
    scale = getScaleFromTransform(imgElement.style.transform);
    if (scale > MIN_SCALE) {
      scale -= ZOOM_STEP;
      updateScale();
    }
  }
}