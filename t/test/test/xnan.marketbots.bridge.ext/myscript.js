function myscript_test() {
	alert('myscript_test');
}





//<a data-v-ec877c4e="" href="/ingresar" class="blue_"><div data-v-ec877c4e="" class="hide-on-small-only"><span data-v-ec877c4e="" class="btn-nav"><i data-v-ec877c4e="" class="material-icons left blue_">person</i> <span data-v-ec877c4e="">Ingresá</span></span></div> <i data-v-ec877c4e="" class="material-icons hide-on-med-and-up blue_">person</i></a>

function rava_login_click() {
	console.log("rava_login_click");
	button_count=$("span.btn-nav").length;
	console.log("button_count:"+button_count);
	$("span.btn-nav").click();
}


// loginform: https://clasico.rava.com/lib/restapi/oauth2/authorize.php?response_type=code&state=https://www.rava.com|&client_id=eze&redirect_uri=https://www.rava.com/oauth
function rava_loginform_fill() {
	console.log("rava_loginform_fill");
	$("input#u_usuario").attr("value","miusuario");
	$("input#u_clave").attr("value","miclave");
	$('button[value="Login"]').click();	
}

//Access-Control-Allow-Origin 
//https://sebhastian.com/javascript-csv-to-array/
// wait con jquery: https://stackoverflow.com/questions/1836105/how-to-wait-5-seconds-with-jquery
// deferred jjquery guide: http://tutorials.jenkov.com/jquery/deferred-objects.html
function test_get_contents() {	
	$.ajax({
	  url: "http://localhost/test/test.php",
	  context: document.body,
	  mode: 'no-cors'
	}).done(function(data) {
	  añert(data);
	});
}
//https://developer.chrome.com/docs/extensions/mv3/intro/mv3-migration/
//https://stackoverflow.com/questions/13591983/onclick-or-inline-script-isnt-working-in-extension/25721457#25721457

$(document).ready(function () {
	//rava_login_click();
	//rava_loginform_fill();
	test_get_contents();
});
