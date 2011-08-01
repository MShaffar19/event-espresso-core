<?php
function enter_attendee_payments() {

    wp_tiny_mce( false, // true makes the editor "teeny"
            array(
                "editor_selector" => "theEditor"//This is the class name of your text field
            )
    );
    global $wpdb, $org_options;
    $event_id = $_REQUEST[ 'event_id' ];
    $today = date( "d-m-Y" );

//Added by Imon
	$multi_reg = false;
	$registration_id = $_REQUEST['registration_id'];
	$registration_ids = array();
	$check = $wpdb->get_row("select * from ".EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE." where registration_id = '$registration_id' ");
	if ( $check !== NULL )
	{
		$registration_id = $check->primary_registration_id;
		$registration_ids = $wpdb->get_results("select * from ".EVENTS_MULTI_EVENT_REGISTRATION_ID_GROUP_TABLE." where primary_registration_id = '$registration_id' ", ARRAY_A);
		$multi_reg = true;
	}
    switch ( $_REQUEST[ 'form_action' ] )
    {
        //Add payment info
        case 'payment':
            if ( $_REQUEST[ 'attendee_action' ] == 'post_payment' )
            {
//Added by Imon
				$primary_row = $wpdb->get_row("select id from ".EVENTS_ATTENDEE_TABLE." where registration_id = '$registration_id' limit 0,1 order by id ");
				$primary_attendee_id = $primary_row->id; // GET the primary attendee id because amount paid info is kept with the primary attendee 
                $payment_status = $_REQUEST[ 'payment_status' ];
                $txn_type = $_REQUEST[ 'txn_type' ];
                $txn_id = $_REQUEST[ 'txn_id' ];
                $quantity = $_REQUEST[ 'quantity' ];
                $amount_pd = $_REQUEST[ 'amount_pd' ];
                $payment_date = $_REQUEST[ 'payment_date' ];
                $coupon_code = $_REQUEST[ 'coupon_code' ];

//Added/updated by Imon
				//Update payment status information for primary attendee
				$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '$payment_status', txn_type = '$txn_type', txn_id = '$txn_id', amount_pd = '$amount_pd', payment_date ='$payment_date', quantity = '$quantity',  coupon_code ='$coupon_code' WHERE registration_id ='" . $registration_id . "' and id = $primary_attendee_id ";
				$wpdb->query( $sql );
				
				if ( count($registration_ids) > 0 )
				{
					foreach($registration_ids as $reg_id)
					{
						// Update payment status information for all attendees
						$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '$payment_status', txn_type = '$txn_type', txn_id = '$txn_id', payment_date ='$payment_date', coupon_code ='$coupon_code' WHERE registration_id ='" . $reg_id['registration_id']. "' ";
						$wpdb->query( $sql );
					}
				}
				else
				{
					// Update payment status information for all attendees
					$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET payment_status = '$payment_status', txn_type = '$txn_type', txn_id = '$txn_id', payment_date ='$payment_date', coupon_code ='$coupon_code' WHERE registration_id ='" . $registration_id . "' ";
					$wpdb->query( $sql );
				}
				
                //Send Payment Recieved Email
                if ( $_REQUEST[ 'send_payment_rec' ] == "send_message" )
                {
                    /*
                     * @todo Do we send an email to each attendee in a group or just the main?
                     */
                    //event_espresso_send_payment_notification( $id );
//Added by Imon
					if ( count($registration_ids) > 0 )
					{
						foreach($registration_ids as $reg_id)
						{
							event_espresso_send_payment_notification(array('registration_id'=>$reg_id['registration_id']));
						}
					}
					else
					{
						event_espresso_send_payment_notification(array('registration_id'=>$registration_id));
					}
                }
            }
            break;

        //Send Invoice
        case 'send_invoice':
//Added by Imon
			if ( $org_options["use_attendee_pre_approval"] == "Y" ) {
				$pre_approve = $_REQUEST['pre_approve'];
				if ( count($registration_ids) > 0 )
				{
					foreach($registration_ids as $reg_id)
					{
						$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET pre_approve = '$pre_approve' WHERE registration_id ='" . $reg_id['registration_id'] . "'";
						$wpdb->query( $sql );
					}
				}
				else
				{
					$sql = "UPDATE " . EVENTS_ATTENDEE_TABLE . " SET pre_approve = '$pre_approve' WHERE registration_id ='" . $registration_id . "'";
					$wpdb->query( $sql );
				}
				
			} else {
				$pre_approve = 0;
			}
			if ( $pre_approve == "0" ) {
	            
				if ( count($registration_ids) > 0 )
				{
					foreach($registration_ids as $reg_id)
					{
						event_espresso_send_invoice( $reg_id['registration_id'], $_REQUEST[ 'invoice_subject' ], $_REQUEST[ 'invoice_message' ] );
					}
				}
				else
				{
					event_espresso_send_invoice( $registration_id , $_REQUEST[ 'invoice_subject' ], $_REQUEST[ 'invoice_message' ] );
				}
				echo '<div id="message" class="updated fade"><p><strong>'.__('Invoice Sent', 'event_espresso').'</strong></p></div>';
			}
            break;
    }

    //Show the forms.
    $id = $registration_id ;
    $attendees = $wpdb->get_results( "SELECT * FROM " . EVENTS_ATTENDEE_TABLE . " WHERE registration_id ='" . $id . "' ORDER BY ID LIMIT 1" );
    foreach ( $attendees as $attendee ) {
        $id = $attendee->id;
		//$registration_id = $attendee->registration_id;//Removed by Imon
        $lname = $attendee->lname;
        $fname = $attendee->fname;
        $address = $attendee->address;
        $city = $attendee->city;
        $state = $attendee->state;
        $zip = $attendee->zip;
        $email = $attendee->email;
        $phone = $attendee->phone;
        $date = $attendee->date;
        $payment_status = $attendee->payment_status;
        $txn_type = $attendee->txn_type;
        $txn_id = $attendee->txn_id;
        $amount_pd = $attendee->amount_pd;
        $quantity = $attendee->quantity;
        $payment_date = $attendee->payment_date;
        $event_id = $attendee->event_id;
        $coupon_code = $attendee->coupon_code;
		$pre_approve = $attendee->pre_approve;
    }

    $events = $wpdb->get_results( "SELECT * FROM " . EVENTS_DETAIL_TABLE . " WHERE id='" . $event_id . "'" );
    foreach ( $events as $event ) {
        $event_id = $event->id;
        $event_name = $event->event_name;
        $event_desc = $event->event_desc;
        $event_description = $event->event_desc;
        $event_identifier = $event->event_identifier;
        $cost = $event->event_cost;
        $active = $event->is_active;
    }

	if ( $_REQUEST[ 'status' ] == 'saved' ){ ?>
    <div id="message" class="updated fade">
        <p><strong><?php _e( 'Payment details saved for', 'event_espresso' ); ?> <?php echo $fname ?> <?php echo $lname ?>.
<?php if ( $_REQUEST[ 'send_payment_rec' ] == "send_message" )
                { ?><?php _e( 'Payment notification has been sent.', 'event_espresso' ); ?><?php } ?>
                        </strong></p>
                </div><?php

            }
?>          		

            <div class="metabox-holder">
                <div class="postbox">
                	<?php
						if ( !$multi_reg )
						{
					?>
                    <h3><?php _e( 'Attendee', 'event_espresso' ); ?> #<?php echo $id ?> | <?php _e( 'Name:', 'event_espresso' ); ?> <?php echo $fname ?> <?php echo $lname ?> | <?php _e( 'Registered For:', 'event_espresso' ); ?> <?php echo $event_name ?></h3>
                    <?php
						}
						else
						{
					?>
                    <h3>
						<?php 
							_e('Payment info for registration ids - ', 'event_espresso'); 
							if ( count($registration_ids) > 0 )
							{
								foreach($registration_ids as $reg_id)
								{
									_e(' #'.$reg_id['registration_id'], 'event_espresso');
								}
							}
						?> 
                    </h3>
                    <?php
						}
					?>

					<div class="inside">
                    <table width="100%" border="0">
                        <tr>
                            <td><strong><?php _e( 'Payment Details', 'event_espresso' ); ?></strong></td>
                            <td><strong><?php _e( 'Invoice/Payment Reminder', 'event_espresso' ); ?></strong></td>
                        </tr>
                        <tr>
                            <td valign="top">
                                <form method="POST" action="<?php echo $_SERVER[ 'REQUEST_URI' ] ?>&status=saved" class="espresso_form">
                                    <fieldset>
                                        <ul>
                                            <li>
                                            <?php _e( 'Payment Status:', 'event_espresso' ); ?>
                                            <?php $values=array(
													  array('id'=>'','text'=> __('None','event_espresso')),
													  array('id'=>'Completed','text'=> __('Completed','event_espresso')),
													  array('id'=>'Pending','text'=> __('Pending','event_espresso')),
													  array('id'=>'Payment Declined','text'=> __('Payment Declined','event_espresso')),
													  array('id'=>'Incomplete','text'=> __('Incomplete','event_espresso')));
															echo select_input('payment_status', $values, $payment_status); ?>
                                            </li>

                                            <li><label><?php _e( 'Transaction Type:', 'event_espresso' ); ?></label> <input type="text" name="txn_type" size="45" value ="<?php echo $txn_type; ?>" /></li>

                                            <li><label><?php _e( 'Transaction ID:', 'event_espresso' ); ?></label> <input type="text" name="txn_id" size="45" value ="<?php echo $txn_id; ?>" /></li>

                                            <li><label><?php _e( 'Amount:', 'event_espresso' ); ?></label> <?php echo $org_options[ 'currency_symbol' ] ?><input type="text" name="amount_pd" size="45" value ="<?php echo $amount_pd; ?>" /></li>
                                <li><label><?php _e( 'Coupon Code', 'event_espresso' ); ?>:</label> <input type="text" name="coupon_code" size="45" value ="<?php echo $coupon_code; ?>" /></li>

                                <li><label><?php _e( 'How Many People:', 'event_espresso' ); ?></label> <input type="text" name="quantity" size="45" value ="<?php echo espresso_count_attendees_for_registration($id); ?>" /></li>

                                <li><label><?php _e( 'Date Paid:', 'event_espresso' ); ?></label> <input <?php echo $payment_date != ""? 'disabled="disabled"':''?>  type="text" name="payment_date" size="45" value ="<?php echo $payment_date != ""? event_date_display($payment_date): event_date_display($today) ?>" /></li>

                                <li><label><?php _e( 'Do you want to send a payment recieved notice to registrant?', 'event_espresso' ); ?> </label><input type="radio" name="send_payment_rec" value="send_message"><?php _e( 'Yes', 'event_espresso' ); ?> <input type="radio" name="send_payment_rec" checked value="N"><?php _e( 'No', 'event_espresso' ); ?></li>

                                <input type="hidden" name="id" value="<?php echo $id ?>">
                                <input type="hidden" name="registration_id" value="<?php echo $registration_id ?>">
                                <input type="hidden" name="form_action" value="payment">
                                <input type="hidden" name="attendee_pay" value="paynow">
                                <input type="hidden" name="event_id" value="<?php echo $event_id ?>">
                                <input type="hidden" name="attendee_action" value="post_payment">
                                <li><input type="submit" name="Submit" value="Post Payment"></li>
                            </ul>
                        </fieldset>
                    </form></td>
                <td valign="top">
                    <form method='post' action="<?php echo $_SERVER[ 'REQUEST_URI' ] ?>&status=invoiced">
                                                                           <input type="hidden" name="id" value="<?php echo $id ?>">
                                                                           <input type="hidden" name="form_action" value="send_invoice">
                                                                           <input type="hidden" name="event_id" value="<?php echo $event_id ?>">
                                                                           
                                                                           <ul>
                                                                           <li><?php _e('Use a ', 'event_espresso'); ?> <a href="admin.php?page=event_emails" target="_blank"><?php _e('pre-existing email', 'event_espresso'); ?></a>?  <?php echo espresso_db_dropdown('id', 'email_name', EVENTS_EMAIL_TABLE, 'email_name', $email_id, 'desc') . ' <a class="ev_reg-fancylink" href="#email_manager_info"><img src="' . EVENT_ESPRESSO_PLUGINFULLURL . '/images/question-frame.png" width="16" height="16" /></a>'; ?> </li>
                                                                           <li><?php _e('OR', 'event_espresso'); ?></li>
                                                                           <li><?php _e('Create a custom email:', 'event_espresso'); ?></li>
                                                                               <li><?php _e( 'Invoice Subject', 'event_espresso' ); ?>: <input type="text" name="invoice_subject" size="45" value="<?php _e( 'Payment Reminder for [event]', 'event_espresso' ); ?>" /></li>
                                                                               <li><?php _e( 'Message', 'event_espresso' ); ?>:<br />
                                                                                   <textarea class="theEditor" id="invoice_message" name="invoice_message">
<?php _e( 'Dear [fname] [lname], <p>Our records show that we have not received your payment of [cost] for [event_link].</p> <p>Please visit [payment_url] to view your payment options.</p><p>[invoice_link]</p><p>Sincerely,<br />' . $Organization = $org_options[ 'organization' ] . '</p>', 'event_espresso' ); ?>
                                                                                   </textarea> <br />
                                                                                   <p align="right"><strong><a class="ev_reg-fancylink" href="#custom_email_info"><?php _e( 'View Custom Email Tags', 'event_espresso' ); ?></a></strong></p>
                                                                                                               </li>
	<?php 
	if ( $org_options["use_attendee_pre_approval"] == "Y" ) { 
		$pre_approve = is_attendee_approved($event_id,$id)==true?1:0;
	?>
	<li><?php _e("Attendee approved?","event_espresso"); ?>:
    	<?php 
		$pre_approval_values=array(array('id'=>'0','text'=> __('Yes','event_espresso')), array('id'=>'1','text'=> __('No','event_espresso')));
		echo select_input("pre_approve",$pre_approval_values,$pre_approve);
		?> 
        <br />
		<?php _e("(If not approved then invoice will not be sent.)","event_espresso"); ?>
	</li>
	<?php } ?>
	<li><input type="submit" name="Submit" value="Send Invoice"></li>
	</ul>
	</form>

                                                                                                   </td>
                                                                                               </tr>
                                                                                           </table>
	<p>
    	<strong>
        	<a href="admin.php?page=events&event_id=<?php echo $event_id; ?>&event_admin_reports=list_attendee_payments">
    			&lt;&lt;<?php _e('Back to List', 'event_espresso'); ?>
			</a>
		</strong>
	</p>
    </div></div>
	<?php

                                                                                       //This show what tags can be added to a custom email.
																					   event_espresso_custom_email_info();
																					   event_list_attendees();
                                                                                   }
