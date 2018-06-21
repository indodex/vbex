<template>
	<div>
		<!--<top-nav></top-nav>-->
		<transition name="fade" mode="out-in">
			<keep-alive>
		    	<router-view></router-view>
		    </keep-alive>
		</transition>
		<!--<fix-menu></fix-menu>-->
    </div>
</template>

<script>
//	import TopNav from '../common/TopNav'
	import fixMenu from '../common/fixMenu'
    export default {
		components:{
//			TopNav,
//			fixMenu
		}
    }
</script>


