<template>
    <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
         id="confirm-document-closing-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    Are you sure you want to close the window? All changes will be lost.
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirm-closing" class="btn btn-danger"
                            @click.prevent="closeDocumentModal()">Yes
                    </button>
                    <button type="button" class="btn btn-secondary"
                            @click.prevent="closeDocumentConfirmDialog(modalId)"
                    >No
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            modalId: String,
        },
        data() {
            return {
                id: this._uid
            }
        },
        methods: {
            closeDocumentModal(){
                $('#confirm-document-closing-modal').modal('hide');

                this.$store.state.currentDocument = null;
                this.$store.state.currentDocumentEditingStatus = null;
                this.$store.state.currentDocumentSignature = null;
            },
            closeDocumentConfirmDialog(id){
                $('#confirm-document-closing-modal').modal('hide');

                setTimeout(function() {
                    $('#'+id).modal('show');
                }, 400);
            },
//            cleanDocument(data){
//                console.log('Start clearing');
//                console.log(data);
//                Object.keys(data).forEach(function(k) {
//
//                    if(typeof data[k] == 'object'){
//
//                        data[k] = this.cleanDocument(data[k])
//                    }
//                    else{
//                        data[k] = null;
//                    }
//                });
//                console.log(data);
//                return data;
//            },
        },
    }
</script>