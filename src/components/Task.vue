<template>
<div class="task">
  <div class="header">
    <div>Status of Task {{id}}: {{task.status}}(<el-button type="text" @click="logVisible = true">check the log</el-button>)</div>

    <div class="switch-menu">
      <el-dropdown>
        <span class="el-dropdown-link">
          Model {{currentModel+1}} (Energy: {{energies[currentModel]}})<i class="el-icon-arrow-down el-icon--right"></i>
        </span>

        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item v-for="(energy, index) in energies" :key="index" @click.native="handleCommand(index)" >Model {{index+1}} (Energy: {{energies[index]}})</el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
    </div>

    <div class="download-button"><a :href="'http://redshift.med.unc.edu/medusadock/actions/task.php?item=output&suffix=pdb&id='+id">Download All Models</a></div>

    <!-- <div style="text-align: center">Model {{currentModel+1}}, Energy: {{energies[currentModel]}}</div>-->
  </div>

  <div style="position:relative">
    <div id="cover" v-show="showCover">Loading the Structure<i class="el-icon-loading"></i></div>
    <div id="viewport" style="width:700px; height:600px;"></div>
  </div>

  <!--
  <div>
    <el-pagination
      background
      layout="prev, pager, next"
      :total="numModels*10"
      :current-page.sync="currentModel"
      @current-change="handleCurrentChange">
    </el-pagination>
  </div>
  -->

  <el-dialog :title="'Log of Task '+id" :visible.sync="logVisible" width="80%">
    <pre>{{task.log}}</pre>
    <span slot="footer" class="dialog-footer">
      <el-button type="primary" @click="logVisible=false">Close</el-button>
    </span>
  </el-dialog>

</div>
</template>

<script>
import axios from 'axios'
import * as NGL from 'ngl'
import { bus } from '../bus.js'

axios.defaults.headers.post['Content-Type'] = 'multipart/form-data'

export default {
  name: 'Task',
  data () {
    return {
      showCover: false,
      logVisible: false,
      stage: '',
      task: {},
      currentModel: 0,
      numModels: 0,
      receptors: [],
      ligands: [],
      energies: []
    }
  },
  computed: {
    id () {
      return this.$route.params.id
    }
  },
  methods: {
    checkRenderStatus (cb) {
      var v = this
      setTimeout(function () {
        var c = v.stage.tasks.count
        console.log(c)
        if (c !== 0) {
          v.checkRenderStatus(cb)
        } else {
          cb()
        }
      }, 500)
    },

    handleCommand (command) {
      var v = this
      v.currentModel = command
      console.log(command)

      for (var i = 0; i < v.numModels; i++) {
        v.receptors[i].setVisibility(false)
        v.ligands[i].setVisibility(false)
      }
      v.receptors[v.currentModel].setVisibility(true)
      v.ligands[v.currentModel].setVisibility(true)
    },

    check () {
      var v = this
      axios.get('http://redshift.med.unc.edu/medusadock/actions/task.php?id=' + v.id).then(response => {
        v.task = response.data

        if (v.task.status === 'finished') {
          v.task.output.split('\n').forEach(function (l) {
            if (l.search('E_Int_terms') !== -1) {
              v.energies.push(l.split(/\s+/)[2])
//              v.energies.push()
            }
          })
          v.showCover = true
          v.stage.loadFile(new Blob([v.task.output], { type: 'text/plain' }), { ext: 'pdb' }).then(function (comp) {
            v.stage.comp = comp
            v.numModels = comp.structure.modelStore.count
            for (var i = 0; i < v.numModels; i++) {
              v.receptors.push(comp.addRepresentation('surface', { sele: ':A/' + i, opacity: 0.8, multipleBond: true }).setVisibility(false))
              v.ligands.push(comp.addRepresentation('licorice', { sele: ':B/' + i, multipleBond: true }).setVisibility(false))
            }
            v.receptors[v.currentModel].setVisibility(true)
            v.ligands[v.currentModel].setVisibility(true)
            v.checkRenderStatus(function () {
              v.showCover = false
              v.stage.autoView()
            })
          })
        } else {
        }
      }).catch(() => {
        console.log('Retrieve files failed!')
      })
//      if (v.task.id !== v.id) {
//        setTimeout(function () {
//          v.check()
//        }, 3000)
//      }
    }
  },
  mounted () {
    var v = this
    v.$nextTick(function () {
      bus.$emit('switch-router', 'Task')
      bus.$emit('switch-task', this.id)

      v.stage = new NGL.Stage('viewport', { backgroundColor: 'white' })
      // v.stage.loadFile('rcsb://1crn', {defaultRepresentation: true})

      v.check()
    })
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

.task .header {
  margin: 20px;
}

.el-form-item {
  margin-bottom: 3px;
}

.compound-list {
  border: 1px solid grey;
  height: 800px;
}
.compound-detail {
  border: 1px solid grey;
  height: 800px;
}

.el-pagination {
  text-align: center;
}

.switch-menu {
  float: left;
}

.download-button {
  float: right;
  color: #5a5e66;
  font-size: 14px;
}

#cover {
  font-size: 30px;
  width: 700px;
  height: 600px;
  line-height: 500px;
  vertical-align: middle;
  text-align: center;
  color: rgb(180, 188, 204);
  position: absolute;
  left: 0;
  top: 0;
  z-index: 99;
  background-color: white;
}

.el-button--text {
  color: #eb9e05;
}
</style>
