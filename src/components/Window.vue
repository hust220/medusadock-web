<template>
  <div class="outer-window" :style="windowStyle">
    <div
       @dblclick="isFullScreen^=true"
       @mousedown.preventDefault="draggable=(isFullScreen?false:true)"
       style="z-index: 96; position: absolute; height: 20px; width: 100%; left: 0px; right: 0px; top: 0px">
    </div>
    <div
       @mousedown.preventDefault="resizePosition='top'" :style="topBorderStyle">
    </div>
    <div
       @mousedown.preventDefault="resizePosition='right'" :style="rightBorderStyle">
    </div>
    <div
       @mousedown.preventDefault="resizePosition='bottom'" :style="bottomBorderStyle">
    </div>
    <div
       @mousedown.preventDefault="resizePosition='left'" :style="leftBorderStyle">
    </div>
    <div
       @mousedown.preventDefault="resizePosition='left-top'" :style="leftTopBorderStyle">
    </div>
    <div
       @mousedown.preventDefault="resizePosition='right-top'" :style="rightTopBorderStyle">
    </div>
    <div
       @mousedown.preventDefault="resizePosition='left-bottom'" :style="leftBottomBorderStyle">
    </div>
    <div
       @mousedown.preventDefault="resizePosition='right-bottom'" :style="rightBottomBorderStyle">
    </div>
    <div class="inner-window">
      <div v-if="isFullScreen" class="zoom" @click="isFullScreen^=true"><i class="el-icon-remove-outline"></i></div>
      <div v-if="!isFullScreen" class="zoom" @click="isFullScreen^=true"><i class="el-icon-circle-plus-outline"></i></div>
      <slot></slot>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Window',

  data () {
    return {
      isFullScreen: true,
      left: 0,
      top: 0,
      width: 1000,
      height: 600,
      draggable: false,
      resizePosition: ''
    }
  },

  methods: {
    drag (e) {
      var v = this
      if (v.draggable) {
//        console.log(document.body.clientHeight)
//        console.log(document.body.clientWidth)
        v.left += e.movementX
        v.top += e.movementY
        if (v.left < 0) v.left = 0
        if (v.top < 0) v.top = 0
//        console.log(`${e.movementX}-${e.movementY} ${v.left}-${v.top}`)
      }
    },

    resize (e) {
      var v = this
      if (v.resizePosition.includes('top')) {
        v.top += e.movementY
        v.height -= e.movementY
        if (v.top < 0) {
          v.height += v.top
          v.top = 0
        }
      }
      if (v.resizePosition.includes('right')) {
        v.width += e.movementX
      }
      if (v.resizePosition.includes('bottom')) {
        v.height += e.movementY
      }
      if (v.resizePosition.includes('left')) {
        v.left += e.movementX
        v.width -= e.movementX
        if (v.left < 0) {
          v.width += v.left
          v.left = 0
        }
      }
    }
  },

  mounted () {
    var v = this
    window.addEventListener('mouseup', () => {
      v.draggable = false
      v.resizePosition = ''
    }, false)
    window.addEventListener('mousemove', (e) => {
      v.drag(e)
      v.resize(e)
    }, false)
  },

  computed: {
    windowStyle () {
      var v = this
      if (v.isFullScreen) {
        return {
          position: 'absolute',
          left: '0px',
          top: '0px',
          right: '0px',
          bottom: '0px'
        }
      } else {
        return {
          position: 'absolute',
          width: `${v.width}px`,
          height: `${v.height}px`,
          top: `${v.top}px`,
          left: `${v.left}px`
        }
      }
    },

    topBorderStyle () {
      var v = this
      var style = {
        zIndex: 97,
        position: 'absolute',
        height: '5px',
        width: '100%',
        top: '-2px'
      }
      if (!v.isFullScreen) style.cursor = 'ns-resize'
      return style
    },

    rightBorderStyle () {
      var v = this
      var style = {
        zIndex: 97,
        position: 'absolute',
        height: '100%',
        width: '5px',
        right: '-2px'
      }
      if (!v.isFullScreen) style.cursor = 'ew-resize'
      return style
    },

    bottomBorderStyle () {
      var v = this
      var style = {
        zIndex: 97,
        position: 'absolute',
        height: '5px',
        width: '100%',
        bottom: '-2px'
      }
      if (!v.isFullScreen) style.cursor = 'ns-resize'
      return style
    },

    leftBorderStyle () {
      var v = this
      var style = {
        zIndex: 97,
        position: 'absolute',
        height: '100%',
        width: '5px',
        left: '-2px'
      }
      if (!v.isFullScreen) style.cursor = 'ew-resize'
      return style
    },

    leftTopBorderStyle () {
      var v = this
      var style = {
        zIndex: 98,
        position: 'absolute',
        height: '7px',
        width: '7px',
        left: '-3px',
        top: '-3px'
      }
      if (!v.isFullScreen) style.cursor = 'nwse-resize'
      return style
    },

    rightTopBorderStyle () {
      var v = this
      var style = {
        zIndex: 98,
        position: 'absolute',
        height: '7px',
        width: '7px',
        right: '-3px',
        top: '-3px'
      }
      if (!v.isFullScreen) style.cursor = 'nesw-resize'
      return style
    },

    leftBottomBorderStyle () {
      var v = this
      var style = {
        zIndex: 98,
        position: 'absolute',
        height: '7px',
        width: '7px',
        left: '-3px',
        bottom: '-3px'
      }
      if (!v.isFullScreen) style.cursor = 'nesw-resize'
      return style
    },

    rightBottomBorderStyle () {
      var v = this
      var style = {
        zIndex: 98,
        position: 'absolute',
        height: '7px',
        width: '7px',
        right: '-3px',
        bottom: '-3px'
      }
      if (!v.isFullScreen) style.cursor = 'nwse-resize'
      return style
    }
  }
}
</script>

<style scoped>

.outer-window {
  box-shadow: 0 0 5px #888888;
}

.inner-window {
  overflow: auto;
  position: absolute;
  left: 0px;
  right: 0px;
  bottom: 0px;
  top: 0px;
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
