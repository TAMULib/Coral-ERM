<?php
$user = !empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : 'API';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
  <head>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="//helpdesk.library.tamu.edu/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="//helpdesk.library.tamu.edu/css/tamu.css">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	crossorigin="anonymous"></script>
    <script type="text/javascript">
      var coralBase = 'http://localhost:8090/php/Coral-ERM/';
	    var coralAPI = coralBase+'resources/api/';
	    function getCoralData(endpoint,parameters) {
		  return $.ajax({
					type: "GET",
					url: coralAPI+endpoint+'/',
          data: parameters
				});
	    }

      function postCoralResource($form) {
        return $.ajax({
                  type: "POST",
                  url: coralAPI+'proposeResource/',
                  data: $form.serialize()
                }).done(function(data) {
                  if (data.resourceID) {
                    $("#doContent").html("Your proposal has been submitted. <a href='"+coralBase+"tamu_trial_feedback.php?resourceid="+data.resourceID+"'>View Now</a>");
                  } else if (data.error) {
                    $("#doContent .error").html("There was an error submitting your proposal.");
                  }
                }).fail(function(data) {
                  $("#doContent .error").html("There was an error submitting your proposal.");
                });
      }

      function setAcquisitionType() {
        getCoralData('getAcquisitionTypeByName',{'name':'Trial Request'}).done(function(data) {
          var acquisitionTypeID = null;
          if (data && $.isNumeric(data)) {
            $("#doAcquisitionTypeId").val(data);
            $("#submitProposeResourceForm").removeAttr("disabled");
          } else {
            $("#doContent .error").html("There was an error setting up the Trial Request form.");
          }
        }).fail(function() {
            $("#doContent .error").html("There was an error setting up the Trial Request form.");
        });
      }

      $(document).ready(function() {
        setAcquisitionType();

        $("#doCheckTitle").change(function() {
          getCoralData('findResourcesByTitle',{'searchTerm':$(this).val()}).done(function(data) {
            if (Array.isArray(data)) {
              var matchesHtml = '<h4>Potential Matches:</h4>';
              $.each(data,function(k,v) {
                matchesHtml += '<div><a target="_blank" href="'+coralBase+'tamu_trial_feedback.php?resourceid='+v.resourceID+'">'+v.titleText+'</a></div>';
              });
              $("#doTitleMatches").html(matchesHtml);
            }
          });
        });

        $("#proposeResourceForm").submit(function() {
          var noteText = "\n";
          $(this).find(".do-note").each(function() {
            noteText += $(this).siblings("label").text()+" "+$(this).val()+"\n\n";
          });
          $("#noteText").val(noteText);
          postCoralResource($(this));
          return false;
        });
      });
    </script>
  </head>
  <body>
    <div class="container" id="doContent">
      <div class="row">
        <div class="col-md-12">
          <ol class="breadcrumb">
              <li class="active"><a href="//helpdesk.library.tamu.edu/erdesk.php">Return to Helpdesk</a></li>
          </ol>
          <h3>Request a Trial</h3>
          <p>Use the form below to request a trial for a new resource.</p>
          <div class="error"></div>
          <form id="proposeResourceForm" name="proposeResourceForm" action="" method="POST">
            <input type="hidden" id="noteText" name="noteText" value="" />
            <input type="hidden" name="acquisitionTypeID" id="doAcquisitionTypeId" value="0" />
            <div class="form-group">
              <label for="titleText">Title of Resource:</label>
              <input type="text" class="form-control" name="titleText" id="doCheckTitle" value=""  minlength="1" required>
            </div>
            <div class="form-group">
              <label for="descriptionText">Description of Resource:</label>
              <textarea type="text" class="form-control" id="descriptionText", name="descriptionText" rows="5"  minlength="1" required></textarea>
            </div>
            <div class="form-group">
              <label for="requestDates">Desired Dates of Trial:</label>
              <input type="text" class="form-control do-note" id="noteDates" value=""  minlength="1" required>
            </div>
            <div class="form-group">
              <label for="note[vendor]">Vendor Contact Information (Name, Phone, Email, Date of Contact):</label>
              <textarea type="text" class="form-control do-note" id="noteVendor" rows="5"  minlength="1" required></textarea>
            </div>
            <div class="form-group">
              <label for="note[quote]">Vendor Price Quote:</label>
              <input type="text" class="form-control do-note" id="noteQuote" value=""  minlength="1" required>
            </div>
            <div class="form-group">
              <label for="note[info]">Notes/Additional Info:</label>
              <textarea type="text" class="form-control do-note" id="note[info]" rows="5" ></textarea>
            </div>
            <input id="submitProposeResourceForm" name="submitProposeResourceForm" type="submit" class="btn btn-primary" value="Submit Request" disabled />
            <input id="clear" type="reset" class="btn" value="Clear Form" />
          </form>
        </div>
      </div>
    </div>
  </body>
</html>
