import React from 'react';
import { __ } from '@wordpress/i18n';

import { EspressoButton, Icon } from '@application/ui/input';
import { EditItemButtonProps } from '@edtrInterfaces/types';
import { useDatetimeContext } from '@edtrHooks/index';
import { useDatetimeItem } from '@edtrServices/apollo/queries';
import { useFormModal } from '@appLayout/formModal';

const EditDateButton: React.FC<EditItemButtonProps> = ({ ...rest }) => {
	const { id: entityId } = useDatetimeContext();
	const { dbId: entityDbId } = useDatetimeItem({ id: entityId }) || {};

	const { openEditor } = useFormModal();

	const onClick = (): void =>
		openEditor({
			editorId: 'editDatetime',
			entityId,
			entityDbId,
		});

	return (
		<EspressoButton
			icon={Icon.EDIT}
			tooltip={__('edit datetime')}
			tooltipProps={{ placement: 'right' }}
			onClick={onClick}
			{...rest}
		/>
	);
};

export default EditDateButton;