export default {
    methods: {
        openWithoutOverlapping() {
            if (!this.checkOpenModals()) {
                this.openModal();
                return;
            }

            let timerId = setInterval(() => {
                if (this.checkOpenModals()) {
                    return;
                }

                this.openModal();
                clearInterval(timerId);
            }, 1000);
        },
        checkOpenModals() {
            const modal = document.querySelector('.modal.in');
            return !!modal;
        },
    },
}