const nl2br = (str) => {
    if(str===null)return;
    str = str.replace(/\r\n/g, "<br />");
    str = str.replace(/(\n|\r)/g, "<br />");
    return str;
}
export{nl2br}
