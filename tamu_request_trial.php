<?php
  $user = empty($_SERVER['REMOTE_USER']) ? 'API' : $_SERVER['REMOTE_USER'];

  $basePath = NULL;
  $shortPath = NULL;
  $longPath = NULL;
  $canonicalPath = NULL;
  $helpdeskPath = 'https://helpdesk.library.tamu.edu';
  $serverPort = $_SERVER['SERVER_PORT'];

  if (isset($_SERVER['SERVER_NAME']) && strlen($_SERVER['SERVER_NAME']) > 0 && isset($_SERVER['REQUEST_SCHEME']) && strlen($_SERVER['REQUEST_SCHEME']) > 0) {
    if (isset($_SERVER['SCRIPT_NAME']) && strlen($_SERVER['SCRIPT_NAME']) > 0) {
      $shortPath = $_SERVER['SCRIPT_NAME'];
      $basePath = dirname($shortPath);
    }

    $canonicalPath = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $shortPath;
    $longPath = '//' . $_SERVER['SERVER_NAME'] . (($serverPort != 80) ? ':'.$serverPort:''). $basePath;
    $longPath = rtrim($longPath,'/');
  }
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
    <style type="text/css" media="all">
        #wrap {
            color: #ffffff;
        }

        #doContent .breadcrumb a,
        #doContent .breadcrumb a:visited {
          color: #2f6fa7;
        }

        #doContent .required-asterisk {
          color: #a80000;
          font-weight: bold;

          padding-left: 5px;
        }

        #doContent .errors .error {
          font-weight: bold;
          margin: 6px 0px;
          padding: 6px;
          border: 1px solid red;
          background-color: #f7d9d9;
        }

        .potential-matches-list {
          margin: 4px;
          padding: 4px;
          border: 1px dashed #e9ba00;
          background-color: #feffdb;
        }

        .potential-matches-title {
          font-size: 1.6rem;
          margin: 0px 0px 4px 0px;
        }

        .potential-matches-item {
          list-style-type: disc;
          margin-left: 20px;
          padding-left: 4px;
        }

        .potential-matches-item a {
          color: #265a87;
        }

        .questions-form .questions-buttons .btn {
          margin-right: 15px;
        }

        .questions-form .questions-buttons .btn:last-of-type {
          margin-right: 0px;
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
      var coralBase = '<?php echo $longPath;?>';
      var coralAPI = coralBase+'/resources/api/';
      function getCoralData(endpoint,parameters) {
        return $.ajax({
                  type: "GET",
                  url: coralAPI + endpoint + '/',
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
                    $("#doContent").html("Your proposal has been submitted.");
                  } else if (data.error) {
                    $("#doContent .errors").empty();
                    $("#doContent .errors").append("<div class=\"error\" role=\"alert\">There was an error submitting your proposal.</div>");
                  }
                }).fail(function(data) {
                  $("#doContent .errors").empty();
                  $("#doContent .errors").append("<div class=\"error\" role=\"alert\">There was an error submitting your proposal.</div>");
                });
      }

      function setAcquisitionType() {
        getCoralData('getAcquisitionTypeByName',{'name':'Trial Request'}).done(function(data) {
          var acquisitionTypeID = null;
          if (data && $.isNumeric(data)) {
            $("#doAcquisitionTypeId").val(data);
            $("#submitProposeResourceForm").removeAttr("disabled");
          } else {
            $("#doContent .errors").empty();
            $("#doContent .errors").append("<div class=\"error\" role=\"alert\">There was an error setting up the Trial Request form.</div>");
          }
        }).fail(function() {
          $("#doContent .errors").empty();
            $("#doContent .errors").append("<div class=\"error\" role=\"alert\">There was an error setting up the Trial Request form.</div>");
        });
      }

      $(document).ready(function() {
        setAcquisitionType();

        $("html").removeClass("no-js");
        $("html").addClass("js");

        $("#titleText").change(function() {
          getCoralData('findResourcesByTitle',{'searchTerm':$(this).val()}).done(function(data) {
            if (Array.isArray(data) && data.length > 0) {
              var matchesHtml = '<h4 class="potential-matches-title">Potential Matches:</h4>';
              $.each(data,function(k,v) {
                matchesHtml += '<li class="potential-matches-item"><a target="_blank" href="'+coralBase+'/tamu_trial_feedback.php?resourceid=' + v.resourceID + '">' + v.titleText + '</a></li>';
              });
              $("#doTitleMatches").html("<ul class=\"potential-matches-list\">" + matchesHtml + "</ul>");
            }
            else {
              $("#doTitleMatches").html("");
            }
          });
        });

        $("#vendorName").change(function() {
          getCoralData('findOrganizationsByName',{'searchTerm':$(this).val()}).done(function(data) {
            if (Array.isArray(data) && data.length > 0) {
              var matchesHtml = '<h4 class="potential-matches-title">Potential Matches:</h4>';
              $.each(data,function(k,v) {
                matchesHtml += '<li class="potential-matches-item"><a data-vendor="'+v.name+'" data-vendorid="'+v.organizationID+'" class="do-select-vendor" href="#">' + v.name + '</a></li>';
              });
              $("#doVendorMatches").html("<ul class=\"potential-matches-list\">" + matchesHtml + "</ul>");
            }
            else {
              $("#doVendorMatches").html("");
            }
          });
        });

        $("#doVendorMatches").on("click",".do-select-vendor",function(e) {
          e.preventDefault();
          $("#noteVendor").val("Checking for Vendor contact info...");
          $("#doVendorMatches").html("");
          var vendor = $(this).data("vendor");
          var vendorID = $(this).data("vendorid");
          $("#doVendorId").val(vendorID);
          $("#vendorName").val(vendor);
          getCoralData('getOrganizationContacts',{'organizationID':vendorID}).done(function(contact) {
            $("#noteVendor").val("");
            if (contact.contactID) {
              var fieldNames = {"name":"Name","phoneNumber":"Phone Number","emailAddress":"Email","title":"Title"};
              $.each(fieldNames, function(key,gloss) {
                if (typeof contact[key] !== 'undefined') {
                  $("#noteVendor").val($("#noteVendor").val()+gloss+" - "+contact[key]+"\r\n");
                }
              });
            }
          });
        });

        $("#proposeResourceForm").submit(function() {
          var noteText = "";
          $(this).find(".do-note").each(function() {
            noteText += $(this).siblings("label").children(".label-text").first().text() + ": " + $(this).val() + ".\n\n";
          });
          $("#noteText").val(noteText);
          postCoralResource($(this));
          return false;
        });
      });
    </script>

    <title>Request a Trial Form</title>
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
          <h1>Request a Trial</h1>
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
            <div><p>Use the form below to request a trial for a new resource.</p></div>
            <div class="errors" aria-live="assertive"></div>
            <form id="proposeResourceForm" action="" method="POST" class="request_trial-form form">
              <input type="hidden" id="noteText" name="noteText" value="">
              <input type="hidden" id="doAcquisitionTypeId" name="acquisitionTypeID" value="0">
              <input type="hidden" id="doVendorId" name="vendorID" value="0">
              <fieldset id="userInformation">
                <legend>User Information</legend>
                <div class="form-group">
                  <label for="selectorEmail"><span class="label-text">E-Mail</span>:<span class="required-asterisk" aria-hidden="true">*</span></label>
                  <input type="email" class="form-control" id="selectorEmail" name="selectorEmail" value="" minlength="1" required>
                </div>
                <div class="form-group">
                  <label for="selectorTitle"><span class="label-text">Title</span>:<span class="required-asterisk" aria-hidden="true">*</span></label>
                  <input type="text" class="form-control" id="selectorTitle" name="selectorTitle" value="" minlength="1" required>
                </div>
                <div class="form-group">
                  <label for="selectorFirstName"><span class="label-text">First Name</span>:<span class="required-asterisk" aria-hidden="true">*</span></label>
                  <input type="text" class="form-control" id="selectorFirstName" name="selectorFirstName" value="" minlength="1" required>
                </div>
                <div class="form-group">
                  <label for="selectorLastName"><span class="label-text">Last Name</span>:<span class="required-asterisk" aria-hidden="true">*</span></label>
                  <input type="text" class="form-control" id="selectorLastName" name="selectorLastName" value="" minlength="1" required>
                </div>
              </fieldset>
              <fieldset id="requestInformation">
                <legend>Request Information</legend>
                <div class="form-group">
                  <label for="titleText"><span class="label-text">Title of Resource</span>:<span class="required-asterisk" aria-hidden="true">*</span></label>
                  <input type="text" class="form-control" id="titleText" name="titleText" value="" minlength="1" required>
                  <div id="doTitleMatches"></div>
                </div>
                <div class="form-group">
                  <label for="descriptionText"><span class="label-text">Description of Resource</span>:<span class="required-asterisk" aria-hidden="true">*</span></label>
                  <textarea type="text" class="form-control" id="descriptionText" name="descriptionText" rows="5" minlength="1" required></textarea>
                </div>
                <div class="form-group">
                  <label for="noteDates"><span class="label-text">Desired Dates of Trial</span>:<span class="required-asterisk" aria-hidden="true">*</span></label>
                  <input type="text" class="form-control do-note" id="noteDates" value="" minlength="1" required>
                </div>
                <div class="form-group">
                  <label for="vendorName"><span class="label-text">Vendor Name</span>:</label>
                  <input type="text" class="form-control" id="vendorName" name="vendorName" value="">
                  <div id="doVendorMatches"></div>
                </div>
                <div class="form-group">
                  <label for="noteVendor"><span class="label-text">Vendor Contact Information (Name, Phone, Email, Date of Contact)</span>:<span class="required-asterisk" aria-hidden="true">*</span></label>
                  <textarea type="text" class="form-control do-note" id="noteVendor" rows="5" minlength="1" required></textarea>
                </div>
                <div class="form-group">
                  <label for="noteQuote"><span class="label-text">Vendor Price Quote</span>:</label>
                  <input type="text" class="form-control do-note" id="noteQuote" value="">
                </div>
                <div class="form-group">
                  <label for="noteInfo"><span class="label-text">Notes/Additional Info</span>:</label>
                  <textarea type="text" class="form-control do-note" id="noteInfo" rows="5" ></textarea>
                </div>
              </fieldset>
              <div class="questions-buttons form-group no-print">
                <input id="submitProposeResourceForm" type="submit" class="btn btn-primary" value="Submit Request" disabled>
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
