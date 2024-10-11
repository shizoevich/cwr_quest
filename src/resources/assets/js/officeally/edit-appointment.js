var page = require('webpage').create();
page.settings.localToRemoteUrlAccessEnabled = true;
page.settings.ignoreSslErrors = true;
page.settings.loadImages = false;

var url = config.url;
var user = config.login;
var password = config.password;
var data = config.data;

"use strict";

if(data) {
    page.open(url, function (status) {
        if (status === 'success') {
            window.setTimeout(function () {
                page.evaluate(function (user, password) {
                    document.getElementById("Login1_UserName").value = user;
                    document.getElementById("Login1_Password").value = password;
                    document.getElementById("Login1_LoginButton").click();
                }, user, password);
                var loginAttempts = 0;
                var interval = window.setInterval(function () {
                    page.evaluate(function (id) {
                        EditAppointment(id, 0, '','');
                    }, data['appointmentID']['value']);

                    window.setTimeout(function () {
                        var selector = page.evaluate(function () {
                            var sel = document.getElementById('EditAppointment_PatientID');
                            if(sel) {
                                return sel.value;
                            } else {
                                return null;
                            }

                        });
                        if(selector) {
                            window.clearInterval(interval);
                            console.log('Edit appointment modal is exists');
                            page.evaluate(function(data) {
                                for(var item in data) {
                                    if(!data[item]['id'] || !data[item]['type']) {
                                        continue;
                                    }
                                    if(data[item]['type'] == 'select') {
                                        var selectSelector = document.getElementById(data[item]['id']);
                                        var selectOptions = selectSelector.options;
                                        for(var i in selectOptions) {
                                            if(selectOptions[i].text == data[item]['value']) {
                                                selectSelector.value = selectOptions[i].value;
                                                break;
                                            }
                                        }
                                    } else {
                                        document.getElementById(data[item]['id']).value = data[item]['value'];
                                    }
                                }
                                document.getElementById('EditAppointment_btnSave').click();
                            }, data);
                            window.setTimeout(function () {
                                phantom.exit()
                            }, 5000);
                        } else {
                            console.log('Edit appointment modal is not exists', typeof selector);
                            loginAttempts++;
                            if(loginAttempts > 5) {
                                window.clearInterval(interval);
                                phantom.exit(2)
                            }
                        }
                    }, 5000);
                }, 20000);   //interval
            }, 5000);
        } else {
            phantom.exit(1);
        }
    });
} else {
    phantom.exit();
}