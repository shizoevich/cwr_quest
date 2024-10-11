<template>
    <div
        id="update-notification"
        class="modal modal-vertical-center fade"
        data-backdrop="static"
        data-keyboard="false"
        role="dialog"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button v-if="!isNotificationRequired" class="close" aria-label="Close" @click="onClose">
                        <span aria-hidden="true">&times;</span>
                    </button>

                    <h4 v-if="selectedNotification && selectedNotification.title" class="modal-head">
                        {{ selectedNotification.title }}
                    </h4>
                </div>

                <div v-if="selectedNotification && selectedNotification.content" v-html="selectedNotification.content" class="modal-body">
                </div>
                
                <div class="modal-footer">
                    <div class="form-group notification-viewed-field">
                        <label>
                            <input type="checkbox" v-model="notificationViewed">
                            <div v-if="selectedNotification && selectedNotification.userName">
                                I, {{ selectedNotification.userName }}, certify that I have read and understand the contents of this notification. <br/>
                                By checking this box, I acknowledge the following:
                                <ul>
                                    <li>I have reviewed and understand the information provided above.</li>
                                    <li>My electronic signature will be recorded and stored on file for compliance purposes.</li>
                                </ul>
                            </div>
                        </label>
                    </div>
                    <div>
                        <button :disabled="!notificationViewed" class="btn btn-primary" @click="onConfirm">Confirm</button>
                        <button v-if="!isNotificationRequired" class="btn btn-secondary" @click="onRemindLater">Remind later</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BootstrapModal from '../mixins/bootstrap-modal';

    export default {
        mixins: [
            BootstrapModal,
        ],
        data() {
            return {
                notifications: [],
                notificationViewed: false,
            };
        },
        computed: {
            selectedNotification() {
                return this.notifications && this.notifications.length ? this.notifications[0] : null;
            },
            isNotificationRequired() {
                return this.selectedNotification && this.selectedNotification.is_required;
            }
        },
        mounted() {
            if(this.$route.path.includes('salary/time-records') || this.$route.path.includes('past-appointments')) {
                return;
            }

            // timeout to fix modals overlapping
            setTimeout(() => {
                this.loadUpdateNotifications();
            }, 1000);
        },
        methods: {
            loadUpdateNotifications() {
                axios.get('/update-notifications/available-list').then((response) => {
                    if (!response.data || !response.data.length) {
                        return;
                    }

                    this.notifications = response.data;
                    this.$nextTick(() => {
                        this.openWithoutOverlapping();
                        this.listenCloseEvent();
                    });
                });
            },
            onClose() {
                this.closeModal();

                if (!this.selectedNotification || this.selectedNotification.is_required) {
                    return;
                }

                this.markAsViewed();
            },
            onConfirm() {
                this.closeModal();
                this.markAsViewed();
            },
            onRemindLater() {
                this.closeModal();
                this.remindLater();
            },
            markAsOpened() {
                if (!this.selectedNotification) {
                    return;
                }

                axios.post(`/update-notifications/${this.selectedNotification.id}/mark-as-opened`);
            },
            markAsViewed() {
                if (!this.selectedNotification) {
                    return;
                }

                axios.post(`/update-notifications/${this.selectedNotification.id}/mark-as-viewed`)
                    .then(() => {
                        if (!window.notificationsTable) {
                            return;
                        }
                        
                        window.notificationsTable.ajax.reload();
                    });
            },
            remindLater() {
                if (!this.selectedNotification) {
                    return;
                }

                axios.post(`/update-notifications/${this.selectedNotification.id}/remind-later`);
            },
            openModal() {
                $('#update-notification').modal('show');
                this.markAsOpened();
            },
            closeModal() {
                $('#update-notification').modal('hide');
                this.resetData();
            },
            listenCloseEvent() {
                $('#update-notification').on('hidden.bs.modal', () => {
                    this.notifications.shift();
                
                    if (!this.notifications.length) {
                        return;
                    }
                    
                    this.$nextTick(() => {
                        this.openWithoutOverlapping();
                    });
                })
            },
            resetData() {
                this.notificationViewed = false;
            }
        },
    }
</script>
