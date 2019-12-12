/**
 * External imports
 */
import { render } from '@wordpress/element';

/**
 * Internal imports
 */
import EventEditor from './EventEditor';
import ContextProviders from '../../infrastructure/services/contextProviders/ContextProviders';
import useEventId from './containers/queries/useEventId';

const container = document.getElementById('normal-sortables');

// create and place our 'ee-editor-div' div before that
const editor = document.createElement('div');

editor.className = 'ee-editor-div-prototype';

container.prepend(editor);

const Editor = () => {
	const eventId = useEventId();
	if (eventId) {
		return (
			<ContextProviders>
				<EventEditor />
			</ContextProviders>
		);
	}
	return null;
};

render(<Editor />, editor);