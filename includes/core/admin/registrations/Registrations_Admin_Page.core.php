<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');		
/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package			Event Espresso
 * @ author				Seth Shoultes
 * @ copyright		(c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license			{@link http://eventespresso.com/support/terms-conditions/}   * see Plugin Licensing *
 * @ link					{@link http://www.eventespresso.com}
 * @ since		 		3.2.P
 *
 * ------------------------------------------------------------------------
 *
 * Registrations_Admin_Page class
 *
 * @package			Event Espresso
 * @subpackage	includes/core/admin/transactions/Registrations_Admin_Page.core.php 
 * @author				Brent Christensen
 *
 * ------------------------------------------------------------------------
 */
class Registrations_Admin_Page extends EE_Admin_Page {

	private $_registration;
	private $_session;
	private static $_reg_status;





	/**
	 * 		constructor
	 * 		@Constructor
	 * 		@access public
	 * 		@return void
	 */
	public function __construct($wp_page_slug) {
		parent::__construct($wp_page_slug);
	}






	protected function _init_page_props() {
		$this->page_slug = REG_PG_SLUG;
		$this->page_label = __('Registrations', 'event_espresso');
	}





	protected function _ajax_hooks() {
		//todo: all hooks for registrations ajax goes in here
	}





	protected function  _define_page_props() {
		$this->_admin_base_url = REG_ADMIN_URL;
		$this->_admin_page_title = $this->page_label;
		$this->_labels = array(
			'buttons' => array(
				'add' => __('Add New Registration', 'event_espresso'),
				'edit' => __('Edit Registration', 'event_espresso'),
				'delete' => __('Delete Registration','event_espresso')
				)
			);
	}







	/**
	 * 		grab url requests and route them
	*		@access private
	*		@return void
	*/
	public function _set_page_routes() {			

		$this->_get_registration_status_array();

		$this->_page_routes = array(
				'default'	=> '_registrations_overview_list_table',
				'view_registration'	=> '_registration_details',
				'edit_registration'	=> array( 'func' => '_registration_details', 'param' => array( 'edit' ), 'noheader' => TRUE ),
				'delete_registration'	=> array(
					'func' => '_delete_registration',
					'noheader' => TRUE
					),
				'reports'	=> '_registration_reports'
		);
		
	}





	protected function _set_page_config() {
		$this->_page_config = array(
			'default' => array(
				'nav' => array(
					'label' => __('Overview', 'event_espresso'),
					'order' => 10
					),
				'list_table' => 'EE_Registrations_List_Table'
				),
			'view_registration' => array(
				'nav' => array(
					'label' => __('REG Details', 'event_espreso'),
					'order' => 5,
					'url' => isset($this->_req_data['reg']) ? add_query_arg(array('reg' => $this->_req_data['reg'] ), $this->_current_page_view_url )  : $this->_admin_base_url,
					'persistent' => FALSE
					),
				'metaboxes' => array('_espresso_news_post_box', '_espresso_links_post_box', '_registration_details_metaboxes')
				),
			'reports' => array(
				'nav' => array(
					'label' => __('Reports', 'event_espresso'),
					'order' => 20
					)
				)
			);
	}





	/**
	 * The below methods aren't used by this class currently
	 */
	protected function _add_screen_options() {}
	protected function _add_help_tabs() {}
	protected function _add_feature_pointers() {}
	public function admin_init() {}
	public function admin_notices() {}
	public function admin_footer_scripts() {}








	/**
	 * 		get list of registration statuses
	*		@access private
	*		@return void
	*/
	private function _get_registration_status_array() {

		global $wpdb;
		$SQL = 'SELECT STS_ID, STS_code FROM '. $wpdb->prefix . 'esp_status WHERE STS_type = "registration"';
		$results = $wpdb->get_results( $SQL );

		self::$_reg_status = array();
		foreach ( $results as $status ) {
			self::$_reg_status[ $status->STS_ID ] = $status->STS_code;
		}
	}








	protected function _add_screen_options_default() {
		$this->_per_page_screen_option();
	}







	public function load_scripts_styles() {
		//style
		wp_enqueue_style('espresso_reg');

		//script
		wp_enqueue_script('espresso_reg');
	}






	public function load_scripts_styles_view_registration() {
		//styles
		wp_enqueue_style('jquery-ui-style');
		wp_enqueue_style('jquery-ui-style-datepicker-css');

		//scripts
		wp_enqueue_script('ee_admin_js');
		wp_enqueue_script('event_editor_js');
	}







	public function load_scripts_styles_reports() {
		//styles
		wp_enqueue_style('jquery-jqplot-css', JQPLOT_URL . 'jquery.jqplot.min.css', array(), EVENT_ESPRESSO_VERSION);

		//scripts
		global $is_IE;
		if ( $is_IE ) {
			wp_enqueue_script( 'excanvas' , JQPLOT_URL . 'excanvas.min.js', array(), ESPRESSO_E, FALSE);
		}
		wp_enqueue_script('jqplot-all');
	}








	protected function _set_list_table_views_default() {
		$this->_views = array(
			'all' => array(
				'slug' => 'all',
				'label' => __('All Registrations', 'event_espresso'),
				'count' => 0,
				'bulk_action' => array(
					'delete_registration' => __('Delete Registrations', 'event_espresso'),
					)
				),
			'month' => array(
				'slug' => 'month',
				'label' => __('This Month', 'event_espresso'),
				'count' => 0,
				'bulk_action' => array(
					'delete_registration' => __('Delete Registrations', 'event_espresso'),
					)
				),
			'today' => array(
				'slug' => 'today',
				'label' => sprintf( __('Today - %s', 'event_espresso'), date('M d, Y', current_time('timestamp', 0) ) ),
				'count' => 0,
				'bulk_action' => array(
					'delete_registration' => __('Delete Registrations', 'event_espresso'),
					)
				)
			);
	}







	protected function _registrations_overview_list_table() {
		$this->display_admin_list_table_page_with_no_sidebar();
	}





	/**
	 * This sets the _registration property for the registration details screen
	 *
	 * @access private
	 * @return void
	 */
	private function _set_registration_object() {
		if ( is_object($this->_registration) )
			return; //get out we've already set the object

		require_once ( EVENT_ESPRESSO_INCLUDES_DIR . 'models/EEM_Base.model.php' );
	    require_once ( EVENT_ESPRESSO_INCLUDES_DIR . 'models/EEM_Registration.model.php' );
	    $REG = EEM_Registration::instance();

		$REG_ID = ( ! empty( $this->_req_data['reg'] )) ? absint( $this->_req_data['reg'] ) : FALSE;

		if ( $this->_registration = $REG->get_registration_for_admin_page( $REG_ID ) )
			return;
		else {
			$error_msg = __('An error occured and the details for Registration ID #', 'event_espresso') . $REG_ID .  __(' could not be retreived.', 'event_espresso');
			EE_Error::add_error( $error_msg, __FILE__, __FUNCTION__, __LINE__ );
			$this->_registration = NULL;
		}
	}





	/**
	 * 		generates HTML for the View Registration Details Admin page
	*		@access protected
	*		@return void
	*/
	protected function _registration_details() {

		global $wpdb, $org_options;
		
		$this->_template_args = array();

		$this->_set_registration_object();

		if ( is_object( $this->_registration) ) {
		
			$this->_session = maybe_unserialize( maybe_unserialize( $this->_registration->TXN_session_data ));

			$title = __( ucwords( str_replace( '_', ' ', $this->_req_action )), 'event_espresso' );
			// add PRC_ID to title if editing 
			$title = $this->_registration->REG_ID ? $title . ' # ' . $this->_registration->REG_ID : $title;


			$this->_template_args['reg_nmbr']['value'] = $this->_registration->REG_ID;
			$this->_template_args['reg_nmbr']['label'] = __( 'Registration Number', 'event_espresso' );

			$this->_template_args['reg_datetime']['value'] = date( 'l F j, Y,    g:i:s a', $this->_registration->REG_date );
			$this->_template_args['reg_datetime']['label'] = __( 'Date', 'event_espresso' );

			$this->_template_args['reg_status']['value'] = self::$_reg_status[ $this->_registration->REG_status ];
			$this->_template_args['reg_status']['label'] = __( 'Registration Status', 'event_espresso' );
			$this->_template_args['reg_status']['class'] = 'status-' . $this->_registration->REG_status;

			$this->_template_args['grand_total'] = $this->_registration->TXN_total;

			$this->_template_args['currency_sign'] = $org_options['currency_symbol'];
			// link back to overview
			$this->_template_args['reg_overview_url'] = REG_ADMIN_URL;	

			// grab header
			$template_path = REG_TEMPLATE_PATH . 'reg_admin_details_header.template.php';
			$this->_template_args['admin_page_header'] = espresso_display_template( $template_path, $this->_template_args, TRUE );
						
		} else {
			
			$this->_template_args['admin_page_header'] = $this->display_espresso_notices();

		}

		// the details template wrapper
		$this->display_admin_page_with_sidebar();		

	}






	protected function _registration_details_metaboxes() {
		$this->_set_registration_object();
		add_meta_box( 'edit-reg-details-mbox', __( 'Registration Details', 'event_espresso' ), array( $this, '_reg_details_meta_box' ), $this->wp_page_slug, 'normal', 'high' );
		add_meta_box( 'edit-reg-registrant-mbox', __( 'Attendee Details', 'event_espresso' ), array( $this, '_reg_registrant_side_meta_box' ), $this->wp_page_slug, 'side', 'high' );
		if ( $this->_registration->REG_is_group_reg ) {
			add_meta_box( 'edit-reg-attendees-mbox', __( 'Other Attendees Registered in the Same Transaction', 'event_espresso' ), array( $this, '_reg_attendees_meta_box' ), $this->wp_page_slug, 'normal', 'high' );
		}
	}






	/**
	 * 		generates HTML for the Registration main meta box
	*		@access public
	*		@return void
	*/
	public function _reg_details_meta_box() {

		global $wpdb, $org_options;


		// process items in cart
		$cart_items = $this->_session['cart']['REG']['items'];
		$this->_template_args['items'] = array();
		$exclude = array( 'attendees' );
		
		if ( ! empty( $cart_items )) {
			foreach ( $cart_items as $line_item_ID => $item ) {
				foreach ( $item as $key => $value ) {
					if ( ! in_array( $key, $exclude )) {
						if ( $key == 'options' ) {
							$options = $value;
							foreach ( $options as $opt => $option ) {
								if ( $opt == 'date' ) {
									$option = strtotime( $option );
								} else if  ( $opt == 'time' ) {
									$ampm = ( (float)$option > 11.59 ) ? (( (float)$option == 24.00 ) ? 'am' : 'pm' ) : 'am';
									$option = strtotime( $option . ' ' . $ampm );
								}
								$this->_template_args['items'][ $item['name'] ][ $opt ] = $option;
							}
						} else {
							$this->_template_args['items'][ $item['name'] ][ $key ] = $value;
						}
					} else {
						$this->_template_args['event_attendees'][ $item['name'] ][ $key ] = $value;
					}
				}
			}
		}


		// process taxes
		if ( $taxes = maybe_unserialize( $this->_registration->TXN_tax_data )) {
			$this->_template_args['taxes'] = $taxes['taxes'];
		} else {
			$this->_template_args['taxes'] = FALSE;
		}

		$this->_template_args['grand_total'] = $this->_registration->TXN_total;

		$this->_template_args['currency_sign'] = $org_options['currency_symbol'];
		$reg_status_class = 'status-' . $this->_registration->STS_ID;
		$reg_details = maybe_unserialize( $this->_registration->TXN_details );


		if ( !is_array($reg_details) || ( is_array($reg_details) && isset($reg_details['REDO_TXN']) && $reg_details['REDO_TXN'] ) ) {
			$reg_details = array();
			$reg_details['method'] = '';
			$reg_details['response_msg'] = '';
			$reg_details['registration_id'] = '';
			$reg_details['invoice_number'] = '';
		} 


		$card_type = isset( $reg_details['card_type'] ) ? ' : ' . $reg_details['card_type'] : '';
		$reg_details['method'] = $reg_details['method'] == 'CC' ? 'Credit Card' . $card_type : $reg_details['method'];
		$this->_template_args['method']['value'] = $reg_details['method'];
		$this->_template_args['method']['label'] = __( 'Payment Method', 'event_espresso' );
		$this->_template_args['method']['class'] = 'regular-text';

		$reg_details['response_msg'] = '<span class="' . $reg_status_class . '">' . $reg_details['response_msg'] . '</span>';
		$this->_template_args['gateway_response_msg']['value'] = $reg_details['response_msg'];
		$this->_template_args['gateway_response_msg']['label'] = __( 'Gateway Response Message', 'event_espresso' );
		$this->_template_args['gateway_response_msg']['class'] = 'regular-text';

		if ( isset( $reg_details['registration_id'] )) {
			$this->_template_args['reg_details']['registration_id']['value'] = $reg_details['registration_id'];
			$this->_template_args['reg_details']['registration_id']['label'] = __( 'Registration ID', 'event_espresso' );
			$this->_template_args['reg_details']['registration_id']['class'] = 'regular-text';
		}

		if ( isset( $reg_details['invoice_number'] )) {
			$this->_template_args['reg_details']['invoice_number']['value'] = $reg_details['invoice_number'];
			$this->_template_args['reg_details']['invoice_number']['label'] = __( 'Invoice Number', 'event_espresso' );
			$this->_template_args['reg_details']['invoice_number']['class'] = 'regular-text';
		}
					


		$this->_template_args['reg_details']['registration_session']['value'] = $this->_registration->REG_session;
		$this->_template_args['reg_details']['registration_session']['label'] = __( 'Registration Session', 'event_espresso' );
		$this->_template_args['reg_details']['registration_session']['class'] = 'regular-text';

		$this->_template_args['reg_details']['ip_address']['value'] = $this->_session['ip_address'];
		$this->_template_args['reg_details']['ip_address']['label'] = __( 'Registration placed from IP', 'event_espresso' );
		$this->_template_args['reg_details']['ip_address']['class'] = 'regular-text';

		$this->_template_args['reg_details']['user_agent']['value'] = $this->_session['user_agent'];
		$this->_template_args['reg_details']['user_agent']['label'] = __( 'Registrant User Agent', 'event_espresso' );
		$this->_template_args['reg_details']['user_agent']['class'] = 'large-text';

		$template_path = REG_TEMPLATE_PATH . 'reg_admin_details_main_meta_box_reg_details.template.php';
		echo espresso_display_template( $template_path, $this->_template_args, TRUE );

	}





	/**
	 * 		generates HTML for the Attendees Registration main meta box
	*		@access public
	*		@return void
	*/
	public function _reg_attendees_meta_box() {

		global $wpdb, $org_options;

	    $REG = EEM_Registration::instance();
		$attendees = $REG->get_registrations_for_transaction( $this->_registration->TXN_ID, $this->_registration->REG_ID );

		$this->_template_args['attendees'] = array();
		$this->_template_args['attendee_notice'] = '';

		if ( empty( $attendees)  || ( is_array($attendees) && empty($attendees[0]) ) ) {
			EE_Error::add_error( __('There are no attendees attached to this registration. Something may have gone wrong with the registration', 'event_espresso'), __FILE__, __FUNCTION__, __LINE__ );
			$this->_template_args['attendee_notice'] = EE_Error::get_notices();
		} else {

			$att_nmbr = 1;
			foreach ( $attendees as $attendee ) {
			
				$this->_template_args['attendees'][ $att_nmbr ]['fname'] = ( isset( $attendee->ATT_fname ) & ! empty( $attendee->ATT_fname ) ) ? $attendee->ATT_fname : '';
				$this->_template_args['attendees'][ $att_nmbr ]['lname'] = ( isset( $attendee->ATT_lname ) & ! empty( $attendee->ATT_lname ) ) ? $attendee->ATT_lname : '';
				$this->_template_args['attendees'][ $att_nmbr ]['email'] = ( isset( $attendee->ATT_email ) & ! empty( $attendee->ATT_email ) ) ? $attendee->ATT_email : '';
				$this->_template_args['attendees'][ $att_nmbr ]['final_price'] = ( isset( $attendee->REG_final_price ) & ! empty( $attendee->REG_final_price ) ) ? $attendee->REG_final_price : '';
				
				$address = array();
				
				if ( isset( $attendee->ATT_address ) && ( ! empty( $attendee->ATT_address ))) {
					$address[] = $attendee->ATT_address;
				}
				if ( isset( $attendee->ATT_address2 ) && ( ! empty( $attendee->ATT_address2 ))) {
					$address[] = $attendee->ATT_address2;
				}
				if ( isset( $attendee->ATT_city ) && ( ! empty( $attendee->ATT_city ))) {
					$address[] = $attendee->ATT_city;
				}
				if ( isset( $attendee->ATT_state ) && ( ! empty( $attendee->ATT_state ))) {
					$address[] = $attendee->ATT_state;
				}
				if ( isset( $attendee->ATT_zip ) && ( ! empty( $attendee->ATT_zip ))) {
					$address[] = $attendee->ATT_zip;
				}
				$this->_template_args['attendees'][ $att_nmbr ]['address'] = implode( ', ', $address );
				
				$this->_template_args['attendees'][ $att_nmbr ]['att_link'] = wp_nonce_url( add_query_arg( array( 'action'=>'edit_attendee', 'id'=>$attendee->ATT_ID ), ATT_ADMIN_URL ), 'edit_attendee_nonce' );
				
				$att_nmbr++;
			}

			//printr( $attendees, '$attendees  <br /><span style="font-size:10px;font-weight:normal;">( file: '. __FILE__ . ' - line no: ' . __LINE__ . ' )</span>', 'auto' );

			$this->_template_args['event_name'] = $this->_registration->event_name;
			$this->_template_args['currency_sign'] = $org_options['currency_symbol'];

	//			$this->_template_args['registration_form_url'] = add_query_arg( array( 'action' => 'edit_registration', 'process' => 'attendees'  ), REG_ADMIN_URL );
		}

		$template_path = REG_TEMPLATE_PATH . 'reg_admin_details_main_meta_box_attendees.template.php';
		echo espresso_display_template( $template_path, $this->_template_args, TRUE );

	}






	/**
	 * 		generates HTML for the Edit Registration side meta box
	*		@access public
	*		@return void
	*/
	public function _reg_registrant_side_meta_box() {

		$this->_template_args['ATT_ID'] = $this->_registration->ATT_ID;
		$this->_template_args['fname'] = $this->_registration->ATT_fname;
		$this->_template_args['lname'] = $this->_registration->ATT_lname;
		$this->_template_args['email'] = $this->_registration->ATT_email;
		$this->_template_args['address'] = $this->_registration->ATT_address;
		$this->_template_args['address2'] = ( ! empty ( $this->_registration->ATT_address2 )) ? '<br />' . $this->_registration->ATT_address2 : '';
		$this->_template_args['city'] = ( ! empty ( $this->_registration->ATT_city )) ? '<br />' . $this->_registration->ATT_city . ', ' : '';
		$this->_template_args['state'] = ( ! empty ( $this->_registration->STA_ID )) ? '<br />' . $this->_registration->STA_ID . ', ' : '';
		$this->_template_args['country'] = ( ! empty ( $this->_registration->CNT_ISO )) ? $this->_registration->CNT_ISO : '';
		$this->_template_args['zip'] = ( ! empty ( $this->_registration->ATT_zip )) ? '<br />' . $this->_registration->ATT_zip : '';
		$this->_template_args['phone'] = $this->_registration->ATT_phone;
		$this->_template_args['social'] = stripslashes( $this->_registration->ATT_social );
		$this->_template_args['comments'] = stripslashes( $this->_registration->ATT_comments );
		$this->_template_args['notes'] = stripslashes( $this->_registration->ATT_notes );

		$template_path = REG_TEMPLATE_PATH . 'reg_admin_details_side_meta_box_registrant.template.php';
		echo espresso_display_template( $template_path, $this->_template_args, TRUE );
	}





	/**
	 * 		generates HTML for the Edit Registration side meta box
	*		@access public
	*		@return void
	*/
	public function _reg_billing_info_side_meta_box() {

		$billing_info = $this->_session['billing_info'];
		//echo printr( $billing_info, '$billing_info' );

		if ( is_array( $billing_info )) {

			$this->_template_args['free_event'] = FALSE;

			$this->_template_args['fname']['value'] = ! empty ( $billing_info['reg-page-billing-fname']['value'] ) ? $billing_info['reg-page-billing-fname']['value'] : '';
			$this->_template_args['fname']['label'] = ! empty ( $billing_info['reg-page-billing-fname']['label'] ) ? $billing_info['reg-page-billing-fname']['label'] :  __( 'First Name', 'event_espresso' );
			
			$this->_template_args['lname']['value'] = ! empty ( $billing_info['reg-page-billing-lname']['value'] ) ? $billing_info['reg-page-billing-lname']['value'] : '';
			$this->_template_args['lname']['label'] = ! empty ( $billing_info['reg-page-billing-lname']['label'] ) ? $billing_info['reg-page-billing-lname']['label'] :  __( 'Last Name', 'event_espresso' );
			
			$this->_template_args['email']['value'] = ! empty ( $billing_info['reg-page-billing-email']['value'] ) ? $billing_info['reg-page-billing-email']['value'] : '';
			$this->_template_args['email']['label'] = __( 'Email', 'event_espresso' );
			
			$this->_template_args['address']['value'] = ! empty ( $billing_info['reg-page-billing-address']['value'] ) ? $billing_info['reg-page-billing-address']['value'] : '';
			$this->_template_args['address']['label'] = ! empty ( $billing_info['reg-page-billing-address']['label'] ) ? $billing_info['reg-page-billing-address']['label'] :  __( 'Address', 'event_espresso' );
			
			$this->_template_args['city']['value'] = ! empty ( $billing_info['reg-page-billing-city']['value'] ) ? $billing_info['reg-page-billing-city']['value'] : '';
			$this->_template_args['city']['label'] = ! empty ( $billing_info['reg-page-billing-city']['label'] ) ? $billing_info['reg-page-billing-city']['label'] :  __( 'City', 'event_espresso' );
			
			$this->_template_args['state']['value'] = ! empty ( $billing_info['reg-page-billing-state']['value'] ) ? $billing_info['reg-page-billing-state']['value'] : '';
			$this->_template_args['state']['label'] = ! empty ( $billing_info['reg-page-billing-state']['label'] ) ? $billing_info['reg-page-billing-state']['label'] :  __( 'State', 'event_espresso' );
			
			$this->_template_args['country']['value'] = ! empty ( $billing_info['reg-page-billing-country']['value'] ) ? $billing_info['reg-page-billing-country']['value'] : '';
			$this->_template_args['country']['label'] = ! empty ( $billing_info['reg-page-billing-country']['label'] ) ? $billing_info['reg-page-billing-country']['label'] : __( 'Country', 'event_espresso' );
			
			$this->_template_args['zip']['value'] = ! empty ( $billing_info['reg-page-billing-zip']['value'] ) ? $billing_info['reg-page-billing-zip']['value'] : '';
			$this->_template_args['zip']['label'] = ! empty ( $billing_info['reg-page-billing-zip']['label'] ) ? $billing_info['reg-page-billing-zip']['label'] :  __( 'Zip Code', 'event_espresso' );

			if ( isset( $billing_info['reg-page-billing-card-nmbr'] )) {

				$this->_template_args['credit_card_info'] = TRUE;

				$ccard = $billing_info['reg-page-billing-card-nmbr']['value'];
				$this->_template_args['card_nmbr']['value'] = substr( $ccard, 0, 4 ) . ' XXXX XXXX ' . substr( $ccard, -4 );
				$this->_template_args['card_nmbr']['label'] = 'Credit Card';

				$this->_template_args['card_exp_date']['value'] = $billing_info['reg-page-billing-card-exp-date-mnth']['value'] . ' / ' . $billing_info['reg-page-billing-card-exp-date-year']['value'];
				$this->_template_args['card_exp_date']['label'] = 'mm / yy';

				$this->_template_args['card_ccv_code']['value'] = $billing_info['reg-page-billing-card-ccv-code']['value'];
				$this->_template_args['card_ccv_code']['label'] = $billing_info['reg-page-billing-card-ccv-code']['label'];

			} else {
				$this->_template_args['credit_card_info'] = FALSE;
			}

		} else {

			$this->_template_args['free_event'] = $billing_info;

		}


		$template_path = REG_TEMPLATE_PATH . 'reg_admin_details_side_meta_box_box_billing_info.template.php';
		echo espresso_display_template( $template_path, $this->_template_args, TRUE );
	}







	/**
	 * 		generates HTML for the View Registration Details Admin page
	*		@access private
	*		@return void
	*/
	private function _delete_registration() {
		echo '<h1>OMG !!! You just deleted everything !!!</h1>';
		echo '<h1>What have you done ?!?!?</h1>';
		echo '<h1>Timmy\'s gonna be maaaaaad at you !!! </h1>';
		echo '<h1>This is the long way of saying, "Todo"</h1>';
	}






	/**
	 * 		generates Business Reports regarding Registrations
	*		@access protected
	*		@return void
	*/
	protected function _registration_reports() {

		do_action('action_hook_espresso_log', __FILE__, __FUNCTION__, '');
	
		$page_args = array();
		
		$page_args['admin_reports'][] = $this->_registrations_per_day_report( '-3 month' );  //  option: '-1 week', '-2 weeks' defaults to '-1 month'
		$page_args['admin_reports'][] = $this->_get_registrations_per_event_report( '-3 month' ); //  option: '-1 week', '-2 weeks' defaults to '-1 month'
//		$page_args['admin_reports'][] = 'chart1';
		
		$template_path = EE_CORE_ADMIN . 'admin_reports.template.php';
		$this->_template_args['admin_page_content'] = espresso_display_template( $template_path, $page_args, TRUE );
		
//		printr( $page_args, '$page_args' );
		
		// the final template wrapper
		$this->display_admin_page_with_no_sidebar();

	}






	/**
	 * 		generates Business Report showing total registratiopns per day
	*		@access private
	*		@return void
	*/
	private function _registrations_per_day_report( $period = '-1 month' ) {
	
		$report_ID = 'reg-admin-registrations-per-day-report-dv';
		$report_JS = 'espresso_reg_admin_regs_per_day';
		
		wp_enqueue_script( $report_JS, REG_ASSETS_URL . $report_JS . '_report.js', array('jquery', 'jqplot'), EVENT_ESPRESSO_VERSION, TRUE);

		require_once ( EVENT_ESPRESSO_INCLUDES_DIR . 'models/EEM_Registration.model.php' );
	    $REG = EEM_Registration::instance();
	 
		if( $results = $REG->get_registrations_per_day_report( $period ) ) {		
			//printr( $results, '$registrations_per_day' );
			$regs = array();
			$xmin = date( 'Y-m-d', strtotime( '+1 year' ));
			$xmax = 0;
			$ymax = 0;
			foreach ( $results as $result ) {
				$regs[] = array( $result->regDate, (int)$result->total );
				$xmin = strtotime( $result->regDate ) < strtotime( $xmin ) ? $result->regDate : $xmin;
				$xmax = strtotime( $result->regDate ) > strtotime( $xmax ) ? $result->regDate : $xmax;
				$ymax = $result->total > $ymax ? $result->total : $ymax;
			}
			
			$xmin = date( 'Y-m-d', strtotime( date( 'Y-m-d', strtotime($xmin)) . ' -1 day' ));			
			$xmax = date( 'Y-m-d', strtotime( date( 'Y-m-d', strtotime($xmax)) . ' +1 day' ));
			// calculate # days between our min and max dates				
			$span = floor( (strtotime($xmax) - strtotime($xmin)) / (60*60*24)) + 1;
			
			$report_params = array(
														'title' 	=> 'Total Registrations per Day',
														'id' 		=> $report_ID,
														'regs' 	=> $regs,												
														'xmin' 	=> $xmin,
														'xmax' 	=> $xmax,
														'ymax' 	=> ceil($ymax * 1.25),
														'span' 	=> $span,
														'width'	=> ceil(900 / $span)												
													);
			wp_localize_script( $report_JS, 'regPerDay', $report_params );
		}
												
		return $report_ID;
	}






	/**
	 * 		generates Business Report showing total registratiopns per event
	*		@access private
	*		@return void
	*/
	private function _get_registrations_per_event_report( $period = '-1 month' ) {
	
		$report_ID = 'reg-admin-registrations-per-event-report-dv';
		$report_JS = 'espresso_reg_admin_regs_per_event';
		
		wp_enqueue_script( $report_JS, REG_ASSETS_URL . $report_JS . '_report.js', array('jquery', 'jqplot'), '1.0', TRUE);

		require_once ( EVENT_ESPRESSO_INCLUDES_DIR . 'models/EEM_Registration.model.php' );
	    $REG = EEM_Registration::instance();
	 
		if( $results = $REG->get_registrations_per_event_report( $period ) ) {		
			//printr( $results, '$registrations_per_event' );
			$regs = array();
			$limits = array();
			$ymax = 0;
			foreach ( $results as $result ) {
				$regs[] = array( $result->event_name, (int)$result->total );
				$ymax = $result->total > $ymax ? $result->total : $ymax;
			}	

			$span = $period == 'week' ? 9 : 33;

			$report_params = array(
														'title' 	=> 'Total Registrations per Event',
														'id' 		=> $report_ID,
														'regs' 	=> $regs,												
														'limits' => $limits,												
														'ymax' 	=> ceil($ymax * 1.25),
														'span' 	=> $span,
														'width'	=> ceil(900 / $span)								
													);
			wp_localize_script( $report_JS, 'regPerEvent', $report_params );		
		}

		return $report_ID;
	}





	/**
	 * get registrations for given parameters (used by list table)
	 * @param  int  $perpage    how many registrations displayed per page
	 * @param  boolean $count   return the count or objects
	 * @return mixed (int|array)  int = count || array of registration objects
	 */
	public function _get_registrations( $perpage, $count = FALSE ) {
		require_once ( EVENT_ESPRESSO_INCLUDES_DIR . 'models/EEM_Base.model.php' );
		require_once ( EVENT_ESPRESSO_INCLUDES_DIR . 'models/EEM_Registration.model.php' );
		$REG = EEM_Registration::instance();

		$EVT_ID = isset( $this->_req_data['event_id'] ) ? absint( $this->_req_data['event_id'] ) : FALSE;
		$CAT_ID = isset( $this->_req_data['category_id'] ) ? absint( $this->_req_data['category_id'] ) : FALSE;
		$reg_status = isset( $this->_req_data['reg_status'] ) ? sanitize_text_field( $this->_req_data['reg_status'] ) : FALSE;
		$month_range = isset( $this->_req_data['month_range'] ) ? sanitize_text_field( $this->_req_data['month_range'] ) : FALSE;
		$today_a = isset( $this->_req_data['status'] ) && $this->_req_data['status'] == 'today' ? TRUE : FALSE;
		$this_month_a = isset( $this->_req_data['status'] ) && $this->_req_data['status'] == 'month' ? TRUE  : FALSE;
		$start_date = FALSE;
		$end_date = FALSE;


		//set orderby
		$this->_req_data['orderby'] = ! empty($this->_req_data['orderby']) ? $this->_req_data['orderby'] : '';

		switch ( $this->_req_data['orderby'] ) {
			case 'REG_ID':
				$orderby = 'REG_ID';
				break;
			case 'Reg_status':
				$orderby = 'STS_ID';
				break;
			case 'ATT_fname':
				$orderby = 'REG_att_name';
				break;
			case 'event_name':
				$orderby = 'event_name';
				break;
			case 'DTT_EVT_start':
				$orderby = 'DTT_EVT_start';
				break;
			default: //'REG_date'
				$orderby = 'REG_date';
		}

		$sort = ( isset( $this->_req_data['order'] ) && ! empty( $this->_req_data['order'] )) ? $this->_req_data['order'] : 'ASC';
		$current_page = isset( $this->_req_data['paged'] ) && !empty( $this->_req_data['paged'] ) ? $this->_req_data['paged'] : 1;
		$per_page = isset( $per_page ) && !empty( $per_page ) ? $per_page : 10;
		$per_page = isset( $this->_req_data['perpage'] ) && !empty( $this->_req_data['perpage'] ) ? $this->_req_data['perpage'] : $per_page;

		$offset = ($current_page-1)*$per_page;
		$limit = array( $offset, $per_page );

		$registrations = $REG->get_registrations_for_admin_page( $EVT_ID, $CAT_ID, $reg_status, $month_range, $today_a, $this_month_a, $start_date, $end_date, $orderby, $sort, $limit, $count );

		return $registrations;
	}






	public function get_registration_status_array() {
		return self::$_reg_status;
	}



}



// end of file:  includes/core/admin/transactions/Registrations_Admin_Page.core.php