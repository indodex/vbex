import Vue from 'vue'
import Vuex from 'vuex'

import AuthUser from './modules/auth-user'
import Login from './modules/login'
import baseConfig from './modules/common-config'

Vue.use(Vuex)

export default new Vuex.Store({
    modules: {
        AuthUser,
        Login,
        baseConfig
    },
    strict: true
})