<template>
<div class="index">
  <el-form ref="form" :model="form" label-width="150px" @keyup.enter="onSubmit" >
    <el-form-item label="Email Address" v-if="!user">
      <el-input v-model="form.email" style="width: 400px"></el-input>
    </el-form-item>

    <el-form-item label="Receptor">
      <input type="file" ref="receptor_pdb" @change="showReceptor($event)">
    </el-form-item>

    <el-form-item label="Ligand">
      <input type="file" ref="ligand_pdb" @change="showLigand($event)">
    </el-form-item>

    <el-form-item label="Binding Site">
      <input type="file" ref="binding_site">
    </el-form-item>

    <el-form-item label="Number of rounds">
      <el-input v-model="form.num" style="width: 150px"></el-input>
    </el-form-item>

    <el-form-item label="">
      <a href="#" @click.prevent="show_options^=true">{{show_options?'Hide':'Show'}} options</a>
    </el-form-item>

    <!--
    <el-form-item label="Allow ROTAMER mode of ligand" v-show="show_options">
      <el-switch v-model="form.allow_rotamer"></el-switch>
    </el-form-item>

    <el-form-item label="Perform backrub minimization" v-show="show_options">
      <el-switch v-model="form.perform_backrub"></el-switch>
    </el-form-item>
    -->

    <!--
    <el-form-item label="Potential type" v-show="show_options">
      <el-select v-model="form.potential" placeholder="Potential Type">
        <el-option
          v-for="item in potential_options"
          :key="item.value"
          :label="item.label"
          :value="item.value">
        </el-option>
      </el-select>
    </el-form-item>
    -->

    <el-form-item label="Constraints" v-show="show_options">
      <el-input type="textarea" :rows="5" v-model="form.constraints"></el-input>
    </el-form-item>

    <el-form-item label="Cutoff" v-show="show_options">
      <el-input v-model="form.cutoff" style="width: 150px"></el-input>
    </el-form-item>

    <!--
    <el-form-item label="Number of poses to explore" v-show="show_options">
      <el-input v-model="form.num_poses"></el-input>
    </el-form-item>
    -->

    <!--
    <el-form-item label="Seed" v-show="show_options">
      <el-input v-model="form.seed"></el-input>
    </el-form-item>
    -->

    <el-form-item>
      <el-button @click="onSubmit" type="primary">Submit</el-button>
      <el-button>Cancel</el-button>
    </el-form-item>
  </el-form>

  <div id="viewport" style="width:800px; height:600px;"></div>

  <el-dialog :visible.sync="show_dialog" width="30%">
    <span>Submitted successfully!</span>
    <span slot="footer" class="dialog-footer">
    <el-button @click="show_dialog = false">New Task</el-button>
    <el-button type="primary" @click="show_dialog = false">Task Status</el-button>
    </span>
  </el-dialog>
</div>

</template>

<script>
import axios from 'axios'
import { bus } from '../bus.js'
import * as NGL from 'ngl'

axios.defaults.headers.post['Content-Type'] = 'multipart/form-data'

export default {
  name: 'Index',
  data () {
    return {
      ngl: {
        stage: '',
        receptor: '',
        ligand: ''
      },
      show_dialog: false,
      show_options: false,
      potential_options: [{
        value: 'spring',
        label: 'Spring Potential'
      }, {
        value: 'squarewell',
        label: 'Square Well Potential'
      }],
      form: {
        num: 1,
        email: '',
        constraints: '',
        cutoff: 10
      }
    }
  },
  computed: {
    user () {
      return this.$store.state.user
    }
  },
  methods: {
    showReceptor (e) {
      let v = this
      let f = e.target.files[0]

      console.log('Loading receptor...')
      v.ngl.stage.getComponentsByName(v.ngl.receptor).forEach(function (c) {
        v.ngl.stage.removeComponent(c)
      })
      v.ngl.receptor = f.name

      v.ngl.stage.loadFile(f, { ext: 'pdb' }).then(function (comp) {
        comp.addRepresentation('surface', { multipleBond: true })
        v.ngl.stage.autoView()
      })
    },

    showLigand (e) {
      let v = this
      let f = e.target.files[0]

      console.log('Loading ligand...')
      v.ngl.stage.getComponentsByName(v.ngl.ligand).forEach(function (c) {
        v.ngl.stage.removeComponent(c)
      })
      v.ngl.ligand = f.name

      v.ngl.stage.loadFile(f, { ext: 'mol2' }).then(function (comp) {
        comp.addRepresentation('ball+stick', { multipleBond: true })
        v.ngl.stage.autoView()
      })
    },

    onSubmit () {
      let v = this
      let formData = new FormData()
      for (let i in v.form) {
        formData.append(i, v.form[i])
      }
      if (v.user) {
        formData.append('email', v.user.email)
      }
      formData.append('receptor_pdb', this.$refs.receptor_pdb.files[0])
      formData.append('ligand_pdb', this.$refs.ligand_pdb.files[0])
      formData.append('binding_site', this.$refs.binding_site.files[0])

      axios({
        method: 'post',
        url: 'http://redshift.med.unc.edu/medusadock/actions/submit.php',
        data: formData,
        config: {headers: {'Content-Type': 'multipart/form-data'}}
      }).then(response => {
        alert('Submitted successfully!')
        window.location.href = '#/' + 'TaskManagement'
      }).catch(() => {
        alert('Submitted failed!')
      })
    }
  },
  mounted () {
    let v = this

    v.$nextTick(function () {
      bus.$emit('switch-router', 'NewTask')

      v.ngl.stage = new NGL.Stage('viewport', { backgroundColor: 'white' })
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
