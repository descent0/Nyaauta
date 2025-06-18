import { previous } from "./Update.mjs";
import { Alignment, dragging, position_upadte } from "./functions/dragging.mjs";
import { heightAndWidthUpdate } from "./functions/heightAndWidth.mjs";
import { selection, setEventListeners } from "./functions/selection.mjs";
import { color_update } from "./functions/textformatting/color_update.mjs";
import { fontUpdate } from "./functions/textformatting/font_update.mjs";
import { size_update } from "./functions/textformatting/size_update.mjs";
import { zoomIn, zoomOut } from "./functions/zoom.mjs";

document.addEventListener("DOMContentLoaded", () => {
  document.getElementById("zoomIn").addEventListener("click", zoomIn);
  document.getElementById("zoomOut").addEventListener("click", zoomOut);

  let s = localStorage.getItem("strwork");
  console.log(s);
  if (s != null) {
    document.getElementById("workpanel").innerHTML = s;
    let elements = document.getElementsByClassName("textBox");
    for (var i = 0; i < elements.length; i++) {
      setEventListeners(elements[i]);
      dragging(elements[i]);
      elements[i].addEventListener("click", selection);
    }
    let imageContainers = document.getElementsByClassName("image-container");
    for (var j = 0; j < imageContainers.length; j++) {
      imageContainers[j].addEventListener("click", selection);
      dragging(imageContainers[j]);
    }

    size_update();
    color_update();
    fontUpdate();
    position_upadte();
    heightAndWidthUpdate();
    Alignment();
  }else if(window.location.search.includes("template_id")) {
const params = new URLSearchParams(window.location.search);
let elements = document.getElementsByClassName("textBox");
    for (var i = 0; i < elements.length; i++) {
      setEventListeners(elements[i]);
      dragging(elements[i]);
      elements[i].addEventListener("click", selection);
    }
    let imageContainers = document.getElementsByClassName("image-container");
    for (var j = 0; j < imageContainers.length; j++) {
      imageContainers[j].addEventListener("click", selection);
      dragging(imageContainers[j]);
    }

    size_update();
    color_update();
    fontUpdate();
    position_upadte();
    heightAndWidthUpdate();
    Alignment();

const id = params.get('template_id');
console.log("Template ID:", id);

  } else {
    let x = "<div id=" + "imagediv" + "></div>";
    document.getElementById("workpanel").innerHTML = x;
    if (width && height) {
      let imagediv = document.getElementById("imagediv");
      imagediv.style.width = width + "px";
      imagediv.style.height = height + "px";
      imagediv.style.backgroundColor = "white";
    }
  }

//save template
document.getElementById("save_template").addEventListener("click", function (e) {
    e.preventDefault();

    const strwork = localStorage.getItem("strwork");
    if (!strwork) {
        alert("No template to save!");
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const id = params.get('template_id');
    const categoryInput = document.getElementById("category_id");
    const categoryId = categoryInput ? categoryInput.value : "";

    let dataToSend = {};
    let templateData = strwork;

    if (!id || id.trim() === "") {
        // CREATE mode
        if (categoryId === "0" || categoryId === "") {
            alert("Please select a category");
            return;
        }

        const templateName = prompt("Enter a name for the template:");
        if (!templateName || templateName.trim() === "") {
            alert("Please enter a valid template name.");
            return;
        }

        dataToSend = {
            name: templateName.trim(),
            category_id: parseInt(categoryId),
            design_data: templateData
        };
    } else {
        // UPDATE mode
        if (categoryId === "0" || categoryId === "") {
            alert("Please select a category");
            return;
        }

        // You can optionally keep the existing name or set a default one
        const templateName = prompt("Enter a name for the template:");
        if (!templateName || templateName.trim() === "") {
            alert("Please enter a valid template name.");
            return;
        }

        dataToSend = {
            template_id: id,
            name: templateName.trim(),
            category_id: parseInt(categoryId),
            design_data: templateData
        };
    }

    fetch("save_template.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(dataToSend)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Full server response:', data);
            if (data.success) {
                alert("Template saved successfully!");
                localStorage.removeItem("strwork");
                window.location.reload();
            } else {
                console.error('Server response:', data);
                alert("Failed to save template: " + (data.message || "Unknown error"));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Error saving template: " + error.message);
        });
});

});



