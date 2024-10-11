<template>
    <div class="loading-overlay">
        <el-spinner :size="150"></el-spinner>
    </div>
</template>
  
<script>

export default {
};
</script>
  
<style lang="scss">
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;

    .el-spinner-inner {
        circle {
            stroke: rgb(64, 158, 255);
        }
    }
}
</style>
  