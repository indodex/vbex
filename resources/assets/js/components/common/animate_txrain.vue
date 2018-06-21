<template>
<canvas id="crain" style="position:fixed; top:0; left:0; right:0; bottom:0; height:100%; width:100%; display:block;"></canvas>
</template>

<script>
export default{
	mounted(){
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
		        
		        drop[i]++;
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
	}
}
</script>

<style>
</style>