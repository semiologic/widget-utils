<?php
/*
 * Widget Utils
 * Author: Denis de Bernardy & Mike Koepke <http://www.semiologic.com>
 * Version: 2.2.1
 */

/**
 * widget_utils
 *
 * @package Widget Utils
 **/

class widget_utils {
	/**
	 * post_meta_boxes()
	 *
	 * @return void
	 **/

	static function post_meta_boxes() {
		static $done = false;

		if ( $done )
			return;

		add_meta_box('post_widget_config', __('This Post In Widgets', widget_utils_textdomain), array('widget_utils', 'post_widget_config'), 'post');
		add_action('save_post', array('widget_utils', 'post_save_widget_config'), 0);

		$done = true;
	} # post_meta_boxes()


	/**
	 * page_meta_boxes()
	 *
	 * @return void
	 **/

	static function page_meta_boxes() {
		static $done = false;

		if ( $done )
			return;

		add_meta_box('page_widget_config', __('This Page In Widgets', widget_utils_textdomain), array('widget_utils', 'page_widget_config'), 'page');
		add_action('save_post', array('widget_utils', 'page_save_widget_config'));

		$done = true;
	} # page_meta_boxes()


	/**
	 * post_widget_config()
	 *
	 * @param object $post
	 * @return void
	 **/

	static function post_widget_config($post) {
		widget_utils::widget_config('post', $post);
	} # post_widget_config()


	/**
	 * page_widget_config()
	 *
	 * @param object $post
	 * @return void
	 **/

    static function page_widget_config($post) {
		widget_utils::widget_config('page', $post);
	} # page_widget_config()


	/**
	 * post_save_widget_config()
	 *
	 * @param int $post_ID
	 * @return void
	 **/

	static function post_save_widget_config($post_ID) {
		widget_utils::save_widget_config($post_ID, 'post');
	} # post_save_widget_config()


	/**
	 * page_save_widget_config()
	 *
	 * @param int $post_ID
	 * @return void
	 **/

    static function page_save_widget_config($post_ID) {
		widget_utils::save_widget_config($post_ID, 'page');
	} # page_save_widget_config()


	/**
	 * widget_config()
	 *
	 * @param string $type
	 * @param object $post
	 * @return void
	 **/

	static function widget_config($type, $post) {
		$post_ID = $post->ID;

		echo '<p>'
			. __('The following fields let you configure options shared by:', widget_utils_textdomain)
			. '</p>' . "\n";

		echo '<ul class="ul-disc">';
		do_action($type . '_widget_config_affected');
		echo '</ul>' . "\n";

		echo '<p>'
			. __('It will <b>NOT</b> affect anything else. In particular WordPress\'s built-in Pages widget.', widget_utils_textdomain)
			. '</p>' . "\n";

		echo '<table style="width: 100%;">';

		echo '<tr valign="top">' . "\n"
			. '<th scope="row" width="120px;">'
			. __('Title', widget_utils_textdomain)
			. '</th>' . "\n"
			. '<td>'
			. '<input type="text" size="58" class="widefat" tabindex="5"'
			. ' name="widgets_label"'
			. ' value="' . esc_attr(get_post_meta($post_ID, '_widgets_label', true)) . '"'
			. ' />'
			. '</td>' . "\n"
			. '</tr>' . "\n";

		echo '<tr valign="top">' . "\n"
			. '<th scope="row">'
			. __('Description', widget_utils_textdomain)
			. '</th>' . "\n"
			. '<td>'
			. '<textarea size="58" class="widefat" tabindex="5" name="widgets_desc">'
			. esc_html(get_post_meta($post_ID, '_widgets_desc', true))
			. '</textarea>'
			. '</td>' . "\n"
			. '</tr>' . "\n";

		echo '<tr valign="top">' . "\n"
			. '<th scope="row">'
			. __('Exclude', widget_utils_textdomain)
			. '</th>' . "\n"
			. '<td>'
			. '<label>'
			. '<input type="checkbox" tabindex="5"'
			. ' name="widgets_exclude"'
			. ( get_post_meta($post_ID, '_widgets_exclude', true)
				? ' checked="checked"'
				: ''
				)
			. ' />'
			. '&nbsp;'
			. __('Exclude this entry from automatically generated lists', widget_utils_textdomain)
			. '</label>'
		 	. '</td>' . "\n"
			. '</tr>' . "\n";

		echo '<tr valign="top">' . "\n"
			. '<th scope="row">'
			. '&nbsp;'
			. '</th>' . "\n"
			. '<td>'
			. '<label>'
			. '<input type="checkbox" tabindex="5"'
			. ' name="widgets_exception"'
			. ( get_post_meta($post_ID, '_widgets_exception', true)
				? ' checked="checked"'
				: ''
				)
			. ' />'
			. '&nbsp;'
			. __('... except for silo stubs, silo maps and smart links.', widget_utils_textdomain)
			. '</label>'
		 	. '</td>' . "\n"
			. '</tr>' . "\n";

		echo '</table>' . "\n";
	} # widget_config()


	/**
	 * save_widget_config()
	 *
	 * @param int $post_ID
	 * @param string $type
	 * @return void
	 **/

    static function save_widget_config($post_ID, $type = null) {
		$post = get_post($post_ID);

		if ( $post->post_type == 'revision' || !$_POST || $post->post_type != $type )
			return;

		if ( isset($_POST['widgets_exclude']) ) {
			update_post_meta($post_ID, '_widgets_exclude', '1');

			if ( isset($_POST['widgets_exception']) )
				update_post_meta($post_ID, '_widgets_exception', '1');
			else
				delete_post_meta($post_ID, '_widgets_exception');
		} else {
			delete_post_meta($post_ID, '_widgets_exclude');
			delete_post_meta($post_ID, '_widgets_exception');
		}

		$label = addslashes(trim(strip_tags(stripslashes($_POST['widgets_label']))));

		if ( $label )
			update_post_meta($post_ID, '_widgets_label', $label);
		else
			delete_post_meta($post_ID, '_widgets_label');

		if ( current_user_can('unfiltered_html') )
			$desc = $_POST['widgets_desc'];
		else
			$desc = wp_filter_post_kses($_POST['widgets_desc']);

		if ( $desc )
			update_post_meta($post_ID, '_widgets_desc', $desc);
		else
			delete_post_meta($post_ID, '_widgets_desc');
	} # save_widget_config()
} # widget_utils
