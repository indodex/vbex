//判断是否为一个整数
function isInteger(obj){
	return Math.floor(obj) === obj;
}
//将浮点转为整型
function toInteger(floatNum){
	var ret = {times:1, num:0}
	if(isInteger(floatNum)){
		ret.num = floatNum;
		return ret;
	}
	var strfi = floatNum + '';
	var dotPos = strfi.indexOf('.');
	var len = strfi.substr(dotPos+1).length;
	var times = Math.pow(10,len);
	var intNum = parseInt(floatNum * times + 0.5, 10);
	ret.times = times;
	ret.num = intNum;
	return ret;
}
//核心方法
function operation(a,b,op){
	var o1 = toInteger(a);
	var o2 = toInteger(b);
	var n1 = o1.num;
	var n2 = o2.num;
	var t1 = o1.times;
	var t2 = o2.times;
	var max = t1 > t2 ? t1 : t2;
	var result = '';
	switch(op){
		case 'add':
			if(t1 === t2){
				result = n1 + n2;
			}else if(t1 > t2){
				result = n1 + n2 * (t1 / t2)
			}else{
				result = n1 * (t2 / t1) + n2
			}
			return result / max;
			break;
		case 'sub':
			if(t1 === t2){
				result = n1 - n2;
			}else if( t1 > t2) {
				result = n1 - n2 * (t1 / t2)
			}else {
				result = n1 * (t2 / t1) - n2
			}
			return result / max;
			break;
		case 'mul':
			result = (n1 * n2) / (t1 * t2);
			return result;
			break;
		case 'div':
			result = (n1 / n2) * (t2 / t1);
			return result;
			break;
	}
}


export default{
	add(a, b) {
		return operation(a, b, 'add');
	},
	sub(a, b) {
		return operation(a, b, 'sub');
	},
	mul(a, b) {
		return operation(a, b, 'mul');
	},
	div(a, b) {
		return operation(a, b, 'div');
	},
	res(a,e){
		var _this = this;
		var n = a;
		if(n > 0 && n < Math.pow(10, -6)){
			n = 0;
			return n.toFixed(e);
		}
		n = n.toString();
		if(n.indexOf('.') == -1){
			return n
		}else{
			n = n.substring(0, n.indexOf('.') + parseInt(e) + 1);
			return n;
		}
		
	}

}
