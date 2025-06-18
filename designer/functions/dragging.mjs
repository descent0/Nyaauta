import { previous } from "../Update.mjs";
import { scale } from "./zoom.mjs";

export function dragging(element) {
  let offsetX, offsetY;
  element.addEventListener("dragstart", (event) => {
    offsetX = event.clientX - element.getBoundingClientRect().left;
    offsetY = event.clientY - element.getBoundingClientRect().top;
    event.dataTransfer.setData("text/plain", "Drag me!");
  });

  element.addEventListener("dragend", function (event) {
    let imgDiv = document.getElementById("imagediv");
    let imgRect = imgDiv.getBoundingClientRect();
    let x = (event.clientX - offsetX - imgRect.left) / scale;
    let y = (event.clientY - offsetY - imgRect.top) / scale;
    element.style.left = x + "px";
    element.style.top = y + "px";

    document.getElementById("x").value = parseInt(element.style.left, 10);
    document.getElementById("y").value = parseInt(element.style.top, 10);
    previous(document.getElementById("imagediv").outerHTML);
  });
}

export function position_upadte() {
  document.getElementById("x").addEventListener("input", () => {
    let val = document.getElementById("x").value;

    document.getElementById("selected").style.left = `${val}px`;

    previous(document.getElementById("imagediv").outerHTML);
  });

  document.getElementById("y").addEventListener("input", () => {
    let val = document.getElementById("y").value;

    document.getElementById("selected").style.top = `${val}px`;

    previous(document.getElementById("imagediv").outerHTML);
  });

  document.getElementById("z").addEventListener("input", () => {
    // console.log("z index updated");
    let val = document.getElementById("z").value;
    document.getElementById("selected").style.zIndex = val;

    previous(document.getElementById("imagediv").outerHTML);
  });
}

export function Alignment() {
  
  document.getElementById("alignment_type").addEventListener("change", (e) => {
    e.preventDefault();
    if (document.getElementById("alignment_type").value == "left") {
      
      console.log(document.getElementById('selected'));
      document.getElementById("selected").style.justifyContent = "left";
      console.log(document.getElementById('selected'));

    } 
    if (document.getElementById("alignment_type").value == "middle") {
      document.getElementById("selected").style.justifyContent = "center";
    } 
    if (document.getElementById("alignment_type").value == "right") {
      document.getElementById("selected").style.justifyContent= "right";
    } 
  });
}
