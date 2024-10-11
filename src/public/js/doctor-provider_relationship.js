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
/******/ 	return __webpack_require__(__webpack_require__.s = 1743);
/******/ })
/************************************************************************/
/******/ ({

/***/ 1599:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__Alert__ = __webpack_require__(27);


$(document).ready(function () {
    var prevProviderID;
    $('.doctor-provider').on('focus', function () {
        prevProviderID = $(this).val();
        if (prevProviderID === null) {
            prevProviderID = -1;
        }
    }).change(function () {
        $('#user_id').val($(this).data('userid'));
        $('#provider_id').val($(this).val());
        $('#provider-name').text($(this).find('option:selected').text());
        $('#user-name').text($(this).parents('tr').find('.user-email').text());
        $('#confirmDialog').modal('show');
    });

    $('#cancel-selection').click(function () {
        $('.doctor-provider[data-userid=' + $('#user_id').val() + "] option[value=" + prevProviderID + ']').prop('selected', true);
    });

    $('#confirm-selection').click(function () {
        var data = {
            userId: $('#user_id').val(),
            providerId: $('#provider_id').val()
        };

        $.ajax({
            type: 'post',
            url: '/dashboard/doctors',
            data: data,
            cache: false,
            error: function error(response) {
                console.log(response);
                var responseJSON = response.responseJSON;
                var message = 'Whoops, looks like something went wrong.';
                if (responseJSON.providerId) {
                    message = responseJSON.providerId[0];
                } else if (responseJSON.userId) {
                    message = responseJSON.userId[0];
                }
                $('.doctor-provider[data-userid=' + $('#user_id').val() + "]").parent().children('.error-message').text(message);
            },
            success: function success(response) {
                console.log(response);
                if (!response.success) {
                    $('.doctor-provider[data-userid=' + $('#user_id').val() + "]").parent().children('.error-message').text(response.errorMessage);
                } else {
                    $('.doctor-provider[data-userid=' + $('#user_id').val() + "]").parent().children('.error-message').empty();
                    $('td[data-userid=' + data.userId + ']').parents('tr').find('a, button, input, a, .btn-file').each(function (i, el) {
                        $(el).removeClass('disabled');
                        $(el).prop('disabled', false);
                        $(el).removeAttr('disabled');
                        if ($(el).hasClass('add-signature')) {
                            $(el).removeClass('btn-danger').addClass('btn-success');
                        }
                    });
                    $('td[data-userid=' + data.userId + '] button').remove();
                    $('td[data-userid=' + data.userId + '] div.checkbox-disable-user').css({ 'display': 'block' });
                }
            }
        });
        $('#confirmDialog').modal('hide');
    });

    $('.show-confirm-deletion-user-modal').click(function () {
        var userID = $(this).data('user');
        $('#confirm-deletion-user-modal #user-id').val(userID);
        $('#confirm-deletion-user-modal').modal('show');
    });

    $('#confirm-deletion-user').click(function () {
        var data = {
            user_id: $('#confirm-deletion-user-modal #user-id').val(),
            _token: $('input[name=_token]').val()
        };
        $.ajax({
            type: 'post',
            url: '/dashboard/users/delete',
            cache: false,
            data: data,
            success: function success(response) {
                console.log(response);
                if (response.success) {
                    window.location.reload();
                    /*$('.show-confirm-deletion-user-modal[data-user='+data.user_id+']').parents('tr').remove();
                    $('#confirm-deletion-user-modal').modal('hide');
                    if(response.message) {
                        Alert.show('#status-alert', response.message, 'alert-success');
                    }*/
                }
            }
        });
    });

    var $userToDisabling = null;
    $(document).on('click', '.enable-disable', function () {
        $userToDisabling = $(this);
        var mode = $(this).data('mode');
        // $('#disable-mode').text(mode);
        $('#user-name2').text($userToDisabling.parents('tr').find('.user-email').text());
        $('#confirmDisablingDialog').modal('show');
    });

    $('#cancel-disabling-user').on('click', function () {
        var prop = $userToDisabling.prop('checked');
        $userToDisabling.prop('checked', !prop);
        $userToDisabling = null;
    });

    $('#confirm-disabling-user').on('click', function () {
        $('#confirmDisablingDialog').modal('hide');

        axios({
            method: 'post',
            url: '/dashboard/enable-or-disable-user',
            data: { userId: $userToDisabling.data('user') }
        }).then(function (response) {
            if (response.data.success) {
                __WEBPACK_IMPORTED_MODULE_0__Alert__["default"].show('#status-alert', response.data.message, 'alert-success');
                if (response.data.enabled === true) {
                    $userToDisabling.attr('data-mode', 'disable');
                    $userToDisabling.find('.glyphicon').removeClass('glyphicon-refresh').addClass('glyphicon-remove');
                    $userToDisabling.parents('tr').find('.status').removeClass('text-danger').addClass('text-success').text('active');
                } else {
                    $userToDisabling.attr('data-mode', 'enable');
                    $userToDisabling.find('.glyphicon').removeClass('glyphicon-remove').addClass('glyphicon-refresh');
                    $userToDisabling.parents('tr').find('.status').removeClass('text-success').addClass('text-danger').text('not active');
                }

                $userToDisabling = null;
                // window.location.reload();
            }
        }).catch(function (err) {});
    });

    $(document).on('click', '.add-tridiuum-credentials', function () {
        var userId = $(this).data('user-id'),
            $modal = $('#tridiuum-credentials-modal'),
            $form = $modal.find('.tridiumm-form');

        $form.find('input[name="user_id"]').val(userId);
        $form.find('input[name="tridiuum_username"]').val('');
        $form.find('input[name="tridiuum_password"]').val('');

        clearError($form.find('input[name="tridiuum_username"]'));
        clearError($form.find('input[name="tridiuum_password"]'));

        $modal.modal('show');
    });

    function setError($field, text) {
        if (!$field) {
            return;
        }

        var $container = $field.parents('.form-group');

        $container.addClass('has-error');
        $container.find('.help-block').html(text);
    }

    function clearError($field) {
        if (!$field) {
            return;
        }

        var $container = $field.parents('.form-group');

        $container.removeClass('has-error');
        $container.find('.help-block').html('');
    }

    $('#tridiuum-save').on('click', function () {
        var $modal = $(this).parents('.modal'),
            $form = $modal.find('.tridiumm-form');

        clearError($form.find('input[name="tridiuum_username"]'));
        clearError($form.find('input[name="tridiuum_password"]'));
        simpleLoader();

        axios({
            method: $form.attr('method'),
            url: $form.attr('action'),
            data: new FormData($form.get(0))
        }).then(function (response) {
            if (response.data.success) {
                reverseLoader();
                $modal.modal('hide');
                window.location.reload();
            }
        }).catch(function (error) {
            reverseLoader();
            var response = error.response;
            if (response.status === 422) {
                for (var prop in response.data) {
                    setError($('input[name=' + prop + ']'), response.data[prop][0]);
                }
            }
        });
    });
});

/***/ }),

/***/ 1743:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(1599);


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