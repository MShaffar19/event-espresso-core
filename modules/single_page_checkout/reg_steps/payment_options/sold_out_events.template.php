
	<h4 class="ee-status sold-out"><b><?php _e('Sold Out', 'event_espresso');?></b></h4>
	<h6 class="pink-text"><?php _e( "We're Sorry", 'event_espresso' ); ?></h6>
	<ul id="spco-sold-out-events-ul"><?php echo $sold_out_events; ?></ul>
	<p id="events-requiring-pre-approval-pg" class="small-text drk-grey-text">
		<?php echo $sold_out_events_msg; ?>
	</p>

	<?php echo $default_hidden_inputs;  ?>
	<?php echo $extra_hidden_inputs;  ?>
