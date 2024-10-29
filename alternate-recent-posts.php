<?php
/*
Plugin Name: Alternate Recent Posts Widget
Plugin URI: http://geeklad.com/alternate-recent-posts-widget
Description: The default WordPress recent posts widget displays the most recent posts regardless of what posts are currently displayed on the screen.  If your front page is already displaying  your most recent posts, it can render the default Recent Posts Widget nearly useless.  This alternate version of the Recent Posts widget will exclude any posts that are currently displayed in the main content of the current page.
Author: GeekLad
Version: 0.2
Author URI: http://geeklad.com/
*/

// His tutorial is located here: http://lonewolf-online.net/computers/wordpress/create-widgets/
// Used Lonewolf's widget tutorial to get started with developing a widget plugin.
// The majority of the rest of the code is a slightly modified version of the recent entries widget in the widgets.php file (from WordPress v2.6.2)

function alternateRecentPosts_control() {
	$options = $newoptions = get_option('widget_alternateRecentPosts');
	if ( $_POST["alternate-recent-posts-submit"] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["alternate-recent-posts-title"]));
		$newoptions['number'] = (int) $_POST["alternate-recent-posts-number"];
		$newoptions['props'] = $_POST["alternate-recent-posts-credit"];
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_alternateRecentPosts', $options);
	}
	$title = attribute_escape($options['title']);
	if ( !$number = (int) $options['number'] )
		$number = 5;
	if ( empty($options['props']) )
		$props = "Yes";
	else
		$props = $options['props'];
?>

			<p><label for="alternate-recent-posts-title"><?php _e('Title:'); ?> <input class="widefat" id="alternate-recent-posts-title" name="alternate-recent-posts-title" type="text" value="<?php echo $title; ?>" /></label></p>
			<p>
				<label for="alternate-recent-posts-number"><?php _e('Number of posts to show:'); ?> <input style="width: 25px; text-align: center;" id="alternate-recent-posts-number" name="alternate-recent-posts-number" type="text" value="<?php echo $number; ?>" /></label>
				<br />
				<small><?php _e('(at most 15)'); ?></small>
				<br />
				<br />
				<label for="alternate-recent-posts-title"><?php _e('Give <a href="http://geeklad.com">GeekLad</a> props for the gadget?'); ?><select id="alternate-recent-posts-credit" name="alternate-recent-posts-credit"><option value="Yes"<?php if($props == "Yes") echo ' selected="selected"'; ?>>Yes</option><option value="No"<?php if($props == "No") echo ' selected="selected"'; ?>>No</option></select>
				<br />
			</p>
			<input type="hidden" id="alternate-recent-posts-submit" name="alternate-recent-posts-submit" value="1" />
<?php
}

function alternateRecentPosts_saveAlreadyDisplayed($content) {
	global $id, $posts_already_displayed;
	
	if ( !isset($posts_already_displayed ) ) {
		$posts_already_displayed = array();
	}
	$posts_already_displayed[] = $id;
	return $content;
}

function widget_alternateRecentPosts($args) {
	global $posts_already_displayed;

	if ( '%BEG_OF_TITLE%' != $args['before_title'] ) {
		if ( $output = wp_cache_get('widget_alternateRecentPosts', 'widget') )
			return print($output);
		ob_start();
	}

	extract($args);
	$options = get_option('widget_alternateRecentPosts');
	$title = empty($options['title']) ? __('Recent Posts') : apply_filters('widget_title', $options['title']);
	if ( !$number = (int) $options['number'] )
		$number = 5;
	else if ( $number < 1 )
		$number = 1;
	else if ( $number > 15 )
		$number = 15;
	if ( empty($options['props']) ||  $options['props'] == "Yes" )
		$title .= "<span style=\"text-transform: none; font-family: Tahoma,Verdana,Arial; font-size: 9px; line-height: 9px;\"><br><a href=\"http://geeklad.com/alternate-recent-posts-widget\">Alternate Recent Posts Widget</a></span><br />";

	$r = new WP_Query(array('showposts' => 100, 'what_to_show' => 'posts',  'nopaging' => 0, 'post_status' => 'publish'));
	if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . $title . $after_title; ?>
			<ul>
			<?php $postcount = 1; while ($r->have_posts() && $postcount <= $number) : $r->the_post(); ?>
			<?php if ( @ !in_array(get_the_ID(), $posts_already_displayed) ) : ?>
			<li><a href="<?php the_permalink() ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); $postcount++;?></a></li><?php endif; ?>
			<?php endwhile; ?>
			</ul>
		<?php echo $after_widget; ?>
<?php
		wp_reset_query();  // Restore global post data stomped by the_post().
	endif;

	if ( '%BEG_OF_TITLE%' != $args['before_title'] )
		wp_cache_add('widget_alternateRecentPosts', ob_get_flush(), 'widget');	
}

function alternateRecentPosts_init()
{
	register_sidebar_widget(__('Alternate Recent Posts'), 'widget_alternateRecentPosts');
	register_widget_control(__('Alternate Recent Posts'), 'alternateRecentPosts_control' );
}
add_action("plugins_loaded", "alternateRecentPosts_init");
add_filter("the_content", "alternateRecentPosts_saveAlreadyDisplayed");
?>
