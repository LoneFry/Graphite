/** **************************************************************************
 * Project     : Graphite
 *                Simple MVC web-application framework
 * Created By  : LoneFry
 *                dev@lonefry.com
 * License     : CC BY-NC-SA
 *                Creative Commons Attribution-NonCommercial-ShareAlike
 *                http://creativecommons.org/licenses/by-nc-sa/3.0/
 *
 * File        : /^CLI/js/CLI.js
 *                Graphite CLI front-end scripts
 ****************************************************************************/

/**
 * Hold the CLI's XHR object
 */
var CLI_XHR;

/**
 * Send a command to the Graphite Shell, via XHR is appropriate
 *
 * @param object oForm The DOM object for the command prompt's form
 *
 * @return bool Whether browser should submit form
 */
function CLI_runCommand(oForm) {
	var cmd = document.getElementById('prompt').value.split(' ')[0];
	if (refreshers) {
		for (f in refreshers) {
			if (refreshers[f] == cmd) {
				return true;
			}
		}
	}
	if (document.getElementById('prompt').disabled) {
		return false;
	}

	CLI_XHR = window.ActiveXObject?new ActiveXObject("Microsoft.XMLHTTP")
				:new XMLHttpRequest();
	var formData = document.getElementById('prompt').value;
	formData = 'prompt=' + (escape(formData).replace(/\+/g,'%2b'));

	CLI_XHR.open('POST', '/Gsh?a', true);
	CLI_XHR.onreadystatechange = CLI_runCommand_;
	CLI_XHR.setRequestHeader("Content-Type", 'application/x-www-form-urlencoded');
	CLI_XHR.setRequestHeader("Content-length", formData.length);
	CLI_XHR.send(formData);

	document.getElementById('prompt').disabled = true;
	return false;
}

/**
 * Process response to XHR created by CLI_runCommand()
 *
 * @param object oXML    XHR object used to send command
 * @param object aParams Parameters of XHR request
 *
 * @return void
 */
function CLI_runCommand_() {
	if (CLI_XHR.readyState != 4) {
		return;
	}
	document.getElementById('buffer').innerHTML += CLI_XHR.responseText;
	document.getElementById('prompt').value = '';
	document.getElementById('prompt').disabled = false;
	document.getElementById('prompt').focus();
	document.getElementById('prompt').scrollIntoView();
}

/**
 * Resize CLI DOM objects based on window size
 *
 * @return void
 */
function CLI_resize() {
	var newHeight = window.innerHeight;
	newHeight -= document.getElementById('header').clientHeight;
	newHeight -= document.getElementById('footer').clientHeight;
	document.getElementById('cli').style.height =
		Math.max(400, newHeight) + 'px';
	document.getElementById('prompt').style.width =
		(document.getElementById('cli').clientWidth-document.getElementById('submit').clientWidth - 40) + 'px';
}
