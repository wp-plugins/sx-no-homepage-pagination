<?php
/*
Plugin Name:  SX No Homepage Pagination
Version:      1.1.1
Plugin URI:   http://www.seomix.fr
Description:  SX No Homepage Pagination removes properly any homepage pagination (whatever plugin or function you are using) and redirect useless paginated content. This plugin works on any default homepage, not on a blog page.
Availables languages : en_EN
Tags: homepage, pagination, page, paged, frontpage, home page, front page
Author: Daniel Roch
Author URI: http://www.seomix.fr
Requires at least: 3.3
Tested up to: 4.2
License: GPL v3

SX No Homepage Pagination - SeoMix
Copyright (C) 2014, Daniel Roch - contact@seomix.fr

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
  Security
*/
if ( ! defined( 'ABSPATH' ) ) exit;


/**
  Don't paginate homepage
  * © Daniel Roch
  */
function seomix_remove_homepage_pagination($query) { 
  if ( is_front_page() && is_home() && $query->is_main_query() )
    // Default homepage - Don't paginate
    $query->set('no_found_rows', true);
}
add_action('pre_get_posts', 'seomix_remove_homepage_pagination');


/**
 Redirect homepage pagination
 * Redirect homepage pagination (if is_front_page is true)
 * © Daniel Roch 
 */ 
function seomix_redirect_homepage_pagination () {
  global $paged, $page;
  // Are we on an homepage pagination ?
  if ( is_front_page() && is_home() && ( $paged >= 2 || $page >= 2 ) ) {
     wp_redirect( home_url() , '301' );
     die;
  }
}
add_action( 'template_redirect', 'seomix_redirect_homepage_pagination' );

/**
 Compatibility check
 * © Julio Potier https://github.com/BoiteAWeb/ActivationTester
 */
add_action( 'admin_init', 'sx_no_homepage_check_version' );
function sx_no_homepage_check_version() {
  // This is where you set you needs
  $mandatory = array(
      'PluginName'=>'SX No Homepage Pagination', 
      'WordPress'=>'3.3' 
    );
  // Avoid Notice error
  $errors = array();
  // loop the mandatory things
  foreach( $mandatory as $what => $how ) {
    switch( $what ) {
      case 'WordPress':
          if( version_compare( $GLOBALS['wp_version'], $how ) < 0 )
          {
            $errors[$what] = $how;
          }
        break;
    }
  }
  // Add a filter for devs
  $errors = apply_filters( 'validate_errors', $errors, $mandatory['PluginName'] );
  // We got errors!
  if( !empty( $errors ) ) {
    global $current_user;
    // We add the plugin name for late use
    $errors['PluginName'] = $mandatory['PluginName'];
    // Set a transient with these errors
    set_transient( 'myplugin_disabled_notice' . $current_user->ID, $errors );
    // Remove the activate flag
    unset( $_GET['activate'] );
    // Deactivate this plugin
    deactivate_plugins( plugin_basename( __FILE__ ) );
  }
}
add_action( 'admin_notices', 'sx_no_homepage_disabled_notice' );
function sx_no_homepage_disabled_notice() {
  global $current_user;
  // We got errors!
  if( $errors = get_transient( 'myplugin_disabled_notice' . $current_user->ID ) ) {
    // Remove the transient
    delete_transient( 'myplugin_disabled_notice' . $current_user->ID );
    // Pop the plugin name
    $plugin_name = array_pop( $errors );
    // Begin the buffer output
    $error = '<ul>';
    // Loop on each error, you can change the "i18n domain" here -> my_plugin (i would like to avoid this)
    foreach( $errors as $what => $how) {
      $error .= '<li>'.sprintf( __( '&middot; Requires %s: <code>%s</code>', 'my_plugin' ), $what, $how ).'</li>';
    }
    // End the buffer output
    $error .= '</ul>';
    // Echo the output using a WordPress string (no i18n needed)
    echo '<div class="error"><p>' . sprintf( __( 'The plugin <code>%s</code> has been <strong>deactivated</strong> due to an error: %s' ), $plugin_name, $error ) . '</p></div>';
  }
}