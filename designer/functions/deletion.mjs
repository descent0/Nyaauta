import { previous } from "../Update.mjs";
import { usedIds } from "./UniqueId.mjs";
export function deletion(){
    if(document.getElementById("selected")!=null){
    document.addEventListener('keydown', (event) => {
        if (event.ctrlKey && event.key === 'x') {
            //updating the set
            usedIds.delete(document.getElementById("selected").getAttribute('uniqueIdentifier'));
            document.getElementById("selected").remove();
        } else if (event.key === "Delete") {
        
            //updating the set
            usedIds.delete(document.getElementById("selected").getAttribute('uniqueIdentifier'));
            document.getElementById("selected").remove();
        }
        previous(document.getElementById("imagediv").outerHTML);
    });
}
}