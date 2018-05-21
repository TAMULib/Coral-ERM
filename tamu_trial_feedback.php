<?php
  $user = empty($_SERVER['REMOTE_USER']) ? 'API' : $_SERVER['REMOTE_USER'];

  $basePath = NULL;
  $shortPath = NULL;
  $longPath = NULL;
  $canonicalPath = NULL;
  $helpdeskPath = '//helpdesk.library.tamu.edu';
  if (isset($_SERVER['SERVER_NAME']) && strlen($_SERVER['SERVER_NAME']) > 0 && isset($_SERVER['REQUEST_SCHEME']) && strlen($_SERVER['REQUEST_SCHEME']) > 0) {
    if (isset($_SERVER['SCRIPT_NAME']) && strlen($_SERVER['SCRIPT_NAME']) > 0) {
      $shortPath = $_SERVER['SCRIPT_NAME'];
      $basePath = dirname($shortPath);
    }

    $canonicalPath = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $shortPath;
    $longPath = '//' . $_SERVER['SERVER_NAME'] . $basePath;
  }

  $fields = [
    [
      'label' => 'Was this trial helpful to your research and/or class assignment(s)',
      'closing' => '?',
      'type' => 'radios',
      'typeSub' => 'other',
      'required' => TRUE,
    ],
    [
      'label' => 'Do you believe this resource will be helpful to your research in the future',
      'closing' => '?',
      'type' => 'radios',
      'typeSub' => 'other',
      'required' => TRUE,
    ],
    [
      'label' => 'If you are a faculty member, would this resource provide support for any of the classes you teach',
      'closing' => '?',
      'type' => 'radios',
      'typeSub' => 'n/a',
      'required' => TRUE,
    ],
    [
      'label' => 'If you are a faculty member, would you refer your students to this resource for class assignments or papers',
      'closing' => '?',
      'type' => 'radios',
      'typeSub' => 'n/a',
      'required' => TRUE,
    ],
    [
      'label' => 'If you are in favor of the library purchasing access to this resource, are there specific features or functions that make this resource particularly useful or better than other resources that are available',
      'closing' => '?',
      'type' => 'textarea',
      'typeSub' => NULL,
      'required' => FALSE,
    ],
    [
      'label' => 'Please add any comments you have about the resource below',
      'closing' => ':',
      'type' => 'textarea',
      'typeSub' => NULL,
      'required' => FALSE,
    ],
  ];
?><!DOCTYPE html>
<html lang="en" dir="ltr" class="no-js">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">

    <base href="<?php print($basePath); ?>/">
    <?php if (isset($canonicalPath)) { ?>
      <link rel="canonical" href="<?php print($canonicalPath); ?>">
    <?php } ?>

    <link rel="shortcut icon" href="<?php print($helpdeskPath); ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="<?php print($helpdeskPath); ?>/css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="<?php print($helpdeskPath); ?>/css/tamu.css" media="all">
    <style type="text/css">
        #wrap {
          color: #ffffff;
        }

        #doContent .required-asterisk {
          color: #a80000;
          font-weight: bold;

          padding-left: 5px;
        }

        #doContent .breadcrumb a,
        #doContent .breadcrumb a:visited {
          color: #2f6fa7;
        }

        .questions-form .questions-radios .questions-legend,
        .questions-form .questions-textarea .questions-label {
          font-size: 14px;
          font-weight: bold;

          margin-bottom: 10px;
        }

        .questions-form .questions-radios,
        .questions-form .questions-textarea {
          margin-bottom: 20px;
        }

        .questions-form .questions-radios > .form-group {
          margin-bottom: 0px;
        }

        .questions-form .questions-radios .questions-radio {
          display: inline-block;
        }

        .questions-form .questions-radios .questions-label {
          min-width: 55px;
        }

        .questions-form .questions-buttons .btn {
          margin-right: 15px;
        }

        .questions-form .questions-buttons .btn:last-of-type {
          margin-right: 0px;
        }

        #doContent .errors .error {
          font-weight: bold;
          margin: 6px 0px;
          padding: 6px;
          border: 1px solid red;
          background-color: #f7d9d9;
        }
    </style>
    <style type="text/css" media="print">
        .no-print {
            display: none;
        }

        #banner {
            height: auto;
        }

        #footer {
            display: none;
        }
    </style>

    <script type="text/javascript" src="<?php print($helpdeskPath); ?>/js/jquery-2.1.4.min.js"></script>
    <script type="text/javascript" src="<?php print($helpdeskPath); ?>/js/bootstrap.min.js"></script>
    <script type="text/javascript">
    var coralAPI = 'resources/api/';
    function getCoralData(endpoint) {
        return $.ajax({
                  type: "GET",
                  url: coralAPI + endpoint + '/'
                });
    }

    function postCoralResourceNote(formData) {
        return $.ajax({
                type: "POST",
                  url: coralAPI + 'postResourceNote/',
                  data: formData
                }).done(function(data) {
                  if (data.resourceNoteID) {
                    $("#doContent").html("Your feedback has been submitted.");
                  } else if (data.error) {
                    $("#doContent .errors").empty();
                    $("#doContent .errors").append("<div class=\"error\" role=\"alert\">There was an error adding your feedback.</div>");
                  }
                }).fail(function(data) {
                  $("#doContent .errors").empty();
                  $("#doContent .errors").append("<div class=\"error\" role=\"alert\">There was an error adding your feedback.</div>");
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
                $("#resourceID").val(data.resourceID);
                $("#addFeedbackForm .do-title").append(data.titleText);
                $("#submitFeedback").prop("disabled", false);
              }
            });
          }
        }

        $("#addFeedbackForm").submit(function() {
          var noteText = "\n";
          $(this).find(".questions-input").each(function() {
            var tag = $(this);
            var forID;
            var label;
            var regex = /[.!?]\s*$/i;

            if ($(tag).hasClass("input-radio")) {
              label = $(tag).closest(".questions-radios").find(".questions-legend").first().find(".label-text").first();
              if ($(tag).is(":checked")) {
                if ($(tag).hasClass("input-radio-other")) {
                  forID = $(tag).attr("data-for");
                  if (forID.length > 0) {
                    noteText += $(label).text() + ": " + $("#" + forID).val();
                    if (!regex.test($("#" + forID).val())) {
                        noteText += ".";
                    }
                    noteText += "\n\n";
                  }
                }
                else {
                  noteText += $(label).text() + ": " + $(tag).val() + ".\n\n";
                }
              }
            }
            else if ($(tag).hasClass("input-textarea")) {
              if ($(tag).val().length > 0) {
                forID = $(tag).attr("id");
                label = $(tag).closest(".questions-textarea").find("[for=" + forID + "]").first().find(".label-text").first();
                if ($(label).text().length > 0) {
                  noteText += $(label).text() + ": " + $(tag).val();
                  if (!regex.test($(tag).val())) {
                      noteText += ".";
                  }
                  noteText += "\n\n";
                }
              }
            }
          });
          $("#noteText").val(noteText);

          var formData = {
            "user": "API",
            "resourceID": $("#resourceID").val(),
            "noteText": noteText
          };
          postCoralResourceNote(formData);
          return false;
        });

        $("html").removeClass("no-js");
        $("html").addClass("js");

        $(".questions-form .questions-radios .questions-radio-other .questions-textfield-other").prop("disabled", true);

        $(".questions-form .questions-radios .questions-radio .input-radio").change(function(e) {
            var textfield = $(this).closest(".questions-radios").find(".questions-textfield-other").first();
            if ($(this).val() === "") {
              $(textfield).prop("disabled", false);
            }
            else {
              $(textfield).prop("disabled", true);
            }
        });

        $("#clear").click(function(e) {
          $(".questions-form .questions-radios .questions-radio-other .questions-textfield-other").prop("disabled", true);
        });
    });
    </script>

    <title>Trial Resource Feedback Form</title>
  </head>
  <body>
    <header id="wrap">
      <nav class="navbar navbar-default no-print">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
          </div>
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <span class="navbar-brand tamu-header-brand tamu-header-display">
              <a class="top-nav-link" href="<?php print($helpdeskPath); ?>"><img src="<?php print($helpdeskPath); ?>/images/tamu-logo-with-bar.png" alt="Texas A&amp;M University Libraries"></a>
              <a class="top-nav-link" href="<?php print($helpdeskPath); ?>"><span class="tamu-header-brand-text hidden-xs">Texas A&amp;M University Libraries</span></a>
            </span>
            <ul class="nav navbar-nav navbar-right">
              <li><a class="nav-link" href="//askus.library.tamu.edu/">Help</a></a></li>
            </ul>
          </div>
        </div>
      </nav>
      <div id="banner">
        <div class="container" role="heading">
          <h1>Trial Resource Feedback for <span class="do-title"></span></h1>
        </div>
      </div>
    </header>
    <section class="container">
      <div class="row">
        <div class="col-md-12">
          <div id="doContent">
            <ol class="breadcrumb no-print">
              <li class="active"><a href="<?php print($helpdeskPath); ?>/erdesk.php">Return to Helpdesk</a></li>
            </ol>
            <div class="errors" aria-live="assertive"></div>
            <div><p>Thank you for helping us make informed decisions about the resources we purchase by providing us with your impressions of our resource trials.</p></div>
            <form id="addFeedbackForm" action="" method="POST" class="questions-form form">
              <input type="hidden" id="noteText" name="noteText" value="">
              <input type="hidden" name="user" value="<?php print($user); ?>">
              <input id="resourceID" type="hidden" name="resourceID" value="0">

              <?php foreach ($fields as $key => $field) { ?>
                <?php if ($field['type'] == 'radios') { ?>
                  <fieldset class="questions-radios">
                    <legend class="questions-legend"><span class="label-text"><?php print($field['label']); ?></span><?php print($field['closing']); ?><?php if ($field['required']) { ?><span class="required-asterisk" aria-hidden="true">*</span><?php } ?></legend>
                    <div class="form-group">
                      <div class="questions-radio questions-radio-normal">
                          <div class="radio-inline">
                            <label class="questions-label"><input type="radio" class="questions-input input-radio" id="question<?php print($key); ?>a" name="tamu-question<?php print($key); ?>" value="yes" required>Yes</label>
                          </div>
                      </div>
                      <div class="questions-radio questions-radio-normal">
                          <div class="radio-inline">
                            <label class="questions-label"><input type="radio" class="questions-input input-radio" id="question<?php print($key); ?>b" name="tamu-question<?php print($key); ?>" value="no">No</label>
                          </div>
                      </div>
                      <?php if ($field['typeSub'] == 'other') { ?>
                        <div class="questions-radio questions-radio-other">
                          <div class="radio-inline">
                            <label class="questions-label"><input type="radio" class="questions-input input-radio input-radio-other" id="question<?php print($key); ?>c" name="tamu-question<?php print($key); ?>" value="" data-for="question<?php print($key); ?>cOther">Other</label>
                          </div>
                          <div class="radio-inline">
                            <label class="questions-label control-label hidden" for="question<?php print($key); ?>cOther">Other</label>
                            <input type="textfield" class="questions-input questions-textfield questions-textfield-other" id="question<?php print($key); ?>cOther" name="tamu-question<?php print($key); ?>" value="">
                          </div>
                        </div>
                      <?php } else if ($field['typeSub'] == 'n/a') { ?>
                        <div class="questions-radio questions-radio-normal">
                          <div class="radio-inline">
                            <label class="questions-label"><input type="radio" class="questions-input input-radio" id="question<?php print($key); ?>b" name="tamu-question<?php print($key); ?>" value="n/a">N/A</label>
                          </div>
                        </div>
                      <?php } ?>
                    </div>
                  </fieldset>
                <?php } else if ($field['type'] == 'textarea') { ?>
                  <div class="questions-textarea form-group">
                    <label for="question<?php print($key); ?>" class="questions-label control-label"><span class="label-text"><?php print($field['label']); ?></span><?php print($field['closing']); ?><?php if ($field['required']) { ?><span class="required-asterisk" aria-hidden="true">*</span><?php } ?></label>
                    <textarea id="question<?php print($key); ?>" class="questions-input input-textarea form-control" name="tamu-question<?php print($key); ?>"></textarea>
                  </div>
                <?php } ?>
              <?php } ?>
              <div class="questions-buttons form-group no-print">
                <input id="submitFeedback" class="btn btn-primary button" type="submit" value="Submit Feedback">
                <input id="clear" type="reset" class="btn button" value="Clear Form">
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
    <footer id="footer">
      <div class="container text-center">
        <ul class="list-inline" role="navigation">
          <li><a href="//library.tamu.edu/giving/">Giving to the Libraries</a></li>
          <li><a href="//www.tamu.edu/">Texas A&amp;M University</a></li>
          <li><a href="//library.tamu.edu/about/employment/index.html">Employment</a></li>
          <li><a href="//library.tamu.edu/services/forms/contact-info.html">Webmaster</a></li>
          <li><a href="//library.tamu.edu/about/general-information/legal-notices.html">Legal</a></li>
          <li><a href="//askus.library.tamu.edu/">Comments</a></li>
          <li><a href="//library.tamu.edu/about/phone/">979-845-5741</a></li>
          <li><a href="//sugar.library.tamu.edu/">Staff Login</a></li>
        </ul>
      </div>
    </footer>
  </body>
</html>
