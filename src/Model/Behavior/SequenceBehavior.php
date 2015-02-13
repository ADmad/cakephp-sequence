<?php
namespace Sequence\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\Utility\Hash;

/**
 * SequenceBehavior maintains a contiguous sequence of integers (starting at 1
 * or other configurable integer) in a selected column, such as `position`, for all
 * records in a table, or groups of records identified by one or more
 * 'scope' fields, when adding, editing (including moving groups) or deleting
 * records.
 *
 * Consider the following simple example with no groups:
 * Record  Position
 * A       1
 * B       2
 * C       3
 * D       4
 * E       5
 * F       6
 * G       7
 *  - Save
 *    - If adding new record
 *      - If order not specified e.g. Record H added:
 *          Inserts H at end of list i.e. highest order + 1
 *      - If order specified e.g. Record H added at position 3:
 *          Inserts at specified order
 *          Increments order of all other records whose order >= order of
 *           inserted record i.e. D, E, F & G get incremented
 *    - If editing existing record:
 *      - If order not specified and scope not specified, or same
 *          No Action
 *      - If order not specified but scope specified and different:
 *          Decrement order of all records whose order > old order in the old
 *           scope, and change order to highest order of new scopes + 1
 *      - If order specified:
 *        - If new order < old order e.g. record E moves from 4 to 2
 *            Increments order of all other records whose order > new order and
 *             order < old order i.e. order of C & D get incremented
 *        - If new order > old order e.g. record C moves from 2 to 4
 *            Decrements order of all other records whose order > old order and
 *             <= new order i.e. order of D & E get decremented
 *        - If new order == old order
 *            No action
 *  - Delete
 *      Decrement order of all records whose order > order of deleted record
 *
 * Inspired by Neil Crooke's Sequence behavior for CakePHP 1.3. Above description
 * has been "borrowed" from it :).
 *
 * @copyright 2015 A. Sarela, aka ADmad
 * @link https://github.com/ADmad/cakephp-sequence
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class SequenceBehavior extends Behavior
{

    /**
     * Default settings.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'order' => 'position',
        'scope' => [],
        'start' => 1,
    ];

    /**
     * Normalize config options.
     *
     * @param array $config Configuration options include:
     * - order : The field name that stores the sequence number.
     *   Defaults is "position".
     * - scope : Array of field names that identify a single group of records
     *   that need to form a contiguous sequence.
     *   Default is empty array, i.e. no scope fields.
     * - start : You can start your sequence numbers at 0 or 1 or any other.
     *   Defaults is 1.
     * @return void
     */
    public function initialize(array $config)
    {
        if (!$this->_config['scope']) {
            return;
        }

        if (is_string($this->_config['scope'])) {
            $this->_config['scope'] = [$this->_config['scope']];
        }
    }

    /**
     * Adds order value if not already set in query.
     *
     * @param \Cake\Event\Event $event The beforeFind event that was fired.
     * @param \Cake\ORM\Query $query The query object.
     * @param \ArrayObject $options The options passed to the find method.
     * @return void
     */
    public function beforeFind(Event $event, Query $query, ArrayObject $options)
    {
        if (!$query->clause('order')) {
            $query->order([$this->_table->alias() . '.' . $this->_config['order'] => 'ASC']);
        }
    }

    /**
     * Sets entity's order and updates order of other records when necessary.
     *
     * @param \Cake\Event\Event $event The beforeSave event that was fired.
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved.
     * @param \ArrayObject $options The options passed to the save method.
     * @return void
     */
    public function beforeSave(Event $event, Entity $entity, ArrayObject $options)
    {
        $config = $this->config();

        $newOrder = null;
        $newScope = [];

        // If scope are specified and data for all scope fields is not
        // provided we cannot calculate new order
        if ($config['scope']) {
            $newScope = $entity->extract($config['scope']);
            if (count($newScope) !== count($config['scope'])) {
                return;
            }
        }

        $orderField = $config['order'];
        $newOrder = $entity->get($orderField);

        // Adding
        if ($entity->isNew()) {
            // Order not specified
            if ($newOrder === null) {
                // Insert at end of list
                $entity->set($orderField, $this->_getHighestOrder($newScope) + 1);
                // Order specified
            } else {
                // Increment order of records it's inserted before
                $this->_sync(
                    [$orderField => $this->_table->query()->newExpr()->add("$orderField + 1")],
                    [$orderField . ' >=' => $newOrder],
                    $newScope
                );
            }

            // Editing
        } else {
            // No action if no new order or scope specified
            if ($newOrder === null && !$newScope) {
                return;
            }

            list($oldOrder, $oldScope) = $this->_getOldValues($entity);

            // No action if new and old scope and order same
            if ($newOrder == $oldOrder &&
                $newScope == $oldScope
            ) {
                return;
            }

            // If changing scope
            if ($newScope && $newScope != $oldScope) {
                // Decrement records in old scope with higher order than moved record old order
                $this->_sync(
                    [$orderField => $this->_table->query()->newExpr()->add("$orderField - 1")],
                    [$orderField . ' >' => $oldOrder],
                    $oldScope
                );

                // Order not specified
                if ($newOrder === null) {
                    // Insert at end of new scope
                    $entity->set(
                        $orderField,
                        $this->_getHighestOrder($newScope) + 1
                    );

                    // Order specified
                } else {
                    // Increment records in new scope with higher order than moved record new order
                    $this->_sync(
                        [$orderField => $this->_table->query()->newExpr()->add("$orderField + 1")],
                        [$orderField . ' >=' => $newOrder],
                        $newScope
                    );
                }
                // Same scope
            } else {
                // If moving up
                if ($newOrder < $oldOrder) {
                    // Increment order of those in between
                    $this->_sync(
                        [$orderField => $this->_table->query()->newExpr()->add("$orderField + 1")],
                        [
                            $orderField . ' >=' => $newOrder,
                            $orderField . ' <' => $oldOrder
                        ],
                        $newScope
                    );

                    // Moving down
                } else {
                    // Decrement order of those in between
                    $this->_sync(
                        [$orderField => $this->_table->query()->newExpr()->add("$orderField - 1")],
                        [
                            $orderField . ' >' => $oldOrder,
                            $orderField . ' <=' => $newOrder
                        ],
                        $newScope
                    );
                }
            }
        }
    }

    /**
     * When you delete a record from a set, you need to decrement the order of all
     * records that were after it in the set.
     *
     * @param \Cake\Event\Event $event The beforeDelete event that was fired.
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved.
     * @return void
     */
    public function beforeDelete(Event $event, Entity $entity)
    {
        $orderField = $this->_config['order'];
        list($oldOrder, $oldScope) = $this->_getOldValues($entity);

        $this->_sync(
            [$orderField => $this->_table->query()->newExpr()->add("$orderField - 1")],
            [$orderField . ' >' => $oldOrder],
            $oldScope
        );
    }

    /**
     * Set order for list of records provided.
     *
     * @param array $data Data.
     * @return bool
     */
    public function setOrder($data)
    {
        $config = $this->config();
        $table = $this->_table;

        $order = $this->_config['start'];
        foreach ($data as $key => $record) {
            $data[$key][$this->_config['order']] = $order++;
        }

        $table->removeBehavior('Sequence');

        $return = $table->connection()->transactional(function ($connection) use ($table, $data) {
            $return = true;
            $entities = $table->newEntities($data);
            $return = true;

            foreach ($entities as $entity) {
                $entity->isNew(false);
                $r = $table->save($entity, ['atomic' => false, 'validate' => false]);
                if ($r == false) {
                    return false;
                }
            }
        });

        $table->addBehavior('Sequence.Sequence', $config);
        return $return;
    }

    /**
     * Get old order and scope values.
     *
     * @param \Cake\ORM\Entity $entity Entity.
     * @return array
     */
    protected function _getOldValues(Entity $entity)
    {
        $config = $this->config();

        $oldRecord = $this->_table->get($entity->get(
            $this->_table->primaryKey(),
            [
                'fields' => array_merge($config['scope'], [$config['order']]),
                'limit' => 1
            ]
        ));
        $oldRecord = $oldRecord->toArray();

        $oldOrder = $oldRecord[$config['order']];
        $oldScope = array_intersect_key($oldRecord, array_flip($config['scope']));
        return [$oldOrder, $oldScope];
    }

    /**
     * Returns the current highest order of all records in the set. When a new
     * record is added to the set, it is added at the current highest order, plus
     * one.
     *
     * @param array $scope Array with scope field => scope values, used for conditions.
     * @return int Value of order field of last record in set
     */
    protected function _getHighestOrder(array $scope = [])
    {
        $orderField = $this->_config['order'];

        // Find the last record in the set
        $last = $this->_table->find()
            ->where($scope)
            ->order([$orderField => 'DESC'])
            ->limit(1)
            ->hydrate(false)
            ->first();

        // If there is a last record (i.e. any) in the set, return the it's order
        if ($last) {
            return $last[$orderField];
        }

        // If there isn't any records in the set, return the start number minus 1
        return ((int)$this->_config['start'] - 1);
    }

    /**
     * Auxiliary function used to alter the value of order fields by a certain
     * amount that match the passed conditions.
     *
     * @param array $fields Fields to update.
     * @param array $conditions Conditions for matching rows.
     * @param array $scope Grouping scope that will be added to coditions.
     * @return int Count of rows updated.
     */
    protected function _sync($fields, $conditions, $scope = null)
    {
        if ($scope) {
            $conditions = array_merge($conditions, $scope);
        }
        return $this->_table->updateAll($fields, $conditions);
    }
}
