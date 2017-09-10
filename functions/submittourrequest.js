var xmlhttp = new XMLHttpRequest();
xmlhttp.onreadystatechange=function() {if (xmlhttp.readyState==4) if (xmlhttp.status==200) { document.getElementById("tourTypes").innerHTML=xmlhttp.responseText;}}
xmlhttp.open("GET","/igis/functions/gettourtypeoptions.php",true);
xmlhttp.send();
function AJAXPost(e,formId) {
	e.preventDefault()
    var elem   = document.getElementById(formId).elements;
    var url    = document.getElementById(formId).action;
    var params = "";
    var value;
    for (var i = 0; i < elem.length; i++) {
        if (elem[i].tagName == "SELECT") {
            value = elem[i].options[elem[i].selectedIndex].value;
        } else {
            value = elem[i].value;
        }
        params += elem[i].name + "=" + encodeURIComponent(value) + "&";
    }
	//alert(params+"/n/nSubmitting to "+url);
    xmlhttp2=new XMLHttpRequest();
	xmlhttp2.onreadystatechange=function() {if (xmlhttp2.readyState==4) if (xmlhttp2.status==200) { alert('Thank you! Your request has been submitted.'); return xmlhttp2.responseText;}}
    xmlhttp2.open("POST",url,true);
	xmlhttp2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xmlhttp2.send(params);
}
