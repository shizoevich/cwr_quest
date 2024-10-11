export default function init({selector = '#tinymce-editor', substitutions = [], onInit = null}) {
    const toolbar = substitutions.length
        ? 'undo redo | blocks | substitutionsButton | bold italic link | alignleft aligncenter alignright | indent outdent | bullist numlist | image media'
        : 'undo redo | blocks | bold italic link | alignleft aligncenter alignright | indent outdent | bullist numlist | image media';

    tinymce.init({
        selector: selector,
        plugins: 'image media lists link noneditable',
        toolbar: toolbar,
        promotion: false,
        menubar: false,
        height: 200,
        content_style: `
            .substitution {
                padding: 4px 8px;
                color: #555555;
                font-size: 12px;
                border-radius: 3px;
                border: 1px solid #ccc;
            }
        `,
        setup: (editor) => onEditorSetup(editor, substitutions, onInit)
    });
}

function onEditorSetup(editor, substitutions, onInit) {
    if (onInit) {
        editor.on('init', onInit);
    }

    registerSubstitutionsButton(editor, substitutions);
}

function registerSubstitutionsButton(editor, substitutions) {
    if (!substitutions || !substitutions.length) {
        return;
    }

    // Don't forget to update SUBSTITUTION_PATTERN on server after substitution format will be changed
    const formatSubstitution = (key) => `<span class="substitution mceNonEditable">${key}</span>`;

    editor.ui.registry.addMenuButton('substitutionsButton', {
        text: 'Substitutions',
        fetch: (callback) => {
            const items = substitutions.map((substitution) => {
                return {
                    type: 'menuitem',
                    text: substitution.label,
                    onAction: (_) => editor.insertContent(formatSubstitution(substitution.key))
                }
            });
            
            callback(items);
        }
    });
}
