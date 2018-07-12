<template>
<div class="new-task">
  <el-steps :active="step" style="cursor: pointer" finish-status="success">
    <el-step @click.native="step=0" :status="getStatus(0)" title="Select Receptor"></el-step>
    <el-step @click.native="step=1" :status="getStatus(1)" title="Select Ligand"></el-step>
    <el-step @click.native="step=2" :status="getStatus(2)" title="Set Binding Site"></el-step>
    <el-step @click.native="step=3" :status="getStatus(3)" title="Set Constraints"></el-step>
    <el-step @click.native="step=4" :status="getStatus(4)" title="Submit"></el-step>
  </el-steps>

  <div id="input-area" style="height: 500px">
    <div v-show="step<3">
      <!-- Select the receptor -->
      <div v-show="step===0">
        <div class="label">Please
          <a class="jn-button" onclick="window.document.getElementById('receptor_pdb').click()">upload the structure</a>
          <el-tooltip class="item" effect="dark" content="Most formats are supported, including '.pdb', '.mol2', ..." placement="bottom">
            <i class="el-icon-question"></i>
          </el-tooltip>
          or
          <el-popover ref="popover-pdbid" placement="bottom-start" v-model="showPdbIdInput" width="400" trigger="click">
            <table>
              <tr>
                <td><el-input @keyup.enter.native="setReceptor()" ref="pdbInput" v-model="pdbid" placeholder="PDB ID"></el-input></td>
                <td><el-button type="warning" plain @click="setReceptor()">Confirm</el-button></td>
              </tr>
            </table>
          </el-popover>
          <a class="jn-button" v-popover:popover-pdbid>input the PDB ID</a>
          <el-tooltip class="item" effect="dark" content="Example: 5i3y" placement="bottom">
            <i class="el-icon-question"></i>
          </el-tooltip>
          of the receptor.
        </div>
        <input type="file" id="receptor_pdb" ref="receptor_pdb" @change="setReceptor($event)" style="display:None;">
      </div>

      <!-- Select the ligand -->
      <div v-show="step===1">
        <div class="label">Please
          <a class="jn-button" onclick="window.document.getElementById('ligand_pdb').click()">upload the structure</a>
          <el-tooltip class="item" effect="dark" content="Only '.mol2' format is supported." placement="bottom">
            <i class="el-icon-question"></i>
          </el-tooltip>
          or
          <el-popover ref="popover-zincid" placement="bottom-start" v-model="showZincIdInput" width="400" trigger="click">
            <table>
              <tr>
                <td><el-input @keyup.enter.native="setLigand()" ref="zincInput" v-model="zincid" placeholder="ZINC ID"></el-input></td>
                <td><el-button type="warning" plain @click="setLigand()">Confirm</el-button></td>
              </tr>
            </table>
          </el-popover>
          <a class="jn-button" v-popover:popover-zincid>input the ZINC ID</a>
          <el-tooltip class="item" effect="dark" content="Example: ZINC0000534" placement="bottom">
            <i class="el-icon-question"></i>
          </el-tooltip>
          of the ligand.
        </div>
        <input type="file" id="ligand_pdb" ref="ligand_pdb" @change="setLigand($event)" style="display:None;">
      </div>

      <!-- Set the binding site -->
      <div v-show="step===2">
        <div class="label">
          Please
          <a class="jn-button" @click="setBindingSite">specify the position</a>
          or
          <a class="jn-button" onclick="window.document.getElementById('binding_site').click()">upload the structure</a>
          of the binding site.
          <el-tooltip class="item" effect="dark" placement="bottom">
          <div slot="content">
            <h4>Step 1: Set the initial position of the binding site by one of the methods below.</h4>
            <div style="padding-left: 30px">
              <ul style="list-style-type: disc">
                <li>Click the 'specify the position' link.</li>
                <li>Click the 'upload the structure' link (Only '.mol2' format is supported).</li>
              </ul>
              <p>A red ball will appear in the middle of the screen.</p>
            </div>

            <h4>Step 2: Drag the ball to specify the binding site.</h4>
            <div style="padding-left: 30px">
              <ul style="list-style-type: disc">
                <li>Drag the red ball in X-Y plane with left mouse while hold on the 'ctrl' key.</li>
                <li>Drag the red ball along the Z-axis with right mouse while hold on the 'ctrl' key.</li>
              </ul>
            </div>
          </div>
          <i class="el-icon-question"></i>
          </el-tooltip>
        </div>
        <input type="file" id="binding_site" ref="binding_site" @change="uploadBindingSite($event)" style="display:None">
      </div>

      <!-- Viewport -->
      <div style="position:relative">
        <div id="cover" v-show="showCover">Loading the Structure<i class="el-icon-loading"></i></div>
        <div id="structure-name" v-if="step<3">
          <div v-if="ngl.receptorName">Receptor: <a :href="$config.HOST+`/medusadock/actions/file.php?name=${ngl.receptorName}&format=pdb`" v-text="ngl.receptorName+'.pdb'"></a></div>
          <div v-if="ngl.ligandName">Ligand: <a :href="$config.HOST+`/medusadock/actions/file.php?name=${ngl.ligandName}&format=mol2`" v-text="ngl.ligandName+'.mol2'"></a></div>
          <div v-if="ngl.bindingSite">Binding Site: {{bindingSiteCenter().map(x => x.toFixed(2)).join(', ')}}</div>
        </div>
        <div id="viewport"></div>
      </div>
    </div>

    <!-- Set the constraints -->
    <div style="position: relative" v-show="step===3">
      <!-- left panel -->
      <div style="height: 100%; width: 420px; float: left">
        <p>Receptor Residues</p>
        <div style="overflow-y: scroll; height: 250px">
          <div v-show="atomSelection.receptor.visible" :style="atomSelection.receptor.style">
            <ul><li class="atom-label" v-for="(atom, index) in atomSelection.receptor.atoms" @click="handleClickAtomLabel($event, atom, 'receptor')" v-text="atom"></li></ul>
          </div>

          <div
            v-for="(res, index) in residues.receptor"
            @click="handleClickResidueLabel($event, index, 'receptor')"
            :class="['residue-label', (index===selected.receptor.residueIndex?'residue-selected':'')]">
            <div class="residue-label-res" v-text="`${res.num}/${res.name}`"></div><div class="residue-label-atom" v-text="(index===selected.receptor.residueIndex?`:${selected.receptor.atomName}`:'')"></div><div class="clear:both"></div>
          </div>
        </div>

        <p>Ligand Residues</p>
        <div style="overflow-y: scroll; height: 30px">
          <div v-show="atomSelection.ligand.visible" :style="atomSelection.ligand.style">
            <ul><li class="atom-label" v-for="(atom, index) in atomSelection.ligand.atoms" @click="handleClickAtomLabel($event, atom, 'ligand')" v-text="atom"></li></ul>
          </div>

          <div
            v-for="(res, index) in residues.ligand"
            @click="handleClickResidueLabel($event, index, 'ligand')"
            :class="['residue-label', (index===selected.ligand.residueIndex?'residue-selected':'')]">
            <div class="residue-label-res" v-text="`${res.num}`"></div><div class="residue-label-atom" v-text="(index===selected.ligand.residueIndex?`:${selected.ligand.atomName}`:'')"></div><div class="clear:both"></div>
          </div>
        </div>

        <p>Distance</p>
        <el-input v-model="constraintDistance">
        <template slot="append">&#8491;</template>
        </el-input>
      </div>

      <!-- middle panel -->
      <div style="height: 100%; width: 80px; float: left; padding-top: 150px">
        <!-- The Add Button -->
        <div style="margin: 10px 0px 20px 10px">
          <el-button
            :disabled="selected.receptor.residueIndex===''||selected.receptor.atomName===''||selected.ligand.residueIndex===''||selected.ligand.atomName===''||constraintDistance===''"
            @click="addConstraints"
            style="width: 100%" size="small" type="primary"
            plain>
            Add<i class="el-icon-arrow-right el-icon--right"></i>
          </el-button>
        </div>

        <!-- The Delete Button -->
        <div style="margin: 10px 0px 20px 10px">
          <el-button
            @click.native="deleteConstraint"
            :disabled="selected.constraintIndex===''"
            style="width: 100%" size="small" type="warning" plain>
            Delete<i class="el-icon-delete"></i>
          </el-button>
        </div>
      </div>

      <!-- right panel -->
      <div style="height: 100%; margin-left: 10px; width: 180px; float: left">
        <p>
          Constraints<el-tooltip class="item" effect="dark" placement="bottom">
          <div slot="content"><b>Add constraints</b><ol><li>Select a receptor atom</li><li>Select a ligand atom</li><li>Set the distance</li><li>Click the 'Add' button</li></ol><b>Delete constraints</b><ol><li>Select a constraint</li><li>Click the 'Delete' button</li></ol></div>
            <i class="el-icon-question"></i>
          </el-tooltip>
        </p>
        <div style="height: 420px; border: 1px solid #e5e5e5;">
          <div v-for="(constraint, index) in constraints" @click="selected.constraintIndex=index" :class="['constraint-label', (index===selected.constraintIndex?'constraint-selected':'')]">
            <div style="float: left; width: 30px; text-align: right" v-text="constraint.receptor.residueIndex+'/'"></div><div style="float: left; width: 30px; text-align: left" v-text="constraint.receptor.atomName"></div>
            <div style="float: left; width: 30px; text-align: right" v-text="constraint.ligand.residueIndex+'/'"></div><div style="float: left; width: 30px; text-align: left" v-text="constraint.ligand.atomName"></div>
            <div style="float: left; width: 30px; text-align: right" v-text="constraint.distance"></div>
            <div style="clear: both"></div>
          </div>
        </div>
      </div>
      <div style="clear: both"></div>
    </div>

    <!-- Set parameters -->
    <div style="position: relative" v-show="step===4">
      <div v-if="!user">
        <div class="label">Email Address</div>
        <el-input v-model="form.email" style="width: 400px"></el-input>
      </div>

      <div class="label">Number of rounds</div>
      <el-input v-model="form.num" style="width: 150px"></el-input>

      <!--
      <div class="label">Constraints</div>
      <el-input type="textarea" :rows="5" v-model="form.constraints"></el-input>
      -->

      <div class="label">Cutoff</div>
      <el-input v-model="form.cutoff" style="width: 150px"></el-input>

      <div></div>
      <div style="float: right">
        <el-button type="warning" @click="submit">Submit<i class="el-icon-arrow-right el-icon--right"></i></el-button>
      </div>
      <div style="clear: both"></div>
    </div>
  </div>

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
      selected: {
        receptor: {
          residueIndex: '',
          atomName: ''
        },
        ligand: {
          residueIndex: '',
          atomName: ''
        },
        constraintIndex: ''
      },

      atomSelection: {
        receptor: {
          atoms: [],
          visible: false,
          style: {position: 'absolute', zIndex: '99', paddingLeft: '10px', width: '100px', backgroundColor: 'white', top: '0px', left: '0px'}
        },
        ligand: {
          atoms: [],
          visible: false,
          style: {position: 'absolute', zIndex: '99', paddingLeft: '10px', width: '100px', backgroundColor: 'white', top: '0px', left: '0px'}
        }
      },

      constraints: [],
      constraintDistance: '',

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
    },
    residues () {
      var v = this
      var receptorResidues = []
      if (v.ngl.receptor) {
        v.ngl.receptor.structure.eachResidue(function (r) {
          var atoms = []
          r.eachAtom(function (a) {
            atoms.push(a.atomname)
          })
          receptorResidues.push({name: r.resname, num: r.resno, atoms})
        })
      }
      var ligandResidues = []
      if (v.ngl.ligand) {
        v.ngl.ligand.structure.eachResidue(function (r) {
          var atoms = []
          r.eachAtom(function (a) {
            atoms.push(a.atomname)
          })
          ligandResidues.push({name: r.resname, num: r.resno, atoms})
        })
      }
      return {receptor: receptorResidues, ligand: ligandResidues}
    }
  },

  methods: {
    addConstraints () {
      var v = this
      var constraint = {
        receptor: {
          residueIndex: v.selected.receptor.residueIndex,
          atomName: v.selected.receptor.atomName
        },
        ligand: {
          residueIndex: v.selected.ligand.residueIndex,
          atomName: v.selected.ligand.atomName
        },
        distance: v.constraintDistance
      }
      console.log(constraint)
      v.constraints.push(constraint)
    },

    deleteConstraint () {
      this.constraints.splice(this.selected.constraintIndex, 1)
      this.selected.constraintIndex = ''
      /*
      var ls = []
      for (var i = 0; i < this.constraints.length; i++) {
        if (i !== this.selected.constraintIndex) ls.push(this.constraints[i])
      }
      this.constraints = ls
      this.selected.constraintIndex = ''
      */
    },

    handleClickResidueLabel (e, ind, receptorOrLigand) {
      var v = this
      var el = e.currentTarget
      var theOther = (receptorOrLigand === 'receptor' ? 'ligand' : 'receptor')

      v.atomSelection[receptorOrLigand].atoms = v.residues[receptorOrLigand][ind].atoms

      v.atomSelection[receptorOrLigand].style.top = `${el.offsetTop - el.parentElement.scrollTop}px`
      v.atomSelection[receptorOrLigand].style.left = `${el.offsetLeft + el.offsetWidth}px`

      v.atomSelection[theOther].visible = false
      if (v.selected[receptorOrLigand].residueIndex === ind) {
        v.atomSelection[receptorOrLigand].visible ^= true
      } else {
        v.atomSelection[receptorOrLigand].visible = true
      }

      v.selected[receptorOrLigand].residueIndex = ind
      v.selected[receptorOrLigand].atomName = ''
    },

    handleClickAtomLabel (e, name, receptorOrLigand) {
      var v = this
      v.atomSelection[receptorOrLigand].visible = false
      v.selected[receptorOrLigand].atomName = name
    },

    getStatus (n) {
      var v = this
      if (n === v.step) {
        return 'process'
      } else {
        if (n === 0) {
          if (v.ngl.receptor) {
            return 'success'
          } else if (n > v.step) {
            return 'wait'
          } else {
            return 'error'
          }
        } else if (n === 1) {
          if (v.ngl.ligand) {
            return 'success'
          } else if (n > v.step) {
            return 'wait'
          } else {
            return 'error'
          }
        } else if (n === 2) {
          if (v.ngl.bindingSite) {
            return 'success'
          } else if (n > v.step) {
            return 'wait'
          } else {
            return 'error'
          }
        } else if (n === 3) {
          if (n < v.step) {
            return 'success'
          } else {
            return 'wait'
          }
        }
      }
    },

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

    setReceptor () {
      let v = this
      let formData = new FormData()

      console.log(arguments)
      if (arguments.length > 0) {
        let f = arguments[0].target.files[0]
        formData.append('file', f)
      } else if (v.pdbid !== '') {
        formData.append('pdbid', v.pdbid)
      } else {
        alert('Please specify the receptor!')
        return
      }

      v.showCover = true
      v.showPdbIdInput = false
      v.ngl.stage.removeComponent(v.ngl.receptor)
      v.ngl.stage.removeComponent(v.ngl.bindingSite)

      axios({
        method: 'post',
        url: v.$config.HOST + '/medusadock/actions/set_receptor.php',
        data: formData,
        config: {headers: {'Content-Type': 'multipart/form-data'}}
      }).then(response => {
        v.ngl.receptorName = response.data
        console.log(v.ngl.receptorName)
        v.ngl.stage.loadFile(v.$config.HOST + `/medusadock/actions/file.php?name=${v.ngl.receptorName}&format=pdb`, { ext: 'pdb' }).then(function (comp) {
          v.ngl.receptor = comp
          v.ngl.receptorSurface = comp.addRepresentation('surface', { probeRadius: 1.5, multipleBond: true, opacity: 0.5 })
          v.checkRenderStatus(function () {
            v.ngl.stage.autoView()
            v.showCover = false
          })
        })
      }).catch(() => {
        alert('Setting Receptor Failed!')
      })
    },

    setLigand () {
      let v = this
      let formData = new FormData()

      console.log(arguments)
      if (arguments.length > 0) {
        let f = arguments[0].target.files[0]
        formData.append('file', f)
      } else if (v.zincid !== '') {
        var m = v.zincid.match(/(zinc)?0*(\d+)/i)
        v.zincid = m[2]
        formData.append('zincid', v.zincid)
      } else {
        alert('Please specify the receptor!')
        return
      }

      v.showCover = true
      v.showZincIdInput = false
      v.ngl.stage.removeComponent(v.ngl.ligand)

      axios({
        method: 'post',
        url: v.$config.HOST + '/medusadock/actions/set_ligand.php',
        data: formData,
        config: {headers: {'Content-Type': 'multipart/form-data'}}
      }).then(response => {
        v.ngl.ligandName = response.data
        v.ngl.stage.loadFile(v.$config.HOST + `/medusadock/actions/file.php?name=${v.ngl.ligandName}&format=mol2`, { ext: 'mol2' }).then(function (comp) {
          v.ngl.ligand = comp
          comp.addRepresentation('ball+stick', { multipleBond: true })
          v.checkRenderStatus(function () {
            v.ngl.stage.autoView()
            v.showCover = false
//            console.log(v.componentToPdb(v.ngl.ligand))
          })
        })
      }).catch(() => {
        alert('Setting Receptor Failed!')
      })
    },

//    uploadReceptor (e) {
//      let v = this
//      let f = e.target.files[0]
//
//      v.showCover = true
//
//      console.log('Loading receptor...')
//      v.ngl.stage.removeComponent(v.ngl.receptor)
//      v.ngl.stage.removeComponent(v.ngl.bindingSite)
//      v.ngl.stage.loadFile(f, { ext: 'pdb' }).then(function (comp) {
//        v.ngl.receptor = comp
//        v.ngl.receptorSurface = comp.addRepresentation('surface', { multipleBond: true, opacity: 0.5, lazy: false })
//        v.checkRenderStatus(function () {
//          v.ngl.stage.autoView()
//          v.showCover = false
//        })
//      })
//    },
//
//    fetchReceptorFromPdb () {
//      let v = this
//      v.showPdbIdInput = false
//
//      v.showCover = true
//      console.log('Loading receptor...')
//      v.ngl.stage.removeComponent(v.ngl.receptor)
//      v.ngl.stage.removeComponent(v.ngl.bindingSite)
//      v.ngl.stage.loadFile(`http://files.rcsb.org/download/${v.pdbid}.cif`, { ext: 'cif' }).then(function (comp) {
//        v.ngl.receptor = comp
//        v.ngl.receptorSurface = comp.addRepresentation('surface', { probeRadius: 1.5, multipleBond: true, opacity: 0.5 })
//        v.checkRenderStatus(function () {
//          v.ngl.stage.autoView()
//          v.showCover = false
//        })
//      })
//    },

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
          v.ngl.stage.autoView()
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
      v.ngl.stage.loadFile(v.$config.HOST + `/medusadock/actions/zinc.php?id=${v.zincid}`, { ext: 'mol2' }).then(function (comp) {
        v.ngl.ligand = comp
        comp.addRepresentation('ball+stick', { multipleBond: true })
        v.checkRenderStatus(function () {
          v.ngl.stage.autoView()
          v.showCover = false
//          console.log(v.componentToPdb(v.ngl.ligand))
        })
      })
      v.showZincIdInput = false
    },

    setBindingSite () {
      console.log('setBindingSite...')
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
      v.showViewArea = true
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
      v.showViewArea = true
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
            let resname = (residue.resname === '<0>' ? 'X' : residue.resname)
            pdb += sprintf('%-6s%5d  %-4s%3s%2s%4d    %8.3f%8.3f%8.3f\n', 'ATOM', atomNum + 1, atom.atomname, resname, chain.chainname, residue.resno, atom.x, atom.y, atom.z)
            atomNum += 1
          })
        })
        pdb += 'TER\n'
      })
      return pdb
    },

    bindingSiteCenter () {
      let center = this.ngl.bindingSite.getCenter()
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
      } else if (!v.form.email) {
        alert('Please provide the email address!')
        return
      }

      if (v.ngl.receptor) {
        formData.append('receptor', v.ngl.receptorName)
//        formData.append('receptor_pdb', v.componentToPdb(v.ngl.receptor))
      } else {
        alert('Please provide the receptor!')
        return
      }

      if (v.ngl.ligand) {
        formData.append('ligand', v.ngl.ligandName)
//        formData.append('ligand_pdb', v.componentToPdb(v.ngl.ligand))
      } else {
        alert('Please provide the ligand!')
        return
      }

      if (v.ngl.bindingSite) {
        formData.append('binding_site', v.bindingSiteCenter().join(' '))
      } else {
        alert('Please specify the binding site!')
        return
      }

      axios({
        method: 'post',
        url: v.$config.HOST + '/medusadock/actions/submit.php',
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
  width: 720px;
  height: 450px;
  margin: 0px auto 0px 0px;
  box-shadow: 0 0 5px #888888;
}

#structure-name {
  font-size: 16px;
  color: rgb(180, 188, 204);
  background-color: rgb(0, 0, 0, 0);
  position: absolute;
  left: 0;
  top: 0;
  padding: 10px;
  z-index: 98;
}

#cover {
  font-size: 30px;
  width: 720px;
  height: 450px;
  line-height: 450px;
  vertical-align: middle;
  text-align: center;
  color: rgb(180, 188, 204);
  position: absolute;
  left: 0;
  top: 0;
  z-index: 99;
}

.residue-label {
  cursor: pointer;
  color: rgb(180, 188, 204);
  font-family: consolas, manaco, monospace;
  font-size: 14px;
  height: 30px;
  width: 80px;
  float: left
}

.residue-label.residue-selected {
  font-weight: 600;
  color: black;
}

.residue-label-res {
  width: 55px;
  float: left;
  text-align: right;
}

.residue-label-atom {
  width: 25px;
  float: left;
  text-align: left;
}

.atom-label {
  cursor: pointer;
  width: 100%;
  font-size: 12px;
  height: 20px;
}

.atom-label:hover {
  /*
  background-color: #e4ecf4;
  */
  background-color: #bfcddc;
}

.constraint-label {
  cursor: pointer;
  padding-left: 10px;
  font-size: 12px;
}

.constraint-label:hover {
  background-color: #bfcddc;
}

.constraint-label.constraint-selected {
  background-color: #bfcddc;
}

</style>
