<template>
	<!--v-drag="percent"-->
	<div class="u-dragbar" :percent="percent" :class="viewcolor">
		<em class="drag-handle" ref="draphandle" @mousedown="dragdown" :title="parseInt(percent*100)+'%'" ></em>
		
		<div class="u-dragbar-tag-box">
			<div class="rank-bar"></div>
			<span @click="rankcheck(0)" style="left:0;" title="0%"></span>
			<span @click="rankcheck(0.2)" style="left:20%;" title="20%"></span>
			<span @click="rankcheck(0.4)" style="left:40%;" title="40%"></span>
			<span @click="rankcheck(0.6)" style="left:60%;" title="60%"></span>
			<span @click="rankcheck(0.8)" style="left:80%;" title="80%"></span>
			<span @click="rankcheck(1)" style="left:100%;" title="100%"></span>
		</div>
	</div>
</div>
</template>

<script>
export default{
	props:['percent', 'viewcolor'],
	data(){
		return{
			theme:''
		}
	},
	mounted(){
		if(this.percent <= 1){
			this.setrank(this.percent)
		}
		
		if(this.viewcolor){
			this.theme = this.viewcolor
		}
	},
	watch:{
		percent(n,o){
			this.setrank(n)
		}
	},
	methods:{
		dragdown(e){
			let vm = this;
			let target = vm.$refs.draphandle;
			let parent = vm.$el;
			let	org_left = e.clientX - target.offsetLeft;
			document.onmousemove = function (e) {
				e.preventDefault();
				let left = e.clientX - org_left;
				if(left < 0){
					left = 0;
				}else if(left > parent.offsetWidth - target.offsetWidth ){
					left = parent.offsetWidth - target.offsetWidth;
				}
				
				var prc = (left/(parent.offsetWidth - target.offsetWidth)).toFixed(2);
				vm.$emit('update:percent', prc);
				vm.$emit('drag', prc);
				parent.querySelector('.rank-bar').style.width = prc*100 + '%';
				target.style.left = left + 'px';
			}
			
			document.onmouseup = function (e) {
				e.preventDefault();
				document.onmousemove = null;
			}
		},
		rankcheck(prc){
			this.setrank(prc);
			this.$emit('drag', prc);
		},
		setrank(prc){
			let target = this.$refs.draphandle;
			let parent = this.$el;
			this.$refs.draphandle.style.left = (parent.offsetWidth - target.offsetWidth)*prc + 'px';
			this.$emit('update:percent', prc);
			parent.querySelector('.rank-bar').style.width = prc*100 + '%';
		}
	}
}
</script>

<style lang="less">

</style>