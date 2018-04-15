<template>
<div id="app">
  <!--<img src="./assets/logo.png">-->
  <user :visible.sync="userVisible"></user>

  <div :class="[$store.state.view==='fullscreen'?'main':'main-window']">
    <table>
    <tr>
        <td class="nav">
          <div class="nav-header">
            <div style="font-size: 20px; float: left">MedusaDock</div>
            <div @click="userVisible=true" style="float: right; cursor: pointer"><img width="15px" src="./assets/user.png"></div>
            <div style="clear: both"></div>
          </div>
          <div class="nav-content">
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
          </div>
        </td>
          <!--
          </el-col>

          <el-col :span="18">
          -->
        <td class="content">
          <div v-if="$store.state.view==='fullscreen'" class="zoom" @click="$store.commit('viewWindow')"><i class="el-icon-remove-outline"></i></div>
          <div v-if="$store.state.view==='window'" class="zoom" @click="$store.commit('viewFullscreen')"><i class="el-icon-circle-plus-outline"></i></div>
          <div class="content-content">
            <router-view/>
          </div>
        </td>
    </tr>
  </table>
</div>
          <!--
          </el-col>
        </el-row> 
      </el-card>
    </el-col>
  </el-row>
  -->
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
      userVisible: false,
      show_submenu: false,
      active: '',
      task_id: ''
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
  /*text-align: center;
  color: #2c3e50;*/
  /*width: 1000px;*/
  width: 100%;
  margin: 0px auto;
  padding: 0px;
  height: 100%;
}

html {
  overflow-y: scroll;
}

body {
  /*padding-right: 15px;*/
  overflow: hidden;
  background: url(http://old.bz55.com/uploads/allimg/140729/1-140HZ92039.jpg) no-repeat 50%;
  margin: 0px;
  padding: 0px;
  width: 100%;
  height: 100%;
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
  /*color: #2d2f33;*/
  height: 56px;
  line-height: 56px;
  cursor: pointer;
}

.submenu-list li.menu-item {
  padding: 0 40px;
}

li.menu-item:hover {
  /*background-color: #ecf5ff*/
}

li.menu-item.is-active {
  color: #ffd04b;
  /*color: #409EFF;*/
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

div.el-card__header {
  padding: 0px 20px;
  /*
  background-color: #223e4a;
  color: white;
  */
}

.grey-button {
  color: #5a5e66;
}

.main {
  position: absolute;
  left: 0px;
  top: 0px;
  right: 0px;
  bottom: 0px;
}

.main-window {
  width: 1000px;
  height: 700px;
  margin: 50px auto;
}

table {
  height: 100%;
  width: 100%;
  border-collapse:collapse;
  overflow: hidden;
}

tr {
  width: 100%;
  height: 100%;
  margin: 0px;
}

.nav {
  height: 100%;
  width: 250px;
  background: #2e3238;
  color: white;
  padding: 0px;
  vertical-align: text-top;
}

.nav-header {
  margin: 20px;
  line-height: 40px;
  vertical-align: middle;
}

.content {
  background-color: white;
  align: center;
  padding: 0px;
  overflow-y: scroll;
  position: relative;
}

.content-content {
  width: 720px;
  height: 700px;
  margin: 0px auto;
}

.zoom {
  position: absolute;
  right: 5px;
  top: 5px;
  font-size: 18px;
  color: #878d99;
  cursor: pointer;
  z-index: 99;
}

</style>

