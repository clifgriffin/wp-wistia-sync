<?php
/*
Plugin Name: Wistia Sync
Plugin URI: https://cgd.io
GitHub Plugin URI: https://github.com/clifgriffin/wp-wistia-sync
Description: Sync Wistia video view count to meta data on WordPress post object.
Version: 1.0.0
Author: CGD Inc.
Author URI: http://cgd.io

------------------------------------------------------------------------
Copyright 2009-2011 Clif Griffin Development Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

// ActiveCampaign API
if ( ! class_exists('WistiaApi') ) {
	require_once("lib/wistia-api/WistiaApi.class.php");
}

// WordPress Simple Settings
if ( ! class_exists('WordPress_SimpleSettings') ) {
	require('lib/wordpress-simple-settings/wordpress-simple-settings.php');
}

class CGD_WistiaSync extends WordPress_SimpleSettings {
	var $prefix = '_cgd_wistia_';
	var $api_key = false;
	var $post_type = false;
	var $video_id_meta_key = false;
	var $play_count_meta_key = false;
	var $schedule = false;
	var $hook = "cgd_wistia_sync";
	var $wistia_api = false;
	var $debug = false;

	public function __construct() {
		parent::__construct();

		// Silence is golden
	}

	function activate() {}

	function deactivate() {
		wp_clear_scheduled_hook($this->hook);
	}

	function start() {
		// Load settings
		$this->load_settings();

		// Setup Wistia API
		if ( ! empty($this->api_key) ) {
			$this->wistia_api = new WistiaApi( $this->api_key );
		}

		// Admin menu
		add_action('admin_menu', array($this, 'menu') );

		// Add save action
		add_action("{$this->prefix}_settings_saved", array($this, 'settings_saved') );

		// Add the sync action
		add_action($this->hook, array($this, 'run_sync') );

		if ( $this->debug && isset($_GET['debug_run_wistia_sync_now']) ) {
			error_log('Debug: Manually running sync.');
			$this->run_sync();
		}
	}

	function menu () {
		add_options_page("Wistia Sync", "Wistia Sync", 'manage_options', "wistia-sync", array($this, 'admin_page') );
	}

	function admin_page() {
		include 'partials/admin.php';
	}

	function load_settings() {
		$this->api_key = $this->get_setting('api_key');
		$this->post_type = $this->get_setting('post_type');
		$this->video_id_meta_key = $this->get_setting('video_id_meta_key');
		$this->play_count_meta_key = $this->get_setting('play_count_meta_key');
		$this->schedule = $this->get_setting('schedule');
	}

	function run_sync() {
		if ( ! $this->can_run_sync() ) return;

		// Get all posts of post type
		$all_posts = get_posts( array('post_type' => $this->post_type, 'showposts' => -1, 'post_status' => 'publish', 'suppress_filters' => true, ) );

		// Go through each post and sync
		foreach( $all_posts as $post ) {
			$video_id = get_post_meta($post->ID, $this->video_id_meta_key, true);

			if ( empty($video_id) ) {
				if ( $this->debug ) {
					error_log("Video ID for {$post->ID} is missing. Skipping.");
				}

				continue;
			}

			$response = $this->wistia_api->mediaShowStats($video_id);

			if ( $this->debug ) {
				error_log($video_id);
				error_log( print_r($response, true) );
			}

			if ( is_object($response) && isset($response->stats) ) {
				update_post_meta( $post->ID, $this->play_count_meta_key, $response->stats->plays );
			}
		}
	}

	function settings_saved() {
		$this->load_settings();

		if ( ! $this->can_run_sync() ) return;

		// Otherwise, reschedule
		wp_clear_scheduled_hook($this->hook);
		wp_schedule_event( time(), $this->schedule, $this->hook );
	}

	function can_run_sync() {
		if (
			empty($this->api_key) ||
			empty($this->post_type) ||
			empty($this->video_id_meta_key) ||
			empty($this->play_count_meta_key) ||
			empty($this->schedule)
		) {
			// We don't have sufficient information to continue
			if ( $this->debug ) {
				error_log('Cannot run sync. Incomplete settings.');
			}
			return false;
		}

		return true;
	}
}

$CGD_WistiaSync = new CGD_WistiaSync();
$CGD_WistiaSync->start();


register_activation_hook( __FILE__, array($CGD_WistiaSync, 'activate') );
register_deactivation_hook( __FILE__, array($CGD_WistiaSync, 'deactivate') );
