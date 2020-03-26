import React from 'react';
import { format } from 'date-fns';
import { __ } from '@wordpress/i18n';

import { Cell } from '@appLayout/espressoTable';
import { filterCellByStartOrEndDate } from '@sharedServices/filterState';
import { ENTITY_LIST_DATE_TIME_FORMAT } from '@appConstants/dateFnsFormats';
import { getBackgroundColorClassName, status } from '@sharedEntities/tickets/helpers';
import { InlineEditText } from '@appInputs/InlineEditInput';
import { shortenGuid } from '@appServices/utilities/text';
import { Ticket } from '@edtrServices/apollo/types';
import TicketActionsMenu from '@edtrUI/tickets/ticketsList/actionsMenu/TicketActionsMenu';
import { useMoneyDisplay } from '@appServices/utilities/money';
import { useTicketMutator } from '@edtrServices/apollo/mutations';
import useTicketRegistrationCount from '@edtrUI/tickets/hooks/useTicketRegistrationCount';
import TicketQuantity from '../cardView/TicketQuantity';
import { BodyRowGeneratorFn } from '@appLayout/entityList';
import { TicketsFilterStateManager } from '@edtrServices/filterState';

import '@application/ui/styles/root/entity-status.css';

type TicketsTableBodyRowGen = BodyRowGeneratorFn<Ticket, TicketsFilterStateManager>;

const bodyRowGenerator: TicketsTableBodyRowGen = ({ entity: ticket, filterState }) => {
	const { displayStartOrEndDate } = filterState;

	const bgClassName = getBackgroundColorClassName(ticket);
	const { formatAmount } = useMoneyDisplay();
	const id = ticket.dbId || shortenGuid(ticket.id);
	const registrationCount = useTicketRegistrationCount(ticket.id);
	const statusClassName = status(ticket);
	const { updateEntity } = useTicketMutator(ticket.id);

	const name = {
		key: 'name',
		type: 'cell',
		className:
			'ee-ticket-list-cell ee-ticket-list-col-name ee-rspnsv-table-column-bigger ee-rspnsv-table-hide-on-mobile',
		value: (
			<InlineEditText
				className={'ee-focus-priority-5'}
				onChange={(name: string): void => {
					if (name !== ticket.name) {
						updateEntity({ name });
					}
				}}
			>
				{ticket.name ? ticket.name : __('Edit title...')}
			</InlineEditText>
		),
	};

	const quantity = {
		key: 'quantity',
		type: 'cell',
		className: 'ee-ticket-list-cell ee-ticket-list-col-quantity ee-rspnsv-table-column-tiny ee-number-column',
		value: <TicketQuantity ticket={ticket} />,
	};

	const cellsData: Array<Cell> = [
		{
			key: 'stripe',
			type: 'cell',
			className: `ee-ticket-list-cell ee-entity-list-status-stripe ${bgClassName} ee-rspnsv-table-column-micro`,
			value: <div className={'ee-rspnsv-table-show-on-mobile'}>{ticket.name}</div>,
		},
		{
			key: 'id',
			type: 'cell',
			className: 'ee-ticket-list-cell ee-ticket-list-col-id ee-rspnsv-table-column-tiny ee-number-column',
			value: id,
		},
		name,
		{
			key: 'start',
			type: 'cell',
			className: 'ee-ticket-list-cell ee-ticket-list-col-start ee-rspnsv-table-column-default',
			value: format(new Date(ticket.startDate), ENTITY_LIST_DATE_TIME_FORMAT),
		},
		{
			key: 'end',
			type: 'cell',
			className: 'ee-ticket-list-cell ee-ticket-list-col-end ee-rspnsv-table-column-default',
			value: format(new Date(ticket.endDate), ENTITY_LIST_DATE_TIME_FORMAT),
		},
		{
			key: 'price',
			type: 'cell',
			className: 'ee-ticket-list-col-hdr ee-ticket-list-col-price ee-rspnsv-table-column-tiny ee-number-column',
			value: formatAmount(ticket.price),
		},
		quantity,
		{
			key: 'sold',
			type: 'cell',
			className: 'ee-ticket-list-cell ee-ticket-list-col-sold ee-rspnsv-table-column-tiny ee-number-column',
			value: ticket.sold,
		},
		{
			key: 'registrations',
			type: 'cell',
			className:
				'ee-ticket-list-cell ee-ticket-list-col-registrations ee-rspnsv-table-column-smaller ee-centered-column',
			value: registrationCount, // should be count of related registrations
		},
		{
			key: 'actions',
			type: 'cell',
			className: 'ee-ticket-list-cell ee-ticket-list-col-actions ee-rspnsv-table-column-big',
			value: <TicketActionsMenu entity={ticket} />,
		},
	];

	const cells = cellsData.filter(filterCellByStartOrEndDate(displayStartOrEndDate));

	return {
		cells,
		className: `ee-editor-date-list-view-row ${statusClassName}`,
		id: `ee-editor-date-list-view-row-${ticket.id}`,
		key: `row-${ticket.id}`,
		type: 'row',
	};
};

export default bodyRowGenerator;