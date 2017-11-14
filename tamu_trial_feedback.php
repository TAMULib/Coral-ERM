<?php
$user = $_SERVER['REMOTE_USER'] ? $_SERVER['REMOTE_USER'] : 'API';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
    <head>
      <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
      <link href="https://library.tamu.edu/assets/css/tamuLib.css" rel="stylesheet" />
      <link href="https://library.tamu.edu/assets/css/app.css" rel="stylesheet" />
      <script src="https://code.jquery.com/jquery-3.2.1.min.js"
    integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
    crossorigin="anonymous"></script>
      <script type="text/javascript">
        var coralBase = 'http://coral.local/resources/';
        var coralAPI = coralBase+'api/';
        function getCoralData(endpoint) {
          return $.ajax({
                    type: "GET",
                    url: coralAPI+endpoint+'/'
                });
        }

        function postCoralResourceNote($form) {
          return $.ajax({
                    type: "POST",
                    url: coralAPI+'postResourceNote/',
                    data: $form.serialize()
                  }).done(function(data) {
                    if (data.resourceNoteID) {
                      $("#doContent").html("Your feedback has been submitted.");
                    } else if (data.error) {
                      $("#doContent .error").html("There was an error adding your feedback.");
                    }
                  }).fail(function(data) {
                    $("#doContent .error").html("There was an error adding your feedback.");
                  });
        }

        function getResource(resourceId) {
          return getCoralData('getResource/'+resourceId);
        }

        $(document).ready(function() {
          var queryString = window.location.search.substring(1);
          if (queryString) {
            var queryObject = JSON.parse('{"' + decodeURI(queryString).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}');
            if (typeof queryObject.resourceid !== 'undefined') {
              getResource(queryObject.resourceid).done(function(data) {
                if (data.resourceID) {
                  $("#doResourceId").val(data.resourceID);
                  $("#doTitle").append(data.titleText);
                  $("#doSubmitFeedback").prop("disabled",false);
      
                  $("#addFeedbackForm").submit(function() {
                    postCoralResourceNote($(this));
                    return false;
                  });
                }
              });
            }
          }
        });
      </script>
    </head>
    <body>
      <h1>Trial Resource Feedback</h1>
      <div id="doContent">
        <div class="error"></div>
        <form id="addFeedbackForm" name="addFeedbackForm" action="" method="POST">
          <input type="hidden" name="user" value="<?php echo $user; ?>">
          <input id="doResourceId" type="hidden" name="resourceID" value="0">
          <fieldset>
            <label for="noteText">Add Feedback For: <span id="doTitle"></span></label>
            <textarea name="noteText"></textarea>
          </fieldset>

          <input id="doSubmitFeedback" class="button" type="submit" name="submitFeedback" value="Send" disabled />
        </form>
      </div>
  </body>
</html>
