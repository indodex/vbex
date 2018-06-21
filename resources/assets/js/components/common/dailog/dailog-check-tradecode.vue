<template>
  	<div class="modal-body">
        <form action="javascript:;" :model="tradeCodeFrom" ref="tradeCodeFrom">
        	<div class="form-group">
        		<input class="form-control" v-model="tradeCodeFrom.tradeCode" placeholder="请输入交易码"/>
        	</div>
        	<div class="form-group">
        		<button class="btn btn-primary form-sub-btn" @click="done">确认输入</button>
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
			this.$emit('cm');
		},
        done(){
            var vm = this
            axios.post(vm.commonApi.api.tradeCheckCode, {
                tradeCode:vm.tradeCodeFrom.tradeCode,
            }).then(function(response){
                var res = new Object(response.data);
                if(res.code != 200){
                	vm.$comfirmbox({ content:res.message, status:res.code })
                }
                vm.close();
            })
            return false;
        }
	},
    data() {
        return {
            tradeCodeFrom:{
                tradeCode:'',
            },
        };
    },
}
</script>