<template>
<div class="taskmanagement">
  <el-table :data="tasks" height="800" style="width: 100%">
    <el-table-column prop="id" label="Task ID">
      <template slot-scope="scope">
        <a :href="'#/Task/'+scope.row.id">{{ scope.row.id }}</a>
      </template>
    </el-table-column>
    <el-table-column prop="userid" label="User ID"></el-table-column>
    <el-table-column prop="tsubmit" label="Submit Time"></el-table-column>
    <el-table-column prop="tprocess" label="Process Time"></el-table-column>
    <el-table-column prop="tfinish" label="Finish Time"></el-table-column>
    <el-table-column prop="status" label="Status"></el-table-column>
  </el-table>
</div>
</template>

<script>
import axios from 'axios'
import { bus } from '../bus.js'

axios.defaults.headers.post['Content-Type'] = 'multipart/form-data'

export default {
  name: 'TaskManagement',
  data () {
    return {
      tasks: []
    }
  },
  methods: {
    check () {
      var v = this
      axios.get('http://redshift.med.unc.edu/medusadock/actions/check.php').then(response => {
        v.tasks = response.data.tasks
      }).catch(() => {
        console.log('get failed')
      })
    }
  },
  mounted () {
    this.$nextTick(function () {
      bus.$emit('switch-router', 'TaskManagement')

      var v = this
      v.check()
      setInterval(function () {
        v.check()
      }, 5000)
    })
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

.el-form-item {
  margin-bottom: 3px;
}

</style>
