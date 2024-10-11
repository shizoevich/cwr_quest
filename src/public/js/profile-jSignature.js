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
/******/ 	return __webpack_require__(__webpack_require__.s = 1749);
/******/ })
/************************************************************************/
/******/ ({

/***/ 1605:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__Alert__ = __webpack_require__(27);


$(document).ready(function () {
    var $signature = $("#signature");
    var signatureIsEmpty = true;
    $signature.jSignature().bind('change', function (e) {
        signatureIsEmpty = false;
        $('#signature').removeClass('input-error');
        $('hr.signature-line').removeClass('signature-line-error');
        $('#status-alert').empty();
    });

    $('#export-signature').click(function () {
        var _this = this;

        if (signatureIsEmpty) {
            __WEBPACK_IMPORTED_MODULE_0__Alert__["default"].show('#status-alert', "Please add your signature.", 'alert-danger');
            $('#signature').addClass('input-error');
            $('hr.signature-line').addClass('signature-line-error');
            return false;
        }

        $(this).prop('disabled', true);
        $('#clear-signature').prop('disabled', true);
        $('#status-alert').empty();
        $('#signature').removeClass('input-error');
        $('hr.signature-line').removeClass('signature-line-error');

        var datapair = $signature.jSignature("getData", "image");
        var imageSrc = "data:" + datapair[0] + "," + datapair[1];
        var data = {
            signature: imageSrc,
            userID: $(this).data('userid') || null,
            _token: $('input[name=_token]').val()
        };

        var url = '/users/save-signature';
        if ($(this).data('token')) {
            url = '/signature/' + $(this).data('token');
        }

        axios({
            method: 'post',
            url: url,
            data: data
        }).then(function (response) {
            if (response.data && response.data.redirectTo) {
                window.location.href = response.data.redirectTo;
            } else {
                var message = response.data && response.data.message;
                if (!message) {
                    message = 'Signature successfully uploaded.';
                }
                __WEBPACK_IMPORTED_MODULE_0__Alert__["default"].show('#status-alert', message, 'alert-success');
            }
        }).catch(function (err) {
            var message = err.response && err.response.data && err.response.data.message;
            if (!message) {
                message = 'Oops, something went wrong!';
            }
            __WEBPACK_IMPORTED_MODULE_0__Alert__["default"].show('#status-alert', message, 'alert-danger');
        }).finally(function () {
            $(_this).prop('disabled', false);
            $('#clear-signature').prop('disabled', false);
        });
    });

    $('#clear-signature').click(function () {
        $signature.jSignature('clear');
        signatureIsEmpty = true;
    });

    var elView = document.getElementById('view');
    if (elView !== null && elView !== undefined) {
        elView.scrollIntoView(false);
    }
});

/***/ }),

/***/ 1749:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(1605);


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