<?php
/** kate: indent-mode cstyle;
 * Plugin Name: Open Close Switch
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Een switch om snel en gemakkelijk content te wijzigen.
 * Version: 0.1
 * Author: Martijn Goedhart
 * Author URI: http://URI_Of_The_Plugin_Author
 * License: GPLv2+
 * Text Domain: ocs
 * Domain Path: /l10n
 */

/* Call the plugin inti function after the plugin is loaded. */
add_action('plugins_loaded', 'ocs_init');
/* register the shortcode [ocs_display ...] */
add_shortcode('ocs_display', 'ocs_tag_func');
/* register dashboard widget */
add_action('wp_dashboard_setup', 'ocs_dashboard_init');
/* enqueue javascripts and stylesheets */
add_action('admin_enqueue_scripts', 'ocs_admin_add_script');

//TODO: make plugin option page
//add_action('admin_menu', 'ocs_menu_init');

/**
 * Initialize the plugin.
 * This loads the correct translation.
 */
function ocs_init()
{
	load_plugin_textdomain('ocs', false, 'open-close-switch/i10n/');
}

/**
 * Register the dashboard widget and config widget at the Wordpress back-end.
 */
function ocs_dashboard_init()
{
	wp_add_dashboard_widget('ocs_dashboard', 'Open Close Switch', 'ocs_dashboard_widget', 'ocs_dashboard_widget_config');
}

function ocs_menu_init()
{
	add_options_page('My Plugin Options', 'My Plugin', 'manage_options', 'my-unique-identifier', 'my_plugin_options' );
}

/**
 * Function to register the javascripts and stylesheets used at several admin pages.
 */
function ocs_admin_add_script()
{
	global $wp_scripts;
	/* Register our script. */
	/* Alter layout of radio buttons. */
	wp_register_script('ocs-admin-script-state', plugins_url('admin_state.js', 'open-close-switch/admin_state.js'), array('jquery-ui-button'), null, true);
	/* Support dynamic add/removal of options and switches. */
	wp_register_script('ocs-admin-script-settings', plugins_url('admin_settings.js', 'open-close-switch/admin_settings.js'), array('jquery'), null, true);
	/* JQuery UI stylesheet which matches the version of the used JQuery UI javascript. */
	wp_register_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/ui-darkness/jquery-ui.min.css', array(), false, 'screen');
	/* Stylesheet to makup the config page. */
	wp_register_style('ocs-admin', plugins_url('admin.css', 'open-close-switch/admin.css'), array('jquery-ui'), false, 'screen');
}

/**
 * Print the dashboard widget.
 * With this witget the switches can be oparated.
 */
function ocs_dashboard_widget()
{
	/* Load the change state script and the jquery-ui and admin stylesheet. */
	wp_enqueue_script('ocs-admin-script-state');
	wp_enqueue_style('jquery-ui');
	wp_enqueue_style('ocs-admin');

	/* Retrieve the saved settings. */
	$settings = ocs_get_settings();

	/* Process the updated state of the switches. */
	if('post' == strtolower($_SERVER['REQUEST_METHOD']) && isset($_POST['ocs_state_update']) && $_POST['ocs_state_update'] == '1')
	{
		/* Save the new switch states and return the new settings. */
		$settings = ocs_save_state();
	}

	echo '<div class="feature_post_class_wrap"><form method="post" name="ocs_state"><input type="hidden" name="ocs_state_update" value="1" />';
	echo '<table class="form-table"><colgroup><col class="ocs-table-label"><col></colgroup>';

	/* Iterate over the registered switches and print a radio box for each switch option. */
	foreach($settings as $id => $switch)
	{
		echo '<tbody class="ocs_switch">';
		echo '<tr><td><strong>' . $switch['name'] . '</strong></td>';
		echo '<td><div class="ocs_radiobuttons">';
		foreach($switch['options'] as $val => $o)
		{
			echo '<input type="radio" id="ocs_' . $id . '_' . $val . '" name="ocs_' . $id . '_state" value="' . $val . '" ' . checked($switch['state'], $val, false)  . '><label for="ocs_' . $id . '_' . $val . '">' . $o['title'] . '</label>';
		}
		echo '</div></td></tr>';
		echo '</tbody>';
	}
	echo '</table>';

	/* Print the submit button. */
	submit_button(__('save', 'ocs'));
	echo '</form></div>';

	return;
}

/**
 * Print the dashboard config widget.
 * With this witget switches can be add, removed of modified.
 */
function ocs_dashboard_widget_config()
{
	/* Load the settings script and the jquery-ui and admin stylesheet. */
	wp_enqueue_script('ocs-admin-script-settings');
	wp_enqueue_style('jquery-ui');
	wp_enqueue_style('ocs-admin');

	/* Retrieve the saved settings. */
	$settings = ocs_get_settings();

	/* Process the updated state of the switches. */
	if('post' == strtolower($_SERVER['REQUEST_METHOD']) && isset($_POST['widget_id']) && $_POST['widget_id'] == 'ocs_dashboard')
        {
		/* Save the new switch configuration and return the new settings. */
		$settings = ocs_save_settings();
        }

	echo '<table class="form-table">';

	/* Iterate over the registered switches and print input fields for each switch value. */
	foreach($settings as $id => $switch)
	{
		echo '<tbody class="ocs-switch" data-id="' . $id . '">';
		echo '<tr><th>id</th><th>' . $id . '</th><td>' . __('usage:', 'ocs') . ' [ocs_display id="' . $id . '"]</td><td><div><p class="ocs-button ocs-remove-switch-button ocs_remove_switch" title="' . __('remove this switch', 'ocs') . '"><span class="ui-icon ui-icon-circle-close"></span></p></div></td></tr>';
		echo '<tr><td><label for="ocs_' . $id . '_name">' . __('name', 'ocs') . '</label></td><td colspan="3"><input type="text" id="ocs_' . $id . '_name" name="ocs_' . $id . '_name" value="' . $switch['name'] . '" /></td></tr>';
		foreach($switch['options'] as $val => $o)
		{
			echo '<tr class="ocs-option" data-value="' . $val . '"><td><label for="ocs_' . $id . '_' . $val . '_title">' . __('title', 'ocs') . '</label></td><td colspan="2"><input type="text" id="ocs_' . $id . '_' . $val . '_title" name="ocs_' . $id . '_' . $val . '_title" value="' . $o['title'] . '" /></td><td><div><p class="ocs-button ocs-remove-option-button ocs_remove_option" title="' . __('remove this option', 'ocs') . '"><span class="ui-icon ui-icon-circle-close"></span></p></div></td></tr>';
			echo '<tr class="ocs-option" data-value="' . $val . '"><td><label for="ocs_' . $id . '_' . $val . '_html">' . __('html code', 'ocs') . '</label></td><td colspan="3"><textarea id="ocs_' . $id . '_' . $val . '_html" name="ocs_' . $id . '_' . $val . '_html" class="mceEditor" rows="4">' . esc_textarea(html_entity_decode($o['html'])) . '</textarea></td></tr>';
		}
		echo '<tr><td></td><td colspan="3"><div><p class="ocs-button ocs-add-button ocs_add_option" title="' . __('add option', 'ocs') . '" data-next-value="' . ($val + 1) . '"><span class="ui-icon ui-icon-circle-plus"></span>' . __('add option', 'ocs') . '</p></div></td></tr>';
		echo '<tr><td colspan="4" style="padding-top: 0; padding-bottom: 0;"><hr /></td></tr>';
		echo '</tbody>';
	}

	echo '<tfoot><tr><td colspan="4"><div><p class="ocs-button ocs-add-button ocs_add_switch" title="' . __('add switch', 'ocs') . '" data-next-id="' . ($id + 1) . '"><span class="ui-icon ui-icon-circle-plus"></span>' . __('add switch', 'ocs') . '</p></div></td></tr></tfoot>';
	echo '</table>';

	return;
}

/**
 * Rewrite an occurance of tag [ocs_display id=?] with the appropriate html code based on the switch state.
 * @param[in] $attrs	Array of attributed value pairs provided in the tag. @note Attribute 'id' is required.
 * @return		The html code to replace the tag with.
 */
function ocs_tag_func($attrs)
{
	/* Check if attribute 'id' is defined. */
	if(!isset($attrs['id'])) return;
	/* Load the switch based on the value of the 'id' attribute. If no matching switch is found, return nothing. */
	if(($switch = ocs_get_settings(intval($attrs['id']))) === null) return;
	$disp = '';
	/* Check if this switch has the correct field set. */
	if(isset($switch['options'][$switch['state']]['html']))
		$disp .= html_entity_decode($switch['options'][$switch['state']]['html']);
	/* Return the html code to replace the tag with. In case the swtich did not had the requested option, return an empty string. */
	return $disp;
}

/**
 * Retrieve the saved settings.
 * Retrieve the setting of one switch or all switches if no identifier is specified.
 * @param[in] $id	Identifier of the requested switch. If no switch with this identifier is found, return 'null'.
 * @return		An array with the settings of the requested switch or all switches in case of identifier was given.
 * 			If the requested identifier is not found return 'null'. If there are no previously saved settings, return an empty array.
 */
function ocs_get_settings($id = null)
{
	/* Load the previously saved settings from the Wordpress back-end. */
	if(!$settings = get_option('ocs_settings'))
		$settings = array();

	/* Check if 'id' is a valid identifier. */
	if(is_int($id) && isset($settings[$id]))
		return $settings[$id];
	/* If no identifier is given return all switches. */
	elseif($id === null)
		return $settings;
	/* If the given identifier is not valid return 'null'. */
	else
		return null;
}

/**
 * Save new settings from POST data.
 * Update the switches name and options based on the POST data. The switch states are preserved, if the option values still exists.
 * @return	Array with the new swtich settings.
 */
function ocs_save_settings()
{
	$settings = array();

	/* Iterate over all POST data. */
	foreach($_POST as $key => $val)
	{
		/* If the key does not matches go to the next element in the array. */
		if(preg_match("/^ocs_([0-9]+)(_(name)|_([0-9]+)_(title|html))$/", $key, $matches) !== 1)
			continue;

		/* This is a OCS key. Save the value in the correct place of the settings array, according to the key anme. */
		if(count($matches) == 6 && $matches[5] == 'title')
			/* The key describes a option title. */
			$settings[$matches[1]]['options'][$matches[4]]['title'] = $val;
		if(count($matches) == 6 && $matches[5] == 'html')
			/* The key describes a option html. Use the appropriate encoding so the html code can be stored in the back-end. */
			$settings[$matches[1]]['options'][$matches[4]]['html'] = htmlentities(stripslashes($val));
		if(count($matches) == 4 && $matches[3] == 'name')
			/* The key describes a switch name. */
			$settings[$matches[1]]['name'] = $val;
	}

	/* Retrieve the old switch settings to preserve the switch states. */
	$settings_old = ocs_get_settings();

	/* Iterate over the new switches and see if there was a matching switch in the old settings. */
	foreach($settings as $id => $switch)
	{
		/* If the switch state is still valid, save it. */
		if(isset($settings_old[$id]['state']))
			$settings[$id]['state'] = ocs_validate_state($switch, $settings_old[$id]['state']);
		else
			$settings[$id]['state'] = null;
	}

	/* Save the switch settings in the Wordpress back-end. */
	update_option('ocs_settings', $settings);

	/* Return the new switch settings. */
	return $settings;
}

/**
 * Save new switch states from POST data.
 * Update the switches state based on the POST data.
 * @return	Array with the new swtich settings.
 */
function ocs_save_state()
{
	/* Retrieve all switch settings. */
	$settings = ocs_get_settings();

	/* Iterate over all switches. */
	foreach($settings as $id => $switch)
	{
		/* If the POST data contains a value for this switch, validate that value and save it as state for the switch. */
		if(isset($_POST['ocs_' . $id . '_state']))
			$settings[$id]['state'] = ocs_validate_state($switch, $_POST['ocs_' . $id . '_state']);
	}

	/* Save the switch settings in the Wordpress back-end. */
	update_option('ocs_settings', $settings);

	/* Return the new switch settings. */
	return $settings;
}

/**
 * Check if the provided value is a valid state for this switch.
 * @param[in] $switch	An array which describes one switch.
 * @param[in] $value	The value to check.
 * @return		Returns the given value if it is a valid state for the given switch, otherwise returns 'null'.
 */
function ocs_validate_state(array $switch, $value)
{
	if(isset($switch['options'][$value], $switch['options'][$value]))
		return $value;
	else
		return null;
}

?>
