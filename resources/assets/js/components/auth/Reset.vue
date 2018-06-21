<template>
<div class="cn lr-page">
	<txrain></txrain>
	<div class="container" style="position:relative; z-index:10;">
        <div class="title text-center">
        	<a href="javascript:history.go(-1);" class="pull-left"><i class="fa fa-angle-left fa-2x" aria-hidden="true"></i> </a>
        	{{$tc('member.actionFirst',2)}}{{$t('member.loginPassword')}}
        </div>
	    <div class="panel col-xs-12 col-sm-6 col-md-5">
	        <form class="form-horizontal" @submit.prevent="emailSend">
	        	<div class="fs-14 mb20 text-gray-light text-center">{{$tc('arts.resetlog', step)}}</div>
	            <div class="input-grunp">
	                <input v-model="email" id="email" type="text" class="form-control input-lg" :placeholder="$t('cmn.email')" :disabled="issend">
	            </div>
	            <div class="clearfix">
	            	<div v-loading="!loaded">
	                	<button type="submit" class="btn btn-primary form-control">{{$t('cmn.send')}}{{$t('cmn.email')}}</button>
	                </div>
	                <div class="input-group-hd">
	                    <router-link class="pull-right text-white" to="/login">{{$tc('cmn.login')}}</router-link>
	                </div>
	            </div>
	        </form>
	    </div>
	    <div class="logo-icon text-center">
	        <img src="/img/logo-icon.png" /></img>
	    </div>
	</div>
</div>
</template>



<script>
	import txrain from '../common/animate_txrain'
    export default {
    	components:{
    		txrain
    	},
        data() {
            return {
            	loaded:true,
            	email:'',
            	issend:false,
            	step:1
            }
        },
        methods: {
        	emailSend(){
        		var vm = this;
        		console.log(this.email)
        		if(!vm.email){
        			vm.$comfirmbox({content:vm.$i18n.t('cmn.please')+vm.$i18n.t('cmn.input')+vm.$i18n.t('cmn.email')})
        		}else{
        			vm.loaded = false;
        			axios.post(vm.commonApi.api.userResetEmail, {email:vm.email}).then(function(response){
        				var res = response.data;
        				vm.loaded = true;
    					vm.$comfirmbox({content:res.message, status:res.code}).then(function(){
    						if(res.code == 200){
    							vm.$router.push({path:'/login'});
    						}
    					});
        			}).catch(function(err){
        				vm.$comfirmbox({content:err});
        				vm.loaded = true;
        			})
        		}
        	},
        }
    }
</script>
