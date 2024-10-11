export default {
    methods: {
        getDocumentTypesHtmlTree(doc_types) {
            for (let k in doc_types) {
                this.getDocumentTypesHtmlTreeRec(doc_types[k], 0);
            }
        },
        getDocumentTypesHtmlTreeRec(doc_types, level) {
            let disabled = !doc_types.clickable ? "class='select-head' disabled" : "";
            let indent = '';
            for (let s = 0; s < level; s++) {
                indent += '&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            if (doc_types.type === 'Other') {
                this.other_type_id = doc_types.id;
            }
            this.document_types_html += "<option " + disabled + " value='" + doc_types.id + "'>" + indent + doc_types.type + "</option>";
            level++;
            for (let i in doc_types.childs) {
                this.getDocumentTypesHtmlTreeRec(doc_types.childs[i], level);
            }
        },

        getDocumentType(doc) {
            if (doc.document_type === 'Other') {
                if(doc.other_document_type) {
                    return doc.other_document_type;
                } else {
                    return doc.document_type;
                }
            } else {
                return doc.document_type;
            }
        },
        getFileIcon(note) {
            if(note.preview) {

                return note.preview;
            }

            let name = note.original_document_name;

            let exists_icons = [
                '7z',
                'rar',
                'zip',
                'word',
                'excel',
                'pdf',
                'image'
            ];
            let extension = this.getFileExt(name);
            switch (extension) {
                case 'doc':
                case 'docx':
                    extension = 'word';
                    break;
                case 'xls':
                case 'xlsx':
                    extension = 'excel';
                    break;
                case 'png':
                case 'jpg':
                case 'jpeg':
                case 'gif':
                    extension = 'image';
                    break;
            }
            if (exists_icons.indexOf(extension) !== -1) {
                this.default_document_previews[note.aws_document_name] = "/images/file_type/" + extension + ".png";
            } else {
                this.default_document_previews[note.aws_document_name] = "/images/file_type/default.png";
            }
        },
        getFileExt(name) {
            let extension = name.split('.');
            extension = extension[extension.length - 1];
            return extension.toLowerCase();
        },
        isFileHasPreview(name) {
            let extension = this.getFileExt(name);
            let extensions = [
                'pdf',
                'png',
                'jpg',
                'jpeg',
                'gif'
            ];
            return extensions.indexOf(extension) !== -1;
        },
    },
    computed: {
        document_previews() {
            return this.$store.state.documents_preview;
        },
    },
}