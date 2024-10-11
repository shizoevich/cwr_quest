<template>
    <div @click="click" ref="burgerButton">
        <div :class="{ 'burger-icon': true, 'show': on }">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        click: {
            type: Function,
            required: true,
        },
        on: {
            type: Boolean,
            required: true,
        },
        setRef: {
            type: Function,
            required: true,
        }
    },

    mounted() {
        this.setRef(this.$refs.burgerButton);
    }
}
</script>

<style scoped>
.burger-icon {
    --animationDuration: 0.3s;
    --animationFillMode: forwards;

    cursor: pointer;
    margin: 0;
    width: 44px;
    height: 34px;
    display: flex !important;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
    padding: 9px;
    border-radius: 3px;
    border: 1px solid rgb(221, 221, 221);
    transition: background-color 0.3s;

    &:hover {
        background-color: rgb(221, 221, 221);
    }

    .bar {
        width: 100%;
        height: 2px;
        background-color: rgb(62, 72, 85);
        transition: opacity 0.3s;

        &:nth-child(1) {
            animation-name: crossBar1Off;
            animation-duration: var(--animationDuration);
            animation-fill-mode: var(--animationFillMode);
        }

        &:nth-child(2) {
            animation-name: crossBar2Off;
            animation-duration: var(--animationDuration);
            animation-fill-mode: var(--animationFillMode);
        }

        &:nth-child(3) {
            animation-name: crossBar3Off;
            animation-duration: var(--animationDuration);
            animation-fill-mode: var(--animationFillMode);
        }
    }
}

.burger-icon.show {

    .bar {
        &:nth-child(1) {
            animation-name: crossBar1On;
            animation-duration: var(--animationDuration);
            animation-fill-mode: var(--animationFillMode);
        }

        &:nth-child(2) {
            animation-name: crossBar2On;
            animation-duration: var(--animationDuration);
            animation-fill-mode: var(--animationFillMode);
        }

        &:nth-child(3) {
            animation-name: crossBar3On;
            animation-duration: var(--animationDuration);
            animation-fill-mode: var(--animationFillMode);
        }
    }
}

@keyframes crossBar1On {
    0% {
        transform: translateY(0) rotate(0);
    }

    50% {
        transform: translateY(6px) rotate(0);
    }

    100% {
        transform: translateY(6px) rotate(45deg);
    }
}

@keyframes crossBar1Off {
    0% {
        transform: translateY(6px) rotate(45deg);
    }

    50% {
        transform: translateY(6px) rotate(0);
    }

    100% {
        transform: translateY(0) rotate(0);
    }
}

@keyframes crossBar2On {
    0% {
        opacity: 1;
    }

    50% {
        opacity: 1;
    }

    100% {
        opacity: 0;
    }
}

@keyframes crossBar2Off {
    0% {
        opacity: 0;
    }

    50% {
        opacity: 0;
    }

    100% {
        opacity: 1;
    }
}

@keyframes crossBar3On {
    0% {
        transform: translateY(0) rotate(0);
    }

    50% {
        transform: translateY(-6px) rotate(0);
    }

    100% {
        transform: translateY(-6px) rotate(-45deg);
    }
}

@keyframes crossBar3Off {
    0% {
        transform: translateY(-6px) rotate(-45deg);
    }

    50% {
        transform: translateY(-6px) rotate(0);
    }

    100% {
        transform: translateY(0) rotate(0);
    }
}
</style>