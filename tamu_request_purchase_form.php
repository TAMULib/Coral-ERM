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
  	    function getCoralData(endpoint,parameters) {
  		  return $.ajax({
  					type: "GET",
  					url: coralAPI+endpoint+'/'
  				});
  	    }

        function postCoralResource($form) {
          return $.ajax({
                    type: "POST",
                    url: coralAPI+'proposeResource/',
                    data: $form.serialize()
                  }).done(function(data) {
                    if (data.resourceID) {
                      $("#doContent").html("Your proposal has been submitted. <a href='"+coralBase+"resource.php?resourceID="+data.resourceID+"'>View Now</a>");
                    } else if (data.error) {
                      $("#doContent .error").html("There was an error submitting your proposal.");
                    }
                  }).fail(function(data) {
                    $("#doContent .error").html("There was an error submitting your proposal.");
                  });
        }

  	    function getResourceTypesAsDropdown() {
  	      getCoralData('getResourceTypes').done(function(data) {
      			if (data) {
      				var html = '';
      				$.each(data,function(k,resourceType) {
      					html += ' <option value="'+resourceType.resourceTypeID+'">'+resourceType.shortName+'</option>"';
      				});
      				$("#resourceTypes").html(html);
      			}
      		});
  	    }

  	    function getAcquisitionTypesAsRadio() {
  	      getCoralData('getAcquisitionTypes').done(function(data) {
    		    if (data) {
    		      var html = '';
    			    $.each(data,function(k,acquisitionType) {
    			      html += ' <input type="radio" name="acquisitionTypeID" value="'+acquisitionType.acquisitionTypeID+'" /> '+acquisitionType.shortName;
    			    });
    			    $("#acquisitionTypes").html(html);
    		    }
    		  });
  	    }

  	    function getResourceFormatsAsDropdown() {
  	      getCoralData('getResourceFormats').done(function(data) {
      			if (data) {
      			  var html = '';
      			  $.each(data,function(k,resourceFormat) {
      			    html += ' <option value="'+resourceFormat.resourceFormatID+'">'+resourceFormat.shortName+'</option>"';
      			  });
      			  $("#resourceFormats").html(html);
      			}
     		  });
  	    }

  	    $(document).ready(function() {
    		  getResourceTypesAsDropdown();
    		  getAcquisitionTypesAsRadio();
    		  getResourceFormatsAsDropdown();

      		$("#proposeResourceForm").submit(function() {
            postCoralResource($(this));
      			return false;
      		});
  	    });
      </script>
    </head>
    <body>
      <h1>Suggest a Purchase</h1>
      <div id="doContent">
        <div class="error"></div>
        <form id="proposeResourceForm" name="proposeResourceForm" action="" method="POST">
          <input type="hidden" name="user" value="<?php echo $user; ?>">
          <fieldset>
          <legend>Product</legend>
          <div class="field">
            <label for="titleText">Title: </label>
            <input name="titleText" type="text" />
          </div>
          <div class="field">
            <label for="descriptionText">Description: </label>
            <textarea name="descriptionText"></textarea>
          </div>
          <div class="field">
            <label for="providerText">Provider: </label>
            <input name="providerText" type="text" />
          </div>
          <div class="field">
            <label for="resourceURL">URL: </label>
            <input name="resourceURL" type="text" />
          </div>

        <fieldset>
          <legend>Format</legend>
          <select id="resourceFormats" name="resourceFormatID"></select>
        </fieldset>

        <fieldset>
          <legend>Acquisition Type</legend>
          <div id="acquisitionTypes"></div>
        </fieldset>

        <fieldset>
          <legend>Resource Type</legend>
          <select id="resourceTypes" name="resourceTypeID"></select>
        </fieldset>

        <fieldset>
          <legend>Notes</legend>
          <label for="noteText">Include any additional information</label>
          <textarea name="noteText"></textarea>
        </fieldset>

        <input class="button" type="submit" name="submitProposeResourceForm" value="Send" />
      </form>
    </div>
  </body>
</html>
