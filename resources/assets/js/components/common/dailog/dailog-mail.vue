<template>
  	<div class="modal-body">
    	<!--<form action="javascript:;" :model="verifyForm" ref="verifyForm" v-if="verifyShow">
    		<div class="single-item">
	        	<div class="form-group">
	        		<button class="btn btn-primary form-sub-btn" @click="send">获取验证码</button>
	        	</div>
        	</div>
        </form>-->
        <form action="javascript:;" :model="mailForm" ref="mailForm">
    		<div class="form-group">
    			<div class="group-col">
    				<a class="col-btn" @click="send" v-if="verifyShow == 'clear'">{{$tc('member.getVerCode')}}</a>
    				<span class="col-btn" v-if="verifyShow == 'sended'">{{$tc('member.sendingVerCode')}}</span>
    				<span class="col-btn" v-if="verifyShow == 'done'">{{$tc('member.sendToEmail')}}</span>
        			<input class="form-control" type="text" v-model="mailForm.emailCode" :placeholder="$tc('member.verificationCode')"/>
        		</div>
        	</div>
        	<div class="form-group">
        		<button class="btn btn-primary form-sub-btn" @click="ok">{{$tc('cmn.confirm')}}</button>
        	</div>
        </form>
  	</div>
</template>

<script>
    export default{
    	mounted(){
    		var vm = this;
    	},
        methods:{
            close(){
                this.mailForm.emailCode = '';
                this.$emit('cm');
            },
            ok:function(){
                var vm = this
                axios.post(this.commonApi.api.emailVerify, {
                    emailCode:vm.mailForm.emailCode,
                }).then(function(response){
                    var res = new Object(response.data);
                    if(res.code == 200) {
                    	vm.verifyShow = 'clear';
                    	vm.close();
                    } else {
                    	vm.$comfirmbox({ content:res.message, status:res.code })
                    }
                })
                return false;
            },
            send:function(){
            	var vm = this;
            	if(vm.verifyShow == 'clear'){
            		vm.verifyShow = 'sended'
	                axios.post(this.commonApi.api.emailSend).then(function(response){
	                    var res = new Object(response.data);
	                    if(res.code == 200) {
	                    	vm.verifyShow= 'done'
	                    } else {
	                    	vm.$comfirmbox({ content:res.message, status:res.code })
	                    }
	                })
                }
            }
        },
        data(){
            return {
                mailForm:{
                    email:'',
                },
                verifyForm:'',
                verifyShow:'clear'
            }
        }
    }
</script>