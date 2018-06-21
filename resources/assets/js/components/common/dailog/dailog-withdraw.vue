<template>
  	<div class="modal-body">
        <form action="javascript:;" :model="walletForm" ref="walletForm" v-if="params.tradeCode == 1 && params.googleSecret == 1">
            <div class="form-group">
                <input class="form-control" v-model="walletForm.address" name="address" :placeholder="$t('cmn.please')+$t('cmn.input')+$t('financial.extractCoin')+$t('cmn.address')"/>
            </div>
            <div class="form-group">
                <input class="form-control" v-model="walletForm.name" name="name" :placeholder="$t('cmn.please')+$t('cmn.input')+$t('cmn.remark')" />
            </div>
            <div class="form-group">
                <input class="form-control" type="password" v-model="walletForm.tradeCode" name="tradeCode" :placeholder="$t('cmn.please')+$t('cmn.input')+$t('cmn.trad')+$t('cmn.password')" />
            </div>
            <div class="form-group" v-if="params.withdrawalOption == 2 || params.withdrawalOption == 3">
                <div class="group-col">
                    <input class="form-control" v-model="walletForm.code" name="google_code" :placeholder="$t('member.verificationCode')" />
                    <a class="col-btn" @click="sendVerifyCode" v-if="!vcount">{{$tc('member.getVerCode',1)}}</a>
                    <span class="col-btn text-gray-light"  v-if="vcount">{{vcount}}{{$tc('member.reGet',1)}}</span>
                </div>
            </div>
            <div class="form-group" v-if="params.withdrawalOption == 1 || params.withdrawalOption == 3">
                <input class="form-control" v-model="walletForm.googleCode" name="google_code" :placeholder="$t('cmn.please')+$t('cmn.input')+$t('cmn.google')+$t('cmn.code')" />
            </div>
            <div class="form-group">
                <button class="btn btn-primary form-sub-btn" @click="addWallet">{{$t('cmn.confirm')}}</button>
                <input type="hidden" v-model="walletForm.coinType" name="coinType" />
            </div>
        </form>
        <div class="modal-body" v-else>
            <div class="row tb-hd text-center" v-if="params.tradeCode != 1">{{$t('cmn.please')}}{{$t('cmn.set')}}{{$t('cmn.trad')}}{{$t('cmn.password')}}</div>
            <div class="row tb-hd text-center" v-if="params.googleSecret != 1">{{$t('cmn.please')}}{{$t('cmn.set')}}{{$t('cmn.google')}}{{$t('cmn.code')}}</div>
        </div>
  	</div>
</template>

<script>
export default{
    props:['params'],
    data(){
        return {
            walletForm:{
                address:'',
                name:'',
                tradeCode:'',
                googleCode:'',
                code:'',
                coinType:this.params.curCoin,
            },
            addWalletSwitch:false,
            vcount:0
        };
    },
	mounted(){
		var vm = this;
	},
	methods:{
		close(){
			this.$emit('cm');
		},
        addWallet(){
            var vm = this;
            if(vm.addWalletSwitch == true) {
                return false;
            }
            vm.addWalletSwitch = true
            axios.post(this.commonApi.api.addWithdrawAddress, {
                address:this.walletForm.address,
                name:this.walletForm.name,
                tradeCode:this.walletForm.tradeCode,
                coinType:this.walletForm.coinType,
                googleCode:this.walletForm.googleCode,
                code:this.walletForm.code
            }).then(function(response){
                vm.addWalletSwitch = false
                var res = new Object(response.data);
                if(res.code == 200) {
                    vm.walletForm = {address:'',name:'',tradeCode:'',code:''};
                    vm.close();
                } else {
					vm.$comfirmbox({ content:res.message, status:res.code })
                    return false;
                }
            })
            return false;
        },
        sendVerifyCode:function(){
            var vm = this
            vm.vcount = 60;
            axios.get(this.commonApi.api.sendVerifyCode).then(function(response){
                var res = new Object(response.data);
                if(res.code != 200) {
                    vm.$comfirmbox({ content:res.message, status:res.code })
                    return ;
                }
                setInterval(() => {
                    if(vm.vcount - 1 > 0)
                        vm.vcount--;
                    else
                        vm.vcount = 0;
                },1000)
            })
            return false;
        }
	}
}
</script>