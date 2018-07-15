
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import VueRouter from 'vue-router'
import router from './routes'
import jwtToken from './helpers/jwt'
import App from './components/App'
//import Auth from './components/Auth'
import store from './store/index'
import utils from './helpers/utils'


import VueI18n from 'vue-i18n'
import zh_CN from './locale/zh_CN';
import VeeValidate, {Validator} from 'vee-validate';

//import ElementUI from 'element-ui'

//host api
import commonApi from './api.js'
import merge from 'lodash/merge'
//import countdown from './components/common/countdown/countdown'
//custom plugin
import confirmbox from './components/common/comfirm'
import dragbar from './components/common/dragbar/dragbar'
import pagers from './components/common/pager'
import loading from './components/common/loading'

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */



axios.interceptors.request.use(function (config) {
    if (jwtToken.getToken()) {
        config.headers['Authorization'] = 'Bearer ' + jwtToken.getToken();
    }
   	return config;
}, function (error) {
    // Do something with request error
    return Promise.reject(error);
});


Vue.use(VueI18n)
Vue.use(VueRouter)

Validator.addLocale(zh_CN);
Vue.use(VeeValidate, {
 locale: 'zh_CN'
});


Vue.prototype.commonApi = commonApi;
Vue.prototype.$merge = merge;	//json merge
//Vue.prototype.$countdown = countdown;
//组件调用
Vue.use(confirmbox)					//确认框
Vue.use(loading)					//加载层
Vue.component('dragbar',dragbar)	//拖动组件
Vue.component('pager',pagers)		//分页插件




var i18n = new VueI18n({
    locale: 'zh-cn', // 语言标识
    messages:{
    	"zh-cn": require('./lang/zh-cn.json'),
    	"zh-tw": require('./lang/zh-tw.json'),
    	"en":require('./lang/en.json'),
    }
})

Vue.component('app', App)
new Vue({
    el: '#app',
    router,
    store,
    i18n
});
