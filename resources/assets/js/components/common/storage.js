const hs = 'hacStorage';
const stg = window.localStorage;
export default {
	init(){
		if(!stg){
			console.log('this browser nonsupport localStorage');
			return false;
		}
		stg.setItem(hs,'{"data":{}}');
	},
	setItem(key, val){
		
		var data = stg.getItem(hs);
		if(!data){
			this.init();
			data = stg.getItem(hs);
		}
		
		console.log(data);
		data = JSON.parse(data);
		data.data[key] = val;
		
		stg.setItem(hs, JSON.stringify(data));
		return data.data;
	},
	getItem(key){
		var data = stg.getItem(hs);
		if(!data){
			return false;
		}
		data = JSON.parse(data);
		return data.data[key];
	},
	remove(key){
		var data = stg.getItem(hs);
		if(!data){
			return false;
		}
		data.parse(data);
		delete data.data[key];
		stg.setItem(hs,JSON.stringify(data));
		return data;
	},
	clear(){
		stg.removeItem(hs)
	}
}