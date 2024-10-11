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
/******/ 	return __webpack_require__(__webpack_require__.s = 1748);
/******/ })
/************************************************************************/
/******/ ({

/***/ 1604:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__Alert__ = __webpack_require__(27);


$(document).ready(function () {

    $("input.user-photo").each(function (i, el) {
        var inputClass = 'danger';
        if ($(el).data('exists') !== undefined) {
            inputClass = 'success';
        }
        $(el).fileinput({
            browseClass: "btn btn-primary btn-" + inputClass,
            showCaption: false,
            showRemove: false,
            showUpload: false,
            browseLabel: 'Add Picture',
            browseIcon: '',
            showPreview: false
        });
    }).change(function () {
        $($(this).parents('form.user-photo-form')).trigger('submit');
    });

    $('form.user-photo-form').submit(function () {
        var fd = new FormData(this);
        var photo = $(this).find('input[type=file]');
        fd.append("photo", $(photo).prop('files')[0]);
        fd.append("userID", $(photo).data('userid'));
        var obj = this;

        $.ajax({
            url: '/users/save-photo',
            type: 'POST',
            data: fd,
            cache: false,
            processData: false,
            contentType: false,
            beforeSend: function beforeSend() {
                $('#status-alert').empty();
            },
            success: function success(response) {
                $($(obj).find('.help-block-xs span')).empty();
                $($(obj).find('.btn-file')).removeClass('btn-danger').addClass('btn-success');
                $(obj).parents('tr').find('td.download-picture-block button').prop('disabled', false);
                __WEBPACK_IMPORTED_MODULE_0__Alert__["default"].show('#status-alert', response.message, 'alert-success');
            },
            error: function error(response) {
                $($(obj).find('.help-block-xs span')).text(response.responseJSON.photo[0]);
            }
        });
        return false;
    });

    $('.file-input .btn-file span.hidden-xs').removeClass('hidden-xs');
});

/***/ }),

/***/ 1748:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(1604);


/***/ }),

/***/ 27:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Alert = function () {
    function Alert() {
        _classCallCheck(this, Alert);
    }

    _createClass(Alert, null, [{
        key: 'show',
        value: function show(appendTo, text, className) {
            var html = '<div id="status-alert" class="alert alert-dismissible ' + className + '" role="alert">' + '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' + text + '</div>';
            $(appendTo).html(html);
        }
    }]);

    return Alert;
}();

/* harmony default export */ __webpack_exports__["default"] = (Alert);
;

/***/ })

/******/ });