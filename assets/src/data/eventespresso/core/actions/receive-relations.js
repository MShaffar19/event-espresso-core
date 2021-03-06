/**
 * Internal imports
 */
import {
	TYPE_QUEUE_RELATION_ADD,
	TYPE_QUEUE_RELATION_DELETE,
} from '../constants';
import { ACTION_TYPES } from './action-types';
const { relations: types } = ACTION_TYPES;

/**
 * External imports
 */
import { singularModelName } from '@eventespresso/model';

/**
 * Action creator for adding relation indexes for entities and their relations.
 *
 * @param {string} modelName
 * @param {number} entityId
 * @param {string} relationName
 * @param {Array} relatedEntityIds
 * @return {
 * 	{
 * 		type: string,
 * 		modelName: string,
 * 		entityId: number,
 * 		relatedEntityIds: Array
 * 	}
 * } An action object.
 */
function receiveRelatedEntities(
	modelName,
	entityId,
	relationName,
	relatedEntityIds
) {
	modelName = singularModelName( modelName );
	relationName = singularModelName( relationName );
	return {
		type: types.RECEIVE_RELATED_ENTITY_IDS,
		modelName,
		entityId,
		relationName,
		relatedEntityIds,
	};
}

/**
 * Action creator for queuing the a relation creation for the given data.
 *
 * @param {string} relationName
 * @param {number} relationEntityId
 * @param {string} modelName
 * @param {number} entityId
 * @return {
 * 	{
 * 		type: string,
 * 		relationName: string,
 * 		relationEntityId: number,
 * 		modelName: string,
 * 		entityId: number,
 * 		queueType: string
 * 	}
 * }
 * An action object.
 */
function receiveDirtyRelationAddition(
	relationName,
	relationEntityId,
	modelName,
	entityId,
) {
	modelName = singularModelName( modelName );
	relationName = singularModelName( relationName );
	return {
		type: types.RECEIVE_DIRTY_RELATION_ADDITION,
		relationName,
		relationEntityId,
		modelName,
		entityId,
		queueType: TYPE_QUEUE_RELATION_ADD,
	};
}

/**
 * Action creator for queueing the relation deletion for the given data.
 *
 * @param {string} relationName
 * @param {number} relationEntityId
 * @param {string} modelName
 * @param {number} entityId
 * @return {
 * 	{
 * 		type: string,
 * 		relationName: string,
 * 		relationEntityId: number,
 * 		modelName: string,
 * 		entityId: number,
 * 		queueType: string
 * 	}
 * } An action object.
 */
function receiveDirtyRelationDeletion(
	relationName,
	relationEntityId,
	modelName,
	entityId,
) {
	modelName = singularModelName( modelName );
	relationName = singularModelName( relationName );
	return {
		type: types.RECEIVE_DIRTY_RELATION_DELETION,
		relationName,
		relationEntityId,
		modelName,
		entityId,
		queueType: TYPE_QUEUE_RELATION_DELETE,
	};
}

/**
 * Action creator for triggering the replacement of any instance of the old
 * entity id in the state for the given model with the new entity id.
 *
 * Typically this is triggered after persisting a new entity to the server and
 * receiving its new entity id on response.
 *
 * @param {string} modelName
 * @param {number} oldEntityId
 * @param {number} newEntityId
 * @return {
 * 	{
 * 		type: string,
 * 		modelName: *,
 * 		oldEntityId: *,
 * 		newEntityId: *
 * 	}
 * } An action object
 */
function receiveUpdatedEntityIdForRelations(
	modelName,
	oldEntityId,
	newEntityId
) {
	modelName = singularModelName( modelName );
	return {
		type: types.RECEIVE_UPDATED_ENTITY_ID_FOR_RELATIONS,
		modelName,
		oldEntityId,
		newEntityId,
	};
}

export {
	receiveRelatedEntities,
	receiveDirtyRelationAddition,
	receiveDirtyRelationDeletion,
	receiveUpdatedEntityIdForRelations,
};
