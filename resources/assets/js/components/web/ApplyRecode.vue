<template>
	<div class="ucontainer nomenu">
		<div class="panel clearfix">
			<div class="col-lg-12">
				<div class="applyTitle text-center">
					<h3>申请记录</h3>
					<p>
						<router-link to="/apply/list"><button class="btn btn-primary">申请列表</button></router-link>
					</p>
				</div>
				<div class="u-tb applyList" v-loading = "!applyList">
					<div class="row tb-hd text-center">
						<div class="col-xs-5">币种名称</div>
						<div class="col-xs-5">申请时间</div>
						<div class="col-xs-2">操作</div>
					</div>
					
					<div class="tb-ctn row" >
						<ul class="tb-list text-center" v-if="applyList">
							<li class="clearfix" v-for="item in applyList">
								<div class="col-xs-5"><div class="applylist-item">{{item.code}}</div></div>
								<div class="col-xs-5"><div class="applylist-item">{{item.createdAt}}</div></div>
	                            <div class="col-xs-2">
	                            	<router-link :to="{path:'/apply/detail', query:{id:item.id}}">
	                            		<button class="btn btn-primary">查看详情</button>
	                            	</router-link>
	                            </div>
							</li>
						</ul>
						<!--<div v-if="!CodeOrder.list.length" class="no-data col-xs-12 text-center">{{$tc('hash.noRecord',1)}}</div>-->
					</div>
					<!--<pager :curnum="CodeOrder.paginate.currentPage" :lastPage="CodeOrder.paginate.lastPage" @skip="'get'+ item.type + 'CodeOrder'"></pager>-->
				</div>
	        </div>
		</div>
	</div>
</template>

<script>
export default {
	data(){
		return {
			applyList:''
		}
	},
	mounted(){
		var vm = this;
		axios.get(this.commonApi.api.applyList).then((response) => {
			var res = new Object(response.data.data);
			vm.applyList = res;
		})
	}
}
</script>
