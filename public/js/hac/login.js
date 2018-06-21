/**
 * Created by mango.xu on 2017/10/26.
 */

(function ($) {
	$('#submit-verify-mail').on('click', function(){
		var _email = $("#verify_mail").val();
	    $('#submit-verify-mail').attr('disabled',true);
	    $.get('/api/verifications',{'email':_email},function(data){
	    	$('#submit-verify-mail').attr('disabled',false);
	        if(data.code == 200){
	        	showToast(data.message, 'success');
	            $('#step li:eq(1) a').tab('show');
	            $('#send-verify-email').val(_email);
	            return false;
	        } else {
	        	showToast(data.message, 'error');
	        }
	    },'json');
	});

	$('#verify-code').on('click', function(){
		var _email = $("#verify_mail").val();
		var _code = $("#code").val();
	    $('#verify-code').attr('disabled',true);
	    $.get('/api/verify/code',{'email':_email, 'code':_code},function(data){
	    	$('#verify-code').attr('disabled',false);
	        if(data.code == 200){
	            $('#step li:eq(2) a').tab('show');
	            return false;
	        } else {
	        	showToast(data.message, 'error');
	        }
	    },'json');
	});

	$('#do-register').on('click', function(){
		var _userdata = {},
			_email = $("#verify_mail").val(),
			_code = $("#code").val(),
			_password = $("#password").val(),
			_repassword = $("#comfim-password").val(),
			_name = $("#name").val();
			_inviteuid = $("#invite_uid").val();

		_userdata = {
			'email' : _email,
			'code' : _code,
			'name' : _name,
			'pwd' : _password,
			'repwd' : _repassword,
			'inviteuid' : _inviteuid,
		};
	    $('#do-register').attr('disabled',true);
	    $.post('/api/register',_userdata,function(data){
	    	$('#do-register').attr('disabled',false);
	        if(data.code == 200){
	            showToast(data.message, 'success');
	            window.location.href='/login';
	            return false;
	        } else {
	        	showToast(data.message, 'error');
	        }
	    },'json');
	});

	$('#do-login').on('click', function(){
		var _userdata = {},
			_email = $("#email").val(),
			_password = $("#password").val(),

		_userdata = {
			'email' : _email,
			'password' : _password,
		};
	    $('#do-login').attr('disabled',true);
	    $.post('/api/login',_userdata,function(data){
	    	$('#do-login').attr('disabled',false);
	        if(data.code == 200){
	            showToast(data.message, 'æ³¨å†ŒæˆåŠŸè¯·ç™»å½•');
	            setTimeout(function () { 
			        window.location.href='/login';
			    }, 4000);
	            return false;
	        } else {
	        	showToast(data.message, 'error');
	        }
	    },'json');
	});
})(jQuery);

var c = document.querySelector("#crain");
var ctx = c.getContext("2d");
	c.width = c.offsetWidth;
	c.height = c.offsetHeight;
var string1 = "010000101001110001";
var fontsize = 9;
var columns = c.width / fontsize;
var drop = new Array();
var fps = 20;
	string1.split("");
var then = new Date();
window.requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame;
for (var x = 0; x < columns; x++) {
    	drop[x] = -(Math.random()*100);
}

function draw(){
	ctx.fillStyle = "rgba(26,31,36,0.3)";
    ctx.fillRect(0, 0, c.width, c.height);
    ctx.fillStyle = "rgba(255,161,0,0.3)";
    ctx.font = fontsize + "px arial";
    for (var i = 0; i < drop.length; i++) {
        var v = string1[Math.floor(Math.random() * string1.length)];
        ctx.fillText(v, i * fontsize, drop[i] * fontsize);
        
        drop[i] += 1.5;
        if (drop[i] * fontsize > c.height && Math.random() > 0.99) {
            drop[i] = 0;
        }
    }
}

(function run (){
	if(window.requestAnimationFrame){
		requestAnimationFrame(function loop(){
			requestAnimationFrame(loop);
			var cur = new Date();
			var delate = cur - then;
			if(delate > 1000/fps){
				then = cur - (delate % (1000/fps))
				draw();
			}
		});
	}else{
		setTimeout(run, 1000/fps);
		draw();
	}
})()