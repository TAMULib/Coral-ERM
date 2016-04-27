$(document).ready(function() {
	$("#submitQuickToDo").live("click", function(e) {
		e.preventDefault();
		submitQuickToDo($("#quickToDoForm"));
	});
});

function submitQuickToDo(formData) {
	$('#submitQuickToDo').attr("disabled", "disabled"); 
	$.ajax({
		type:       "POST",
		url:        "ajax_htmldata.php?action=getSearchResources",
		cache:      false,
		data:       formData.serialize(),
		success:    function(html) {
						tb_remove();
						$("#searchResourceISBNOrISSN").val(formData.find("#toDoISBNOrISSN").val());
						$("#div_feedback").html("&nbsp;");
						$('#div_searchResults').html(html);  
					}
	});
}