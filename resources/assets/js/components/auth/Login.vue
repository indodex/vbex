<template>
<div class="cn lr-page">
	<txrain></txrain>
	<div class="container" v-if="isVerify == 0" style="position:relative; z-index:10;">
        <div class="title text-center"><a href="javascript:history.go(-1);" class="pull-left"><i class="fa fa-angle-left fa-2x" aria-hidden="true"></i> </a>{{$tc('cmn.login')}}</div>
	    <div class="panel col-xs-12 col-sm-6 col-md-5">
	        <form class="form-horizontal" @submit.prevent="getSafeOption">
	        	<div class="input-group-hd">{{$tc('member.usernamePwd')}}</div>
	            <div class="input-grunp">
	                <input v-model="account" 
	                       id="account" type="text" class="form-control input-lg" :placeholder="$tc('cmn.email')+$tc('cmn.address')+'/'+$tc('cmn.phone')" name="account" required>
	            </div>
	            <div class="input-grunp">
	                <input v-model="password"
	                       id="password" type="password" class="form-control input-lg" :placeholder="$tc('cmn.password')" name="password" required>
	            </div>
	            <div class="clearfix">
	            	<div v-loading="!loaded">
	                	<button type="submit" class="btn btn-primary form-control">{{$tc('cmn.login')}}</button>
	                </div>
	                <div class="input-group-hd">
	                    <!--<a class="pull-left text-white" href="/password/reset">{{$tc('cmn.forgetPassword')}}</a>-->
	                    <router-link class="pull-left text-white" to="/reset">{{$tc('cmn.forgetPassword')}}</router-link>
	                    <router-link class="pull-right text-white" to="/regist">{{$tc('cmn.regist')}}</router-link>
	                </div>
	            </div>
	        </form>
	    </div>
	    <div class="logo-icon text-center">
	        <img src="/img/logo-icon.png" /></img>
	    </div>
	</div>
    <div class="container" style="position:relative; z-index:10;" v-if="isVerify == 1" >
        <div class="title text-center">
            <a href="javascript:history.go(-1);" class="pull-left">
                <i class="fa fa-angle-left fa-2x" aria-hidden="true"></i> 
            </a>
            {{$tc('cmn.login')}}{{$tc('cmn.check')}}
        </div>
        <div class="panel col-xs-12 col-sm-6 col-md-5">
            <form class="form-horizontal" @submit.prevent="login">
                
                <div class="input-grunp form-group" v-if="safeLevel == 2 || safeLevel == 3" style="width:100%; margin-left:0;">
                    <div class="group-col">
                        <a class="col-btn" @click="sendEmailCode" v-if="countdown == 0" style="line-height:46px;">{{$tc('cmn.gain')}}{{$tc('cmn.code')}}</a>
                        <span class="col-btn" v-if="countdown > 0">{{ countdown }}{{$tc('member.reGet')}}</span>
                        <span class="col-btn" v-if="countdown < 0">{{$tc('member.sendingVerCode')}}</span>
                        <input class="form-control input-lg" type="text" v-model="emailCode" v-if="isMobile == 1" :placeholder=" $t('cmn.mobile') + $t('cmn.code')"/>
                        <input class="form-control input-lg" type="text" v-model="emailCode" v-else :placeholder=" $t('cmn.email') + $t('cmn.code')"/>
                    </div>
                </div>
                <div class="input-grunp" v-if="safeLevel == 1 || safeLevel == 3">
                    <input v-model="googleCode" 
                           v-validate data-vv-rules="required" :data-vv-as="$tc('member.googleCode')"
                           id="googleCode" type="googleCode" class="form-control input-lg" :placeholder="$tc('member.googleCode')" name="googleCode" required>
                </div>
                <div class="clearfix">
                	<div v-loading="!loaded">
                    	<button type="submit" class="btn btn-primary form-control">{{$tc('cmn.check')}}</button>
                    </div>
                    <div class="input-group-hd">
                        
                        <a class="pull-right text-white" href="/">{{$tc('cmn.goBack')}}</a>
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
    import jwtToken from './../../helpers/jwt'
    import { ErrorBag } from 'vee-validate'
	import txrain from '../common/animate_txrain'
    export default {
    	components:{
    		txrain
    	},
        data() {
            return {
                account: '',
                email:'',
                password: '',
                googleCode:'',
                emailCode:'',
                safeLevel:0,
                isVerify:0,
                countdown:0,
                interval:'',
                loaded:true,
                isMobile:'',
                bag : new ErrorBag()
            }
        },
        computed: {
            mismatchError() {
                return this.bag.has('password:auth') && !this.errors.has('password')
            }
        },
        methods: {
            login() {
            	var vm = this;
            	vm.loaded = false;
                this.$validator.validateAll().then(result => {
                    if (result) {
						
                        let formData = {
                            account: this.account,
                            password: this.password,
                            emailCode: this.emailCode,
                            googleCode: this.googleCode
                        }
                        
                        this.$store.dispatch('loginRequest', formData).then(response => {
                            if(response.code == 421) {
                            	vm.$comfirmbox({content:response.message});
                            	vm.loaded = true;
                            } else if(response.code == 200) {
                                window.location.href="/user";
                            } else {
                            	vm.$comfirmbox({content:response.message});
                            	vm.loaded = true;
                            }
                        }).catch(error => {
                        	vm.loaded = true;
                            if(error.code === 421) {
                                vm.$comfirmbox({content:response.message});
                            }
                        })
                    }
                    //
                })
            },
            getSafeOption(){
                var vm = this;
                vm.loaded = false;
                axios.post(vm.commonApi.api.loginSafeOption, {
                    account: this.account,
                    password: this.password
                }).then(function(response){
                     var _response = response.data;
                     vm.loaded = true;
                     if(_response.code == 200) {
                        if (_response.data.loginOption == 0) {
                            vm.login();
                        }else{
                            vm.isVerify = 1;
                            vm.safeLevel = _response.data.loginOption;
                            vm.countdown = _response.data.time;
                            vm.isMobile = _response.data.isMobile;
                        }
                     }else{
                        vm.$comfirmbox({content:_response.message});
                     }
                }).catch(error => {
                	vm.loaded = true;
                    vm.$comfirmbox({content:error});
                });
            },
            sendEmailCode:function(){
                var vm = this;
                vm.countdown = -1;
                axios.post(this.commonApi.api.sendVerifyCodeForLogin,{
                    account: this.account,
                    password: this.password
                }).then(function(response){
                    var res = new Object(response.data);
                    if (res.code == 200) {
                        vm.countdown = 60;
                        vm.doneInterval()
                    }
                });
            },
            doneInterval:function(){
                var vm = this;
                this.interval = setInterval(function(){
                    vm.countdown -- ;

                    if (vm.countdown == 0) {
                        clearInterval(vm.interval);
                    }
                },1000);
            },
        }
    }
</script>

<!--<style lang="less">
 	@import url('../../../../../public/css/less/hac/hac-content.less');
</style>-->