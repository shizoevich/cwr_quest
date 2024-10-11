<template>
    <div>
        <el-button @click="openModal" type="primary">Schedule Appointment</el-button>
    </div>
</template>

<script>
    export default {
        name: 'ScheduleAppointments',
        methods: {
            openModal() {
                this.$emit('openScheduleModal');
            }
        }
    }
</script>