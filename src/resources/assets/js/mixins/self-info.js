export default {
    methods : {

    },
    computed: {
        isUserAdmin() {
            return this.$store.state.isUserAdmin;
        },
        isUserSecretary() {
            return this.$store.state.isUserSecretary;
        },
    },
}