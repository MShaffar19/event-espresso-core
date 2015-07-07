<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
/**
 *
 * Display Strategy for line item tables in the admin.
 *
 * @package       Event Espresso
 * @subpackage    core
 * @author		  Darren Ethier
 * @since		  4.8
 *
 */

class EE_Admin_Table_Line_Item_Display_Strategy implements EEI_Line_Item_Display {


	/**
	 * whether to display the taxes row or not
	 * @type bool $_show_taxes
	 */
	private $_show_taxes = FALSE;

	/**
	 * html for any tax rows
	 * @type string $_show_taxes
	 */
	private $_taxes_html = '';


	/**
	 * total amount including tax we can bill for at this time
	 * @type float $_grand_total
	 */
	private $_grand_total = 0.00;



	/**
	 * @return float
	 */
	public function grand_total() {
		return $this->_grand_total;
	}



	/**
	 * This is used to output a single
	 * @param EE_Line_Item $line_item
	 * @param array        $options
	 * @return mixed
	 */
	public function display_line_item( EE_Line_Item $line_item, $options = array() ) {

		EE_Registry::instance()->load_helper( 'Template' );
		EE_Registry::instance()->load_helper( 'HTML' );

		$html = '';
		// set some default options and merge with incoming
		$default_options = array(
			'odd' => true,
			'use_table_wrapper' => true,
			'table_css_class' => 'admin-primary-mbox-tbl',
			'taxes_tr_css_class' => 'admin-primary-mbox-taxes-tr',
			'total_tr_css_class' => 'admin-primary-mbox-total-tr'
		);
		$options = array_merge( $default_options, (array)$options );

		switch( $line_item->type() ) {

			case EEM_Line_Item::type_line_item:
				// item row
				$html .= $this->_item_row( $line_item, $options );
				break;

			case EEM_Line_Item::type_sub_line_item:
				$html .= $this->_sub_item_row( $line_item, $options );
				break;

			case EEM_Line_Item::type_sub_total:
				//loop through children
				$child_line_items = $line_item->children();
				//loop through children
				foreach ( $child_line_items as $child_line_item ) {
					//recursively feed children back into this method
					$html .= $this->display_line_item( $child_line_item, $options );
				}
				$html .= $this->_sub_total_row( $line_item, $options );
				break;

			case EEM_Line_Item::type_tax:
				if ( $this->_show_taxes ) {
					$this->_taxes_html .= $this->_tax_row( $line_item, $options );
				}
				break;

			case EEM_Line_Item::type_tax_sub_total:
				//no tax subtotal for admin table.
				break;

			case EEM_Line_Item::type_total:
				// determine whether to display taxes or not
				$this->_show_taxes = $line_item->get_total_tax() > 0 ? true : false;
				// get all child line items
				$children = $line_item->children();
				// loop thru all non-tax child line items
				foreach( $children as $child_line_item ) {
					if ( $child_line_item->type() != EEM_Line_Item::type_tax_sub_total ) {
						// recursively feed children back into this method
						$html .= $this->display_line_item( $child_line_item, $options );
					}
				}

				$html .= $this->_taxes_html;
				$html .= $this->_total_row( $line_item, $options );
				if ( $options['use_table_wrapper'] ) {
					$html = $this->_table_header( $options ) . $html . $this->_table_footer( $options );
				}
				break;

		}

		return $html;
	}


	/**
	 * Table header for display.
	 * @since 4.8
	 * @options array Array of options for the table.
	 * @return string
	 */
	protected function _table_header( $options ) {
		$html = EEH_HTML::table( '','', $options['table_css_class'] );
		$html .= EEH_HTML::thead();
		$html .= EEH_HTML::tr();
		$html .= EEH_HTML::th( __( 'Type', 'event_espresso'), '', 'jst-left' );
		$html .= EEH_HTML::th( __( 'Name', 'event_espresso' ), '', 'jst-left' );
		$html .= EEH_HTML::th( __( 'Amount', 'event_espresso' ), '', 'jst-cntr' );
		$html .= EEH_HTML::th( __( 'Qty', 'event_espresso' ), '', 'jst-cntr' );
		$html .= EEH_HTML::th( __( 'Line Total', 'event_espresso'), '', 'jst-cntr' );
		$html .= EEH_HTML::tbody();
		return $html;
	}


	/**
	 * Table footer for display
	 * @since 4.8
	 * @param array $options array of options for the table.
	 * @return string
	 */
	protected function _table_footer( $options ) {
		return EEH_HTML::tbodyx() .  EEH_HTML::tablex();
	}



	/**
	 *    _item_row
	 *
	 * @param EE_Line_Item $line_item
	 * @param array        $options
	 * @return mixed
	 */
	private function _item_row( EE_Line_Item $line_item, $options = array() ) {
		$line_item_related_object = $line_item->get_object();
		$parent_line_item_related_object = $line_item->parent() instanceof EE_Line_Item ? $line_item->parent()->get_object() : null;
		// start of row
		$row_class = $options['odd'] ? 'item odd' : 'item';
		$html = EEH_HTML::tr( '', '', $row_class );

		//Type Column
		$type_html = $line_item->OBJ_type() ? $line_item->OBJ_type() . '<br />' : '';
		$type_html .= '<span class="ee-line-item-id">' . $line_item->code() . '</span>';
		$html .= EEH_HTML::td( $type_html, '', 'jst-left' );

		//Name Column
		$name_link = $line_item_related_object instanceof EEI_Admin_Links ? $line_item_related_object->get_admin_details_link() : '';

		//related object scope.
		$parent_related_object_name = $parent_line_item_related_object instanceof EEI_Line_Item_Object ? $parent_line_item_related_object->get_object_name() : $line_item->parent()->name();
		$parent_related_object_link = $parent_line_item_related_object instanceof EEI_Admin_Links ? $parent_line_item_related_object->get_admin_details_link() : '';

		$name_html = $line_item_related_object instanceof EEI_Line_Item_Object ? $line_item_related_object->get_object_name() : $line_item->name();
		$name_html = $name_link ? '<a href="' . $name_link . '">' . $name_html . '</a>' : $name_html;
		$name_html .= $line_item->is_taxable() ? ' *' : '';
		$name_html .= '<br>';
		$name_html .=  sprintf(
			_x( '%1$sfor the %2$s, %3$s', 'eg. "for the Event, My Cool Event"', 'event_espresso'),
			'<span class="ee-line-item-related-parent-object">',
			$line_item->parent() instanceof EE_Line_Item ? $line_item->parent()->name() : __( 'Item', 'event_espresso' ),
			$parent_related_object_link ? '<a href="' . $parent_related_object_link . '">' . $parent_related_object_name . '</a>' : $parent_related_object_name
		);
		$html .= EEH_HTML::td( $name_html, '', 'jst-left' );

		//Amount Column
		if ( $line_item->is_percent() ) {
			$html .= EEH_HTML::td( $line_item->percent() . '%', '', 'jst-rght' );
		} else {
			$html .= EEH_HTML::td( $line_item->unit_price_no_code(), '', 'jst-rght' );
		}

		//QTY column
		$html .= EEH_HTML::td( $line_item->quantity(), '', 'jst-rght' );

		//total column
		$html .= EEH_HTML::td( EEH_Template::format_currency( $line_item->total(), false, false ), '', 'jst-rght' );

		//finish things off and return
		$html .= EEH_HTML::trx();
		return $html;
	}



	/**
	 * 	_sub_item_row
	 *
	 * @param EE_Line_Item $line_item
	 * @param array        $options
	 * @return mixed
	 */
	private function _sub_item_row( EE_Line_Item $line_item, $options = array() ) {
		//for now we're not showing sub-items
		return '';
	}



	/**
	 * 	_tax_row
	 *
	 * @param EE_Line_Item $line_item
	 * @param array        $options
	 * @return mixed
	 */
	private function _tax_row( EE_Line_Item $line_item, $options = array() ) {
		// start of row
		$html = EEH_HTML::tr( '', 'admin-primary-mbox-taxes-tr' );
		// name th
		$html .= EEH_HTML::th(  $line_item->name() . '(' . $line_item->get_pretty( 'LIN_percent' ) . '%)', '',  'jst-rght', '', ' colspan="4"' );
		// total th
		$html .= EEH_HTML::th( EEH_Template::format_currency( $line_item->total(), false, false ), '', 'jst-rght' );
		// end of row
		$html .= EEH_HTML::trx();
		return $html;
	}




	/**
	 * 	_total_row
	 *
	 * @param EE_Line_Item $line_item
	 * @param string       $text
	 * @param array        $options
	 * @return mixed
	 */
	private function _sub_total_row( EE_Line_Item $line_item, $text = '', $options = array() ) {
		//currently not showing subtotal row
		return '';
	}



	/**
	 * 	_total_row
	 *
	 * @param EE_Line_Item $line_item
	 * @param string       $text
	 * @param array        $options
	 * @return mixed
	 */
	private function _total_row( EE_Line_Item $line_item, $text = '', $options = array() ) {
		$html = '';
		// start of row
		$html = EEH_HTML::tr( '', '', 'admin-primary-mbxo-total-tr' );
		// Total th label
		$total_label = sprintf( __( 'Transaction Total %s', 'event_espresso' ),  '(' . EE_Registry::instance()->CFG->currency->code . ')' );
		$html .= EEH_HTML::th( $total_label, '',  'jst-rght',  '',  ' colspan="4"' );
		// total th

		$html .= EEH_HTML::th( EEH_Template::format_currency( $line_item->total(), false, false ), '',  'jst-rght' );
		// end of row
		$html .= EEH_HTML::trx();
		return $html;
	}

} // End of EE_Admin_Table_Line_Item_Display_Strategy