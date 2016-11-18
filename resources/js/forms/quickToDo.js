$(document).ready(function() {

	$("#quickToDoForm").live("submit", function() {
		var $formData = $("#quickToDoForm");
		var isnInput = $formData.find("#toDoISBNOrISSN");
		var externalIdInput = $formData.find("#externalId");

		if (isnInput.val()) {
			submitQuickToDo(isnInput);
		} else if (externalIdInput.val())  {
			getDataByExternalId($formData);
		}
		return false;
	});

	$("#externalId").change(function() {
		$("#external_error").text('');
		$("#forceDuplicate").fadeOut("fast");
	});
});

function enterLoadingState() {
	$("#external_error").text('');
	$('#submitQuickToDo').attr("disabled", "disabled");
	$("#formButtons").addClass("loading");
}

function exitLoadingState() {
	$("#formButtons").removeClass("loading"); 
	$('#submitQuickToDo').removeAttr("disabled");
}


function getDataByExternalId(form) {
	enterLoadingState();
	$.ajax({
		type:       "POST",
		url:        "ajax_processing.php?action=submitNewResource",
		cache:      false,
		data:       form.serialize(),
		success:    function(data) {
						resourceResult = JSON.parse(data);
						if (resourceResult.resourceID) {
							window.parent.location = "resource.php?ref=new&resourceID="+resourceResult.resourceID;
							tb_remove();
						} else if (resourceResult.error) {
							$("#external_error").text(resourceResult.error);
							if(resourceResult.isDuplicate) {
								$("#forceDuplicate").fadeIn("fast");
							}
							exitLoadingState();
						}
					},
		error: 		function() {
						exitLoadingState();
					}
	});
}

function submitQuickToDo(isnInput) {
	$('#submitQuickToDo').attr("disabled", "disabled");
	$.ajax({
		type:       "POST",
		url:        "ajax_htmldata.php?action=getSearchResources",
		cache:      false,
		data:       isnInput.serialize(),
		success:    function(html) {
						tb_remove();
						$("#searchResourceISBNOrISSN").val(isnInput.val());
						$("#div_feedback").html("&nbsp;");
						$('#div_searchResults').html(html);  
					}
	});
}