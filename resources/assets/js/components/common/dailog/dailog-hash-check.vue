<template>
  	<div class="modal-body">
        <form action="javascript:;" :model="codeForm" ref="codeForm">
        	<div class="form-group">
        		<input class="form-control" v-model="codeForm.code" :placeholder="$tc('hash.hashCode',1)"/>
        	</div>
        	<div class="form-group">
        		<button class="btn btn-primary form-sub-btn" @click="done">{{$tc('member.ok',1)}}</button>
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
            axios.post(vm.commonApi.api.checkHashCode, {
                code:vm.codeForm.code,
            }).then(function(response){
                var res = new Object(response.data);
                vm.$comfirmbox({ content:res.message, status:res.code })
                vm.close();
            })
            return false;
        }
	},
    data() {
        return {
            codeForm:{
                // num:'',
                code:'',
            },

        };
    },
}
</script>