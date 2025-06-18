import { previous } from "../Update.mjs";

export function heightAndWidthUpdate() {
  document.getElementById("height").addEventListener("input", (event) => {
    event.preventDefault();
    let val = document.getElementById("height").value;
    document.getElementById("selected").style.height = `${val}px`;
    document.getElementById("selected").style.width = "auto";
    const child = selected.firstElementChild;
    if (child && child.tagName.toLowerCase() === "img") {
      child.style.height = `${val}px`;
      child.style.width="auto";
    }
    previous(document.getElementById("imagediv").outerHTML);
  });

  document.getElementById("width").addEventListener("input", (event) => {
    event.preventDefault();
    let val = document.getElementById("width").value;
    document.getElementById("selected").style.width = `${val}px`;
    document.getElementById("selected").style.height = "auto";
    const child = selected.firstElementChild;
    if (child && child.tagName.toLowerCase() === "img") {
      child.style.width = `${val}px`;
      child.style.height="auto";
    }

    previous(document.getElementById("imagediv").outerHTML);
  });
}
