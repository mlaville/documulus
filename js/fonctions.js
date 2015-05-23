
function browser() {

	var userAgent = navigator.userAgent;
	var tabBrowser = ["Firefox", "Chrome", "Opera"];
	
	function isBigEnough(element, index, array) {  
	  return (element >= 10);  
	}  
var filtered = [12, 5, 8, 130, 44].filter(isBigEnough);  

}