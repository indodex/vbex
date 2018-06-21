import * as types from './../mutation-type'

export default {
    state: {
        hashSwitch:false,
        lang:null
    },
    mutations: {
        [types.BASE_CONFIG](state, payload) {
            state.hashSwitch = payload.hashSwitch
            state.lang = payload.lang
        },
    },
    getters:{
    	common_cnf : (state, getters) => {
    		return state
    	}
    },
    actions: {
        setBaseConfig({commit, dispatch}) {
            axios.get('/api/v1/config/initialize').then(response => {
            	var res = response.data;
            	if(res.code == 200){
	                commit({
	                    type: types.BASE_CONFIG,
						hashSwitch: res.data.rechargeCodeSwitch,
						lang: res.data.land
	                })
               }
            }).catch(error => {
                console.log(error)
           })
        }
    }
}