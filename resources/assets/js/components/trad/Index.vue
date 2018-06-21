<template>
	<div>
		<!--<top-nav></top-nav>-->
		<transition name="fade" mode="out-in">
			<router-view></router-view>
		</transition>
    </div>
</template>

<script>
//	import TopNav from '../common/TopNav'
    export default {
		components:{
//			TopNav,
		}
    }
</script>



<!--<style lang="less">
	@import url('../../../../../public/css/less/hac/hac-content-2.less');
</style>-->