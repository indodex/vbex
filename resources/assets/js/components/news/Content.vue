<template>
	<div class="ucontainer nomenu">
		<bread-head></bread-head>
		<div class="panel" v-loading="!loaded">
			<div class="news-wrap">
				<div class="clearfix">
					
					<div class="date-box">
						<div class="ym">
							<span>{{ news.year }}</span>
						</div>
						<div class="dt">
							<div class="d">{{ news.day }}</div>
							<div class="t">{{ news.time }}</div>
						</div>
					</div>
					
					<div class="detail-block">
						<div class="title">
							<h3>{{ news.title }}</h3>
							<p>{{ news.description }}</p>
						</div>
						<div class="article" v-html="news.content"></div>
					</div>
					
				</div>
			</div>
		</div>
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
			news:{year:'',time:'',title:'',description:'',content:''},
			loaded:false,
		}
	},
	activated(){
		var vm = this;
		let path;
		let info = this.$route.query.info;
		for(let i in vm.news){
			vm.news[i] = '';
		}
		
		if(info){
			path = vm.commonApi.api['article'+info];
		}else{
			path = vm.commonApi.api.newsContent;
		}
		this.loaded = false;
		axios.get(path, {params:{id:vm.$route.query.id}}).then(function(response){
        	vm.loaded = true;
        	if(response.data.code == 200){
                vm.news = response.data.data;
			}
        }).catch(function(){
        	vm.loaded = true;
        })
	}
}
</script>
