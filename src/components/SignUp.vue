<template>
  <el-dialog width="30%" :visible="visible" @update:visible="updateVisible">
    <el-form @keyup.enter="submit" label-position="left" label-width="150px" :model="form">
      <el-form-item label="User Name">
        <el-input v-model="form.username" auto-complete="off"></el-input>
      </el-form-item>

      <el-form-item label="Password">
        <el-input type="password" v-model="form.password1" auto-complete="off"></el-input>
      </el-form-item>

      <el-form-item label="Confirm Password">
        <el-input type="password" v-model="form.password2" auto-complete="off"></el-input>
      </el-form-item>

      <el-form-item label="Email">
        <el-input v-model="form.email" auto-complete="off"></el-input>
      </el-form-item>

    </el-form>

    <div slot="footer" class="dialog-footer">
      <el-button @click="updateVisible(false)">Cancel</el-button>
      <el-button type="warning" @click="submit">Sign Up</el-button>
    </div>
  </el-dialog>
</template>

<script>
import axios from 'axios'

export default {
  name: 'signup',

  data () {
    return {
      form: {
        username: '',
        password1: '',
        password2: ''
      }
    }
  },

  props: ['visible'],

  methods: {
    updateVisible (val) {
      this.$emit('update:visible', val)
    },

    submit () {
      let v = this
      let formData = new FormData()
      for (let i in v.form) {
        formData.append(i, v.form[i])
      }

      axios({
        method: 'post',
        url: v.$config.HOST + '/medusadock/actions/sign_up.php',
        data: formData,
        config: {headers: {'Content-Type': 'multipart/form-data'}}
      }).then(response => {
        var r = response.data
        if (r.status === 1) {
          this.$store.commit('login', r.user)
          alert('Congratulations! Your account is set up successfully!')
          v.updateVisible(false)
        } else {
          alert('Sign up failed! ' + r.msg)
        }
        // window.location.href = '#/' + 'TaskManagement'
      }).catch(() => {
        alert('Sign up failed!')
      })
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.dialog {
width: 100%;
height: 100%;
background: rgba(0,0,0,.8);
}

.loginPage {
position: absolute;
top: 50%;
left: 50%;
margin-top: -150px;
margin-left: -175px;
width: 350px;
min-height: 300px;
padding: 30px 20px 20px;
border-radius: 8px;
box-sizing: border-box;
background-color: #fff;
}

.loginPage p {
color: red;
text-align: left;
}
</style>
