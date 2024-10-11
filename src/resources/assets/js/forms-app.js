require('./bootstrap');
import 'element-ui/lib/theme-chalk/index.css';
// import 'es6-promise/auto';

import Vue from 'vue';
import Vuex from 'vuex';
import VueRouter from "vue-router";
import routes from "./forms-routes";
import store from './forms-store';
import moment from "moment-timezone";
import VueMomentJS from "vue-momentjs";
import VueTheMask from 'vue-the-mask';
import VeeValidate from 'vee-validate';
import DevelopMode from './mixins/develop_mode';

import {DatePicker, Select, Option, Pagination, Notification, Message} from 'element-ui';

import lang from 'element-ui/lib/locale/lang/en';
import locale from 'element-ui/lib/locale';

locale.use(lang);

moment.tz.setDefault("America/Los_Angeles");
Vue.use(VueMomentJS, moment);
Vue.use(VueTheMask);
Vue.use(Vuex);
Vue.use(VueRouter);
Vue.use(VeeValidate, {
    events: 'input|blur',
});

Vue.prototype.$notify = Notification;
Vue.prototype.$message = Message;

VeeValidate.Validator.extend('minLength', {
    getMessage (field, [length]) {
        return `At least ${length} item${parseInt(length, 10) === 1 ? '' : 's'} must be selected.`;
    },
    validate (value, [length]) {
        return value.length >= length;
    }
});

VeeValidate.Validator.extend('card_expiration_date', {
    validate(value) {
        if (value.length === 5) {
            let splitedDate = value.split('/');
            let monthStr = splitedDate[0];
            let yearStr = splitedDate[1];
            let monthInt = parseInt(monthStr, 10);
            let yearInt = parseInt(yearStr, 10);

            if (monthInt > 12 || monthInt < 1) {
                return false;
            } else {
                let nowDate = moment();
                let nowMonth = parseInt(nowDate.format('MM'), 10);
                let nowYear = parseInt(nowDate.format('YY'), 10);
                
                return (monthInt >= nowMonth && yearInt == nowYear) || (yearInt > nowYear);
            }
        } else {
            return false;
        } 
    }
})


const router = new VueRouter({
    mode           : 'history',
    routes         : routes,
    linkActiveClass: 'active'
});
router.beforeEach((to, from, next) => {
    if(typeof from.query.develop_mode !== 'undefined' && typeof to.query.develop_mode === 'undefined') {
        let newPath = to.path;
        newPath += '?develop_mode=' + (from.query.develop_mode ? 'true' : 'false');
        next(newPath);
    } else {
        next();
    }
});
Vue.component('form-first', require('./components/forms/FormFirst.vue'));
Vue.component('pageloader', require('./components/Pageloader.vue'));
Vue.component('modal', require('./components/Modal.vue'));
Vue.component('square-payment-form', require('./components/forms/partials/SquarePaymentForm.vue'));
Vue.component('square-payment-form-optimized', require('./components/forms/partials/SquarePaymentFormOptimized.vue'));
Vue.component('add-credit-card', require('./components/forms/AddCreditCard.vue'));
Vue.component('are-you-still-here-modal', require('./components/AreYouStillHereModal.vue'));
Vue.component(DatePicker.name, DatePicker);
Vue.component(Select.name, Select);
Vue.component(Option.name, Option);
Vue.component(Pagination.name, Pagination);
Vue.component('patient-form-layout', require('./layouts/PatientFormLayout.vue'));
Vue.component('signature-form', require('./components/forms/patients/SignatureForm.vue'));
Vue.component('telehealth-form', require('./components/forms/patients/TelehealthForm.vue'));

const app = new Vue({
    store,
    router,
    mixins: [DevelopMode]
}).$mount('#app-forms');

$(document).ready(function () {

    $('body')
        .on('focusin', '.form-note .form-control, .form-note .dropdown-form-control', function (e) {
            $(this).parents('.form-group').addClass('focus');
        })
        .on('focusout', '.form-note .form-control, .form-note .dropdown-form-control', function (e) {
            $(this).parents('.form-group').removeClass('focus');
            var elementVal = $(this).val();
            if (elementVal && elementVal.trim() !== '') {
                $(this).parents('.form-group').removeClass('error-focus');
            }
        })
        .on('change', '.signature', function (e) {
            $(this).parents('.form-group').removeClass('error-focus');
        });

    $('.form-note textarea.form-control').scroll(function () {
        if ($(this).scrollTop() > 8) {
            $(this).prev().hide();
        } else {
            $(this).prev().show();
        }
    });

});