<template>
	<div class="ucontainer nomenu">
		<bread-head></bread-head>
		<div class="panel" v-if="news.list.length">
			<div  class="news-wrap">
				<div v-for="row in news.list" class="news-title-block">
					<router-link :to="{path:'/news/content', query:{id:row.id}}" class="news-title-block clearfix">
						<div class="date-box">
							<div class="ym">
								<span>2017-12</span>
							</div>
							<div class="dt">
								<div class="d" >03</div>
								<div class="t">14:20</div>
							</div>
						</div>
						<div class="news-title-box">
							<div class="title">{{ row.title }}</div>
							<i class="fa fa-angle-right"></i>
						</div>
					</router-link>
				</div>
			</div>
			<!--<pager :curnum="news.paginate.currentPage" :lastPage="news.paginate.lastPage" @skip="getList"></pager>-->
		</div>
		<div v-if="!news.list.length" class="no-data col-xs-12 text-center">{{$t('cmn.noRecords')}}</div>
	</div>
</template>

<script>
import breadHead from '../common/breadHead'
export default{
	components:{
		breadHead
	},
	data(){
		return{
//			testn:3
			news:{list:'', loaded:false, paginate:{currentPage:''}},
		}
	},
	mounted(){
		this.getList();
    },
	methods:{
		getList(num){
			var vm = this,
    			page = num || 1

    		axios.get(this.commonApi.api.newsList, {params:{page:page}}).then(function(response){
            	if(response.data.code == 200){
            		vm.$merge(vm.news, response.data.data)
                    vm.news.list = response.data.data.list;
                    // console.log(vm.records.list)
    			}else{
                	vm.records.list = '';
                	vm.records.paginate.lastPage = vm.records.paginate.currentPage = 1;
    			}
	        })
		}
	}
}
</script>

<style>
</style>