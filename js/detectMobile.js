/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

var deviceIphone = "iphone";
var deviceIpod = "ipod";
var deviceIpad = "ipad";

//Initialize our user agent string to lower case.
var uagent = navigator.userAgent.toLowerCase();
var isIDevice = false;
//**************************
// Detects if the current device is an iPhone.
function DetectIphone()
{
   if (uagent.search(deviceIphone) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current device is an iPod Touch.
function DetectIpod()
{
   if (uagent.search(deviceIpod) > -1)
      return true;
   else
      return false;
}

//**************************
// Detects if the current device is an iPad Touch.
function DetectIpad()
{
   if (uagent.search(deviceIpad) > -1)
      return true;
   else
      return false;
}
//**************************
// Detects if the current device is an iPhone or iPod Touch.
function DetectIDevice()
{
    if (DetectIphone()){
       isIDevice = true;
       return true;
    }
    else if (DetectIpod()){
       isIDevice = true;
       return true;
    }
    else if (DetectIpad()){
       isIDevice = true;
       return true;
    }
    else{
       isIDevice = false;
       return false;
    }
}
