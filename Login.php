<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login Page</title>
  <link rel="stylesheet" href="./framework/jquery-ui-1.12.1/jquery-ui.css">
  <script src="./framework/jquery-3.2.1.js"></script>
  <script src="./framework/jquery-ui-1.12.1/jquery-ui.js"></script>
  <style type="text/css">
    #loginBtn {
    position:absolute;
    top:0;
    right:0;
    }
  </style>
  <script>
  isAdmin = false;
  $(function(){
    $( "#loginDlg" ).dialog({
        autoOpen: false,
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
          "Login": function() {
            submitLoginForm();
          },
          Cancel: function() {
            $( this ).dialog( "close" );
          }
        }
      });

      $( "#createUserDlg" ).dialog({
          autoOpen: false,
          resizable: false,
          height: "auto",
          width: 400,
          modal: true,
          buttons: {
            "Create": function() {
              submitCreateUserForm();
             },
            Cancel: function() {
              $( this ).dialog( "close" );
            }
          }
        });

      function submitLoginForm() {
        $.ajax({
            type: 'POST',
            url: './PasswordVerification.php',
            data: {
                username: $('#username').val(),
                password: $('#password').val()
            },
            success: function (data) {
              console.log(data);

              $('#password').val('');
              if(false === data['userFind']) {
                //confirm('User Not Find, Please try again!');
                $( "#loginDlg" ).dialog('option','title','User Not Find, Please try again!');
              }
              else if(false === data['psw_verified']) {
                //confirm('Wrong Password, Please try again!');
                $( "#loginDlg" ).dialog('option','title','Wrong Password, Please try again!');
              }
              else {
                $('#loginDlg').dialog( "close" );
                confirm('Welcome back, ' + data['username']);
                //$( "#loginDlg" ).dialog('option','title','Creat New Users');

                if('administrator' === data['permission']) {
                  isAdmin = true;
                  $('#loginBtn').html('Create New User');
                }
                else {
                  $('#loginBtn').html(data['username']);
                  $('#loginBtn').button('disable');
                }
              }
            },
            error: function (request, status, error) {
              alert('Error in fetch user data from database');
              console.log(status);
              console.log(error);
              console.log(request.responseText);
            },
            dataType: 'json',
            async: true
        });


      }

      function submitCreateUserForm() {
        result = inputValidation();
        if(true === result) {
          $.ajax({
              type: 'POST',
              url: './CreateNewUser.php',
              data: {
                  username: $('#username_new').val(),
                  password: $('#password_new').val()
              },
              success: function (data) {
                console.log(data);
                if(true == data['success']) {
                  confirm('Success : name#' + data['username'] + ' id#' + data['id'] + ' created');
                  $('#username_new').val('');
                  $('#password_new').val('');
                  $('#password_new_repeat').val('');
                }
                else {
                  confirm('Error: ' + data['error']);
                }

              },
              error: function (request, status, error) {
                alert('Error in fetch user data from database');
                console.log(status);
                console.log(error);
                console.log(request.responseText);
              },
              dataType: 'json',
              async: true
          });
        }
        else {
          $('#createUserDlg').dialog('option','title',result);
        }
      }

      function inputValidation() {
        result = true;
        if('' == $('#username_new').val()) {
          result = 'username cannot be blank';
        }
        else if($('#password_new').val() !== $('#password_new_repeat').val()) {
          result = 'please keep the password same';
        }
        else if($('#username_new').val().length < 3) {
          result = 'length of username cannot less than 3';
        }
        else if($('#password_new').val().length < 6) {
          result = 'length of password cannot less than 6';
        }

        return result;

      }

      $( "#loginBtn" ).button().on( "click", function() {
        if(false === isAdmin) {
          $( "#loginDlg" ).dialog('option','title','Login');
          $( "#loginDlg" ).dialog( "open" );
        }
        else if(true === isAdmin) {
          $( "#createUserDlg" ).dialog('option','title','Create New User');
          $( "#createUserDlg" ).dialog( "open" );
        }

        });

        $("#username").keypress(function(event){
            if(event.keyCode == 13){//Enter Key
              submitLoginForm();
            }
        });

        $("#password").keypress(function(event){
            if(event.keyCode == 13) {//Enter Key
              submitLoginForm();
            }
        });

        $("#username_new").keypress(function(event){
            if(event.keyCode == 13){//Enter Key
              submitCreateUserForm();
            }
        });

        $("#password_new").keypress(function(event){
            if(event.keyCode == 13) {//Enter Key
              submitCreateUserForm();
            }
        });

        $("#password_new_repeat").keypress(function(event){
            if(event.keyCode == 13) {//Enter Key
              submitCreateUserForm();
            }
        });



    });
  </script>
</head>
<body>
<button name="loginBtn" id="loginBtn">Login</button><br />

<div id="loginDlg" title="Login">
    <div>
      <input type="text" name="username" id="username" placeholder="Username"><br /><br />
      <input type="password" name="password" id="password" placeholder="Password"><br />
    </div>
</div>

<div id="createUserDlg" title="Create New User">
    <div>
      <input type="text" name="username_new" id="username_new" placeholder="Username"><br /><br />
      <input type="password" name="password_new" id="password_new" placeholder="Password"><br />
      <input type="password" name="password_new_repeat" id="password_new_repeat" placeholder="Password"><br />
    </div>
</div>


</body>
</html>
