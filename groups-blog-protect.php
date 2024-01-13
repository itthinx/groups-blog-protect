<?php
/**
 * groups-blog-protect.php
 *
 * Copyright (c) 2013-2024 "kento" Karim Rahimpur www.itthinx.com
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
 * Description: Protect access to blogs via group memberships powered by <a href="https://wordpress.org/plugins/groups/">Groups</a>.
 * Version: 1.4.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: itthinx
 * Author URI: https://www.itthinx.com
 * Donate-Link: https://www.itthinx.com
 * Text Domain: groups-blog-protect
 * Domain Path: /languages
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
		if ( is_admin() ) {
			add_filter( 'plugin_action_links_'. plugin_basename( __FILE__ ), array( __CLASS__, 'admin_settings_link' ) );
		}
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
		}
	}

	/**
	 * Adds plugin links.
	 *
	 * @param array $links
	 * @param array $links with additional links
	 *
	 * @return array links
	 */
	public static function admin_settings_link( $links ) {
		$links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=groups-blog-protect' ) ),
			esc_html__( 'Settings', 'groups-blog-protect' )
		);
		$links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( 'https://docs.itthinx.com/document/groups-blog-protect' ),
			esc_html__( 'Documentation', 'groups-blog-protect' )
		);
		return $links;
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

		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Access denied.', 'groups-blog-protect' ) );
		}

		if ( !self::groups_is_active() ) {
			echo '<p>';
			echo sprintf(
				esc_html__( 'Please install and activate %s to use this plugin.', 'groups-blog-protect' ),
				'<a href="https://wordpress.org/plugins/groups/">Groups</a>'
			);
			echo '</p>';
			return;
		}

		$http_status_codes = array(
			'301' => __( 'Moved Permanently', 'groups-blog-protect' ),
			'302' => __( 'Found', 'groups-blog-protect' ),
			'303' => __( 'See Other', 'groups-blog-protect' ),
			'307' => __( 'Temporary Redirect', 'groups-blog-protect' )
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

			echo '<p class="info">';
			echo esc_html__( 'The settings have been saved.', 'groups-blog-protect' );
			echo '</p>';
		}

		$redirect_to     = Groups_Options::get_option( 'groups-blog-protect-to', 'login' );
		$post_id         = Groups_Options::get_option( 'groups-blog-protect-post-id', '' );
		$redirect_status = Groups_Options::get_option( 'groups-blog-protect-status', '301' );

		echo '<h1>';
		echo esc_html__( 'Groups Blog Protect', 'groups-blog-protect' );
		echo '</h1>';

		echo '<div class="settings">';
		echo '<form name="settings" method="post" action="">';
		echo '<div>';

		echo '<h2>' . esc_html__( 'Redirection', 'groups-blog-protect' ) . '</h2>';

		echo '<p>';
		echo '<label>';
		echo sprintf( '<input type="radio" name="redirect_to" value="none" %s />', $redirect_to == 'none' ? ' checked="checked" ' : '' );
		echo ' ';
		echo esc_html__( 'Do not redirect', 'groups-blog-protect' );
		echo '</label>';
		echo '</p>';

		echo '<label>';
		echo sprintf( '<input type="radio" name="redirect_to" value="post" %s />', $redirect_to == 'post' ? ' checked="checked" ' : '' );
		echo ' ';
		echo esc_html__( 'Redirect to a post', 'groups-blog-protect' );
		echo '</label>';

		echo '<div style="margin: 1em 0 0 2em">';

		echo '<label>';
		echo esc_html__( 'Post ID', 'groups-blog-protect' );
		echo ' ';
		echo sprintf( '<input type="text" name="post_id" value="%s" />', $post_id );
		echo '</label>';

		if ( !empty( $post_id ) ) {
			$post_title = get_the_title( $post_id );
			echo '<div>';
			echo sprintf( esc_html__( 'Post title: %s', 'groups-blog-protect' ), esc_html( $post_title ) );
			echo '</div>';
		}

		echo '<div class="description">';
		echo esc_html__( 'Indicate the ID of a post to redirect to, leave it empty to redirect to the home page.', 'groups-blog-protect' );
		echo '<br/>';
		echo esc_html__( 'The title of the post will be shown if a valid post ID has been given.', 'groups-blog-protect' );
		echo '</div>';

		echo '</div>';

		echo '<p>';
		echo '<label>';
		echo sprintf( '<input type="radio" name="redirect_to" value="login" %s />', $redirect_to == 'login' ? ' checked="checked" ' : '' );
		echo ' ';
		echo esc_html__( 'Redirect to the WordPress login', 'groups-blog-protect' );
		echo '</label>';
		echo '</p>';

		echo '<div style="border-top:1px solid #eee; margin-top:1em; padding-top: 1em;"></div>';

		echo '<h2>';
		echo esc_html__( 'Status Code', 'groups-blog-protect' );
		echo '</h2>';

		echo '<p>';
		echo '<label>';
		echo esc_html__( 'Redirect Status Code', 'groups-blog-protect' );
		echo ' ';
		echo '<select name="status">';
		foreach ( $http_status_codes as $code => $name ) {
			echo sprintf(
				'<option value="%s" %s >%s (%s)</option>',
				esc_attr( $code ),
				$redirect_status === $code ? ' selected="selected" ' : '',
				esc_html( $name ),
				esc_html( $code )
			);
		}
		echo '</select>';
		echo '</label>';
		echo '</p>';

		wp_nonce_field( 'admin', 'groups-blog-protect', true, true );

		echo '<br/>';

		echo '<div class="buttons">';
		echo sprintf( '<input class="create button button-primary" type="submit" name="submit" value="%s" />', esc_html__( 'Save', 'groups-blog-protect' ) );
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

		if ( class_exists( 'Groups_User' ) ) { // faster than self::groups_is_active

			$protecting_group_name = Groups_Registered::REGISTERED_GROUP_NAME;
			if ( defined( 'GROUPS_BLOG_PROTECT_GROUP' ) ) {
				if ( is_string( GROUPS_BLOG_PROTECT_GROUP ) ) {
					$protecting_group_name = GROUPS_BLOG_PROTECT_GROUP;
				}
			}
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();
				if ( defined( 'GROUPS_BLOG_PROTECT_GROUP_' . $blog_id ) ) {
					if ( is_string( GROUPS_BLOG_PROTECT_GROUP . $blog_id ) ) {
						$protecting_group_name = GROUPS_BLOG_PROTECT_GROUP . $blog_id;
					}
				} else if ( defined( 'GROUPS_BLOG_PROTECT_GROUP' ) ) {
					if ( is_string( GROUPS_BLOG_PROTECT_GROUP ) ) {
						$protecting_group_name = GROUPS_BLOG_PROTECT_GROUP;
					}
				}
			}
			$protecting_group_name = trim( $protecting_group_name );
			$protecting_group = Groups_Group::read_by_name( $protecting_group_name );
			if ( !$protecting_group ) {
				error_log( sprintf( 'Groups Blog Protect is set to protect using the group %s but the group does not exist.', esc_html( $protecting_group_name ) ) );
				if ( $protecting_group !== Groups_Registered::REGISTERED_GROUP_NAME ) {
					$protecting_group = Groups_Group::read_by_name( Groups_Registered::REGISTERED_GROUP_NAME );
					if ( !$protecting_group ) {
						error_log( sprintf( 'Groups Blog Protect tried to protect using the group %s as a fallback but it does not exist.', esc_html( Groups_Registered::REGISTERED_GROUP_NAME ) ) );
					}
				}
			}

			$user_id = get_current_user_id();
			$groups_user = new Groups_User( $user_id );
			// must be a member of the Registered group to access
			if ( !$groups_user->is_member( $protecting_group->group_id ) ) {

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
	 *
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
