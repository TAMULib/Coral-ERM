/*
**************************************************************************************************************************
** CORAL Authentication Module v. 1.0
**
** Copyright (c) 2010 University of Notre Dame
**
** This file is part of CORAL.
**
** CORAL is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
**
** CORAL is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along with CORAL.  If not, see <http://www.gnu.org/licenses/>.
**
**************************************************************************************************************************
*/

var gt = new Gettext({ 'domain' : 'messages' });
function _(msgid) {
    return gt.gettext(msgid);
}

function checkModules($module) {
    var $listUnordered = $module.siblings("ul")
    var $listItem = $listUnordered.children("li");
    if ($module.is(":checked")) {
        if ($listItem.children(".jqPrivileges:checked").length == 0) {
          $listUnordered.children("li:last-child").children(".jqPrivileges").attr("checked","checked");
        }
        $listItem.children(".jqPrivileges").removeAttr("disabled");
    } else {
        $listItem.children(".jqPrivileges").attr("disabled","disabled");
    }
}

$(document).ready(function(){

	updateUsers();

    //at initial load/error load, check to see if we need to do set any module privileges up
    $(".jqModule").each(function() {
        checkModules($(this));
    });

    $(".jqModule").change(function() {
        checkModules($(this));
    });

    $("#textLoginID").change(function() {
        var $loginInput = $(this);
        $.ajax({
            type:       "GET",
            url:        "ajax_htmldata.php",
            cache:      false,
            data:       "action=checkLoginID&loginID="+$(this).val(),
            success:    function(data) {
                var response = JSON.parse(data);
                if (response.result) {
                    $("#span_errors").html("");
                    $loginInput.data("valid",1);
                } else {
                    $("#span_errors").html(_("loginID is already in use"));
                    $loginInput.data("valid",0);
                }
            },
            error: function(data) {
                $loginInput.data("valid",0);
            }
        });
    });

});



function updateUsers() {

  $.ajax({
	 type:       "GET",
	 url:        "ajax_htmldata.php",
	 cache:      false,
	 data:       "action=getUsers",
	 success:    function(html) {
		$("#div_users").html(html);
		tb_reinit();
		bind_removes();
	 }


  });
}


function submitUserForm(){
  if (validateForm() === true) {
    var modulePrivileges = {};
    $(".jqPrivileges:checked:not(:disabled)").each(function() {
        modulePrivileges[$(this).parents(".moduleDetails").find(".jqModule").val()] = $(this).val();
    });

	// ajax call to add/update
	$.post("ajax_processing.php?action=submitUser", { loginID: $("#textLoginID").val(), editLoginID: $("#editLoginID").val(), password: $("#password").val(), adminInd: getCheckboxValue('adminInd'), modulePrivileges: modulePrivileges } ,
		function(data){
			tb_remove();
			updateUsers();
			return false;
		}
	);
	return false;

  }
  return false;
}

function validateForm (){
    var control=true;

    if (($("#password").val() != '') && ($("#password").val() != $("#passwordReenter").val())){
        $("#span_errors").html(_("Passwords do not match"));
        $("#passwordReenter").focus();
        control = false;
    }

    if (($("#editLoginID").val() == '') && (($("#password").val() == ''))){
        $("#span_errors").html(_("Password is required"));
        $("#password").focus();
        control = false;
    }
    if (($("#textLoginID").val() == '')){
        $("#span_errors").html(_("UserID is required"));
        $("#textLoginID").focus();
        control = false;
    }

    return control;
}

  function bind_removes(){


  	 $(".deleteUser").unbind('click').click(function () {
	  if (confirm(_("Do you really want to delete this user?")) == true) {
		  $.ajax({
			 type:       "GET",
			 url:        "ajax_processing.php",
			 cache:      false,
			 data:       "action=deleteUser&loginID=" + $(this).attr("id"),
			 success:    function(html) {
				 updateUsers();
			 }



		 });
	  }
  	 });
  }
