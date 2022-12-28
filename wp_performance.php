<?php
/*
Plugin Name: Website Speed Test
Description: Measures the speed of a website using Google PageSpeed Insights and displays the results to the user.
Version: 1.0
Author: Walled Mahmoud Soliman
*/

// This function will be called when the plugin is activated
function wst_activate() {
  // Schedule a recurring event to run the performance test every day
  wp_schedule_event(time(), 'daily', 'wst_perform_test');
}
register_activation_hook(__FILE__, 'wst_activate');

// This function will be called when the plugin is deactivated
function wst_deactivate() {
  // Remove the scheduled event
  wp_clear_scheduled_hook('wst_perform_test');
}
register_deactivation_hook(__FILE__, 'wst_deactivate');


// This function will be called to perform the performance test
function wst_perform_test() {
  // Get the URL of the page to test from the plugin settings
  $url = get_option('wst_url');

  // Use the Google PageSpeed Insights API to test the performance of the page
  $response = wp_remote_get("https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&strategy=mobile");
  $data = json_decode($response['body'], true);
  $speed = $data['lighthouseResult']['categories']['performance']['score'];

  // Save the test results to the plugin options
  update_option('wst_last_test_time', time());
  update_option('wst_last_test_speed', $speed);
}
add_action('wst_perform_test', 'wst_perform_test');


// This function will be called to display the test results to the user
function wst_admin_notices() {
  // Get the time of the last test and the speed score from the plugin options
  $last_test_time = get_option('wst_last_test_time');
  $speed = get_option('wst_last_test_speed');

  // Only display the notice if a test has been performed
  if ($last_test_time) {
    // Calculate how long ago the test was performed
    $time_since_last_test = human_time_diff($last_test_time);

    // Display a notice with the test results
    echo "<div class='notice notice-success is-dismissible'>";
    echo "The speed of your website was last tested $time_since_last_test ago and received a score of $speed.";
    echo "</div>";
  }
}
add_action('admin_notices', 'wst_admin_notices');


// This function will be called to add a settings page to the plugin
function wst_add_settings_page() {
  add_options_page('Website Speed Test', 'Website Speed Test', 'manage_options', 'wst-settings', 'wst_settings_page');
}
add_action('admin_menu', 'wst_add_settings_page');

