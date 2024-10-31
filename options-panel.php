<?php
/*
The settings page
*/

function wprn_menu_item() {
	global $wprn_settings_page_hook;
    $wprn_settings_page_hook = add_plugins_page(
        'Nofollower Settings',         			   	// The title to be displayed in the browser window for this page.
        'Nofollower Settings',			            // The text to be displayed for this menu item
        'administrator',            				// Which type of users can see this menu item  
        'wprn_settings',    						// The unique ID - that is, the slug - for this menu item
        'wprn_render_settings_page'     			// The name of the function to call when rendering this menu's page  
    );
}
add_action( 'admin_menu', 'wprn_menu_item' );

function wprn_scripts_styles($hook) {
	global $wprn_settings_page_hook;
	if( $wprn_settings_page_hook != $hook )
		return;
	wp_enqueue_style("options_panel_stylesheet", plugins_url( "static/css/options-panel.css" , __FILE__ ), false, "1.0", "all");
	wp_enqueue_script("options_panel_script", plugins_url( "static/js/options-panel.js" , __FILE__ ), false, "1.0");
	wp_enqueue_script('common');
	wp_enqueue_script('wp-lists');
	wp_enqueue_script('postbox');
}
add_action( 'admin_enqueue_scripts', 'wprn_scripts_styles' );

function wprn_render_settings_page() {
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"></div>
<h2>Nofollower Settings</h2>
	<?php settings_errors(); ?>
	<div class="clearfix paddingtop20">
		<div class="first ninecol">
			<form method="post" action="options.php">
				<?php settings_fields( 'wprn_settings' ); ?>
				<?php do_meta_boxes('wprn_metaboxes','advanced',null); ?>
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			</form>
		</div>
		<div class="last threecol">
			<div class="side-block">
				Like the plugin? Give it a good rating on CodeCanyon.
			</div>
		</div>
	</div>
</div>
<?php }

function wprn_create_options() {
	add_settings_section( 'general_settings_section', null, null, 'wprn_settings' );
	add_settings_section( 'user_settings_section', null, null, 'wprn_settings' );
	add_settings_section( 'cats_settings_section', null, null, 'wprn_settings' );
	add_settings_section( 'type_settings_section', null, null, 'wprn_settings' );

    add_settings_field(
        'nofollow_all', '', 'wprn_render_settings_field', 'wprn_settings', 'general_settings_section',
		array(
			'title' => 'Nofollow all links',
			'desc' => 'All the links in content will be nofollowed',
			'id' => 'nofollow_all',
			'type' => 'checkbox',
			'group' => 'wprn_all_settings'
		)
    );

    add_settings_field(
        'nofollow_external', '', 'wprn_render_settings_field', 'wprn_settings', 'general_settings_section',
		array(
			'title' => 'Nofollow external links only',
			'desc' => 'Leave unchecked if you want to nofollow external as well as internal links',
			'id' => 'nofollow_externals',
			'type' => 'checkbox',
			'group' => 'wprn_all_settings'
		)
    );

    add_settings_field(
        'nofollow_archives', '', 'wprn_render_settings_field', 'wprn_settings', 'general_settings_section',
		array(
			'title' => 'Nofollow archive page links',
			'desc' => 'The links on archives will be nofollowed',
			'id' => 'nofollow_archives',
			'type' => 'checkbox',
			'group' => 'wprn_all_settings'
		)
    );

	add_settings_field(
        'nofollow_users', '', 'wprn_render_settings_field', 'wprn_settings', 'user_settings_section',
		array(
			'title' => 'Nofollow Users',
			'desc' => 'Add nofollow tags to links in these users posts',
			'id' => 'nofollow_users',
			'type' => 'multicheckbox',
			'items' => wprn_get_users_array(),
			'group' => 'wprn_all_settings'
		)
    );

    add_settings_field(
        'nofollow_roles', '', 'wprn_render_settings_field', 'wprn_settings', 'user_settings_section',
		array(
			'title' => 'Nofollow Roles',
			'desc' => 'The links in posts created by these roles should be nofollowed',
			'id' => 'nofollow_roles',
			'type' => 'multicheckbox',
			'items' => wprn_get_roles_array(),
			'group' => 'wprn_all_settings'
		)
    );

    add_settings_field(
        'nofollow_cats', '', 'wprn_render_settings_field', 'wprn_settings', 'cats_settings_section',
		array(
			'title' => 'Nofollow Categories',
			'desc' => 'Add nofollow tags to posts in this category',
			'id' => 'nofollow_cats',
			'type' => 'multicheckbox',
			'items' => wprn_get_cats_array(),
			'group' => 'wprn_all_settings'
		)
    );


    add_settings_field(
        'nofollow_types', '', 'wprn_render_settings_field', 'wprn_settings', 'type_settings_section',
		array(
			'title' => 'Nofollow Types',
			'desc' => 'The links in posts of these types should be nofollowed',
			'id' => 'nofollow_types',
			'type' => 'multicheckbox',
			'items' => wprn_get_types_array(),
			'group' => 'wprn_all_settings'
		)
    );

	register_setting('wprn_settings', 'wprn_all_settings', 'wprn_settings_validation');
}
add_action('admin_init', 'wprn_create_options');

function wprn_settings_validation($input){
	return $input;
}

function wprn_add_meta_boxes(){
	add_meta_box("wprn_general_settings_metabox", 'General Settings', "wprn_metaboxes_callback", "wprn_metaboxes", 'advanced', 'default', array('settings_section'=>'general_settings_section'));
	add_meta_box("wprn_user_settings_metabox", 'Users', "wprn_metaboxes_callback", "wprn_metaboxes", 'advanced', 'default', array('settings_section'=>'user_settings_section'));
	add_meta_box("wprn_cats_settings_metabox", 'Categories', "wprn_metaboxes_callback", "wprn_metaboxes", 'advanced', 'default', array('settings_section'=>'cats_settings_section'));
	add_meta_box("wprn_type_settings_metabox", 'Post Types', "wprn_metaboxes_callback", "wprn_metaboxes", 'advanced', 'default', array('settings_section'=>'type_settings_section'));
}
add_action( 'admin_init', 'wprn_add_meta_boxes' );

function wprn_metaboxes_callback($post, $args){
	do_settings_fields( "wprn_settings", $args['args']['settings_section'] );
	submit_button('Save Changes', 'secondary', null, false);
}

function wprn_render_settings_field($args){
	$option_value = get_option($args['group']);
?>
	<div class="row clearfix">
		<div class="col colone"><?php echo $args['title']; ?></div>
		<div class="col coltwo">
	<?php if($args['type'] == 'text'): ?>
		<input type="text" id="<?php echo $args['id'] ?>" name="<?php echo $args['group'].'['.$args['id'].']'; ?>" value="<?php echo esc_attr($option_value[$args['id']]); ?>">
	<?php elseif ($args['type'] == 'select'): ?>
		<select name="<?php echo $args['group'].'['.$args['id'].']'; ?>" id="<?php echo $args['id']; ?>">
			<?php foreach ($args['options'] as $key=>$option) { ?>
				<option <?php selected($option_value[$args['id']], $key); echo 'value="'.$key.'"'; ?>><?php echo $option; ?></option><?php } ?>
		</select>
	<?php elseif($args['type'] == 'checkbox'): ?>
		<input type="hidden" name="<?php echo $args['group'].'['.$args['id'].']'; ?>" value="0" />
		<input type="checkbox" name="<?php echo $args['group'].'['.$args['id'].']'; ?>" id="<?php echo $args['id']; ?>" value="1" <?php if(isset($option_value[$args['id']]))checked($option_value[$args['id']]); ?> />
	<?php elseif($args['type'] == 'textarea'): ?>
		<textarea name="<?php echo $args['group'].'['.$args['id'].']'; ?>" type="<?php echo $args['type']; ?>" cols="" rows=""><?php if ( $option_value[$args['id']] != "") { echo stripslashes(esc_textarea($option_value[$args['id']]) ); } ?></textarea>
	<?php elseif($args['type'] == 'multicheckbox'):
		foreach ($args['items'] as $key => $checkboxitem ):
	?>
		<div class="checkbox-item">
			<input type="hidden" name="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>" value="0" />
			<input type="checkbox" name="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>" id="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>" value="1" <?php checked($option_value[$args['id']][$key]); ?> />
			<label for="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>"><?php echo $checkboxitem; ?></label>
		</div>
	<?php endforeach; ?>
	<?php elseif($args['type'] == 'multitext'):
		foreach ($args['items'] as $key => $textitem ):
	?>
		<label for="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>"><?php echo $textitem; ?></label><br/>
		<input type="text" id="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>" name="<?php echo $args['group'].'['.$args['id'].']['.$key.']'; ?>" value="<?php echo esc_attr($option_value[$args['id']][$key]); ?>"><br/>
	<?php endforeach; endif; ?>
		</div>
		<div class="col colthree"><small><?php echo $args['desc'] ?></small></div>
	</div>
<?php
}

function wprn_get_users_array(){
	$raw_users = get_users();
	$users = array();
	$user = null;
	if(is_array($raw_users) && count($raw_users) > 0)
		foreach ($raw_users as $key => $user) {
			$users[$user->user_login] = $user->display_name;
		}
	return $users;
}

function wprn_get_roles_array(){
	$raw_roles = get_editable_roles();
	$roles = array();
	$role = null;
	if(is_array($raw_roles) && count($raw_roles) > 0)
		foreach ($raw_roles as $key => $role) {
			$roles[$key] = $role['name'];
		}
	return $roles;
}

function wprn_get_cats_array(){
	$raw_cats = get_categories();
	$cats = array();
	$cat = null;
	if(is_array($raw_cats) && count($raw_cats) > 0)
		foreach ($raw_cats as $key => $cat) {
			$cats[$cat->slug] = $cat->name;
		}
	return $cats;
}

function wprn_get_types_array(){
	$raw_types = get_post_types(array('public' => true, 'publicly_queryable' => true), 'objects');
	$types = array();
	$type = null;
	if(is_array($raw_types) && count($raw_types) > 0)
		foreach ($raw_types as $key => $type) {
			$types[$key] = $type->labels->singular_name;
		}
	return $types;
}

?>