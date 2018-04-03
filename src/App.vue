<template>
<div id="app">
  <!--<img src="./assets/logo.png">-->
  <login :visible.sync="loginVisible"></login>
  <sign-up :visible.sync="signupVisible"></sign-up>
  <user :visible.sync="userVisible"></user>

  <el-row>
    <el-col :span="24" :offset="0">
      <el-card class="box-card">
        <div slot="header" class="clearfix">
          <div style="float: left; margin: 13px 1px">MedusaDock</div>

          <div style="float: right" v-if="user">
            <el-button class="grey-button" type="text" @click.native="userVisible=true">{{user.username}}</el-button>
            <el-button class="grey-button" type="text" @click.native="$store.commit('logout')">Logout</el-button>
          </div>

          <div style="float: right" v-else="user">
            <el-button class="grey-button" type="text" @click.native="signupVisible=true">Sign Up</el-button>
            <el-button class="grey-button" type="text" @click.native="loginVisible=true">Login</el-button>
          </div>

          <div style="clear: both"></div>
        </div>
        <el-row>
          <el-col :span="6" class="left-nav">
            <ul class="menu">
              <li :class="['menu-item', active=='Home'?'is-active':'']" @click="active='Home';redirect('#/Home')">
                <i class="el-icon-menu"></i>
                <span>Introduction</span>
              </li>
              <li :class="['menu-item', active=='NewTask'?'is-active':'']" @click="active='NewTask';redirect('#/NewTask')">
                <i class="el-icon-tickets"></i>
                <span>New Task</span>
              </li>
              <li :class="['menu-item', active=='TaskManagement'?'is-active':'']" @click="active='TaskManagement';redirect('#/TaskManagement')">
                <i class="el-icon-setting"></i>
                <span>Task Management</span>
              </li>
              <li :class="['menu-item', active=='Task'?'is-active':'']" @click="active='Task';redirect('#/Task/'+task_id)" v-show="task_id">
                <i class="el-icon-star-off"></i>
                <span>Task {{task_id}}</span>
              </li>

              <div class="submenu">
                <li class="submenu-title menu-item" @click="show_submenu^=true">
                  <i class="el-icon-plus"></i>
                  <span>Other Services</span>
                  <i class="submenu-arrow el-icon-arrow-up" v-if="show_submenu"></i>
                  <i class="submenu-arrow el-icon-arrow-down" v-else></i>
                </li>
                <ul class="submenu-list" v-show="show_submenu">
                  <li class="menu-item" @click="redirect('http://redshift.med.unc.edu/eris')">
                    <i class="el-icon-arrow-right"></i>
                    <span>Eris</span>
                  </li>
                  <li class="menu-item" @click="redirect('http://redshift.med.unc.edu/chiron')">
                    <i class="el-icon-arrow-right"></i>
                    <span>Chiron</span>
                  </li>
                  <li class="menu-item" @click="redirect('http://redshift.med.unc.edu/ifold')">
                    <i class="el-icon-arrow-right"></i>
                    <span>iFold</span>
                  </li>
                  <li class="menu-item" @click="redirect('http://redshift.med.unc.edu/ifoldrna')">
                    <i class="el-icon-arrow-right"></i>
                    <span>iFoldRNA</span>
                  </li>
                </ul>
              </div>

              <li class="menu-item" @click="redirect('http://redshift.med.unc.edu')">
                <i class="el-icon-location"></i>
                <span>Dokholyan Lab</span>
              </li>
            </ul>
          </el-col>

          <el-col :span="18">
            <router-view/>
          </el-col>
        </el-row> 
      </el-card>
    </el-col>
  </el-row>
</div>
</template>

<script>
import { bus } from '@/bus.js'
import Login from '@/components/Login'
import SignUp from '@/components/SignUp'
import User from '@/components/User'

export default {
  name: 'app',
  data () {
    return {
      loginVisible: false,
      signupVisible: false,
      userVisible: false,
      show_submenu: false,
      active: '',
      task_id: ''
    }
  },
  computed: {
    user () {
      return this.$store.state.user
    }
  },
  components: {
    Login,
    SignUp,
    User
  },
  methods: {
    redirect (url) {
      window.location.href = url
    }
  },
  mounted () {
    var v = this
    bus.$on('switch-router', (name) => {
      v.active = name
    })
    bus.$on('switch-task', (id) => {
      v.task_id = id
    })
  }
}
</script>

<style>
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  /*text-align: center;*/
  color: #2c3e50;
  width: 1000px;
  margin: 0px auto;
}

html {
  overflow-y: scroll;
}

body {
  padding-right: 15px;
  overflow: hidden;
}

.left-nav {
  margin-bottom: 0px;
}

.box-card {
  padding: 0px 0px;
}

a {
  text-decoration: None;
}

ul {
  list-style: None;
  padding-left: 0px;
  margin: 0px;
}

li.menu-item {
  padding: 0 20px;
  font-size: 14px;
  color: #2d2f33;
  height: 56px;
  line-height: 56px;
  cursor: pointer;
}

.submenu-list li.menu-item {
  padding: 0 40px;
}

li.menu-item:hover {
  background-color: #ecf5ff
}

li.menu-item.is-active {
  color: #409EFF;
}

li.menu-item * {
  vertical-align: middle;
}

li.menu-item i {
  margin-right: 5px;
  width: 24px;
  text-align: center;
  font-size: 18px;
  color: #878d99;
}

li.menu-item.is-active i {
  color: inherit;
}

.submenu-title {
  position: relative;
}

li.menu-item i.submenu-arrow {
  position: absolute;
  right: 20px;
  top: 50%;
  margin-top: -7px;
  font-size: 12px;
}

#bg {
    margin: 0px 0px 10px 0px;
}

.el-card__header {
  padding: 0px 20px;
}

.grey-button {
  color: #5a5e66;
}

</style>

