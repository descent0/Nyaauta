import { previous } from "../../Update.mjs";

export function fontUpdate(){
    document.getElementById("fontList").addEventListener("change", function (event) {
        event.preventDefault();
        let val = this.value;
            document.getElementById("selected").style.fontFamily = `'${val}', sans-serif`;
            updateJSON("id", "selectedTextBox", "fontFamily", `${val} , sans-serif`);
        previous(document.getElementById("imagediv").outerHTML);
    
    });
}

