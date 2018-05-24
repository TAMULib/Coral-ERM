<form id='quickToDoForm'>
	<span id="external_error" class='error smallDarkRedText'></span>
	<table>
		<tr>
			<td><label>PO:</label></td>
			<td>
				<input type="text" name="externalId" id="externalId" />
				<span id='span_error_externalId' class='error smallDarkRedText'></span>
			</td>
		</tr>
		<tr>
			<td><label>ISSN:</label></td>
			<td>
				<input type="text" name="search[resourceISBNOrISSN]" id="toDoISBNOrISSN"/>
				<span id='span_error_search_issn' class='error smallDarkRedText'></span>
			</td>
		</tr>
	</table>
	<div id="forceDuplicate">
		<input type="checkbox" name="forceDuplicate" value="1" /> Import anyway
	</div>
	<div id="formButtons">
		<table class='noBorderTable' style='width:125px;'>
			<tr>
				<td style='text-align:left'><input class="submit-button" type='submit' value='submit' name='submitQuickToDo' id='submitQuickToDo'></td>
				<td style='text-align:right'><input class="cancel-button" type='button' value='cancel' onclick="tb_remove();"></td>
			</tr>
		</table>
	</div>
</form>
<script type="text/javascript" src="js/forms/quickToDo.js?random=<?php echo rand(); ?>"></script>