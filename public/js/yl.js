// JavaScript Document
function displaySubMenu(li) { 
var subMenu = li.getElementsByTagName("div")[0]; 
subMenu.style.display = "block"; 
} 
function hideSubMenu(li) { 
var subMenu = li.getElementsByTagName("div")[0]; 
subMenu.style.display = "none"; 
} 

var flag=false;
setInterval(function() {
if (flag==false&&document.getElementById("blink")) {
document.getElementById("blink").style.color = "#FFFFFF";
document.getElementById("blinks").style.color = "#FFFFFF";
flag=true;
return;
}
if (flag==true&&document.getElementById("blink")) {
document.getElementById("blink").style.color = "#ffff00";
document.getElementById("blinks").style.color = "#ffff00";
flag=false;
return;
} 
}, 700);
