import Vue from 'vue'
import Router from 'vue-router'
import Home from '@/components/Home'
import NewTask from '@/components/NewTask'
import TaskManagement from '@/components/TaskManagement'
import Task from '@/components/Task'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'index',
      component: Home
    },
    {
      path: '/home',
      name: 'Home',
      component: Home
    },
    {
      path: '/newtask',
      name: 'NewTask',
      component: NewTask
    },
    {
      path: '/taskmanagement',
      name: 'TaskManagement',
      component: TaskManagement
    },
    {
      path: '/task/:id',
      name: 'Task',
      component: Task
    }
  ]
})
