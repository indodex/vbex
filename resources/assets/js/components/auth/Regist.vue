<template>
<div class="cn lr-page">
	<txrain></txrain>
	<div class="container" style="position:relative; z-index:10;">
        <div class="title text-center">
        	<a href="javascript:history.go(-1);" class="pull-left"><i class="fa fa-angle-left fa-2x" aria-hidden="true"></i> </a>
        	{{$t('cmn.user')}}{{$t('cmn.regist')}}
        </div>
	    <div class="panel col-xs-12 col-sm-6 col-md-5" v-if="step == 1">
	        <form class="form-horizontal" @submit.prevent="emailCheck">
	        	<div class="fs-14 mb20 text-gray-light">{{$t('arts.registLog')}}</div>
	            <div class="input-grunp">
	                <input v-model="subform.email" id="email" type="text" class="form-control input-lg" :placeholder="$t('cmn.email')">
	            </div>
	            <div class="clearfix">
	            	<div v-loading="!loaded">
	                	<button type="submit" class="btn btn-primary form-control">{{$tc('cmn.nextstep')}}</button>
	                </div>
	                <div class="input-group-hd">
	                    <router-link class="pull-right text-white" to="/login">{{$tc('cmn.login')}}</router-link>
	                </div>
	            </div>
	        </form>
	    </div>
	    <div class="panel col-xs-12 col-sm-6 col-md-5" v-if="step == 2">
	        <form class="form-horizontal" @submit.prevent="codeCheck">
	        	<div class="fs-14 mb20 text-gray-light">{{$t('arts.codelog')}}</div>
	            <div class="input-grunp">
	                <input v-model="subform.code" id="code" type="text" class="form-control input-lg" :placeholder="$t('cmn.email')+$t('cmn.code')">
	            </div>
	            <div class="clearfix">
	            	<div v-loading="!loaded">
	                	<button type="submit" class="btn btn-primary form-control">{{$tc('cmn.nextstep')}}</button>
	                </div>
	                <div class="input-group-hd">
	                    <a class="pull-left text-white" @click="stepCheck(1)" style="cursor:pointer;">{{$tc('cmn.prevstep')}}</a>
	                    <router-link class="pull-right text-white" to="/login">{{$tc('cmn.login')}}</router-link>
	                </div>
	            </div>
	        </form>
	    </div>
	    <div class="panel col-xs-12 col-sm-6 col-md-5" v-if="step == 3">
	        <form class="form-horizontal" @submit.prevent="registFormSubmit">
	        	<div class="input-grunp">
	                <input v-model="subform.username" id="name" type="text" class="form-control input-lg" :placeholder="$t('referrer.username')" required>
	            </div>
	            <div class="input-grunp">
	                <input v-model="subform.pwd" id="pwd" type="password" class="form-control input-lg" :placeholder="$t('cmn.password')" required>
	            </div>
	            <div class="input-grunp">
	                <input v-model="subform.repwd" id="repwd" type="password" class="form-control input-lg" :placeholder="$t('cmn.confirm')+$t('cmn.password')" required>
	            </div>
	            <div class="clearfix">
	            	<div v-loading="!loaded">
	                	<button type="submit" class="btn btn-primary form-control">{{$tc('cmn.regist')}}</button>
	                </div>
	                <div class="input-group-hd">
	                	<a class="pull-left text-white" @click="stepCheck(1)" style="cursor:pointer;">{{$tc('cmn.prevstep')}}</a>
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
            	subform:{username:'', pwd:'', repwd:'', email:'', code:''},
            	loaded:true,
            	step:1,
            	inviteid:(this.$route.query.inviteid || 0)
            }
        },
//      mounted(){
//			console.log(this.inviteid)
//      },
        methods: {
        	emailCheck(){
        		var vm = this;
        		if(!vm.subform.email){
        			vm.$comfirmbox({content:vm.$i18n.t('cmn.please')+vm.$i18n.t('cmn.input')+vm.$i18n.t('cmn.email')})
        		}else{
        			vm.loaded = false;
        			axios.get(vm.commonApi.api.userRegistEmailboxCheck, {params:{email:vm.subform.email}}).then(function(response){
        				var res = response.data;
        				vm.loaded = true;
        				if(res.code == 200){
        					vm.stepCheck(2)
        				}else{
        					vm.$comfirmbox({content:res.message});
        				}
        			}).catch(function(err){
        				vm.$comfirmbox({content:err});
        				vm.loaded = true;
        			})
        		}
        	},
        	codeCheck(){
        		var vm = this;	
        		if(!vm.subform.code){
        			vm.$comfirmbox({content:vm.$i18n.t('cmn.please')+vm.$i18n.t('cmn.input')+vm.$i18n.t('cmn.code')})
        		}else{
        			vm.loaded = false;
        			axios.get(vm.commonApi.api.userRegistCodeCheck, {params:{email:vm.subform.email, code:vm.subform.code}}).then(function(response){
        				var res = response.data;
        				vm.loaded = true;
        				if(res.code == 200){
        					vm.stepCheck(3)
        				}else{
        					vm.$comfirmbox({content:res.message});
        				}
        			}).catch(function(err){
        				vm.$comfirmbox({content:err});
        				vm.loaded = true;
        			})
        		}
        	},
        	registFormSubmit(){
        		var vm = this;
    			vm.loaded = false;
        		axios.post(vm.commonApi.api.userRegist, {
                    email: vm.subform.email,
                    code: vm.subform.email,
                    name: vm.subform.username,
                    pwd: vm.subform.pwd,
                    repwd: vm.subform.repwd,
                    inviteuid:vm.inviteid
                }).then(function(response){
                	var res = response.data;
    				vm.loaded = true;
                	if(res.code == 200){
                		vm.$comfirmbox({content:res.message, status:res.code}).then(function(){
							window.location.href = '/login';
                		});
                		setTimeout(function(){
                			window.location.href = '/login';
                		},5000)
                	}else{
                		vm.$comfirmbox({content:res.message})
                	}
                }).catch(function(err){
    				vm.$comfirmbox({content:err});
    				vm.loaded = true;
    			})
        	},
        	stepCheck(i){
        		this.step = parseInt(i)
        	}
        }
    }
</script>
