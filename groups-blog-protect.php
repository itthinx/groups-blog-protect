<?php
/**
 * groups-blog-protect.php
 *
 * Copyright (c) 2013 "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package groups-blog-protect
 * @since groups-blog-protect 1.0.0
 *
 * Plugin Name: Groups Blog Protect
 * Plugin URI: http://www.itthinx.com/plugins/groups
 * Description: Protect access to blogs by group membership.
 * Version: 1.0.0
 * Author: itthinx
 * Author URI: http://www.itthinx.com
 * Donate-Link: http://www.itthinx.com
 * License: GPLv3
 */

define( 'GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN', 'groups-blog-protect' );

/**
 * Redirection.
 */
class Groups_Blog_Protect {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		// register_activation_hook(__FILE__, array( __CLASS__,'activate' ) );
		register_deactivation_hook(__FILE__,  array( __CLASS__,'deactivate' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ) );
	}

	/**
	 * Nothing to do.
	 */
	public static function activate() {
	}

	/**
	 * Delete settings.
	 */
	public static function deactivate() {
		if ( self::groups_is_active() ) {
			Groups_Options::delete_option( 'groups-blog-protect-to' );
			Groups_Options::delete_option( 'groups-blog-protect-post-id' );
			Groups_Options::delete_option( 'groups-blog-protect-groups' );
		}
	}

	/**
	 * Add the Settings > Groups Blog Protect section.
	 */
	public static function admin_menu() {
		add_options_page(
			'Groups Blog Protect',
			'Groups Blog Protect',
			'manage_options',
			'groups-blog-protect',
			array( __CLASS__, 'settings' )
		);
	}

	/**
	 * Admin settings.
	 */
	public static function settings() {

		global $wpdb;
		
		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Access denied.', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN ) );
		}

		if ( !self::groups_is_active() ) {
			echo '<p>';
			echo __( 'Please install and activate <a href="http://wordpress.org/extend/plugins/groups/">Groups</a> to use this plugin.', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN );
			echo '</p>';
			return;
		}

		$http_status_codes = array(
			'301' => __( 'Moved Permanently', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN ),
			'302' => __( 'Found', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN ),
			'303' => __( 'See Other', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN ),
			'307' => __( 'Temporary Redirect', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN )
		);

		if ( isset( $_POST['action'] ) && ( $_POST['action'] == 'save' ) && wp_verify_nonce( $_POST['groups-blog-protect'], 'admin' ) ) {

			if ( !empty( $_POST['redirect_to'] ) ) {
				switch( $_POST['redirect_to'] ) {
					case 'none' :
					case 'post' :
					case 'login' :
						Groups_Options::update_option( 'groups-blog-protect-to', $_POST['redirect_to'] );
						break;
				}
			}

			if ( !empty( $_POST['post_id'] ) ) {
				Groups_Options::update_option( 'groups-blog-protect-post-id', intval( $_POST['post_id'] ) );
			} else {
				Groups_Options::delete_option( 'groups-blog-protect-post-id' );
			}
			
			if ( key_exists( $_POST['status'], $http_status_codes ) ) {
				Groups_Options::update_option( 'groups-blog-protect-status', $_POST['status'] );
			}
			
			$access_groups_selected = array();
			if ( !empty( $_POST['access_groups'] ) ) {
				$access_groups = $_POST['access_groups'];
				foreach( $access_groups as $access_group ) {
						$access_groups_selected[] = $access_group;
				}
			}
			Groups_Options::update_option( 'groups-blog-protect-groups', $access_groups_selected );
			
			echo
			'<p class="info">' .
			__( 'The settings have been saved.', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN ) .
			'</p>';
		}

		$redirect_to     = Groups_Options::get_option( 'groups-blog-protect-to', 'login' );
		$post_id         = Groups_Options::get_option( 'groups-blog-protect-post-id', '' );
		$redirect_status = Groups_Options::get_option( 'groups-blog-protect-status', '301' );
		
		$registered_group = Groups_Group::read_by_name(Groups_Registered::REGISTERED_GROUP_NAME);
		$access_groups_selected = Groups_Options::get_option( 'groups-blog-protect-groups', array($registered_group->group_id) );
		
		echo '<h1>';
		echo __( 'Groups Blog Protect', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN );
		echo '</h1>';

		echo '<div class="settings">';
		echo '<form name="settings" method="post" action="">';
		echo '<div>';

		echo '<h2>' . __( 'Redirection', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN ) . '</h2>';

		echo '<p>';
		echo '<label>';
		echo sprintf( '<input type="radio" name="redirect_to" value="none" %s />', $redirect_to == 'none' ? ' checked="checked" ' : '' );
		echo ' ';
		echo __( 'Do not redirect', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		echo '<label>';
		echo sprintf( '<input type="radio" name="redirect_to" value="post" %s />', $redirect_to == 'post' ? ' checked="checked" ' : '' );
		echo ' ';
		echo __( 'Redirect to a post', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN );
		echo '</label>';

		echo '<div style="margin: 1em 0 0 2em">';

		echo '<label>';
		echo __( 'Post ID', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN );
		echo ' ';
		echo sprintf( '<input type="text" name="post_id" value="%s" />', $post_id );
		echo '</label>';

		if ( !empty( $post_id ) ) {
			$post_title = get_the_title( $post_id );
			echo '<div>';
			echo sprintf( __( 'Post title: %s', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN ), $post_title );
			echo '</div>';
		}

		echo '<div class="description">';
		echo __( 'Indicate the ID of a post to redirect to, leave it empty to redirect to the home page.', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN );
		echo '<br/>';
		echo __( 'The title of the post will be shown if a valid post ID has been given.', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN );
		echo '</div>';
		
		echo '</div>';

		echo '<p>';
		echo '<label>';
		echo sprintf( '<input type="radio" name="redirect_to" value="login" %s />', $redirect_to == 'login' ? ' checked="checked" ' : '' );
		echo ' ';
		echo __( 'Redirect to the WordPress login', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN );
		echo '</label>';
		echo '</p>';

		echo '<div style="border-top:1px solid #eee; margin-top:1em; padding-top: 1em;"></div>';

		echo '<h2>' . __( 'Status Code', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN ) . '</h2>';

		echo
			'<p>' .
			'<label>' .
			__( 'Redirect Status Code', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN ) .
			' ' .
			'<select name="status">';
		foreach ( $http_status_codes as $code => $name ) {
			echo '<option value="' . esc_attr( $code ) . '" ' . ( $redirect_status == $code ? ' selected="selected" ' : '' ) . '>' . $name . ' (' . $code . ')' . '</option>';
		}
		echo
			'</select>' .
			'</label>' .
			'</p>';

		// blancoleon - 20130320
		
		echo '<div style="border-top:1px solid #eee; margin-top:1em; padding-top: 1em;"></div>';

		echo '<h2>' . __( 'Groups', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN ) . '</h2>';
		
		echo '<div class="description">';
		echo __( 'Groups that can access to blog.', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN );
		echo '</div>';
		
		$group_table = _groups_get_tablename( 'group' );
		
		$query = $wpdb->prepare("SELECT * FROM $group_table", array());
	
		$results = $wpdb->get_results( $query, OBJECT );
		
		echo '<ul>';
		foreach( $results as $group ) {
			$checked = in_array( $group->group_id, $access_groups_selected ) ? ' checked="checked" ' : '';
			echo '<li>';
			echo '<label>';
			echo '<input name="access_groups[]" type="checkbox" value="' . $group->group_id . '" ' . $checked . '/>';
			echo $group->name;
			echo '</label>';
			echo '</li>';
		}
		echo '<ul>';
		
		
		wp_nonce_field( 'admin', 'groups-blog-protect', true, true );

		echo '<br/>';

		echo '<div class="buttons">';
		echo sprintf( '<input class="create button" type="submit" name="submit" value="%s" />', __( 'Save', GROUPS_BLOG_PROTECT_PLUGIN_DOMAIN ) );
		echo '<input type="hidden" name="action" value="save" />';
		echo '</div>';

		echo '</div>';
		echo '</form>';
		echo '</div>';
		
		
	}

	/**
	 * Handles template redirection.
	 */
	public static function template_redirect() {

		global $wp_query;

		if ( class_exists( 'Groups_User_Group' ) ) { // faster than self::groups_is_active

			// blancoleon - 20130320
			
			$registered_group = Groups_Group::read_by_name(Groups_Registered::REGISTERED_GROUP_NAME);
			$access_groups_selected = Groups_Options::get_option( 'groups-blog-protect-groups', array($registered_group->group_id) );
		
			$user = new Groups_User( get_current_user_id() );
			$user_groups = $user->groups;

			$user_groups_ids = array();
			foreach ($user_groups as $user_group) {
				$user_groups_ids[] = $user_group->group->group_id;
			}
			
			$intersect = array_intersect($access_groups_selected, $user_groups_ids);
			
			// must be a member of any group selected 
			if (sizeof($intersect) <= 0) {
				$redirect_to     = Groups_Options::get_option( 'groups-blog-protect-to', 'login' );
				$post_id         = Groups_Options::get_option( 'groups-blog-protect-post-id', '' );
				$redirect_status = intval( Groups_Options::get_option( 'groups-blog-protect-status', '301' ) );

				$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

				$current_post_id = url_to_postid( $current_url );
				if ( !$current_post_id ) {
					$current_post_id = $wp_query->get_queried_object_id();
				}

				if ( $current_post_id !== $post_id ) {

					switch( $redirect_to ) {

						case 'login' :
							if ( $current_url !== wp_login_url( $current_url ) ) {
								wp_redirect( wp_login_url( $current_url ), $redirect_status );
								exit;
							}
							break;

						case 'post' :
							if ( empty( $post_id ) ) {
								if ( !is_home() ) {
									wp_redirect( get_home_url(), $redirect_status );
									exit;
								}
							} else {
								if ( untrailingslashit( $current_url ) !== untrailingslashit( get_permalink( $post_id ) ) ) {
									wp_redirect( get_permalink( $post_id ), $redirect_status );
									exit;
								}
							}
							break;

						// default is 'none' and no action needs to be taken
					}

				}
			}
		}

	}

	/**
	 * Returns true if the Groups plugin is active.
	 * @return boolean true if Groups is active
	 */
	private static function groups_is_active() {
		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_sitewide_plugins = array_keys( $active_sitewide_plugins );
			$active_plugins = array_merge( $active_plugins, $active_sitewide_plugins );
		}
		return in_array( 'groups/groups.php', $active_plugins ); 
	}
}
Groups_Blog_Protect::init();
