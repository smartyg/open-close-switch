var $j = jQuery.noConflict();

$j(function()
{
	register_handlers();
});

function unregister_handlers()
{
	$j(".ocs_add_option").off();
	$j(".ocs_add_switch").off();
	$j(".ocs_remove_option").off();
	$j(".ocs_remove_switch").off();
}

function register_handlers()
{
	unregister_handlers();

	$j(".ocs_add_option").click(
		function()
		{
			var next = $j(this).data("next-value");
			var row = $j(this).parents("tr");
			var id = $j(this).parents("tbody").data("id");
			var ident = 'ocs_' + id + '_' + next;
			var html = '<tr class="ocs-option" data-value="' + next + '"><td><label for="' + ident + '_title">title</label></td><td colspan="2"><input type="text" id="' + ident + '_title" name="' + ident + '_title"></td><td><div><p class="ocs-button ocs-remove-option-button ocs_remove_option" title="remove this option"><span class="ui-icon ui-icon-circle-close"></span></p></div></td></tr>';
			html = html + '<tr class="ocs-option" data-value="' + next + '"><td><label for="' + ident + '_html">html code</label></td><td colspan="3"><textarea id="' + ident + '_html" name="' + ident + '_html" class="mceEditor" rows="4"></textarea></td></tr>';
			row.before(html);
			$j(this).data("next-value", next + 1);
			register_handlers();
		}
	);

	$j(".ocs_add_switch").click(
		function()
		{
			var next = $j(this).data("next-id");
			var row = $j(this).parents("tfoot");
			var ident = 'ocs_' + next;
			var html = '<tbody class="ocs-switch" data-id="' + next + '">';
			html = html + '<tr><th>id</th><th>' + next + '</th><td>usage: [ocs_display id="' + next + '"]</td><td><p class="ocs-button ocs-remove-switch-button ocs_remove_switch" title="remove this switch"><span class="ui-icon ui-icon-circle-close"></span></p></div></td></tr>';
			html = html + '<tr><td><label for="' + ident + '_name">name</label></td><td colspan="3"><input type="text" id="' + ident + '_name" name="' + ident + '_name" value="" \></td></tr>';
			html = html + '<tr><td></td><td colspan="3"><div><p class="ocs-button ocs-add-button ocs_add_option" title="add option" data-next-value="2"><span class="ui-icon ui-icon-circle-plus"></span>add option</p></div></td></tr>';
			html = html + '<tr><td colspan="4" style="padding-top: 0; padding-bottom: 0;"><hr /></td></tr>';
			html = html + '</tbody>';
			row.before(html);
			$j(this).data("next-id", next + 1);
			register_handlers();
		}
	);

	$j(".ocs_remove_switch").click(
		function()
		{
			var id = $j(this).parents("tbody").data("id");
			ocs_remove_switch(id);
		}
	);

	$j(".ocs_remove_option").click(
		function()
		{
			var id = $j(this).parents("tbody").data("id");
			var val = $j(this).parents("tr").data("value");
			ocs_remove_option(id, val);
		}
	);
}

function ocs_remove_switch(id)
{
	$j("tbody[data-id='" + id + "']").remove();
}

function ocs_remove_option(id, val)
{
	$j("tbody[data-id='" + id + "'] > tr[data-value='" + val + "']").remove();
}
