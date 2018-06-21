const countdown = function (num,msg){
	let timer = setInterval((count = parseInt(num)) => {
//		console.log(count, num)
		if(count - 1 >= 0){
			console.log(this)
			count--;
			num = count;
		}else{
			console.log('stop')
			num = msg;
			clearInterval(timer)
		}
	}, 1000)
}

export default countdown