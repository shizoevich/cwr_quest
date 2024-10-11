/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;
/******/
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1753);
/******/ })
/************************************************************************/
/******/ ({

/***/ 1609:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__plugins_tinymce_editor__ = __webpack_require__(737);


$(document).ready(function () {
    var substitutions = [];
    function fetchEditorData() {
        axios({
            method: 'get',
            url: '/update-notification-substitutions/api'
        }).then(function (response) {
            substitutions = response.data || [];
        }).catch(function (err) {
            console.log('error: ', err);
        }).finally(function () {
            __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_0__plugins_tinymce_editor__["a" /* default */])({
                substitutions: substitutions,
                onInit: onEditorInit
            });
        });
    }

    fetchEditorData();

    function onEditorInit() {
        var preloader = $('.preloader');
        if (preloader.length) {
            preloader.remove();
        }
    }

    $(document).on('click', '#preview-btn', function () {
        if (!validateForm()) {
            return;
        }

        var previewDialog = $('#preview-dialog');
        if (!previewDialog.length) {
            return;
        }

        var titleValue = $('#notification_title').val();
        var contentValue = tinymce.get('tinymce-editor').getContent();

        previewDialog.find('.modal-head').text(titleValue);
        previewDialog.find('.modal-body').html(contentValue);
        previewDialog.modal('show');
    });

    function validateForm() {
        $('.form-group.has-error').each(function () {
            $(this).removeClass('has-error');
            $(this).find('.help-block.with-errors').html('');
        });

        var errors = [];
        var nameValue = $('#name').val();
        if (!nameValue) {
            errors.push({
                field: '#name',
                message: 'The name field is required.'
            });
        }
        var titleValue = $('#notification_title').val();
        if (!titleValue) {
            errors.push({
                field: '#notification_title',
                message: 'The title field is required.'
            });
        }
        var contentValue = tinymce.get('tinymce-editor').getContent();
        if (!contentValue) {
            errors.push({
                field: '#tinymce-editor',
                message: 'The content field is required.'
            });
        }

        errors.forEach(function (error) {
            var parent = $(error.field).parents('.form-group');
            if (!parent || !parent.length) {
                return;
            }
            parent.addClass('has-error');
            parent.find('.help-block.with-errors').html('\n                <strong>' + error.message + '</strong>\n            ');
        });

        return !errors.length;
    }

    $(document).on('click', '#submit-btn', function () {
        $('#template-form').trigger('submit');
    });

    $(document).on('click', '#cancel-btn', function () {
        window.history.back();
    });
});

/***/ }),

/***/ 1753:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(1609);


/***/ }),

/***/ 737:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (immutable) */ __webpack_exports__["a"] = init;
function init(_ref) {
    var _ref$selector = _ref.selector,
        selector = _ref$selector === undefined ? '#tinymce-editor' : _ref$selector,
        _ref$substitutions = _ref.substitutions,
        substitutions = _ref$substitutions === undefined ? [] : _ref$substitutions,
        _ref$onInit = _ref.onInit,
        onInit = _ref$onInit === undefined ? null : _ref$onInit;

    var toolbar = substitutions.length ? 'undo redo | blocks | substitutionsButton | bold italic link | alignleft aligncenter alignright | indent outdent | bullist numlist | image media' : 'undo redo | blocks | bold italic link | alignleft aligncenter alignright | indent outdent | bullist numlist | image media';

    tinymce.init({
        selector: selector,
        plugins: 'image media lists link noneditable',
        toolbar: toolbar,
        promotion: false,
        menubar: false,
        height: 200,
        content_style: '\n            .substitution {\n                padding: 4px 8px;\n                color: #555555;\n                font-size: 12px;\n                border-radius: 3px;\n                border: 1px solid #ccc;\n            }\n        ',
        setup: function setup(editor) {
            return onEditorSetup(editor, substitutions, onInit);
        }
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
    var formatSubstitution = function formatSubstitution(key) {
        return '<span class="substitution mceNonEditable">' + key + '</span>';
    };

    editor.ui.registry.addMenuButton('substitutionsButton', {
        text: 'Substitutions',
        fetch: function fetch(callback) {
            var items = substitutions.map(function (substitution) {
                return {
                    type: 'menuitem',
                    text: substitution.label,
                    onAction: function onAction(_) {
                        return editor.insertContent(formatSubstitution(substitution.key));
                    }
                };
            });

            callback(items);
        }
    });
}

/***/ })

/******/ });