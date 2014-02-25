<?php
/*
Plugin Name:  SX No Homepage Pagination
Version:      1.1
Plugin URI:   http://www.seomix.fr
Description:  SX No Homepage Pagination removes properly any homepage pagination (whatever plugin or function you are using) and redirect useless paginated content. This plugin works on any default homepage, not on a blog page.
Availables languages : en_EN
Tags: homepage, pagination, page, paged, frontpage, home page, front page
Author: Daniel Roch
Author URI: http://www.seomix.fr
Requires at least: 3.3
Tested up to: 3.8
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
 Check Compatibility
 * http://wptavern.com/how-to-prevent-wordpress-plugins-from-activating-on-sites-with-incompatible-hosting-environments
 */
class seomix_nohomepage_checkcompatibility {
    function __construct() {
        add_action( 'admin_init', array( $this, 'check_version' ) );

        // Don't run anything else in the plugin, if we're on an incompatible WordPress version
        if ( ! self::compatible_version() ) {
            return;
        }
    }

    // The primary sanity check, automatically disable the plugin on activation if it doesn't
    // meet minimum requirements.
    static function activation_check() {
        if ( ! self::compatible_version() ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die('SX No homepage Pagination requires WordPress 3.3 or higher!');
        }
    }

    // The backup sanity check, in case the plugin is activated in a weird way,
    // or the versions change after activation.
    function check_version() {
        if ( ! self::compatible_version() ) {
            if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
                deactivate_plugins( plugin_basename( __FILE__ ) );
                add_action( 'admin_notices', array( $this, 'disabled_notice' ) );
                if ( isset( $_GET['activate'] ) ) {
                    unset( $_GET['activate'] );
                }
            }
        }
    }

    function disabled_notice() {
       echo '<div class="updated"><p><strong>SX No homepage Pagination requires WordPress 3.3 or higher!</strong></p></div>';
    }

    static function compatible_version() {
        if ( version_compare( $GLOBALS['wp_version'], '3.3', '<' ) ) {
             return false;
         }
        return true;
    }
}

global $sxnohomepagecheck;
$sxnohomepagecheck = new seomix_nohomepage_checkcompatibility();
register_activation_hook( __FILE__, array( 'seomix_nohomepage_checkcompatibility', 'activation_check' ) );