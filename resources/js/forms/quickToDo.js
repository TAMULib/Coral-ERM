$(document).ready(function() {
	$("#submitQuickToDo").live("click", function(e) {
		e.preventDefault();
		submitQuickToDo($("#quickToDoForm"));
	});
});

function submitQuickToDo(formData) {
	console.log(formData);
	$('#submitQuickToDo').attr("disabled", "disabled"); 
	  $.ajax({
		 type:       "POST",
		 url:        "ajax_processing.php?action=submitQuickToDo",
		 cache:      false,
		 data:       formData.serialize(),
		 success:    function(html) {
			if (html){
				$("#span_errors").html(html);
				$("#submitQuickToDo").removeAttr("disabled");
			}else{
				window.parent.tb_remove();
				return false;
			}			
		 }
	 });
}