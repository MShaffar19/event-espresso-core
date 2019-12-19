import { OperationVariables } from 'apollo-client';
import usePriceQueryOptions from '../../queries/prices/usePriceQueryOptions';
import useOnCreatePrice from './useOnCreatePrice';
import useOnUpdatePrice from './useOnUpdatePrice';
import useOnDeletePrice from './useOnDeletePrice';
import {
	Mutator,
	MutationType,
	MutationInput,
	OnUpdateFnOptions,
	MutatorGeneratedObject,
} from '../../../../../application/services/apollo/mutations/types';
import { ReadQueryOptions } from '../../queries/types';
import { Price } from '../../types';
import { PriceMutationCallbackFn } from '../types';

/**
 *
 */
const usePriceMutator = (): Mutator => {
	const options: ReadQueryOptions = usePriceQueryOptions();

	const onCreatePrice: PriceMutationCallbackFn = useOnCreatePrice();
	const onUpdatePrice: PriceMutationCallbackFn = useOnUpdatePrice();
	const onDeletePrice: PriceMutationCallbackFn = useOnDeletePrice();

	const createVariables = (mutationType: MutationType, input: MutationInput): OperationVariables => {
		const mutationInput: MutationInput = {
			clientMutationId: `${mutationType}_PRICE`,
			...input,
		};

		return {
			input: mutationInput,
		};
	};

	const mutator = (mutationType: MutationType, input: MutationInput): MutatorGeneratedObject => {
		// `ticketId` must be passed for price creation
		// so as to properly set the relations
		const { ticketId, ...restInput } = input;
		const { priceType: priceTypeId } = input;
		const variables: OperationVariables = createVariables(mutationType, restInput);
		/**
		 * @todo update optimisticResponse
		 */
		let optimisticResponse: any;

		const onUpdate = ({ proxy, entity: price }: OnUpdateFnOptions<Price>): void => {
			// Read the existing data from cache.
			const { espressoPrices: prices = {} } = proxy.readQuery(options);

			switch (mutationType) {
				case MutationType.Create:
					onCreatePrice({ proxy, prices, price, ticketId, priceTypeId });
					break;
				case MutationType.Update:
					onUpdatePrice({ price, priceTypeId });
					break;
				case MutationType.Delete:
					onDeletePrice({ proxy, prices, price });
					break;
			}
		};

		return { variables, optimisticResponse, onUpdate };
	};

	return mutator;
};

export default usePriceMutator;
