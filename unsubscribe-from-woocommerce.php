<?php
/*
Plugin Name: Unsubscribe from WooCommerce
Plugin URI: https://wordpress.org/plugins/unsubscribe-from-woocommerce/
Description: To add a unsubscribe from WooCommerce in the EDIT ACCOUNT DETAILS page.
Version: 0.1.1
Author: Codex
Author URI: http://codex-lab.com/
License: GPL2
Text Domain: unsubscribe-from-woocommerce
Domain Path: /languages
*/

/*  Copyright 2016 Takafumi Yamashita (email : yama@codex-lab.com)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
     published by the Free Software Foundation.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
// If this file is called directly, abort.
if( ! defined( 'WPINC' ) ) {
    die;
}

// Load plugin functions.
add_action( 'plugins_loaded', 'unsubscribe_from_woocommerce_plugin', 0 );

class Unsubscribe_From_Woocommerce{

	public function __construct() {

		// Getting Session
		if( !session_id() ){
			session_start();
		}
		// load_plugin_textdomain
		$this->load_plugin_textdomain();
		// main function
		add_action( 'woocommerce_edit_account_form_end', array ($this ,'unsubscribe' ));
		add_action( 'init', array ($this ,'buffer' ));
	}

	public function load_plugin_textdomain() {

		load_plugin_textdomain('unsubscribe-from-woocommerce', false, dirname( plugin_basename( __FILE__ ) )."/languages");

	}

	public function buffer() {

		ob_start();

	}

	public function unsubscribe(){
		// Exclude administrator 
		if (!current_user_can('administrator')) : ?>
			<form action="" method="post">
			
			<?php	$unsubscribe_nonce=$_REQUEST['_unsubscribe_wpnonce'];

			if($_POST["user_delete"] &&  wp_verify_nonce($unsubscribe_nonce, 'unsubscribe_nonce')): ?>

				<?php //Caution before the deletion
                echo "<p>". esc_html__( 'Are you sure you want to unregister?' , 'unsubscribe-from-woocommerce') ."<br>" . esc_html__( 'Since the member information is completely removed, benefits also because it is peripheral associated with the member information, such as points and coupons, please note.', 'unsubscribe-from-woocommerce' ). "</p>"; ?>
		
				<?php $nonce_confirme = wp_create_nonce('unsubscribe_nonce_confirme'); ?>
				<input type="hidden" name="_wpnonce_confirme" value="<?php echo $nonce_confirme; ?>" />
				<input type="hidden" name="user_delete_confirme" value="1" />
				<input type="submit" class="button" value="<?php esc_html_e( 'Unregister', 'unsubscribe-from-woocommerce' ); ?>" />
		
			<?php else :?>
		
				<?php $nonce = wp_create_nonce('unsubscribe_nonce'); ?>
				<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>" />
		
				<?php $unsubscribe_nonce = wp_create_nonce('unsubscribe_nonce'); ?>
				<input type="hidden" name="_unsubscribe_wpnonce" value="<?php echo $unsubscribe_nonce; ?>" />
				<input type="hidden" name="user_delete" value="1" />
				<input type="submit" class="button" value="<?php esc_html_e( 'To deregister', 'unsubscribe-from-woocommerce' ); ?>" />
			<?php endif; ?>
			</form>
		<?php
		$nonce_confirme=$_REQUEST['_wpnonce_confirme'];
			if($_POST["user_delete_confirme"] &&  wp_verify_nonce($nonce_confirme, 'unsubscribe_nonce_confirme')){
				require_once ABSPATH."/wp-admin/includes/user.php";
				global $userdata;
				get_currentuserinfo();
			
				$author = $userdata->ID;
				$delcomp = wp_delete_user($author);

				if($delcomp){
					$_SESSION = array();
					if( session_id() ){
						session_destroy();
					}
					wp_logout();
					global $current_user;
					$current_user = null;
					wp_safe_redirect( home_url(), 303 );
					exit;
				} else {
					esc_html_e( 'An error occurred during the deregistration process.', 'unsubscribe-from-woocommerce' ); 
					wp_safe_redirect( home_url(), 303 );
					exit;
				}
			}
		endif;
	}
}

function unsubscribe_from_woocommerce_fallback_notice() {
	?>
    <div class="error">
        <ul>
            <li><?php esc_html_e( 'Unsubscribe from WooCommerce is enabled but not effective. It requires WooCommerce in order to work.', 'unsubscribe-from-woocommerce' );?></li>
        </ul>
    </div>
    <?php
}

function unsubscribe_from_woocommerce_plugin() {
    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        new Unsubscribe_From_WooCommerce();
    } else {
        add_action( 'admin_notices', 'unsubscribe_from_woocommerce_fallback_notice' );
    }
}
