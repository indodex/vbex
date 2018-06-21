<template>
  	<div class="modal-body">
        <form action="javascript:;" :model="resetPwdForm" ref="resetPwdForm">
        	<div class="form-group">
        		<input class="form-control" type="password" v-model="resetPwdForm.oldPassword" :placeholder="$tc('member.pwdOld',1)" />
    			<!--<p class="help-block"></p>-->
        	</div>
        	<div class="form-group">
        		<input class="form-control" type="password" v-model="resetPwdForm.password" :placeholder="$tc('member.pwdNew',1)" />
        	</div>
        	<div class="form-group">
        		<input class="form-control" type="password" v-model="resetPwdForm.confirmPassword" :placeholder="$tc('member.pwdConfirm',1)" />
        	</div>
            <div class="form-group" v-if="params.mobile != ''">
                <div class="group-col">
	                <input class="form-control" type="text" v-model="resetPwdForm.code" :placeholder="$tc('member.smsCode',1)" />
	                <a class="col-btn" @click="send" v-if="!vcount">{{$t('member.getVerCode')}}</a>
	                <span class="col-btn text-gray-light"  v-if="vcount">{{vcount}}{{$tc('member.reGet',1)}}</span>
                </div>
            </div>
            <div class="form-group" v-if="params.mobile == '' && params.email != ''">
                <div class="group-col">
                    <input class="form-control" type="text" v-model="resetPwdForm.code" :placeholder="$tc('cmn.please')+$tc('cmn.input')+$tc('member.emailCode')"/>
                    <a class="col-btn" @click="sendEmail" v-if="!vcount">{{$t('member.getVerCode')}}</a>
                    <span class="col-btn text-gray-light"  v-if="vcount">{{vcount}}{{$tc('member.reGet',1)}}</span>
                    
                </div>
            </div>
            <div class="form-group" v-if="params.googleSecret == 1">
                <input class="form-control" v-model="resetPwdForm.oneCode" :placeholder="$tc('member.googleCode',1)"/>
            </div>
        	<div class="form-group" v-loading="!loaded">
        		<button class="btn btn-primary form-sub-btn" @click="ok">{{$tc('member.ok',1)}}</button>
        	</div>
        </form>
  	</div>
</template>

<script>
    export default{
        props:['params'],
    	mounted(){
    		var vm = this;
    	},
    	data(){
            return {
                resetPwdForm:{
                    oldPassword:'',
                    password:'',
                    confirmPassword:'',
                    oneCode:'',
                    code:'',
                },
                vcount:0,
                loaded:true,
            }
       },
        methods:{
            close(){
                this.resetPwdForm.oldPassword = '';
                this.resetPwdForm.password = '';
                this.resetPwdForm.confirmPassword = '';
                this.resetPwdForm.oneCode = '';
                this.resetPwdForm.code = '';
                this.$emit('cm');
            },
            ok:function(){
                var vm = this;
                vm.loaded = false;
                axios.post(this.commonApi.api.resetPassword, {
                    oldPassword:vm.resetPwdForm.oldPassword,
                    password:vm.resetPwdForm.password,
                    confirmPassword:vm.resetPwdForm.confirmPassword,
                    oneCode:vm.resetPwdForm.oneCode,
                    code:vm.resetPwdForm.code,
                }).then(function(response){
                	vm.loaded = true;
                    var res = new Object(response.data);
                    if(res.code == 200) {
                        vm.close();
                        vm.$comfirmbox({ content:res.message, status:res.code })
                    } else {
                        vm.$comfirmbox({ content:res.message, status:res.code })
                    }
                })
                return false;
            },
            send:function(){
                var vm = this;
                vm.vcount = 60;
                axios.post(this.commonApi.api.sendCode).then(function(response){
                    var res = new Object(response.data);
                    if(res.code == 200){
	                    setInterval(() => {
	                    	if(vm.vcount - 1 > 0)
	                    		vm.vcount--;
	                    	else
	                    		vm.vcount = 0
	                    },1000)
                    }else{
                    	vm.$comfirmbox({ content:res.message, status:res.code })
                    	vm.vcount = 0;
                    }
                }).catch(function(err){
                	vm.$comfirmbox({ content:err })
                })
                return false;
            },
            sendEmail:function(){
                var vm = this;
                vm.vcount = 60;
                axios.post(this.commonApi.api.emailSend).then(function(response){
                    var res = new Object(response.data);
                    if(res.code == 200){
	                    setInterval(() => {
	                    	if(vm.vcount - 1 > 0)
	                    		vm.vcount--;
	                    	else
	                    		vm.vcount = 0
	                    },1000)
                    }else{
                    	vm.$comfirmbox({ content:res.message, status:res.code })
                    	vm.vcount = 0;
                    }
                }).catch(function(err){
                	vm.$comfirmbox({ content:err })
                })
            }
        }
    }
</script>