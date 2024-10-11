export default {
    methods: {
        checkDevelopMode() {
            let isDevelop = (typeof this.$route.query.develop_mode !== 'undefined')
                && this.$route.query.develop_mode === 'true';
            this.$store.state.develop_mode = isDevelop;

            return isDevelop;
        }
    },
    mounted() {
        this.checkDevelopMode();
    }
}