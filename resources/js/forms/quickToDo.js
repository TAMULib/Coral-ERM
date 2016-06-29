$(document).ready(function() {

	$("#quickToDoForm").live("submit", function() {
		var $formData = $("#quickToDoForm");
		var isnInput = $formData.find("#toDoISBNOrISSN");
		var externalIdInput = $formData.find("#externalId");

		if (isnInput.val()) {
			submitQuickToDo(isnInput);
		} else if (externalIdInput.val())  {
			getDataByExternalId(externalIdInput.val());
		}
		return false;
	});
});

function getDataByExternalId(externalId) {
	//load new resource form into the open thickbox modal
	$("#TB_ajaxContent").html("");
	var tbParams = {"width":730,"height":498};
	tb_resize(tbParams.width,tbParams.height);
	tb_show(null,"ajax_forms.php?action=getUpdateProductForm&height="+tbParams.height+"&width="+tbParams.width+"&modal=true&externalId="+externalId);
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