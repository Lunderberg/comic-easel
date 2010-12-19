<?php

// actions
add_action('admin_menu', 'ceo_add_menu_pages');
add_action('wp_dashboard_setup', 'ceo_add_dashboard_widgets' );

// add_action( 'admin_notices', 'ceo_test_information' );

// INIT ComicPress Manager pages & hook activation of scripts per page.
function ceo_add_menu_pages() {
	global $pagenow, $post_type;
	
	$menu_location = 'edit.php?post_type=comic';
	$plugin_title = __('Comic Easel', 'comiceasel');
	$image_title = __('Image Manager', 'comiceasel');
	$chapter_title = __('Chapter Manager', 'comiceasel');
	$config_title = __('Config', 'comiceasel');
	$debug_title = __('Debug', 'comiceasel');
	$upload_title = __('Upload', 'comiceasel');
	
	// the ceo_pluginfo used here actually initiates it.
	$image_manager_hook = add_submenu_page($menu_location,  $plugin_title . ' - ' . $image_title, $image_title, 'edit_theme_options', 'comiceasel-image-manager', 'ceo_image_manager');
	$chapter_manager_hook = add_submenu_page($menu_location, $plugin_title . ' - ' . $chapter_title, $chapter_title, 'edit_theme_options', 'comiceasel-chapter-manager', 'ceo_chapter_manager');
	$config_hook = add_submenu_page($menu_location, $plugin_title . ' - ' . $config_title, $config_title, 'edit_theme_options', 'comiceasel-config', 'ceo_manager_config');
	$debug_hook = add_submenu_page($menu_location, $plugin_title . ' - ' . $debug_title, $debug_title, 'edit_theme_options', 'comiceasel-debug', 'ceo_debug');
	$upload_hook = add_submenu_page($menu_location, $plugin_title . ' - ' . $upload_title, $upload_title, 'edit_theme_options', 'comiceasel-upload', 'ceo_upload');

	// post_type is only found on the post-new.php with $_GET, so when the $pagenow is post.php it will not be able to strictly determine the post type so it will be executed on all already made post/page edits
	if (($_GET['post_type'] == 'comic') || ($post_type == 'comic'))   {
		
	}
	
	// Notice how its checking the _GET['page'], do this for the other areas
	// if you need to execute scripts on the particular areas
	if (isset($_GET['page'])) {
		switch ($_GET['page']) {
			case 'comiceasel-chapter-manager':
				add_action('admin_print_scripts-' . $chapter_manager_hook, 'ceo_load_scripts_chapter_manager');
				break;
			case 'comiceasel-image-manager':
			default:
				add_action('admin_print_scripts-' . $image_manager_hook, 'ceo_load_scripts_image_manager');
				add_action('admin_print_styles-' . $image_manager_hook, 'ceo_load_styles_image_manager');
				break;
		}
	}
}

function ceo_load_scripts_chapter_manager() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-sortable');
}

function ceo_load_scripts_image_manager() {
	wp_enqueue_script('comiceasel-fileuploader-script', ceo_pluginfo('plugin_url') .'/js/fileuploader.js');		
}

function ceo_load_styles_image_manager() {
	wp_enqueue_style('comiceasel-fileuploader-style', ceo_pluginfo('plugin_url') .'/css/fileuploader.css');
}

// This is done this way to *not* load pages unless they are called, self sufficient code,
// but since attached to the ceo-core it can use the library in core. so the global functions used in multiple areas
// go into the ceo-core.php file, while local functions that are only run on the individual pages go on those pages
// the "forms" if there are any call the same page back up.

function ceo_image_manager() {
	require_once('ceo-image-manager.php');
}

function ceo_chapter_manager() {
	require_once('ceo-chapter-manager.php');
}

function ceo_manager_config() {
	require_once('ceo-config.php');
}

function ceo_debug() {
	require_once('ceo-debug.php');
}

function ceo_upload() {
	require_once('ceo-upload.php');
}

/**
 * This set of functions is for displaying the dashboard feed widget.
 *
 */
function ceo_dashboard_feed_widget() {
	wp_widget_rss_output('http://frumph.net/?feed=rss2', array('items' => 2, 'show_summary' => true));
} 

function ceo_add_dashboard_widgets() {
	wp_add_dashboard_widget('ceo_dashboard_widget', 'Frumph.NET News', 'ceo_dashboard_feed_widget');	
}

// ajax stuff
// if both logged in and not logged in users can send this AJAX request,
// add both of these actions, otherwise add only the appropriate one
// add_action( 'wp_ajax_nopriv_ceo_comic_upload', 'myajax_submit' );
add_action( 'wp_ajax_ceo_uploader', 'ceo_comic_upload' );

function ceo_comic_upload() {
	require_once('functions/uploader.php');
	$result = ceo_handleUpload();
	// to pass data through iframe you will need to encode all html tags
	echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
	exit;
}

add_action( 'wp_ajax_ceo_thumb_update', 'ceo_comic_thumb_update' );

function ceo_comic_thumb_update() {
	$post = get_post( $_GET['post_id']);
	echo ceo_display_comic_thumbnail('small', $post , false, 198); 
	exit;
}

add_action( 'wp_ajax_ceo_comic_add', 'ceo_comic_add' );

function ceo_comic_add() {
	$comicfiles = $_GET['comicfile'];
	if (!($comicfiles == '')){
		$pieces = explode(",", $comicfiles);
		foreach ($pieces as $comicfile){
			add_post_meta($_GET['post_id'], 'comic', $comicfile, false);
		}
	}
	return 'success';
	exit;
}

add_action('admin_head', 'comic_admin_css');
function comic_admin_css() {

    global $post_type;

    if (($_GET['post_type'] == 'comic') || ($post_type == 'comic')) :

        echo "<link type='text/css' rel='stylesheet' href='" . plugins_url('/css/fileuploader.css', __FILE__) . "' />";
		echo "<script type='text/javascript' src='". plugins_url('/js/fileuploader.js',  __FILE__) . "'></script>";
    endif;

}

?>