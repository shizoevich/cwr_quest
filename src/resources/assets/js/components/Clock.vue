<template>
    <div>
        <div id="page-content-wrapper" v-if="wrapper">
            <div id="page-content" class="content-with-footer">
                <slot />
                <div class="section section-clock">
                    <h2 class="current-time">{{ currentTime }}</h2>
                </div>
            </div>
        </div>
        <div class="section section-clock" v-else>
            <h2 class="current-time">{{ currentTime }}</h2>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                currentTime: this.getCurrentTime(),
                wrapper: true,
                formattedTime: '',
            }
        },

        props: [
            'withWrapper'
        ],

        mounted() {
            if(typeof this.withWrapper === 'boolean') {
                this.wrapper = this.withWrapper;
            }

            window.setInterval(() => {
                this.updateCurrentTime();
            }, 1000);
        },

        methods: {
            getCurrentTime() {
                return this.$moment().format('hh:mm:ss A');
            },

            updateCurrentTime() {
                const newTime = this.addOneSecond(this.currentTime);
                this.currentTime = newTime;
            },

            addOneSecond(time) {
                const currentTime = moment(time, 'hh:mm:ss A');
                currentTime.add(1, 'seconds');
                return currentTime.format('hh:mm:ss A');
            },

            formatDate() {
                let hours = this.currentTime.getHours();
                let minutes = this.currentTime.getMinutes();
                let seconds = this.currentTime.getSeconds();
                let ampm = (hours > 11) ? "PM" : "AM";
                if(hours > 12) {
                    hours -= 12;
                } else if(hours === 0) {
                    hours = "12";
                }
                if(hours < 10) {
                    hours = "0" + hours;
                }
                if(minutes < 10) {
                    minutes = "0" + minutes;
                }
                if(seconds < 10) {
                    seconds = "0" + seconds;
                }
                this.formattedTime =  hours + ":" + minutes + ":" + seconds + " " + ampm;
            }
        }
    }
</script>

<style scoped>
    .current-time {
        text-align: center;
        font-weight: 700;
        margin-top: 22px;
        margin-bottom: 22px;
    }
</style>