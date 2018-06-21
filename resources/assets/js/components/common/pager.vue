<template>
	<div class="tb-pager">
		<div class="inner">
			<a class="prev" :class="(parseInt(curnum) > 1)||'disabled'" @click="prev">{{$t('cmn.prevpage')}}</a>
			<!--<span>第{{curnum}}页</span>-->
			<span>{{$t('cmn.curpage',{page:curnum})}}</span>
			<a class="next":class="(parseInt(curnum) < parseInt(lastPage))||'disabled'"  @click="next">{{$t('cmn.nextpage')}}</a>
		</div>
	</div>
</template>

<script>
export default{
	props:['curnum','lastPage'],
	methods:{
		prev(){
			var cur = parseInt(this.curnum);
			if(cur > 1){
				this.$emit('skip', cur - 1);
			}else{
				
			}
		},
		next(){
			var cur = parseInt(this.curnum);
			var per = parseInt(this.lastPage);
			if(cur < per)
			this.$emit('skip', cur + 1);
		}
	}
}
</script>

