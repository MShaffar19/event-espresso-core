/**
 * Internal dependencies
 */
import { Datetime } from '../../../../eventEditor/data/types';

const activeUpcoming = (dates: Datetime[]) => {
	return dates.filter(({ isActive, isUpcoming }) => isActive || isUpcoming);
};

export default activeUpcoming;
