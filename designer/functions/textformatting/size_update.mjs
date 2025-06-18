import { previous } from "../../Update.mjs";

export function size_update(){
    document.getElementById("size").addEventListener("input", (event) => {
        event.preventDefault();
        let val = document.getElementById("size").value;
            console.log("Updating font size of selected div:");
            document.getElementById("selected").style.fontSize = `${val}px`;
        previous(document.getElementById("imagediv").outerHTML);
    });
}
