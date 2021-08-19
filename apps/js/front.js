/*
 * Skycloud Software
 * Copyright 2020
 *
 * Login javascript
 */

  var back_error = 'Internal Error 内部错误';
  var dict = {};
  var loaded = false;
  if (! loaded) {
    dict = {SKYFORM_WEB_INTERFACE: 'SkyForm',
            USERNAME: 'Username',
            PASSWORD: 'Password',
            LOGIN: 'Login',
            NEED_USERNAME: 'Need to input user name'
           };
    $.ajax({
      type: "post",
      url: "php/init.php",
      contentType: "application/json",
      async: false,
      cache: false,
      success: function(data) {
        var d = JSON.parse(data);
        if (d.code == 0) {
          localStorage.setItem('dict', JSON.stringify(d.data));
          dict = d.data;
          if (d.conf.registration == 'yes' || d.conf.registration == true) {
              $('#register').html(dict.REGISTER);
          }
        } else {
          $('#errmsg').html(d.message);
        }
      },
      error: function() {
        $('#errmsg').html(back_error);
      }
    });
    loaded = true;
  }
 
  $('head').append('<title>' + dict.SKYFORM_WEB_INTERFACE + '</title>');
  $('#logo').html(dict.SKYFORM_WEB_INTERFACE);
  $('#username').attr('placeholder', dict.USERNAME);
  $('#password').attr('placeholder', dict.PASSWORD);
  $('#submit-button').val(dict.LOGIN);

  var input = document.getElementById("password");
    input.addEventListener("keyup", function(event) {
      if (event.keyCode == 13) { /* press enter to login */
        event.preventDefault();
        document.getElementById("submit-button").click();
      }
  });

  function login() {
    var auth_data = {username: $("#username").val(),
                     password: $("#password").val()
                    };
    if (auth_data.username == '') {
      $('#errmsg').html(dict.NEED_USERNAME);
      return;
    }
    $.ajax({
      type: "post",
      url: "php/login.php",
      contentType: "application/json",
      data: JSON.stringify(auth_data),
      datatype: "json",
      success: function(data) {
        var d = JSON.parse(data);
        if (d.code == 0) {
           window.location.href = d.data.url;
        } else {
           $('#errmsg').html(dict[d.message] ? 
               dict[d.message] : d.message);
        }
      },
      error: function() {
        $('#errmsg').html(back_error);
      }
    });
  }
