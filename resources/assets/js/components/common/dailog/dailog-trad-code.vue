<template>
	<div class="modal-body">
    	<form action="javascript:;" :model="tradeForm" ref="tradeForm">
    		<input type="hidden" value="1" ref='is_tradeCode' v-if="params.tradeCode == 1">
            <input type="hidden" value="0" ref='is_tradeCode' v-else>
    		<div class="form-group" v-if="params.tradeCode == 1">
        		<input class="form-control" type="password" v-model="tradeForm.oldTradeCode" :placeholder="$tc('member.tradCode',0)"/>
        	</div>
        	<div class="form-group">
        		<input class="form-control" type="password" v-model="tradeForm.tradeCode" :placeholder="$tc('member.tradCode',1)"/>
        	</div>
        	<div class="form-group">
        		<input class="form-control" type="password" v-model="tradeForm.confirmTradeCode" :placeholder="$tc('member.tradCode',2)" />
        	</div>
            <div class="form-group">
                <div class="group-col">
                    <a class="col-btn" @click="sendVerifyCode" v-if="countdown == 0">{{$tc('member.getVerCode',1)}}</a>
                    <span class="col-btn" v-if="countdown > 0">{{ countdown }}{{$tc('member.reGet',1)}}</span>
                    <span class="col-btn" v-if="countdown < 0">{{$tc('member.sendingVerCode',1)}}</span>
                    <!-- <input class="form-control" type="text" v-model="verifyCode" :placeholder="$t('cmn.code')+'('+$t('cmn.mobile')+'/'+$t('cmn.email')+')'"/> -->
                    <input class="form-control" type="text" v-if="params.mobile" v-model="verifyCode" :placeholder=" $t('cmn.mobile') + $t('cmn.code')"/>
                    <input class="form-control" type="text" v-else v-model="verifyCode" :placeholder=" $t('cmn.email') + $t('cmn.code')"/>
                </div>
            </div>
        	<div class="form-group">
        		<input class="form-control" v-if="params.googleSecret == 1" v-model="tradeForm.oneCode" :placeholder="$tc('member.googleCode',1)" />
        		<div v-else-if="params.tradeCode == 1" ></div>
        		<input class="form-control" v-else type="password" v-model="tradeForm.password" :placeholder="$tc('member.loginPassword',1)"/>
        	</div>
        	<div class="form-group" v-loading="!okloaded">
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
	methods:{
		close(){
			this.tradeForm.oldTradeCode = '';
			this.tradeForm.tradeCode = '';
			this.tradeForm.confirmTradeCode = '';
			this.tradeForm.oneCode = '';
			this.$emit('cm');
		},
        ok:function(){
            var vm = this
            var url = vm.commonApi.api.resetTradeCode;
            vm.okloaded = false;
            if(this.$refs.is_tradeCode.value == 0){
            	url = vm.commonApi.api.setTradeCode;
            }
            axios.post(url, {
            	oldTradeCode:vm.tradeForm.oldTradeCode,
                tradeCode:vm.tradeForm.tradeCode,
                confirmTradeCode:vm.tradeForm.confirmTradeCode,
                oneCode:vm.tradeForm.oneCode,
                password:vm.tradeForm.password,
                verifyCode:vm.verifyCode
            }).then(function(response){
                vm.okloaded = true;
            	var res = new Object(response.data);
                if (res.code == 200) {
                    vm.close();
                }
				vm.$comfirmbox({ content:res.message, status:res.code })
            })
            return false;
        },
        checkEmailLock:function(){
            var vm = this;
            axios.get(this.commonApi.api.checkEmailLock).then(function(response){
                var res = new Object(response.data);
                if (res.code == 200) {
                    vm.countdown = res.data;
                    vm.doneInterval();
                }
            });
        },
        sendVerifyCode:function(){
            var vm = this;
            vm.countdown = -1;
            axios.get(this.commonApi.api.sendVerifyCode).then(function(response){
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
        }
	},
    data(){
        return {
            tradeForm:{
            	oldTradeCode:'',
                tradeCode:'',
                confirmTradeCode:'',
                oneCode:'',
                password:'',
            },
            verifyCode:"",
            countdown:0,
            interval:'',
            okloaded:true,
        }
    }
}
</script>