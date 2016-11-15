$(document).ready(function() {

	$("#quickToDoForm").live("submit", function() {
		var $formData = $("#quickToDoForm");
		var isnInput = $formData.find("#toDoISBNOrISSN");
		var externalIdInput = $formData.find("#externalId");

		if (isnInput.val()) {
			submitQuickToDo(isnInput);
		} else if (externalIdInput.val())  {
			getDataByExternalId(externalIdInput);
		}
		return false;
	});
});

function getDataByExternalId(externalIdInput) {
	$('#submitQuickToDo').attr("disabled", "disabled");
	$("#formButtons").addClass("loading");
	$.ajax({
		type:       "POST",
		url:        "ajax_processing.php?action=submitNewResource",
		cache:      false,
		data:       externalIdInput.serialize(),
		success:    function(data) {
						resourceResult = JSON.parse(data);
						if (resourceResult.resourceID) {
							window.parent.location = "resource.php?ref=new&resourceID="+resourceResult.resourceID;
							tb_remove();
						}
					},
		error: 		function() {
						$("#formButtons").removeClass("loading"); 
						$('#submitQuickToDo').removeAttr("disabled");
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