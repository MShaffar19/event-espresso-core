import { EditorModalProps } from '@appLayout/editorModal';
import { EntityId } from '@edtrServices/apollo/types';

export type EditorModals = {
	addDatetime: EditorModalProps;
	editDatetime: EditorModalProps;
	addTicket: EditorModalProps;
	editTicket: EditorModalProps;
	ticketPriceCalculator: EditorModalProps;
};

export type EditorModalsHook = (entityId: EntityId) => EditorModals;