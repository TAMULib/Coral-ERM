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
	//load new resource form into the open thickbox modal
	tb_show(null,"ajax_forms.php?action=getUpdateProductForm&height=498&width=730&modal=true&search[po]="+poInput.val());
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