<template>
	<ul class="fix-menu">

		<li v-for="(list, index) in menuList" v-if="index == currentModel">
			<router-link v-for="(item, index ) in list" v-if="item.visible" :to="item.label" :key="index">
				<i class="fa fs-20" :class="'fa-'+item.icon"></i><br />{{item.name}}
			</router-link>
		</li>
	</ul>
</template>

<script>
import {mapGetters} from 'vuex'
export default{
	computed:{
        ...mapGetters([
            'common_cnf',
        ]),
        hashVisble(){
        	return this.common_cnf.hashSwitch;
        }
    },
    watch:{
    	hashVisble(n,o){
    		var visible = (n == 0)?false:true;
    		this.menuList['user'][1]['visible'] = visible;
    	}
    },
    mounted(){
    	var visible = (this.hashVisble == 0)?false:true;
		this.menuList['user'][1]['visible'] = visible;
    },
	data(){
		return{
			currentModel:this.$route.matched[0].name,
			menuList:{
				user:[
					{ name:this.$i18n.t('cmn.user'), label:'/user/info', icon:'user', type:'info', visible:true },
					{ name:this.$i18n.t('cmn.hash'), label:'/user/hash', icon:'gears', type:'hash', visible:false  },
					{ name:this.$i18n.t('cmn.invitor'), label:'/user/invite', icon:'book', type:'invite', visible:true },
				],
				financial:[
					{ name:this.$i18n.t('cmn.assets'), label:'/financial/center', icon:'bitcoin', type:'center', visible:true},
					{ name:this.$i18n.t('cmn.recharge'), label:'/financial/recharge', icon:'mail-forward', type:'recharge', visible:true},
					{ name:this.$i18n.t('cmn.withdraw'), label:'/financial/withdraw', icon:'reply-all', type:'withdraw', visible:true},
					{ name:this.$i18n.t('cmn.bill'), label:'/financial/bill', icon:'file', type:'bill', visible:true},
					{ name:this.$i18n.t('cmn.entrust'), label:'/financial/entrust', icon:'file', type:'entrust', visible:true}
				]
			},
		}
	}
}
</script>

















