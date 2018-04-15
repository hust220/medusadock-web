<template>
<div class="new-task">
  <el-steps :active="step" finish-status="success">
    <el-step @click.native="step=0" title="Select Receptor"></el-step>
    <el-step @click.native="step=1" title="Select Ligand"></el-step>
    <el-step @click.native="step=2" title="Set Binding Site"></el-step>
    <el-step @click.native="step=3" title="Submit"></el-step>
  </el-steps>

  <div id="input-area" style="height: 500px">
    <div v-show="step<3">
      <div v-show="step===0">
        <div class="label">Please
          <el-tooltip class="item" effect="dark" content="Only '.pdb' format is supported." placement="bottom">
            <el-button type='text' onclick="window.document.getElementById('receptor_pdb').click()">upload the structure</el-button>
          </el-tooltip> or
          <el-popover ref="popover-pdbid" placement="bottom-start" v-model="showPdbIdInput" width="400" trigger="click">
            <table>
              <tr>
                <td><el-input v-model="pdbid" placeholder="PDB ID"></el-input></td>
                <td><el-button type="warning" plain @click="fetchReceptorFromPdb">Confirm</el-button></td>
              </tr>
            </table>
          </el-popover>
          <el-button type='text' v-popover:popover-pdbid>input the PDB ID</el-button> of the receptor.
        </div>
        <input type="file" id="receptor_pdb" ref="receptor_pdb" @change="uploadReceptor($event)" style="display:None;">
      </div>

      <div v-show="step===1">
        <div class="label">Please
          <el-tooltip class="item" effect="dark" content="Only '.mol2' format is supported." placement="bottom">
            <el-button type='text' onclick="window.document.getElementById('ligand_pdb').click()">upload the structure</el-button>
          </el-tooltip> or
          <el-popover ref="popover-zincid" placement="bottom-start" v-model="showZincIdInput" width="400" trigger="click">
            <table>
              <tr>
                <td><el-input v-model="zincid" placeholder="ZINC ID"></el-input></td>
                <td><el-button type="warning" plain @click="fetchLigandFromZinc">Confirm</el-button></td>
              </tr>
            </table>
          </el-popover>
          <el-button type='text' v-popover:popover-zincid>input the ZINC ID</el-button> of the ligand.
        </div>
        <input type="file" id="ligand_pdb" ref="ligand_pdb" @change="uploadLigand($event)" style="display:None;">
      </div>

      <div v-show="step===2">
        <div class="label">Please <el-button type="text" @click.native="setBindingSite">specify the position</el-button> or <el-button type='text' onclick="window.document.getElementById('binding_site').click()">upload the structure</el-button> of the binding site.</div>
        <input type="file" id="binding_site" ref="binding_site" @change="uploadBindingSite($event)" style="display:None">
      </div>

      <div style="position:relative">
        <div id="cover" v-show="showCover">Loading the Structure<i class="el-icon-loading"></i></div>
        <div id="structure-name" v-text="(step==0?ngl.receptor.name:(step==1?ngl.ligand.name:''))" v-if="step<3"></div>
        <div id="viewport"></div>
      </div>

    </div>

    <div v-show="step===3" style="height: 500px">
      <div class="label" v-if="!user">Email Address</div>
      <el-input v-model="form.email" style="width: 400px"></el-input>

      <div class="label">Number of rounds</div>
      <el-input v-model="form.num" style="width: 150px"></el-input>

      <div class="label">Constraints</div>
      <el-input type="textarea" :rows="5" v-model="form.constraints"></el-input>

      <div class="label">Cutoff</div>
      <el-input v-model="form.cutoff" style="width: 150px"></el-input>
    </div>
  </div>

  <el-button v-if="step>0" style="float: left; margin-top: 12px;" @click="prev" icon="el-icon-arrow-left">Prev</el-button>
  <el-button v-if="(step==0&&ngl.receptor)||(step==1&&ngl.ligand)||(step==2&&ngl.bindingSite)" type="primary" style="float: right; margin-top: 12px;" @click="next">Next<i class="el-icon-arrow-right el-icon--right"></i></el-button>
  <el-button v-if="step>=3" type="success" style="float: right; margin-top: 12px;" @click="submit">Submit<i class="el-icon-arrow-right el-icon--right"></i></el-button>
  <div style="clear: both"></div>

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
import { Vector3, Matrix4 } from 'three'
import { sprintf } from 'sprintf-js'

axios.defaults.headers.post['Content-Type'] = 'multipart/form-data'

export default {
  name: 'Index',
  data () {
    return {
      pdbid: '',
      zincid: '',
      showCover: false,
      showPdbIdInput: false,
      showZincIdInput: false,
      step: 0,
      receptorLabel: 'Please upload the receptor structure.',
      ligandLabel: 'Please upload the ligand structure.',
      bindingSiteLabel: 'Please select the binding site.',
      ngl: {
        stage: '',
        receptor: '',
        receptorName: '',
        ligand: '',
        ligandName: '',
        bindingSite: '',
        receptorSurface: ''
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
    next () {
      var v = this
      v.step += 1
    },
    prev () {
      var v = this
      v.step -= 1
    },
    checkRenderStatus (cb) {
      var v = this
      setTimeout(function () {
        var c = v.ngl.stage.tasks.count
        console.log(c)
        if (c !== 0) {
          v.checkRenderStatus(cb)
        } else {
          cb()
        }
      }, 500)
    },
    uploadReceptor (e) {
      let v = this
      let f = e.target.files[0]

      v.showCover = true
      console.log('Loading receptor...')
      v.ngl.stage.removeComponent(v.ngl.receptor)
      v.ngl.stage.removeComponent(v.ngl.bindingSite)
      v.ngl.stage.loadFile(f, { ext: 'pdb' }).then(function (comp) {
        v.ngl.receptor = comp
        v.ngl.receptorSurface = comp.addRepresentation('surface', { multipleBond: true, opacity: 0.5, lazy: false })
        v.checkRenderStatus(function () {
          comp.autoView()
          v.showCover = false
        })
      })
    },

    fetchReceptorFromPdb () {
      let v = this
      v.showPdbIdInput = false

      v.showCover = true
      console.log('Loading receptor...')
      v.ngl.stage.removeComponent(v.ngl.receptor)
      v.ngl.stage.removeComponent(v.ngl.bindingSite)
      v.ngl.stage.loadFile(`http://files.rcsb.org/download/${v.pdbid}.cif`, { ext: 'cif' }).then(function (comp) {
        v.ngl.receptor = comp
        v.ngl.receptorSurface = comp.addRepresentation('surface', { multipleBond: true, opacity: 0.5 })
        v.checkRenderStatus(function () {
          comp.autoView()
          v.showCover = false
        })
      })
    },

    uploadLigand (e) {
      let v = this
      let f = e.target.files[0]

      console.log('Loading ligand...')
      v.showCover = true
      v.ngl.stage.removeComponent(v.ngl.ligand)
      v.ngl.stage.loadFile(f, { ext: 'mol2' }).then(function (comp) {
        comp.addRepresentation('ball+stick', { multipleBond: true })
        v.ngl.ligand = comp
        v.checkRenderStatus(function () {
          comp.autoView()
          v.showCover = false
        })
      })
    },

    fetchLigandFromZinc () {
      var v = this
      var m = v.zincid.match(/(zinc)?0*(\d+)/i)
      v.zincid = m[2]
      v.showCover = true
      console.log('Loading Ligand...')
      v.ngl.stage.removeComponent(v.ngl.ligand)
      v.ngl.stage.loadFile(`http://redshift.med.unc.edu/medusadock/actions/zinc.php?id=${v.zincid}`, { ext: 'mol2' }).then(function (comp) {
        v.ngl.ligand = comp
        comp.addRepresentation('ball+stick', { multipleBond: true })
        v.checkRenderStatus(function () {
          comp.autoView()
          v.showCover = false
        })
      })
    },

    setBindingSite () {
      let v = this

      v.ngl.stage.removeComponent(v.ngl.bindingSite)

      var shape = new NGL.Shape('shape')

      if (v.ngl.receptor) {
        let center = v.ngl.receptor.getCenter()
        shape.addSphere([center.x, center.y, center.z], [1, 0, 0], 1)
      } else {
        shape.addSphere([0, 0, 0], [1, 0, 0], 1)
      }

      v.showCover = true
      var shapeComp = v.ngl.stage.addComponentFromObject(shape)
      shapeComp.addRepresentation('buffer')
      v.ngl.bindingSite = shapeComp

      v.checkRenderStatus(function () {
        v.showCover = false
        v.colorPocket()
      })
    },

    uploadBindingSite (e) {
      let v = this
      let f = e.target.files[0]

      console.log('Loading ligand...')
      v.showCover = true
      v.ngl.stage.removeComponent(v.ngl.bindingSite)
      v.ngl.stage.loadFile(f, { ext: 'mol2' }).then(function (comp) {
        var shape = new NGL.Shape('shape')
        var center = comp.getCenter()
        shape.addSphere([center.x, center.y, center.z], [1, 0, 0], 1)
        var shapeComp = v.ngl.stage.addComponentFromObject(shape)
        shapeComp.addRepresentation('buffer')
        v.ngl.bindingSite = shapeComp

        v.checkRenderStatus(function () {
          v.showCover = false
          v.colorPocket()
        })
      })
    },

    panComponentXY (x, y) {
      let v = this
      const tmpPanMatrix = new Matrix4()
      const tmpPanVector = new Vector3()

      let t = v.ngl.stage.trackballControls

      const scaleFactor = t.controls.getCanvasScaleFactor(0)
      tmpPanVector.set(x, y, 0)
      tmpPanVector.multiplyScalar(scaleFactor)

      tmpPanMatrix.extractRotation(t.component.transform)
      tmpPanMatrix.premultiply(t.viewer.rotationGroup.matrix)
      tmpPanMatrix.getInverse(tmpPanMatrix)
      tmpPanVector.applyMatrix4(tmpPanMatrix)

      t.component.position.add(tmpPanVector)
      t.component.updateMatrix()

      v.colorPocket()
    },

    colorPocket () {
      let v = this

      if (v.ngl.receptor && v.ngl.bindingSite) {
        let receptor = v.ngl.receptor
        let pos = v.ngl.bindingSite.getCenter()

        var dist = function (atom, pos) {
          var x = atom.x - pos.x
          var y = atom.y - pos.y
          var z = atom.z - pos.z
          return Math.sqrt(x * x + y * y + z * z)
        }

        let resList = []
        receptor.structure.eachResidue(function (r) {
          const count = r.atomCount
          const offset = r.atomOffset
          const ap = r.structure._ap
          const end = offset + count

          for (let i = offset; i < end; ++i) {
            ap.index = i
            if (dist(ap, pos) < 8) {
              resList.push(r.resno)
              break
            }
          }
        })
        console.log(resList.join(' or '))
        var schemeId = NGL.ColormakerRegistry.addSelectionScheme([[ 'green', resList.join(' or ') ]], 'hi')
        v.ngl.receptorSurface.setColor(schemeId)
      }
    },

    panComponentZ (x, y) {
      let v = this
      const tmpPanMatrix = new Matrix4()
      const tmpPanVector = new Vector3()

      let t = v.ngl.stage.trackballControls

      let r = Math.sqrt(x * x + y * y)
      r = (y > 0 ? r : -r)
      const scaleFactor = t.controls.getCanvasScaleFactor(0)
      tmpPanVector.set(0, 0, r)
      tmpPanVector.multiplyScalar(scaleFactor)

      tmpPanMatrix.extractRotation(t.component.transform)
      tmpPanMatrix.premultiply(t.viewer.rotationGroup.matrix)
      tmpPanMatrix.getInverse(tmpPanMatrix)
      tmpPanVector.applyMatrix4(tmpPanMatrix)

      t.component.position.add(tmpPanVector)
      t.component.updateMatrix()

      v.colorPocket()
    },

    componentToPdb (comp) {
      let pdb = ''
      let atomNum = 0
      comp.structure.eachChain(function (chain) {
        chain.eachResidue(function (residue) {
          residue.eachAtom(function (atom) {
            pdb += sprintf('%-6s%5d  %-4s%3s%2s%4d    %8.3f%8.3f%8.3f\n', 'ATOM', atomNum + 1, atom.atomname, residue.resname, chain.chainname, residue.resno, atom.x, atom.y, atom.z)
            atomNum += 1
          })
        })
        pdb += 'TER\n'
      })
      return pdb
    },

    bindingSiteCenter () {
      let center = this.bindingSite.getCenter()
      return [center.x, center.y, center.z]
    },

    submit () {
      let v = this
      let formData = new FormData()
      for (let i in v.form) {
        formData.append(i, v.form[i])
      }
      if (v.user) {
        formData.append('email', v.user.email)
      }
      formData.append('receptor_pdb', v.componentToPdb(v.ngl.receptor))
      formData.append('ligand_pdb', v.componentToPdb(v.ngl.ligand))
      formData.append('binding_site', v.bindingSiteCenter().join(' '))

//      formData.append('receptor_pdb', this.$refs.receptor_pdb.files[0])
//      formData.append('ligand_pdb', this.$refs.ligand_pdb.files[0])
//      formData.append('binding_site', this.$refs.binding_site.files[0])

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
      v.ngl.stage.mouseControls.remove('drag-left-ctrl')
      v.ngl.stage.mouseControls.add('drag-left-ctrl', function (stage, x, y) {
        v.panComponentXY(x, y)
//        stage.trackballControls.panComponent(x, y)
      })
      v.ngl.stage.mouseControls.remove('drag-right-ctrl')
      v.ngl.stage.mouseControls.add('drag-right-ctrl', function (stage, x, y) {
        v.panComponentZ(x, y)
//        stage.trackballControls.panComponent(x, y)
      })
    })
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

.new-task {
  padding-top: 30px;
}

i.prompt {
  font-size: 12px;
}

.el-form-item {
  margin-bottom: 3px;
}

#input-area {
  margin-top: 10px;
}

.label {
  font-size: 14px;
  height: 40px;
  line-height: 40px;
  vertical-align: middle;
}

#viewport {
  width: 600px;
  height: 400px;
  position: absolute;
  left: 0;
  top: 0;
}

#structure-name {
  font-size: 16px;
  color: rgb(180, 188, 204);
  position: absolute;
  left: 0;
  top: 0;
  padding: 10px;
  z-index: 98;
}

#cover {
  font-size: 30px;
  width: 600px;
  height: 400px;
  line-height: 400px;
  vertical-align: middle;
  text-align: center;
  color: rgb(180, 188, 204);
  position: absolute;
  left: 0;
  top: 0;
  z-index: 99;
}

</style>
