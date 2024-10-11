<template>
  <el-popover
    placement="top-start"
    :title="title"
    trigger="hover"
    :disabled="!content"
    :offset="offset">
    <i class="el-icon-question custom-help-icon" slot="reference" :style="{fontSize: iconFontSize + 'px'}"></i>
    <div v-html="content"></div>
  </el-popover>
</template>

<script>
export default {
  props: {
    title: {
      required: false,
      type: String
    },
    content: {
      type: String,
      default: "",
    },
    offset: {
        type: Number,
        default: 0,
    },
    iconFontSize: {
        type: Number,
        default: 14,
    }
  }
}
</script>

<style scoped>
  .custom-help-icon {
    font-size: 14px;
    cursor: pointer;
  }
  .custom-help-icon:hover {
    opacity: 0.7;
  }
</style>