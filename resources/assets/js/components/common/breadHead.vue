<template>
	<div class="breadHead">
		<span class="m fs-18">{{mainame}}</span>
		<!--<span>/<i class="fa fa-home"></i></span>-->
		/<span class="fs-14">{{routename}}</span>
	</div>
</template>

<script>
export default{
	computed:{
		mainame(){
			var vm = this;
			return vm.$t('cmn.' + this.$route.matched[0].name) + vm.$t('cmn.center')
		},
		routename(){
			var vm = this;
			return vm.$t('cmn.' + this.$route.matched[1].name)
		}
	},
}
</script>
