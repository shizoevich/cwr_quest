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
/******/ 	return __webpack_require__(__webpack_require__.s = 1754);
/******/ })
/************************************************************************/
/******/ ({

/***/ 1610:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__Alert__ = __webpack_require__(27);


$(document).ready(function () {
    var templatesTable = $('#templatesTable').DataTable({
        ajax: {
            url: window.location.pathname + '/api',
            dataSrc: ''
        },
        "searching": true,
        "pageLength": 15,
        "lengthChange": false,
        columns: [{ data: 'id', orderable: false, searchable: false }, { data: 'name', orderable: true, searchable: true }, { data: 'notification_title', orderable: true, searchable: true }, {
            orderable: false,
            searchable: false,
            render: function render(data, type, row) {
                return '\n                        <a href="/update-notifications/create?template_id=' + row.id + '" style="text-decoration:none;margin-right:10px;" onclick="event.stopPropagation()">\n                            <span class="glyphicon glyphicon-send"></span>\n                        </a>\n                        <a href="/update-notification-templates/' + row.id + '/edit" style="text-decoration:none;margin-right:10px;" onclick="event.stopPropagation()">\n                            <span class="glyphicon glyphicon-pencil"></span>\n                        </a>\n                        <a id="delete-template" href="" style="text-decoration:none;" data-id="' + row.id + '">\n                            <span class="glyphicon glyphicon-remove"></span>\n                        </a>\n                    ';
            }
        }],
        hideEmptyCols: true,
        createdRow: function createdRow(row, data) {
            $(row).addClass(data.rowClass);
        }
    });

    $(document).on('click', '#templatesTable > tbody > tr', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var data = templatesTable.row(this).data();
        if (!data) {
            return;
        }

        var previewDialog = $('#preview-dialog');
        if (!previewDialog.length) {
            return;
        }

        previewDialog.find('.modal-head').text(data.notification_title);
        previewDialog.find('.modal-body').html(data.notification_content);
        previewDialog.modal('show');
    });

    var selectedTemplateId = null;
    $(document).on('click', '#delete-template', function (event) {
        event.preventDefault();
        event.stopPropagation();
        selectedTemplateId = $(this).data('id');
        $('#confirm-deleting-dialog').modal('show');
    });

    $('#cancel-deleting-btn').on('click', function () {
        $('#confirm-deleting-dialog').modal('hide');
        selectedTemplateId = null;
    });

    $('#confirm-deleting-btn').on('click', function () {
        $('#confirm-deleting-dialog').modal('hide');

        axios({
            method: 'delete',
            url: '/update-notification-templates/' + selectedTemplateId
        }).then(function () {
            __WEBPACK_IMPORTED_MODULE_0__Alert__["default"].show('#status-alert', 'Template deleted successfully.', 'alert-success');
            selectedTemplateId = null;
            templatesTable.ajax.reload();
        }).catch(function (err) {
            console.log('error: ', err);
            __WEBPACK_IMPORTED_MODULE_0__Alert__["default"].show('#status-alert', 'Oops, something went wrong!', 'alert-danger');
        });
    });
});

/***/ }),

/***/ 1754:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(1610);


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