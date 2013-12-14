<?php
/**
 * 
 * @package: phpBB 3.0.9 :: BRIDGE phpBB & WordPress -> WordPress root/wp-content/themes/phpBB/includes
 * @version: $Id: wp_phpbb_bridge_core.php, v0.0.9 2011/10/01 11:10:01 leviatan21 Exp $
 * @copyright: leviatan21 < info@mssti.com > (Gabriel) http://www.mssti.com/phpbb3/
 * @license: http://opensource.org/licenses/gpl-license.php GNU Public License 
 * @author: leviatan21 - http://www.phpbb.com/community/memberlist.php?mode=viewprofile&u=345763
 * 
 */

/**
 * @ignore
*/

/**
 * Get current settings page tab
 */
function propress_get_current_tab() {

	$page = 'propress-settings';
	if ( isset( $_GET['page'] ) && 'propress-reference' == $_GET['page'] ) {
		$page = 'general';
	}
    if ( isset( $_GET['tab'] ) ) {
        $current = $_GET['tab'];
    } else {
		$propress_options = propress_get_options();
		if ( 'propress-settings' == $page ) {
			$current = 'general';
		} else if ( 'propress-reference' == $page ) {
			$current = $propress_options['default_reference_tab'];
		}
    }	
	return apply_filters( 'propress_get_current_tab', $current );
}

/**
 * Define Admin Page Tab Markup
 * 
 * @uses	propress_get_current_tab()	defined in \functions\options.php
 * @uses	propress_get_settings_page_tabs()	defined in \functions\options.php
 * 
 * @link	http://www.onedesigns.com/tutorials/separate-multiple-theme-options-pages-using-tabs	Daniel Tara
 */
function propress_get_page_tab_markup() {

	$page = 'propress-settings';

    $current = propress_get_current_tab();
	
	$tabs = propress_get_settings_page_tabs();
    
    $links = array();
    
    foreach( $tabs as $tab ) {
		$tabname = $tab['name'];
		$tabtitle = $tab['title'];
        if ( $tabname == $current ) {
            $links[] = "<a class='nav-tab nav-tab-active' href='?page=$page&tab=$tabname'>$tabtitle</a>";
        } else {
            $links[] = "<a class='nav-tab' href='?page=$page&tab=$tabname'>$tabtitle</a>";
        }
    }
    
    echo '<div id="icon-themes" class="icon32"><br /></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach ( $links as $link )
        echo $link;
    echo '</h2>';
    
}

