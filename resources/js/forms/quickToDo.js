$(document).ready(function() {

	$("#quickToDoForm").live("submit", function() {
		var $formData = $("#quickToDoForm");
		var isnInput = $formData.find("#toDoISBNOrISSN");
		var poInput = $formData.find("#searchPO");

		if (isnInput.val()) {
			submitQuickToDo(isnInput);
		} else if (poInput.val())  {
			getDataByPO(poInput);
		}
		return false;
	});
});

function getDataByPO(poInput) {
	//load new resource modal and populate it with the data returned by the server
	tb_show(null,"ajax_forms.php?action=getNewResourceForm&height=503&width=775&resourceID=&modal=true&search[po]="+poInput.val());
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