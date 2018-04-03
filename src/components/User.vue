<template>
  <el-dialog :visible="visible" @update:visible="updateVisible">
    <table>
      <tr><td width="150px">User ID:</td><td v-text="user.id"></td></tr>
      <tr><td>User Name:</td><td v-text="user.username"></td></tr>
      <tr><td>Email:</td><td v-text="user.email"></td></tr>
    </table>
  </el-dialog>
</template>

<script>
export default {
  name: 'user',

  computed: {
    user () {
      return this.$store.state.user
    }
  },

  props: ['visible'],

  methods: {
    updateVisible (val) {
      this.$emit('update:visible', val)
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
