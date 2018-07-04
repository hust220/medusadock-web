// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import Vuex from 'vuex'
import App from './App'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import router from './router'
import vueConfig from 'vue-config'

const configs = {
  HOST: 'http://' + window.location.hostname
}

Vue.config.productionTip = false

Vue.use(ElementUI)
Vue.use(Vuex)
Vue.use(vueConfig, configs)

const store = new Vuex.Store({
  state: {
    user: '',
    view: 'fullscreen'
  },
  mutations: {
    login (state, user) {
      state.user = user
    },

    logout (state) {
      state.user = ''
    },

    viewFullscreen (state) {
      state.view = 'fullscreen'
    },

    viewWindow (state) {
      state.view = 'window'
    }
  }
})

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  template: '<App/>',
  components: { App },
  render: h => h(App)
})
