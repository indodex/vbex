import * as types from './../mutation-type'

export default {
    state: {
        authenticated: false,
        name: null,
        email: null,
        inviteUrl: null,
        avatar: null,
    },
    mutations: {
        [types.SET_AUTH_USER](state, payload) {
            state.authenticated = true
            state.uid = payload.user.id
            state.name = payload.user.name
            state.email = payload.user.email
            state.avatar = payload.user.avatar
            state.inviteUrl = payload.user.inviteUrl
            state.tradeOption = payload.user.tradeOption
        },
        [types.UNSET_AUTH_USER](state) {
            state.authenticated = false
            state.uid = null
            state.name = null
            state.email = null
            state.avatar = null
            state.inviteUrl = null
            state.tradeOption = null
        }
    },
    getters:{
    	user_state : (state, getters) => {
    		return state
    	}
    },
    actions: {
        setAuthUser({commit, dispatch}) {
           return axios.get('/api/v1/user/info').then(response => {
                commit({
                    type: types.SET_AUTH_USER,
                    user: response.data.data
                })
            }).catch(error => {
                dispatch('refreshToken')
           })
        },
        unsetAuthUser({commit}) {
            commit({
                type: types.UNSET_AUTH_USER
            })
        },
        refreshToken({commit, dispatch}) {
            return axios.post('/api/token/refresh').then(response => {
                dispatch('loginSuccess',response.data)
            }).catch(error => {
                dispatch('logoutRequest')
            })
        },
    }
}