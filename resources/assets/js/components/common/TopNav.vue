<template>
<div class="header">
        <nav class="navbar navbar-inverse navbar-fixed-top  navbar-affix affix-top hac-navbar" role="navigation" data-spy="affix" data-offset-top="60" >
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/"><div class="logo"><img src="/img/logo.png?v=2018052301" alt=""></div></a>
                </div>
                <div class="navbar-collapse collapse " aria-expanded="true">
                    <!--<ul class="nav navbar-nav fs-14">
                        <li>
                            <router-link to="/">{{$t('cmn.home')}}</router-link>
                        </li>
                        <li class="market-slidown">
                        	<div class="navbar-nav-slidown">
                        		<div class="inner" v-loading="!currencies.loaded">
	                        		<div class="l-block pull-left">
	                        			<ul>
	                        				<li @mouseenter="toShowCurrencies(i)" :class="(i == currencies.index)&&'active'" v-for="(m,i) in markets">{{ m.market }}{{$t('cmn.block')}}</li>
	                        			</ul>
	                        		</div>
	                        		<div class="r-block pull-left" >
	                        			<div class="lab-wrap clearfix">
	                        				<span  class="coin-lab" v-for="c in currencies.list">
		                        				<router-link :to="{path:'/market/trad', query:{market:c.market}}">
		                        					<img class="logo" v-if="c.logo" :src="c.logo" alt=""/>{{ c.symbol }}
		                        				</router-link>
	                        				</span>
	                        			</div>
	                        		</div>
                        		</div>
                        	</div>
                            <router-link :to="{path:'/market', query:{market:'ETH_BTC'}}">{{$t('cmn.trad')}}{{$t('cmn.center')}}<i class="fa fa-angle-down"></i></router-link>
                        </li>
                        <li>
                            <router-link to="/news">{{$t('cmn.news')}}</router-link>
                        </li>
                    </ul>-->
                    <ul class="nav navbar-nav navbar-right fs-14">
                    	<li>
                            <router-link to="/">{{$t('cmn.home')}}</router-link>
                        </li>
                        <li>
                            <router-link :to="{path:'/market', query:{market:'ETH_BTC'}}">{{$t('cmn.trad')}}{{$t('cmn.center')}}</router-link>
                        </li>
                        <li>
                           	<a>VBC</a>
                        </li>
                        <li>
                           	<a>白皮书</a>
                        </li>
                        <!--财务中心-->
                    	<!--<li class="blance-slidown" v-if="user.authenticated">
                    		<div class="navbar-nav-slidown">
                        		<div class="blance-slidown-hd">
                        			<div class="num-info clearfix">
                        				<div class="pull-left text-gray-light">{{$t('arts.acntVal')}} </div>
                        				<div class="pull-left codetype-list">
                        					<span class="text-primary">{{balances.viewCode}} <i class="fa fa-angle-down"></i></span>
                        					<ul class="text-gray-light" v-if="balances.proportion">
                        						<li v-for="(act, index) in balances.proportion" @click="setCoin(act,index)">{{act.code}}</li>
                        					</ul>
                        				</div>
                        			</div>
                        			<div class="num text-gray fs-20">{{balances.viewPrice}}</div>
                        			<div class="handler text-gray-light">
                        				<span>{{$t('arts.hideZero')}}</span>
	                        			<a class="checkswitch-box">
					    					<label class="checkswitch" >
					    						<input id="blanceSlidownSwitch" type="checkbox" v-model="toggleCode"/>
					    						<i></i>
					    					</label>
				    					</a>
                        			</div>
                        		</div>
                        		<div class="u-tb" v-loading="!balances.loaded">
									<div class="tb-hd clearfix text-gray">
					                    <div class="col-xs-6">{{$t('cmn.codeType')}}</div>
										<div class="col-xs-6 text-right">{{$t('cmn.total')}}</div>
									</div>
									<div class="tb-ctn">
										<ul class="tb-list" v-if="balances.list" >
											<li v-for="item in balances.list" class="clearfix text-gray-light" v-show="!(item.totalStr == 0&&toggleCode)">
												<div class="col-xs-6">
													<span class="coin-lab"><img alt="" /> {{item.coin}}</span>
												</div>
												<div class="col-xs-6 text-right">{{item.totalStr}}</div>
											</li>
										</ul>
									</div>
								</div>
                        	</div>
                    		<router-link to="/financial">{{$t('cmn.financial')}}{{$t('cmn.center')}}</router-link>
                    	</li>-->
                        <li v-if="!user.authenticated"><router-link to="/login">{{$t('cmn.login')}}</router-link></li>
                        <li v-if="!user.authenticated"><a href="/register">{{$t('cmn.regist')}}</a></li>
                        <li v-if="user.authenticated"><router-link to="/user">{{$t('cmn.user')}}{{$t('cmn.center')}}</router-link></li>
                        <li v-if="user.authenticated"><a @click.prevent="logout">{{$t('cmn.exit')}}</a></li>
                        
                        <li >
                    		<div class="navbar-nav-slidown">
                                <a v-for="(item, index) in langList" @click="selectLang(index)">{{item}}</a>
                            </div>
                            <a>
                            	<span class="lang-btn">
                            		<i class="fa fa-globe"></i>
                            		{{curlang}}
                            	</span>
                            </a>
                    	</li>
                    </ul>
                </div>
        	</div>
        </nav>
    </div>
</template>


<script>
    import {mapState} from 'vuex'
    import floatMath from '../common/floatMath.js'
    import Cookie from 'js-cookie'
    export default {
        name:'top-menu',
        computed:{
            ...mapState({
                user: state => state.AuthUser,
                baseConfig: state => state.baseConfig
            })
        },
        data() {
            return {
                markets:'',
//              currencies:{list:'', index:0, loaded:true},
//              balances:{viewPrice:'--', viewCode:'--', price:'', list:'', loaded:true, proportion:''},
                toggleCode:true,
                langList:new Object(),
                curlang:''
            };
        },
        mounted(){
        	var vm = this,
        		browserLang = (navigator.language || navigator.browserLanguage).toLowerCase();
        		
//          vm.getMarkets();
//          vm.getBalanceData();
            browserLang = this.$i18n.messages[browserLang]?browserLang:'zh-cn';
            for(var i in this.$i18n.messages){
            	switch(i){
            		case 'zh-cn':
            			vm.langList[i] = '简体中文';
            			break;
            		case 'zh-tw':
            			vm.langList[i] = '繁体中文';
            			break;
            		case 'en':
            			vm.langList[i] = 'English';
            			break;
            	}
            }
            
            this.curlang = vm.langList[Cookie.get('lang')] || vm.langList[browserLang];
        },
        methods:{
            logout() {
                this.$store.dispatch('logoutRequest').then(response => {
                    window.location.href="/";
                })
            },
//          getMarkets(){
//              var vm = this;
//              vm.currencies.loaded = false;
//              axios.get(this.commonApi.api.getMarkets, {params:{'isChildren':1}}).then(function(response){
//                  vm.currencies.loaded = true;
//                  if(response.data.code == 200){
//                      vm.markets = response.data.data.data;
//                      vm.currencies.list = vm.markets[0].currencies;
//                  }else{
//                      vm.markets = '';
//                  }
//              })
//          },
            toShowCurrencies(i){
                var vm = this;
                vm.currencies.index = i;
                vm.currencies.list = vm.markets[i].currencies;
            },
//          getBalanceData(){
//          	var vm = this;
//          	vm.balances.loaded = false;
//      		axios.get(vm.commonApi.api.balanceUrl).then(function(response){
//  				vm.balances.loaded = true;
//					if(response.data.code == 200){
//              		vm.$merge(vm.balances, response.data.data)
//                      vm.balances.list = response.data.data.list;
//                      vm.balances.price = response.data.data.price;
//						vm.balances.proportion = response.data.data.proportion;
//						vm.balances.viewCode = vm.balances.proportion[0].code;
//						vm.balances.viewPrice = vm.balanceCompute(vm.balances.price,vm.balances.proportion[0].proportion)
//					}else{
//	                	vm.balances.list = '';
//      			}
//          	})
//         },
           setCoin(obj){
           		var res_ = '';
       			switch(obj.code.toLowerCase()){
       				case 'cny':
       					res_ = 2;
       					break;
       				case 'usd':
       					res_ = 4;
       					break;
       				default:
       					break;
       			}
       			this.balances.viewCode = obj.code;
				this.balances.viewPrice = this.balanceCompute(this.balances.price, obj.proportion, res_);
           },
           balanceCompute(num, pro, res){
           		var res_ = res?res:2
           		return floatMath.res(floatMath.mul(parseFloat(num), parseFloat(pro)), res_);
           },
           selectLang(lang){
//				this.$i18n.locale = lang;
//				this.curlang = this.langList[lang];
				Cookie.set('lang',lang);
				window.location.reload();	//刷新重新加载语言包,晚点再弄
            	
            }
        }
    }
</script>