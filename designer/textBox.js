import { selection, setEventListeners } from "./functions/selection.mjs";
import { Alignment, dragging } from "./functions/dragging.mjs";
import { color_update } from "./functions/textformatting/color_update.mjs";
import { fontUpdate } from "./functions/textformatting/font_update.mjs";
import { size_update } from "./functions/textformatting/size_update.mjs";
import { position_upadte } from "./functions/dragging.mjs";

import { usedIds } from "./functions/UniqueId.mjs";
import { UniqueId } from "./functions/UniqueId.mjs";

import { scale } from "./functions/zoom.mjs";
import { heightAndWidthUpdate } from "./functions/heightAndWidth.mjs";


document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("imagediv").addEventListener("dblclick", (event) => {
        // console.log("doubleclick");
        const newTextBox = document.createElement("div");
        newTextBox.innerText = "edit me";
        newTextBox.className = `textBox`;
        newTextBox.draggable = true;
        newTextBox.style.height="fit-content";
        newTextBox.style.width="fit-content";
        newTextBox.style.position = "absolute";
        newTextBox.style.fontSize = "20px";
        newTextBox.style.color = document.getElementById("color").value;
        newTextBox.style.fontWeight = "600";
        let parentRect = document.getElementById("imagediv").getBoundingClientRect();
        newTextBox.style.left = `${(event.clientX - parentRect.left) / scale}px`;
        newTextBox.style.top = `${(event.clientY - parentRect.top) / scale}px`;

        //giving the textbox unique id

        newTextBox.setAttribute("uniqueIdentifier", UniqueId(usedIds));

        let imgDiv = document.getElementById("imagediv");
        imgDiv.appendChild(newTextBox);
        console.log("appended");

        setEventListeners(newTextBox);

        //dragging

        dragging(newTextBox);
        //selection
        newTextBox.addEventListener("click", selection);
    });
    
    //textformatting
    size_update();
    color_update();
    fontUpdate();
    position_upadte();
    heightAndWidthUpdate();
    Alignment();
});
