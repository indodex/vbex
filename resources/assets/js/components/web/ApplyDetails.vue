<template>
	<div class="ucontainer nomenu">
		<div class="panel clearfix" v-loading="isloaded">
			<div class="col-lg-12">
				<div class="applyTitle text-center" v-if="!isloaded">
					<h3>{{detailList.code}}</h3>
					<p>
						<router-link to="/apply/list" style="margin:0 10px;"><button class="btn btn-primary">申请列表</button></router-link>
						<router-link to="/apply/recode" style="margin:0 10px;"><button class="btn btn-primary">申请记录</button></router-link>
					</p>
				</div>
				<div class="u-tb" >
					<div class="tb-ctn row" >
						<ul class="tb-list text-center" v-if="!isloaded" >
							<li class="clearfix" v-for="(item, key) in detailList">
								<div class="col-xs-4 text-right"></div>
								<div class="col-xs-8 text-left">{{item}}</div>
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
			isloaded:false,
			detailList:''
		}
	},
	mounted(){
		let vm = this,
			cid = vm.$route.query ? vm.$route.query.id : '';
		vm.isloaded = true;
		axios.get(this.commonApi.api.applyDetail, {params:{id:cid}}).then((response) => {
			var res = new Object(response.data.data);
			vm.isloaded = false;
			vm.detailList = res;
			
		})
	}
}
</script>
