<?php

/**
 * Adapted from https://github.com/DevinVinson/WordPress-Plugin-Boilerplate under the GPL v2 license
 * Modified: 5/8/23
 * 
 * 
 * Provide a admin area view for the plugin's 'CAA Task App' page.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/r-sauers/CAA-Task-Plugin
 * @since      1.0.0
 *
 * @package    CAA_Task_Plugin
 * @subpackage CAA_Task_Plugin/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <p>Explanation of how to use plugin goes here</p>
    <ul>
        <li> Client ID: <?php echo $client_id ?></li>
        <li> Redirect URI: <?php echo esc_html($redirect_uri) ?></li>
    </ul>
        
    <button onclick=<?php 
    echo 'window.location.href="https://launchpad.37signals.com/authorization/new?type=web_server&client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '"';
    ?> >Login</button>
</div>
<?php