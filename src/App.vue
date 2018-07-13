<template>
  <window>
  <user :visible.sync="userVisible"></user>
  <table>
    <tr>
      <td class="nav unselectable">
        <div class="nav-header">
          <div style="font-size: 20px; float: left">MedusaDock v2.0</div>
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
                <li class="menu-item" @click="redirect($config.HOST + '/eris')">
                  <i class="el-icon-arrow-right"></i>
                  <span>Eris</span>
                </li>
                <li class="menu-item" @click="redirect($config.HOST + '/chiron')">
                  <i class="el-icon-arrow-right"></i>
                  <span>Chiron</span>
                </li>
                <li class="menu-item" @click="redirect($config.HOST + '/ifold')">
                  <i class="el-icon-arrow-right"></i>
                  <span>iFold</span>
                </li>
                <li class="menu-item" @click="redirect($config.HOST + '/ifoldrna')">
                  <i class="el-icon-arrow-right"></i>
                  <span>iFoldRNA</span>
                </li>
                <li class="menu-item" @click="redirect($config.HOST + '/drugdisco')">
                  <i class="el-icon-arrow-right"></i>
                  <span>DrugDisco</span>
                </li>
              </ul>
            </div>

            <li class="menu-item" @click="redirect($config.HOST)">
              <i class="el-icon-location"></i>
              <span>Dokholyan Lab</span>
            </li>
          </ul>
        </div>
      </td>

      <td class="content">
        <div class="content-content">
          <router-view/>
        </div>
      </td>
    </tr>
  </table>
  </window>
</template>

<script>
import { bus } from '@/bus.js'
import Login from '@/components/Login'
import SignUp from '@/components/SignUp'
import User from '@/components/User'
import Window from '@/components/Window'

export default {
  name: 'app',
  data () {
    return {
      left: '',
      top: '',
      draggable: false,
      userVisible: false,
      show_submenu: false,
      active: '',
      task_id: ''
    }
  },
//  computed: {
//    mainStyle () {
//      var v = this
//      if (v.$store.state.view === 'fullscreen') {
//        return {
//          position: 'absolute',
//          left: '0px',
//          top: '0px',
//          right: '0px',
//          bottom: '0px'
//        }
//      } else {
//        return {
//          width: '1000px',
//          height: '600px',
//          position: 'absolute',
//          top: `${v.top}px`,
//          left: `${v.left}px`
//        }
//      }
//    }
//  },
  components: {
    Login,
    SignUp,
    User,
    Window
  },
  methods: {
    redirect (url) {
      window.location.href = url
    }
//    dragMainWindow (e) {
//      var v = this
//      if (v.draggable) {
//        v.left += e.movementX
//        v.top += e.movementY
//        if (v.left < 0) v.left = 0
//        if (v.top < 0) v.top = 0
//      }
//    }
  },
  mounted () {
    var v = this
    bus.$on('switch-router', (name) => {
      v.active = name
    })
    bus.$on('switch-task', (id) => {
      v.task_id = id
    })
    var top = (document.body.clientHeight - 600) / 2
    var left = (document.body.clientWidth - 1000) / 2
    v.top = (top < 0 ? 0 : top)
    v.left = (left < 0 ? 0 : left)
  }
}
</script>

<style>
html {
  overflow-y: auto;
  width: 100%;
  height: 100%;
  background: url('./assets/app-bg.jpg') no-repeat 50%;
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

body {
  margin: 0px;
  padding: 0px;
  width: 100%;
  height: 100%;
}

#app {
  margin: 0px;
  padding: 0px;
  width: 100%;
  height: 100%;
  position: relative;
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
  min-width: 250px;
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
  overflow-y: auto;
  position: relative;
  background-image: url('./assets/intro-bg.png');
  background-repeat: no-repeat;
  background-position: 0px 0px;
  background-size: auto 220px;
}

.content-content {
  width: 720px;
  height: 600px;
  margin: 0px auto;
}

/*
.zoom {
  position: absolute;
  right: 5px;
  top: 5px;
  font-size: 18px;
  color: #878d99;
  cursor: pointer;
  z-index: 99;
}
*/

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

.unselectable {
  -moz-user-select: -moz-none;
  -khtml-user-select: none;
  -webkit-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

a {
  color: #eb9e05;
}

a:hover {
  color: #ad8134;
}

a:visited {
  color: #ad8134;
}

.el-button--text {
  color: #eb9e05;
}

a.jn-button {
  color: #eb9e05;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  display: inline-block;
  line-height: 1;
  border: 1px solid transparent;
  background: transparent;
  padding: 12px 0px;
}

a:hover.jn-button {
  color: #ad8134;
}

a:visited.jn-button {
  color: #eb9e05;
}

</style>

