export default{
	add(a, b) {
		var _this = this;
	    var c, d, e;
	    try {
	        c = a.toString().split(".")[1].length;
	    } catch (f) {
	        c = 0;
	    }
	    try {
	        d = b.toString().split(".")[1].length;
	    } catch (f) {
	        d = 0;
	    }
	    return e = Math.pow(10, Math.max(c, d)), (_this.mul(a, e) + mul(b, e)) / e;
	},
	sub(a, b) {
		var _this = this;
	    var c, d, e;
	    try {
	        c = a.toString().split(".")[1].length;
	    } catch (f) {
	        c = 0;
	    }
	    try {
	        d = b.toString().split(".")[1].length;
	    } catch (f) {
	        d = 0;
	    } 
	    return e = Math.pow(10, Math.max(c, d)), (_this.mul(a, e) - mul(b, e)) / e;
	},
	mul(a, b) {
		var _this = this;
	    var c = 0,
	        d = a.toString(),
	        e = b.toString();
	    try {
	        c += d.split(".")[1].length;
	    } catch (f) {}
	    try {
	        c += e.split(".")[1].length;
	    } catch (f) {}
	    return Number(d.replace(".", "")) * Number(e.replace(".", "")) / Math.pow(10, c);
	},
	div(a, b) {
		var _this = this;
	    var c, d, e = 0,
	        f = 0;
	    try {
	        e = a.toString().split(".")[1].length;
	    } catch (g) {}
	    try {
	        f = b.toString().split(".")[1].length;
	    } catch (g) {}
	    return c = Number(a.toString().replace(".", "")), d = Number(b.toString().replace(".", "")), _this.mul(c / d, Math.pow(10, f - e));
	},
	//Math.floor这总方法当数字过大的时候会变成科学计数法
//	res(a, e){
//		var _this = this;
//		var n = Math.pow(10, e);
//		console.log(_this.mul(a, n), n)
//		return parseFloat(Math.floor(_this.mul(a, n)) / n);
//	},
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
