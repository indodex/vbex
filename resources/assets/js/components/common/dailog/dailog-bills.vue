<template>
  	<div class="modal-body">
        <div class="trad-data-list">
            <div class="u-tb">
                <div class="row tb-hd text-center">
                    <div class="col-xs-3">{{$tc('cmn.bargain')}}{{$tc('cmn.time')}}</div>
                    <div class="col-xs-3">{{$tc('cmn.bargain')}}{{$tc('cmn.quantity')}}({{ currency.main }})</div>
                    <div class="col-xs-3">{{$tc('cmn.bargain')}}{{$tc('cmn.price')}}({{ currency.exchange }})</div>
                    <div class="col-xs-3">{{$tc('financial.transaction')}}({{ currency.exchange }})</div>
                </div>

                <div class="tb-ctn row">
                    <ul class="tb-list text-center" v-if="completed.length">
                        <li class="clearfix" v-for="c in completed">
                            <div class="col-xs-3">{{ c.createdAt }}</div>
                            <div class="col-xs-3">
                                {{ c.num }}
                            </div>
                            <div class="col-xs-3">{{ c.price }}</div>
                            <div class="col-xs-3">{{ c.count }}</div>
                        </li>
                    </ul>
                    <div v-if="!completed.length" class="no-data col-xs-12 text-center">{{$tc('cmn.noRecords')}}</div>
                </div>
                
            </div>
        </div>
  	</div>
</template>

<script>
export default{
    props:['params'],
    data(){
        return {
            currency:'',
            completed:''
        };
    },
	mounted(){
		var vm = this;
        vm.getOrderDetails();
	},
	methods:{
		close(){
			this.$emit('cm');
		},
        getOrderDetails: function(e){
            var vm = this;
            axios.get(this.commonApi.api.tradesDetails, {params:{'id':vm.params.id, 'market':vm.params.market}}).then(function(response){
                 var _response = response.data;
                 if(_response.code == 200) {
                    vm.currency = _response.data.currency;
                    vm.completed = _response.data.list;
                 }
            });
        }
	}
}
</script>