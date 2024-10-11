<template>
    <div class="thanks-page">
        <div class="container">
            <h1>Thank you!</h1>
            <router-link to="/chart" class="el-button el-button--primary"><span>Go to Chart</span></router-link>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'TimeRecordsThanksPage'
    }
</script>

<style lang="scss">
    .thanks-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;

        .container {
            text-align: center;
        }

        h1 {
            font-weight: bold;
            font-size: 48px;
            margin-bottom: 30px;
        }

        .el-button {
            text-decoration: none;
        }
    }
</style>