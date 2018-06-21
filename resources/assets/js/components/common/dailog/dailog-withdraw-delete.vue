<template>
  	<div class="modal-body">
        <form action="javascript:;" :model="deleteWalletForm" ref="deleteWalletForm">
            <div class="form-group">
                <input class="form-control" v-model="params.address" name="address" disabled="disabled" :placeholder="$t('financial.extractCoin')+$t('cmn.address')" />
            </div>
            <div class="form-group">
                <input class="form-control" v-model="params.name" name="name" disabled="disabled" :placeholder="$t('cmn.address')+$t('cmn.remark')" />
            </div>
            <div class="form-group">
                <input class="form-control" type="password" v-model="deleteWalletForm.tradeCode" :placeholder="$t('cmn.trad')+$t('cmn.password')" />
            </div>
            <div class="form-group">
                <input class="form-control" v-model="deleteWalletForm.googleCode" :placeholder="$t('cmn.please')+$t('cmn.input')+$t('cmn.google')+$t('cmn.code')" />
            </div>
            <div class="form-group">
                <button class="btn btn-primary form-sub-btn" @click="delAddress(params.id)">{{$t('cmn.confirm')}}</button>
            </div>
        </form>
  	</div>
</template>

<script>
export default{
	props:['params'],
    data(){
        return {
            deleteWalletForm:{
                tradeCode:'',
                googleCode:''
            },
        };
    },
	mounted(){
		var vm = this;
	},
	methods:{
		close(){
			this.$emit('cm');
		},
        delAddress(addressId){
            var vm = this;
            axios.post(this.commonApi.api.delWithdrawAddress, {
                id:addressId,
                tradeCode:this.deleteWalletForm.tradeCode,
                googleCode:this.deleteWalletForm.googleCode
            }).then(function(response){
                var res = new Object(response.data);
                if(res.code == 200) {
                    vm.close();
                } else {
                    alert(res.message);
                    return false;
                }
            })
            return false;
        }
	}
}
</script>