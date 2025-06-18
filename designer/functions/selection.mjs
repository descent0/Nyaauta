import { deletion } from "./deletion.mjs";

export function selection(event) {
  const selectedElement = event.target;

  document.getElementById("selected")?.removeAttribute("id");

  selectedElement.id = "selected";

  // Update the input fields
  const rect = selectedElement.getBoundingClientRect();
  document.getElementById("x").value = parseInt(selectedElement.style.left, 10) || 0;
  document.getElementById("y").value = parseInt(selectedElement.style.top, 10) || 0;
  document.getElementById("size").value = parseInt(selectedElement.style.fontSize, 10) || 12;
  document.getElementById("z").value = parseInt(selectedElement.style.zIndex, 10) || 1;
  document.getElementById("color").style.backgroundColor = selectedElement.style.color || 'black';
  document.getElementById("height").value = selectedElement.offsetHeight;
  document.getElementById("width").value = selectedElement.offsetWidth;
}

export function setEventListeners(newTextBox) {
    let selectedDiv = null;
  
    // Add event listener to newTextBox
    newTextBox.addEventListener("click", () => {
      newTextBox.style.border = "2px blue solid";
      newTextBox.contentEditable = true;
  
      // Update selectedDiv and remove id from previous selected div
      if (selectedDiv!== null) {
        const prevSelectedDiv = document.getElementById(selectedDiv);
        if (prevSelectedDiv) {
          prevSelectedDiv.removeAttribute("id");
        }
      }
  
      selectedDiv = newTextBox.id;
      newTextBox.id = "selectedTextBox";

      deletion(); // remove the new textbox
    });
  
    // Add event listener to document
    document.addEventListener("click", (event) => {
      if (!newTextBox.innerText.trim()) {
        usedIds.delete(
          document.getElementById("selectedTextBox").getAttribute("uniqueIdentifier")
        );
        newTextBox.remove();
      } else if (!newTextBox.contains(event.target)) {
        newTextBox.style.border = "none";
        newTextBox.contentEditable = false;
      }
    });
  }