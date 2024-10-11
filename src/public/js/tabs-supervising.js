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
/******/ 	return __webpack_require__(__webpack_require__.s = 1752);
/******/ })
/************************************************************************/
/******/ ({

/***/ 1608:
/***/ (function(module, exports) {



$(document).ready(function () {
    var superviseesTable = $('#supervisees_table').DataTable({
        ajax: {
            url: window.location.pathname + '/supervisees/api',
            dataSrc: ''
        },
        'paging': false,
        'lengthChange': false,
        'searching': false,
        'ordering': true,
        'info': false,
        'autoWidth': false,
        order: [[1, 'asc']],
        columns: [{ data: 'index', orderable: false }, {
            data: 'provider_name',
            orderable: true,
            render: function render(data, type, row) {
                if (type === 'display') {
                    if (!row.is_active) {
                        return data + ' <span class=\'text-danger\' style="margin-left:5px;">Access suspended</span>';
                    }
                }

                return data;
            }
        }, { data: 'attached_at', orderable: true }, { data: 'patients_count', orderable: true }],
        hideEmptyCols: true,
        createdRow: function createdRow(row, data) {
            $(row).addClass(data.rowClass);
        },
        fnDrawCallback: function fnDrawCallback() {
            updateTableRowNumbers(this);
        }
    });

    function updateTableRowNumbers(el) {
        var api = el.api();
        var startIndex = api.context[0]._iDisplayStart;

        api.column(0, { search: "applied", order: "applied" }).nodes().each(function (cell, i) {
            cell.innerHTML = startIndex + i + 1;
        });
    }

    $('#is_supervisor-yes').change(function () {
        storeIsSupervisor(JSON.parse(this.value), this.dataset.providerId);
        toggleSupervisorsSelect(JSON.parse(this.value));
    });

    $('#is_supervisor-no').change(function () {
        storeIsSupervisor(JSON.parse(this.value), this.dataset.providerId);
        toggleSupervisorsSelect(JSON.parse(this.value));
        removeSuperviseesTable();
    });

    function removeSuperviseesTable() {
        $('#supervisees_table_wrapper').remove();
    }

    function storeIsSupervisor(value, providerId) {
        disableIsSupervisorInputs(true);

        axios.post('/api/providers/' + providerId + '/is-supervisor', { 'is_supervisor': value }).then(function () {
            var statusString = '';
            if (value) {
                statusString = 'enabled';
            } else {
                statusString = 'disabled';
            }

            var message = 'Supervisor status was ' + statusString + '.';

            setSupervisorMessage(message, 'success');
        }).catch(function () {
            var message = 'Whoops, looks like something went wrong.';
            setSupervisorMessage(message, 'error');
        }).finally(function () {
            disableIsSupervisorInputs(false);
        });
    }

    function disableIsSupervisorInputs(disabled) {
        $('#is_supervisor-yes').attr('disabled', disabled);
        $('#is_supervisor-no').attr('disabled', disabled);
        $('#supervisors').attr('disabled', disabled);
    }

    function setSupervisorMessage(text, status) {
        var messageBox = $('#supervisor_message');
        messageBox.removeClass();
        messageBox.addClass(status === 'error' ? 'error-message' : 'success-message');
        messageBox.text(text);

        setTimeout(function () {
            resetSupervisorMessage();
        }, 10000);
    }

    function resetSupervisorMessage() {
        var messageBox = $('#supervisor_message');
        messageBox.removeClass();
        messageBox.text('');
    }

    function toggleSupervisorsSelect(value) {
        $('#supervisors').val('');
        if (value) {
            $('.supervisors-field').addClass('hidden');
        } else {
            $('.supervisors-field').removeClass('hidden');
        }
    }

    $('#supervisors').change(function () {
        $('#confirmDialogSupervising #provider_id').val($(this).data('providerId'));
        $('#confirmDialogSupervising #supervisor_id').val($(this).val());

        var supervisorName = $(this).find('option:selected').text();
        var userName = $('.profile-control').find('.provider-name').text();
        var message = 'Are you sure that you want to assign supervisor <span class="text-bold">' + supervisorName + '</span> to <span class="text-bold">' + userName + '</span>?';
        if (!$(this).val()) {
            message = 'Are you sure that you want to unassign supervisor from <span class="text-bold">' + userName + '</span>?';
        }

        $('#confirmDialogSupervising #assign_message').html(message);
        $('#confirmDialogSupervising').modal('show');
    });

    $('#confirm-selection-supervising').click(function () {
        var providerId = $('#confirmDialogSupervising #provider_id').val();
        var supervisorId = $('#confirmDialogSupervising #supervisor_id').val();
        var date = $('#confirmDialogSupervising input[name="date"]').val();

        axios.post('/api/providers/' + providerId + '/attach-supervisor', { 'supervisor_id': supervisorId, date: date }).then(function () {
            var statusString = '';
            if (supervisorId) {
                statusString = 'assigned';
            } else {
                statusString = 'unassigned';
            }

            var message = 'Supervisor was ' + statusString + ' successfully.';

            setSupervisorMessage(message, 'success');

            $('#supervisors').data('initialValue', supervisorId);
        }).catch(function (error) {
            var message = 'Whoops, looks like something went wrong.';
            if (error.response.data && error.response.data.message) {
                message = error.response.data.message;
            }
            setSupervisorMessage(message, 'error');

            $('#supervisors').val($('#supervisors').data('initialValue'));
        });

        $('#confirmDialogSupervising').modal('hide');
    });

    $('#cancel-selection-supervising').click(function () {
        $('#supervisors').val($('#supervisors').data('initialValue'));
        $('#confirmDialogSupervising').modal('hide');
    });
});

/***/ }),

/***/ 1752:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(1608);


/***/ })

/******/ });