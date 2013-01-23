<?php
if (!defined('EVENT_ESPRESSO_VERSION') )
	exit('NO direct script access allowed');

/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for Wordpress
 *
 * @package		Event Espresso
 * @author		Seth Shoultes
 * @copyright	(c)2009-2012 Event Espresso All Rights Reserved.
 * @license		http://eventespresso.com/support/terms-conditions/  ** see Plugin Licensing **
 * @link		http://www.eventespresso.com
 * @version		3.2.P
 *
 * ------------------------------------------------------------------------
 *
 * General_Settings_Admin_Page
 *
 * This contains the logic for setting up the Custom General_Settings related pages.  Any methods without phpdoc comments have inline docs with parent class. 
 *
 * NOTE:  TODO: This is a straight conversion from the legacy 3.1 settings page.  It is NOT optimized and will need modification to fully use the new system (and also will need adjusted when Questions and Questions groups model is implemented)
 *
 * @package		General_Settings_Admin_Page
 * @subpackage	includes/core/admin/General_Settings_Admin_Page.core.php
 * @author			Brent Christensen
 *
 * ------------------------------------------------------------------------
 */
class General_Settings_Admin_Page extends EE_Admin_Page {

	/**
	 * _question
	 * holds the specific question object for the question details screen
	 * @var object
	 */
	protected $_yes_no_values = array();

	/**
	 * _question_group
	 * holds the specific question group object for the question group details screen
	 * @var object
	 */
	protected $_question_group;



	public function __construct() {
		parent::__construct();
		$this->_yes_no_values = array(
			array('id' => TRUE, 'text' => __('Yes', 'event_espresso')),
			array('id' => FALSE, 'text' => __('No', 'event_espresso'))
		);
	}



	protected function _init_page_props() {
		$this->page_slug = GEN_SET_PG_SLUG;
		$this->page_label = GEN_SET_LABEL;
	}




	protected function _ajax_hooks() {
		//todo: all hooks for events ajax goes in here.
	}





	protected function _define_page_props() {
		$this->_admin_base_url = GEN_SET_ADMIN_URL;
		$this->_admin_page_title = GEN_SET_LABEL;
		$this->_labels = array();
	}




	protected function _set_page_routes() {
		$this->_page_routes = array(
			'default' => '_espresso_page_settings',
			'update_espresso_page_settings' => array(
				'func' => '_update_espresso_page_settings',
				'noheader' => TRUE,
				),
			'template_settings' => '_template_settings',
			'update_template_settings' => array(
				'func' => '_update_template_settings',
				'noheader' => TRUE,
				),
			'google_map_settings' => '_google_map_settings',
			'update_google_map_settings' => array(
				'func' => '_update_google_map_settings',
				'noheader' => TRUE,
				),
			'your_organization_settings' => '_your_organization_settings',
			'update_your_organization_settings' => array(
				'func' => '_update_your_organization_settings',
				'noheader' => TRUE,
				),
			'admin_option_settings' => '_admin_option_settings',
			'update_admin_option_settings' => array(
				'func' => '_update_admin_option_settings',
				'noheader' => TRUE,
				)
			);
	}





	protected function _set_page_config() {
		$this->_page_config = array(
			'default' => array(
				'nav' => array(
					'label' => __('Espresso Pages'),
					'order' => 20
					),
				'metaboxes' => array( '_publish_post_box', '_espresso_news_post_box', '_espresso_links_post_box' )
				),
			'template_settings' => array(
				'nav' => array(
					'label' => __('Templates'),
					'order' => 30
					),
				'metaboxes' => array( '_publish_post_box', '_espresso_news_post_box', '_espresso_links_post_box' )
				),
			'google_map_settings' => array(
				'nav' => array(
					'label' => __('Google Maps'),
					'order' => 40
					),
				'metaboxes' => array('_publish_post_box',  '_espresso_news_post_box', '_espresso_links_post_box' )
				),
			'your_organization_settings' => array(
				'nav' => array(
					'label' => __('Your Organization'),
					'order' => 50
					),
				'metaboxes' => array('_publish_post_box',  '_espresso_news_post_box', '_espresso_links_post_box' )
				),
			'admin_option_settings' => array(
				'nav' => array(
					'label' => __('Admin Options'),
					'order' => 60
					),
				'metaboxes' => array( '_publish_post_box', '_espresso_news_post_box', '_espresso_links_post_box' )
				)
			);
	}



	protected function _add_screen_options() {
	}

	protected function _add_screen_options_default() {
		$this->_per_page_screen_option();
	}

	protected function _add_screen_options_question_groups() {
		$this->_per_page_screen_option();
	}

	protected function _add_help_tabs() {}
	protected function _add_feature_pointers() {}
	public function load_scripts_styles() {
		//styles
		wp_enqueue_style('jquery-ui-style');
		//scripts
		wp_enqueue_script('ee_admin_js');		
	}
	public function admin_init() {}
	public function admin_notices() {}
	public function admin_footer_scripts() {}


	public function load_scripts_styles_your_organization_settings() {	
		//styles
		wp_enqueue_style('thickbox');
		//scripts
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_register_script( 'organization_settings', GEN_SET_ASSETS_URL . 'your_organization_settings.js', array( 'jquery','media-upload','thickbox' ), EVENT_ESPRESSO_VERSION, TRUE );
		wp_enqueue_script( 'organization_settings' );	
		$confirm_image_delete = array( 'text' => __('Do you really want to delete this image? Please remember to save your settings to complete the removal.', 'event_espresso')); 
		wp_localize_script( 'organization_settings', 'confirm_image_delete', $confirm_image_delete );
		// 

	}


	/*************		Espresso Pages 		*************/


	protected function _espresso_page_settings() {
	
		global $org_options;
		$this->_template_args['event_ssl_active'] = isset( $org_options['event_ssl_active'] ) && ! empty( $org_options['event_ssl_active'] ) ? $org_options['event_ssl_active'] : FALSE;

		$this->_template_args['event_page_id'] = isset( $org_options['event_page_id'] ) ? $org_options['event_page_id'] : NULL;
		$this->_template_args['event_reg_page'] = isset( $org_options['event_page_id'] ) ? get_page( $org_options['event_page_id'] ) : FALSE;

		$this->_template_args['return_url'] = isset( $org_options['return_url'] ) ? $org_options['return_url'] : NULL;
		$this->_template_args['thank_you_page'] = isset( $org_options['return_url'] ) ? get_page( $org_options['return_url'] ) : FALSE;

		$this->_template_args['notify_url'] = isset( $org_options['notify_url'] ) ? $org_options['notify_url'] : NULL;
		$this->_template_args['transactions_page'] = isset( $org_options['notify_url'] ) ? get_page( $org_options['notify_url'] ) : FALSE;

		$this->_template_args['cancel_return'] = isset( $org_options['cancel_return'] ) ? $org_options['cancel_return'] : NULL;
		$this->_template_args['cancel_return_page'] = isset( $org_options['cancel_return'] ) ? get_page( $org_options['cancel_return'] ) : FALSE;
		
		$this->_set_add_edit_form_tags( 'update_espresso_page_settings' );
		$this->_set_publish_post_box_vars( NULL, FALSE, FALSE, NULL, FALSE );
		$this->_template_args['admin_page_content'] = espresso_display_template( GEN_SET_TEMPLATE_PATH . 'espresso_page_settings.template.php', $this->_template_args, TRUE );
		$this->display_admin_page_with_sidebar();	
		
	}

	protected function _update_espresso_page_settings() {
		
		$data = array();
		$data['event_page_id'] = isset( $this->_req_data['event_page_id'] ) ? absint( $this->_req_data['event_page_id'] ) : NULL;
		$data['return_url'] = isset( $this->_req_data['return_url'] ) ? absint( $this->_req_data['return_url'] ) : NULL;
		$data['cancel_return'] = isset( $this->_req_data['cancel_return'] ) ? absint( $this->_req_data['cancel_return'] ) : NULL;
		$data['notify_url'] = isset( $this->_req_data['notify_url'] ) ? absint( $this->_req_data['notify_url'] ) : NULL;
		
		$success = $this->_update_general_settings( $data, __FILE__, __FUNCTION__, __LINE__ );
		$this->_redirect_after_action( $success, 'Template Settings', 'updated', array() );
		
	}



	/*************		Templates 		*************/


	protected function _template_settings() {
	
		global $org_options;
		$this->_template_args['org_options'] = $org_options;

		$this->_set_add_edit_form_tags( 'update_template_settings' );
		$this->_set_publish_post_box_vars( NULL, FALSE, FALSE, NULL, FALSE );
		$this->_template_args['admin_page_content'] = espresso_display_template( GEN_SET_TEMPLATE_PATH . 'template_settings.template.php', $this->_template_args, TRUE );
		// the details template wrapper
		$this->display_admin_page_with_sidebar();	
	}

	protected function _update_template_settings() {
		
		$data = array();
//		$data['event_page_id'] = absint( $this->_req_data['event_page_id'] );
//		$data['return_url'] = absint( $this->_req_data['return_url'] );
//		$data['cancel_return'] = absint( $this->_req_data['cancel_return'] );
//		$data['notify_url'] = absint( $this->_req_data['notify_url'] );
		
		$success = $this->_update_general_settings( $data, __FILE__, __FUNCTION__, __LINE__ );
		$this->_redirect_after_action( $success, 'Template Settings', 'updated', array() );
		
	}



	/*************		Google Maps 		*************/


	protected function _google_map_settings() {
	
		global $org_options;
		
		$this->_template_args['default_logo_url'] = isset( $org_options['default_logo_url'] ) ? $org_options['default_logo_url'] : '';


		$this->_set_add_edit_form_tags( 'update_google_map_settings' );
		$this->_set_publish_post_box_vars( NULL, FALSE, FALSE, NULL, FALSE );
		$this->_template_args['admin_page_content'] = espresso_display_template( GEN_SET_TEMPLATE_PATH . 'google_map.template.php', $this->_template_args, TRUE );
		// the details template wrapper
		$this->display_admin_page_with_sidebar();	
	}

	protected function _update_google_map_settings() {
		
		$data = array();
//		$data['event_page_id'] = absint( $this->_req_data['event_page_id'] );
//		$data['return_url'] = absint( $this->_req_data['return_url'] );
//		$data['cancel_return'] = absint( $this->_req_data['cancel_return'] );
//		$data['notify_url'] = absint( $this->_req_data['notify_url'] );
		
		$success = $this->_update_general_settings( $data, __FILE__, __FUNCTION__, __LINE__ );
		$this->_redirect_after_action( $success, 'Google Map Settings', 'updated', array() );
		
	}



	/*************		Your Organization 		*************/


	protected function _your_organization_settings() {
	
		global $org_options;
		$this->_template_args['default_logo_url'] = isset( $org_options['default_logo_url'] ) ? $this->_display_nice( $org_options['default_logo_url'] ) : FALSE;
		$this->_template_args['organization'] = isset( $org_options['organization'] ) ? $this->_display_nice( $org_options['organization'] ) : '';
		$this->_template_args['organization_street1'] = isset( $org_options['organization_street1'] ) ? $this->_display_nice( $org_options['organization_street1'] ) : '';
		$this->_template_args['organization_street2'] = isset( $org_options['organization_street2'] ) ? $this->_display_nice( $org_options['organization_street2'] ) : '';
		$this->_template_args['organization_city'] = isset( $org_options['organization_city'] ) ? $this->_display_nice( $org_options['organization_city'] ) : '';
		$this->_template_args['organization_state'] = isset( $org_options['organization_state'] ) ? $this->_display_nice( $org_options['organization_state'] ) : '';
		$this->_template_args['organization_zip'] = isset( $org_options['organization_zip'] ) ? $this->_display_nice( $org_options['organization_zip'] ) : '';
		$this->_template_args['organization_country'] = isset( $org_options['organization_country'] ) ? $this->_display_nice( $org_options['organization_country'] ) : '';
		$this->_template_args['currency_symbol'] = isset( $org_options['currency_symbol'] ) ? $this->_display_nice( $org_options['currency_symbol'] ) : '';
		$this->_template_args['contact_email'] = isset( $org_options['contact_email'] ) ? $this->_display_nice( $org_options['contact_email'] ) : '';
		
		
		$this->_set_add_edit_form_tags( 'update_your_organization_settings' );
		$this->_set_publish_post_box_vars( NULL, FALSE, FALSE, NULL, FALSE );
		$this->_template_args['admin_page_content'] = espresso_display_template( GEN_SET_TEMPLATE_PATH . 'your_organization_settings.template.php', $this->_template_args, TRUE );
		// the details template wrapper
		$this->display_admin_page_with_sidebar();	
	}

	protected function _update_your_organization_settings() {
		
		$data = array();
		$data['default_logo_url'] = isset( $this->_req_data['default_logo_url'] ) ? esc_url_raw( $this->_req_data['default_logo_url'] ) : NULL;
		$data['organization'] = isset( $this->_req_data['organization'] ) ? sanitize_text_field( $this->_req_data['organization'] ) : NULL;
		$data['organization_street1'] = isset( $this->_req_data['organization_street1'] ) ? sanitize_text_field( $this->_req_data['organization_street1'] ) : NULL;
		$data['organization_street2'] = isset( $this->_req_data['organization_street2'] ) ? sanitize_text_field( $this->_req_data['organization_street2'] ) : NULL;
		$data['organization_city'] = isset( $this->_req_data['organization_city'] ) ? sanitize_text_field( $this->_req_data['organization_city'] ) : NULL;
		$data['organization_state'] = isset( $this->_req_data['organization_state'] ) ? sanitize_text_field( $this->_req_data['organization_state'] ) : NULL;
		$data['organization_zip'] = isset( $this->_req_data['organization_zip'] ) ? sanitize_text_field( $this->_req_data['organization_zip'] ) : NULL;
		$data['organization_country'] = isset( $this->_req_data['organization_country'] ) ? absint( $this->_req_data['organization_country'] ) : NULL;
		$data['contact_email'] = isset( $this->_req_data['contact_email'] ) ? sanitize_email( $this->_req_data['contact_email'] ) : NULL;
		
//		printr( $this->_req_data, '$this->_req_data  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
//		printr( $data, '$data  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );

		$success = $this->_update_general_settings( $data, __FILE__, __FUNCTION__, __LINE__ );
		$this->_redirect_after_action( $success, 'Your Organization Settings', 'updated', array( 'action' => 'your_organization_settings' ) );
		
	}
	/*************		Admin Options 		*************/


	protected function _admin_option_settings() {
	
		global $org_options;
		$this->_template_args['org_options'] = $org_options;
		$this->_template_args['values'] = $this->_yes_no_values;
		$this->_template_args['expire_on_registration_end'] = isset( $org_options['expire_on_registration_end'] ) ? $this->_display_nice( $org_options['expire_on_registration_end'] ) : FALSE;

		$this->_set_add_edit_form_tags( 'update_admin_option_settings' );
		$this->_set_publish_post_box_vars( NULL, FALSE, FALSE, NULL, FALSE );
		$this->_template_args['admin_page_content'] = espresso_display_template( GEN_SET_TEMPLATE_PATH . 'admin_option_settings.template.php', $this->_template_args, TRUE );
		// the details template wrapper
		$this->display_admin_page_with_sidebar();	
	}

	protected function _update_admin_option_settings() {
		
		$data = array();
//		$data['event_page_id'] = absint( $this->_req_data['event_page_id'] );
//		$data['return_url'] = absint( $this->_req_data['return_url'] );
//		$data['cancel_return'] = absint( $this->_req_data['cancel_return'] );
//		$data['notify_url'] = absint( $this->_req_data['notify_url'] );
		
		$success = $this->_update_general_settings( $data, __FILE__, __FUNCTION__, __LINE__ );
		$this->_redirect_after_action( $success, 'Admin Options', 'updated', array() );
		
	}







	/***********/





	/**
	 * correct variable display
	 *
	 * @param array $var
	 * @return string
	 */
	private function _display_nice( $var ) {
		return htmlentities( stripslashes( $var ), ENT_QUOTES, 'UTF-8' );
	}




	/**
	 * updates user_meta
	 *
	 * @param array $data
	 * @return string
	 */
	private function _update_general_settings( $data, $file, $func, $line ) {
		global $espresso_wp_user;
		// grab existing org options
		$org_options = get_user_meta( $espresso_wp_user, 'events_organization_settings', TRUE );
//		printr( $data, '$data  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
//		printr( $org_options, '$org_options  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
//		if ( ! empty( $org_options )) {
		// make sure everything is in arrays
		$org_options = is_array( $org_options ) ? $org_options : array( $org_options );
		$data = is_array( $data ) ? $data : array( $data );
		foreach ( $data as $key => $value ) {
			$data[ $key ] = addslashes( html_entity_decode( $value, ENT_QUOTES, 'UTF-8' ));
		}
		// overwrite existing org options with new data
		$data = array_merge( $org_options, $data );
		// and save it
		if ( update_user_meta( $espresso_wp_user, 'events_organization_settings', $data )) {
			EE_Error::add_success( __('Organization details saved', 'event_espresso'));
			return TRUE;
		} else {
			$user_msg = __('Unable to save Organization details.', 'event_espresso');
			EE_Error::add_error( $user_msg, $file, $func, $line  );
			return FALSE;
		}			
//		}

	}



	/**
	 * displays edit and view links for critical EE pages
	 *
	 * @access public 
	 * @param WP page object $ee_page
	 * @return string
	 */
	public static function edit_view_links( $ee_page_id ) {
		$links = '<a href="' . add_query_arg( array( 'post' => $ee_page_id, 'action' => 'edit' ),  admin_url( 'post.php' )) . '" >' . __('Edit', 'event_espresso') . '</a>';
		$links .= ' &nbsp;|&nbsp; ';
		$links .= '<a href="' . get_permalink( $ee_page_id ) . '" >' . __('View', 'event_espresso') . '</a>';
		return $links;
	}
	
	
	

	/**
	 * displays page and shortcode status for critical EE pages
	 *
	 * @param WP page object $ee_page
	 * @return string
	 */
	public static function page_and_shortcode_status( $ee_page, $shortcode ) {
//		printr( $ee_page, '$ee_page  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span>', 'auto' );
//		echo '<h4>$shortcode : ' . $shortcode . '  <br /><span style="font-size:10px;font-weight:normal;">' . __FILE__ . '<br />line no: ' . __LINE__ . '</span></h4>';
		// page status
		if ( isset( $ee_page->post_status ) && $ee_page->post_status == 'publish') { 
			$pg_colour = 'green';
			$pg_status = __('Page Status OK', 'event_espresso');
		 } else { 
			$pg_colour = 'red';
			$pg_status = __('Page Visibility Problem', 'event_espresso');
		}
		
		// shortcode status
		if ( isset( $ee_page->post_content ) && strpos( $ee_page->post_content, $shortcode ) !== FALSE ) { 
			$sc_colour = 'green';
			$sc_status = __('Shortcode OK', 'event_espresso');
		 } else { 
			$sc_colour = 'red';
			$sc_status = __('Shortcode Problem', 'event_espresso');
		}

		return '<span style="color:' . $pg_colour . '; margin-right:2em;"><strong>' . $pg_status . '</strong></span><span style="color:' . $sc_colour . '"><strong>' . $sc_status . '</strong></span>';		

	}
	
	
	

	/**
	 * generates a dropdown of all parent pages - copied from WP core
	 *
	 * @param unknown_type $default
	 * @param unknown_type $parent
	 * @param unknown_type $level
	 * @return unknown
	 */
	public static function page_settings_dropdown( $default = 0, $parent = 0, $level = 0 ) {
		global $wpdb;
		$items = $wpdb->get_results( $wpdb->prepare("SELECT ID, post_parent, post_title FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'page' AND post_status != 'trash' ORDER BY menu_order", $parent) );

		if ( $items ) {
			foreach ( $items as $item ) {
				$pad = str_repeat( '&nbsp;', $level * 3 );
				if ( $item->ID == $default)
					$current = ' selected="selected"';
				else
					$current = '';

				echo "\n\t<option class='level-$level' value='$item->ID'$current>$pad " . esc_html($item->post_title) . "</option>";
				parent_dropdown( $default, $item->ID, $level +1 );
			}
		} else {
			return false;
		}
	}


} //ends Forms_Admin_Page class