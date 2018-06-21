import VueRouter from 'vue-router'
import Store from './store/index'
import jwtToken from './helpers/jwt'
let routes = [
    {
        path:'/',
        name:'index',
        component:r => require.ensure([], () => r(require('./components/web/Index.vue')), 'webPage'),
    },
    {
        path: '/dailog/tip',
        name: 'tip',
		component:r => require.ensure([], () => r(require('./components/web/Tip.vue')), 'webPage'),
    },
    {
        path:'/apply/list',
        name:'apply',
		component:r => require.ensure([], () => r(require('./components/web/ApplyList.vue')), 'webPage')
    },
    {
		path:'/apply/recode',
		name:'applyrecode',
		component:r => require.ensure([], () => r(require('./components/web/ApplyRecode.vue')), 'webPage')
    },
    {
        path:'/apply/detail',
        name:'apply',
		component:r => require.ensure([], () => r(require('./components/web/ApplyDetails.vue')), 'webPage')
    },
	{
        path: '/user',
       	redirect:'/user/info',
       	name:'user',
        component:r => require.ensure([], () => r(require('./components/user/Index.vue')), 'user'),
        children:[
        	{ 
        		path:'/user/info',
        		name:'account',
        		component: r => require.ensure([], () => r(require('./components/user/Info.vue')), 'user'),  
//              meta: {requiresAuth: true}
        	},
        	{
        		path:'/user/invite',
        		name:'invitor',
        		component: r => require.ensure([], () => r(require('./components/user/Invite.vue')), 'user'),
                meta: {requiresAuth: true}
        	},
        	{
        		path:'/user/hash',
        		name:'hash',
        		component: r => require.ensure([], () => r(require('./components/user/Hash.vue')), 'user'),
        	}
        ]
    },
    {
    	path:'/financial',
    	redirect:'/financial/center',
    	name:'financial',
    	component:r => require.ensure([], () => r(require('./components/financial/Index.vue')), 'financial'),
    	children:[
    		{
    			path:'/financial/center',
    			name:'assets',
    			component: r => require.ensure([], () => r(require('./components/financial/Center.vue')), 'financial'),
    		},
    		{
    			path:'/financial/recharge',
    			name:'recharge',
    			component: r => require.ensure([], () => r(require('./components/financial/Recharge.vue')), 'financial'),
    		},
    		{
    			path:'/financial/withdraw',
    			name:'withdraw',
    			component: r => require.ensure([], () => r(require('./components/financial/Withdraw.vue')), 'financial'),
    		},
    		{
    			path:'/financial/bill',
    			name:'bill',
    			component: r => require.ensure([], () => r(require('./components/financial/Bill.vue')), 'financial'),
    		},
    		{
    			path:'/financial/entrust',
    			name:'entrust',
    			component: r => require.ensure([], () => r(require('./components/financial/Entrust.vue')), 'financial'),
                meta: {requiresAuth: true}
    		}
    	]
    },
    {
    	path:'/news',
    	redirect:'/news/news-list',
    	name:'news',
    	component:r => require.ensure([], () => r(require('./components/news/Index.vue')), 'news'),
    	children:[
    		{
    			path:'/news/news-list',
    			name:'newslist',
    			component: r => require.ensure([], () => r(require('./components/news/List.vue')), 'news'),
    		},
    		{
    			path:'/news/content',
    			name:'content',
    			component: r => require.ensure([], () => r(require('./components/news/Content.vue')), 'news'),
    		}
    	]
    },
    {
    	path:'/market',
    	redirect:'/market/trad',
    	name:'market',
    	component:r => require.ensure([], () => r(require('./components/trad/Index.vue')), 'trad'),
    	children:[
    		{
    			path:'/market/trad',
    			name:'trad',
    			component: r => require.ensure([], () => r(require('./components/trad/Kline.vue')), 'trad'),
    		},
    		{
    			path:'/market/trading',
    			name:'trading',
    			component: r => require.ensure([], () => r(require('./components/trad/Trading.vue')), 'trad'),
    		},
    	]
    },
    {
        path: '/login',
        name: 'logins',
        component:r => require.ensure([], () => r(require('./components/auth/Login.vue')), 'webPage'),
        meta: {requiresAuth: false}
    },
    {
        path: '/regist',
        name: 'regists',
        component:r => require.ensure([], () => r(require('./components/auth/Regist.vue')), 'webPage'),
        meta: {requiresAuth: false}
    },
    {
        path: '/reset',
        name: 'reset',
        component:r => require.ensure([], () => r(require('./components/auth/Reset.vue')), 'webPage'),
        meta: {requiresAuth: false}
    }

]

const router = new VueRouter({
    mode: 'history',
    routes
})

router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth) {
        if (Store.state.AuthUser.authenticated || jwtToken.getToken()) {
            return next()
        } else {
            return next({'name': 'logins'})
        }
    }
    if (to.meta.requiresGuest) {
        if (Store.state.AuthUser.authenticated || jwtToken.getToken()) {
            return next({'name': 'logins'})
        } else {
            return next()
        }
    }
    next()
})

export default router
