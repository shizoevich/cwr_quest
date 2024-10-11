<template>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h1 class="salary-title text-center">
                    Timesheets
                </h1>
                <div class="content-wrapper">
                    <timesheets-panel/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import TimesheetsPanel from './panel/Panel';

    export default {
        name: 'Timesheets',
        components: {TimesheetsPanel}
    }
</script>