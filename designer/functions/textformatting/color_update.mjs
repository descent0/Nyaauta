import { previous } from "../../Update.mjs";

export function  color_update(){
    document.getElementById("color").addEventListener("input", () => {
        let val = document.getElementById("color").value;
            document.getElementById("selected").style.color = val;
    
        document.getElementById("color").style.backgroundColor = val;
        document.getElementById("color").style.color = val;
       previous(document.getElementById("imagediv").outerHTML);
    });
}
