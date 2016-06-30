# WP Wistia Sync

A simple plugin to sync Wistia's play count to a meta field on WordPress posts.

Requirements:
- a post type
- a meta field for your Wistia video ID
- a meta field to your play count
- the ability to click a save button

This plugin is provided with no warranty and is licensed under the GPLv3 license. Pull requests welcome.

## Instructions

- Upload plugin and Activate
- Go to Settings -> Wistia Sync
- Input your Wistia API Key, and other settings. (Note: Wistia API key needs read access to everything)

## Debugging Instructions

If you're having a problem, you can use these instructions.

- Change $debug to true in the class variables.
- Add ?debug_run_wistia_sync_now=true to a URL on the site and press enter.  
- Check the PHP error log. 
