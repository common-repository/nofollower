<?php
/*
Plugin Name: Nofollower
Plugin URI: http://wpgurus.net/
Description: Add nofollow tag to links in the content of your website.
Version: 1.0
Author: Hassan Akhtar
Author URI: http://wpgurus.net/
License: GPL2
*/

/**********************************************
*
* Replace in post content
*
***********************************************/

add_filter('the_content', 'wprn_post_nofollow');
add_filter('the_excerpt', 'wprn_post_nofollow');
function wprn_post_nofollow($content) {
	global $post;
	$nofollow 			= false;
	$user 				= get_user_by( 'id', $post->post_author );
	$roles 				= $user->roles;
	$post_categories 	= wp_get_post_categories( $post->ID );
	$type 				= $post->post_type;
	$options			= get_option('wprn_all_settings');

	if($options['nofollow_all'])
		$nofollow = true;

	if($options['nofollow_archives'] && is_archive())
		$nofollow = true;

	if($options['nofollow_users'][$user->user_login])
		$nofollow = true;

	foreach ($roles as $key => $role) {
		if($options['nofollow_roles'][$role])
			$nofollow = true;
	}

	foreach($post_categories as $cid){
		$cat = get_category( $cid );
		if($options['nofollow_cats'][$cat->slug])
			$nofollow = true;
	}

	if(isset($options['nofollow_types'][$type]) && $options['nofollow_types'][$type])
		$nofollow = true;

	if($nofollow){
		if($options['nofollow_externals'])
			return stripslashes(wp_rel_nofollow($content));
		else
			return preg_replace_callback('/<a[^>]+/', 'wprn_post_nofollow_callback', $content);
	}
	return $content;
}

function wprn_post_nofollow_callback($matches) {
    $link = $matches[0];
    $site_link = get_bloginfo('url');
    if (strpos($link, 'rel') === false) {
        $link = preg_replace("%(href=\S(?!$site_link))%i", 'rel="nofollow" $1', $link);
    } elseif (preg_match("%href=\S(?!$site_link)%i", $link)) {
        $link = preg_replace('/rel=\S(?!nofollow)\S*/i', 'rel="nofollow"', $link);
    }
    return $link;
}

/**********************************************
*
* Include Options Panel
*
***********************************************/

include('options-panel.php');