import Vue from 'vue'
import Vuex from 'vuex'
import auth from './modules/auth'
import snackbar from './modules/snackbar'
import users from './modules/users'
import googleUsers from './modules/google_users'
import staff from './modules/jobs'
import teachers from './modules/teachers'

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'

export default new Vuex.Store({
  modules: {
    auth,
    snackbar,
    users,
    staff,
    teachers,
    googleUsers
  },
  strict: debug
})