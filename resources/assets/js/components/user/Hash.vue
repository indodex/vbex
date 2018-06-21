<template>
	<div class="ucontainer">
		<bread-head></bread-head>
		<div class="panel">
			<div class="p-title">
				<span class="fs-16 n">{{$tc('hash.myHash',1)}}</span>
				<a class="btn btn-primary pull-right mt5" data-toggle="modal" data-target="#hashcheck-dailog" style="margin-left:10px;">{{$tc('hash.checkHash',1)}}</a>
				<a class="btn btn-primary pull-right mt5" data-toggle="modal" data-target="#hashuse-dailog">{{$tc('hash.useHash',1)}}</a>
			</div>
			<div class="row mb20">
				<div class="col-md-8 row fs-14 text-gray-light">
					<div class="col-xs-4 mb10">
						{{$tc('hash.usdAmount',1)}}<span>{{ hashDetail.usd || 0}}</span>
					</div>
					<div class="col-xs-4 mb10"><span>&nbsp</span></div>
					<div class="col-xs-4 mb10"><span>&nbsp</span></div>
					<div class="col-sm-4 mb10">
						{{$tc('hash.amountAll',1)}}<span>{{ hashDetail.amountAll || 0}}</span>
					</div>
					<div class="col-sm-4 mb10">
						{{$tc('hash.amountViable',1)}}<span>{{ hashDetail.amountViable || 0}}</span>
					</div>
					<div class="col-sm-4 mb10">
						{{$tc('hash.amountUnusable',1)}}<span>{{ hashDetail.amountUnusable || 0}}</span>
					</div>
					<div class="col-sm-4 mb10">
						{{$tc('hash.countAll',1)}}<span>{{ hashDetail.countAll || 0}}</span>
					</div>
					<div class="col-sm-4 mb10">
						{{$tc('hash.countViable',1)}}<span>{{ hashDetail.countViable || 0}}</span>
					</div>
					<div class="col-sm-4 mb10">
						{{$tc('hash.countUnusable',1)}}<span>{{ hashDetail.countUnusable || 0}}</span>
					</div>
				</div>
			</div>
			<div class="">
				<a class="btn btn-primary" @click="createHash('create')">{{$tc('hash.createHash',1)}}</a>
			</div>
		</div>
		
		<div class="panel">
			<div class="p-title">
				<span class="fs-16">{{$tc('hash.createDetail',1)}}</span>
			</div>
			<div class="u-tb"  v-loading="!records.loaded">
				<div class="row tb-hd text-center">
					<!-- <div class="col-xs-2">ID</div> -->
					<div class="col-xs-4">{{$t('hash.hashCode')}}</div>
					<div class="col-xs-1">{{$tc('hash.amount',1)}}</div>
					<div class="col-xs-2">{{$tc('hash.createTime',1)}}</div>
					<div class="col-xs-2">{{$tc('hash.doneTime',1)}}</div>
					<div class="col-xs-1">{{$tc('hash.rechargeUser',1)}}</div>
					<div class="col-xs-2">{{$tc('hash.status',1)}}</div>
				</div>
				
				<div class="tb-ctn row" >
					<ul class="tb-list text-center"  v-if="records.list.length">
						<li class="clearfix" v-for="record in records.list">
							<div class="col-xs-4 handler">{{record.code}}<a class="text-primary" v-if="!record.isshow" @click="detailHash(record.id)">查看</a></div>
							<div class="col-xs-1">{{record.amount}}</div>
                            <div class="col-xs-2">{{record.createdAt}}</div>
							<div class="col-xs-2">{{record.doneAt}}</div>
							<div class="col-xs-1">{{record.rechargeUser}}</div>
							<div class="col-xs-2">{{record.auditStr}}/{{record.status}}</div>
						</li>
					</ul>
					<div v-if="!records.list.length" class="no-data col-xs-12 text-center">{{$tc('hash.noRecord',1)}}</div>
				</div>
				<pager :curnum="records.paginate.currentPage" :lastPage="records.paginate.lastPage" @skip="getRecordData"></pager>
			</div>
		</div>
		
		<div class="panel">
			<div class="p-title">
				<span class="fs-16 n">{{CodeOrder.labeltxt}}{{$tc('hash.rechargeOrder',1)}}</span>
    			<div class="added">
    				<div class="pull-left">
    					<div class="dropdown">
    						<div data-toggle="dropdown" aria-haspopup="true">{{$tc('hash.type',1)}}{{CodeOrder.labeltxt}}<i class="fa fa-angle-down"></i></div>
    						<ul class="dropdown-menu">
                                <li><a @click="getCodeOrder('user')">{{$tc('hash.personal',1)}}</a></li>
                                <li><a @click="getCodeOrder('system')">{{$tc('hash.system',1)}}</a></li>
    						</ul>
    					</div>
    				</div>
    			</div>
			</div>
			<div class="u-tb"  v-loading="!CodeOrder.loaded">
				<div class="row tb-hd text-center">
					<!-- <div class="col-xs-2">ID</div> -->
					<div class="col-xs-4">{{$tc('hash.hashCode',1)}}</div>
					<div class="col-xs-2">{{$tc('hash.amount',1)}}</div>
					<div class="col-xs-2">{{$tc('hash.createTime',1)}}</div>
					<div class="col-xs-3" v-if=" type == 'user'">{{$tc('hash.source',1)}}</div>
					<div class="col-xs-1" v-if=" type != 'user'"></div>
					<div class="col-xs-1">{{$tc('hash.status',1)}}</div>
				</div>
				
				<div class="tb-ctn row" >
					<ul class="tb-list text-center"  v-if="CodeOrder.list.length">
						<li class="clearfix" v-for="item in CodeOrder.list">
							<div class="col-xs-4">{{item.code}}</div>
							<div class="col-xs-2">{{item.amount}}</div>
                            <div class="col-xs-2">{{item.createdAt}}</div>
							<div class="col-xs-3" v-if=" type == 'user'">{{item.user}}</div>
							<div class="col-xs-1" v-if=" type != 'user'"></div>
							<div class="col-xs-1">{{item.status}}</div>
						</li>
					</ul>
					<div v-if="!CodeOrder.list.length" class="no-data col-xs-12 text-center">{{$tc('hash.noRecord',1)}}</div>
				</div>
				<pager :curnum="CodeOrder.paginate.currentPage" :lastPage="CodeOrder.paginate.lastPage" @skip="'get'+ item.type + 'CodeOrder'"></pager>
			</div>
		</div>
		<dailog boxid="hash" :boxtitle="hashDailogInfo.title" :params="hashDailogInfo.params" @modalcallback="hashCallback" boxsize="sm"></dailog>
		<dailog boxid="hashcheck" :boxtitle="$tc('hash.checkHash',1)" boxsize="sm"></dailog>
		<dailog boxid="hashuse" :boxtitle="$tc('hash.useHash',1)" @modalcallback="reLoad" boxsize="sm"></dailog>
	</div>
</template>

<script>
import breadHead from '../common/breadHead'
import dailog from '../common/dailog/dailog'
export default{
    components: {
		breadHead,
		dailog
    },
    data() {
      return {
        // items:[],
        hashDailogInfo:{title:'', params:''},
        records:{list:'', loaded:false, paginate:{currentPage:''}},
        hashDetail:'',
        CodeOrder:{list:'', loaded:false, labeltxt:'', paginate:{currentPage:''}},
        userCodeOrder:{list:'', type:'User', loaded:false, paginate:{currentPage:''}},
        systemCodeOrder:{list:'', type:'System', loaded:false, paginate:{currentPage:''}},
      	type:'user'
      };
    },
    mounted(){
//		this.getHashDetail();
//		this.getRecordData();
//		this.getUserCodeOrder();
//		this.getSystemCodeOrder();
		this.getCodeOrder('user');
		this.reLoad();
    },
    methods:{
    	getHashDetail(){
    		var vm = this;
	        axios.get(this.commonApi.api.hashDetail).then(function(response){
	             vm.hashDetail = response.data.data;
	        });
    	},
    	getRecordData(num){
    		var vm = this,
    			page = num || 1
    		
    		vm.records.loaded = false;
    		axios.get(this.commonApi.api.record, {params:{page:page}}).then(function(response){
	            vm.records.loaded = true;
            	if(response.data.code == 200){
            		vm.$merge(vm.records, response.data.data) 
                    vm.records.list = response.data.data.list;
//                  console.log(vm.records.list)
                	vm.records.paginate.lastPage = vm.records.paginate.currentPage = 1;
    			}
	        })
    		
    	},
    	getCodeOrder(type){
    		var vm = this;
    		vm.type = type;
    		vm.CodeOrder = vm[type+'CodeOrder'];
    		switch(type){
    			case 'user':
    				vm.CodeOrder.labeltxt = vm.$tc('hash.personal');
    				break;
    			case 'system':
    				vm.CodeOrder.labeltxt = vm.$tc('hash.system');
    				break;
    		}
    	},
    	getUserCodeOrder(num){
    		var vm = this,
    			page = num || 1;
    		vm.userCodeOrder.loaded = false;
    		axios.get(this.commonApi.api.userRechargeOrder, {params:{page:page}}).then(function(response){
    			vm.userCodeOrder.loaded = true;
				var res = response.data;
				if(res.code == 200){
					vm.$merge(vm.userCodeOrder, res.data);
					vm.userCodeOrder.list = res.data.list;
					vm.userCodeOrder.paginate.lastPage = vm.records.paginate.currentPage = 1;
				}
	       	});
    	},
    	getSystemCodeOrder(num){
    		var vm = this,
    			page = num || 1;
    		vm.systemCodeOrder.loaded = false;
    		axios.get(this.commonApi.api.systemRechargeOrder, {params:{page:page}}).then(function(response){
				vm.systemCodeOrder.loaded = true;
				var res = response.data;
				if(res.code == 200){
					vm.$merge(vm.systemCodeOrder, res.data);
					vm.systemCodeOrder.list = res.data.list;
					vm.systemCodeOrder.paginate.lastPage = vm.records.paginate.currentPage = 1;
				}
	        })
    	},
    	hashCallback(obj){
    		if(obj.type == 'createhash'){
    			this.reLoad();
    		}else if(obj.type == 'detailhash'){
//  			$('#code-cell-'+obj.id).html(obj.code)
				for(var i = 0; i < this.records.list.length; i++){
//					console.log(this.records.list[i].id)
					if(this.records.list[i].id == obj.id){
//						vm.$set(vm.records.list[index], 'sended', 'loading...')
						this.records.list[i].code = obj.code;
						this.records.list[i].isshow = true;
						console.log(this.records.list[i].code)
					}
				}
    		}
    	},
    	reLoad(){
    		this.getHashDetail();
			this.getRecordData();
			this.getUserCodeOrder();
			this.getSystemCodeOrder();
    	},
    	createHash(){
    		this.hashDailogInfo.params = {type:'createhash'}
    		this.hashDailogInfo.title = this.$i18n.t('hash.createHash');
    		$('#hash-dailog').modal('show');
    	},
    	detailHash(id){
    		this.hashDailogInfo.params = {type:'detailhash',dataid:id}
    		this.hashDailogInfo.title = this.$i18n.t('cmn.lookOver') + this.$i18n.t('hash.hashCode');
    		$('#hash-dailog').modal('show');
    	}
    	
    }
}
</script>
