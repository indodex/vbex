<template>
<div class="cn lr-page">
	<div class="container" style="position:relative; z-index:10;">
        <div class="title text-center">
        	<!--<a href="javascript:history.go(-1);" class="pull-left">
        		<i class="fa fa-angle-left fa-2x" aria-hidden="true"></i> 
        	</a>-->
        	{{title}}
        </div>
	    <div class="tip-panel">
	        <!--<div class="input-group-hd">{{message}}</div>-->
	        <em class="sta-logo" :class="status"></em>
	        <!--<em class="sta-logo success"></em>-->
	        <div class="tip-content">
	        	{{message}}
	        </div>
	    </div>
	    <div class="logo-icon text-center">
	        <img src="/img/logo-icon.png" /></img>
	    </div>
	</div>
</div>
</template>



<script>
    export default {
    	components:{
    		
    	},
        data() {
            return {
            	title:'',
                message:'',
                status:'',
            }
        },
        mounted() {
        	var vm = this;
        	var type_ = vm.$route.query.infotype;
			switch(type_){
				case 'withdrawApply':
					vm.title = vm.$i18n.t('cmn.email') + vm.$i18n.t('cmn.check');
					vm.withdrawApply();
					break;
				case 'resetSuccess':
					vm.resetSuccess();
					break;
				default:
					vm.message = vm.$i18n.t('cmn.dataerror');
					vm.status = 'warning';
					break;
			}
        },
        methods: {
            withdrawApply() {
                var vm = this
                var id_ = vm.$route.query.id

                axios.get(this.commonApi.api.withdrawConfirm, {params:{'id':id_}}).then(function(response){
                    var res = new Object(response.data);
                    vm.message = res.message;
                    if(res.code == 200){
                    	vm.status = 'success';
                    }else{
                    	vm.status = 'warning'
                    }    
                })
            },
            resetSuccess(){
            	var vm = this;
            	vm.message = vm.$i18n.t('tips.resetPwdSuccess');
            	vm.status = 'success';
            	setTimeout(function(){
            		vm.$router.push({path:'/login'})
            	},3000)
            }
        }
    }
</script>
