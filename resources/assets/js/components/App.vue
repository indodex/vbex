<template>
    <div class="clearfix">
        <top-nav v-if='visible'></top-nav>
        <transition name="fade" mode="out-in">
            <router-view></router-view>
        </transition>
        <!--<footer-view></footer-view>-->
    </div>
</template>

<script>
    import jwtToken from './../helpers/jwt'
    import Cookie from 'js-cookie'
	import TopNav from './common/TopNav'
    export default {
        created() {
            if (jwtToken.getToken()) {
                this.$store.dispatch('setAuthUser')
            } else if(Cookie.get('auth_id')) {
                this.$store.dispatch('refreshToken')
            }
            this.$store.dispatch('setBaseConfig');
            
            var browserLang = (navigator.language || navigator.browserLanguage).toLowerCase();
            browserLang = this.$i18n.messages[browserLang]?browserLang:'zh-cn';
            this.$i18n.locale = Cookie.get('lang') || browserLang;
        },
        data(){
        	return{
                visible:false
        	}
        },
        components:{
        	TopNav
        },
        mounted(){
        	var vm = this;
        	vm.visible = (vm.$route.name == 'logins' || vm.$route.name == 'regists' || vm.$route.name == 'reset')?false:true;
            vm.$router.afterEach(function(){
            	vm.visible = (vm.$route.name == 'logins'|| vm.$route.name == 'regists' || vm.$route.name == 'reset')?false:true;
            })
            
        }
    }

</script>


<style lang="less">
	@import url('../../../../public/css/less/hac/hac-content-2.less');
</style>