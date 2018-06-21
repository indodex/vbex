import Vue from 'vue'
import comfirmComponent from './comfirm.vue'
import merge from 'lodash/merge'

let instance;

let globalConfig = {};

let comfirmConstructor = Vue.extend(comfirmComponent);

let initInstance = () => {
	instance = new comfirmConstructor({
		el:document.createElement('div')
	});
	document.getElementById('app').appendChild(instance.$el)
}

let Comfirmbox = (options) => {
	initInstance();
	
//	options.title = content;
//	options.content = content;
	
	merge(instance.$data, options);
	
	let prm = new Promise((resolve, reject)=>{
		instance.show = true;
		
		//fix 弹窗出来后依旧没有收回
//		let inputs = Array.prototype.slice.call(document.querySelectorAll('input'));
//		inputs.forEach((input)=>{
//          input.blur();
//      });
//		console.log(resolve, reject)
		let success = instance.success;
		let fail = instance.fail;
//		
		instance.success = () => {
			success();
			resolve('ok');
		}		
		
		instance.fail = () => {
			fail();
			reject('fail');
		}	
	});
	
//	console.log(prm)
	
	return prm;
	
}

export default {
	install (Vue, options){
		Vue.prototype.$comfirmbox = Comfirmbox;
	}
}
