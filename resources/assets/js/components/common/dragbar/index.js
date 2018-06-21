import Vue from 'vue'
import dragbarComponent from './dragbar.vue'

//const dragbar;

export default {
	install(Vue, options){
		Vue.component('dragbar',dragbarComponent);
		Vue.directive('drag', {
			bind (el, binding) {
//				var _handler = el.querySelector('.drag-handle')
				let _handler = document.createElement('em'),
					parent = el;
				_handler.className = 'drag-handle';
				parent.appendChild(_handler);
				
				
//				console.log(/^[0-9]*$/.test(pct))
				
				
				_handler.onmousedown = function (e) {
					e.preventDefault();
					var target = this,
						org_left = e.clientX - target.offsetLeft;
					document.onmousemove = function (e) {
						e.preventDefault();
						var left = e.clientX - org_left;
						if(left < 0){
							left = 0;
						}else if(left > parent.offsetWidth - target.offsetWidth){
							left = parent.offsetWidth - target.offsetWidth;
						}
						
						binding.value = left/(parent.offsetWidth - target.offsetWidth);
						
						target.style.left = left + 'px';
					}
					
					document.onmouseup = function (e) {
						e.preventDefault();
						document.onmousemove = null;
					}
				}
				
				
			},
			inserted (el, binding) {
				let parent = el;
				let target = el.querySelector('em');
				let pct = binding.value;
				
				
				if(pct <= 1){
					target.style.left = (parent.offsetWidth - target.offsetWidth)*pct + 'px';
				}
			},
			update (el) {
//				console.log(el)
			}
		})
	}
}



























