import React, { useCallback } from 'react';
import { __ } from '@wordpress/i18n';

import { ConfirmDelete } from '@appDisplay/confirm';
import { useDataState } from '../data';
import { ButtonType } from '@application/ui/input';
import { useMemoStringify } from '@application/services/hooks';

const DeleteAllPricesButton: React.FC = () => {
	const { prices, deletePrice, updateTicketPrice } = useDataState();

	const buttonProps = useMemoStringify({
		buttonText: __('Delete all prices'),
		buttonType: ButtonType.ACCENT,
	});

	const onConfirm = useCallback(() => {
		prices.forEach((price) => {
			// delete the price from TPC state.
			deletePrice(price.id, price.isNew || price.isDefault);
		});
		// Set ticket price to 0
		updateTicketPrice(0);
	}, [prices, deletePrice]);

	const message = __(
		"Are you sure you want to delete all of this ticket's prices and make it free? This action is permanent and can not be undone."
	);

	const title = __('Delete all prices?');

	return <ConfirmDelete buttonProps={buttonProps} onConfirm={onConfirm} message={message} title={title} />;
};

export default DeleteAllPricesButton;
