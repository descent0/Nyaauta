//checking unique ids
export const usedIds = new Set();
 
 export function UniqueId(usedIds) {
  let id = null;
  while (id==null){
    if(usedIds.has(id)){
        id=null;
    }else{
        id=generateID();
        usedIds.add(id); 
    }
}
  return id;
}
// console.log(usedIds);

//id generator
function generateID() {
    const charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+1234567890";
    const length = Math.floor(Math.random() * charset.length) + 1;

    let possibleID = "";
    for (let i = 0; i < length; i++) {
        const randomIndex = Math.floor(Math.random() * charset.length);
        possibleID += charset[randomIndex];
    }

    return possibleID;
}


