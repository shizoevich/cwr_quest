<template>
    <img :src="default_image_url" :alt="default_alt" :class="default_classes">
</template>

<script>
    export default {

        data() {
            return {
                default_image_url: '../../images/pageloader.gif',
                default_alt: 'Loading...',
                default_classes: 'img-responsive inline-block'
            }
        },

        props: [
            'classes',
            'addClasses',
            'imageUrl',
            'imageAlt'
        ],

        mounted() {
            if(this.imageUrl) {
                this.default_image_url = this.imageUrl;
            }
            if(this.imageAlt) {
                this.default_alt = this.imageAlt;
            }
            if(this.classes) {
                this.default_classes = this.classes;
            }
            if(this.addClasses) {
                this.default_classes += " " + this.addClasses;
            }
        }
    }
</script>