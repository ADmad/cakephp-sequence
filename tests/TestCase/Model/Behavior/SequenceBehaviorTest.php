<?php
namespace ADmad\Sequence\Test\TestCase\Model\Behavior;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class Item extends Entity
{
    protected $_accessible = ['*' => true, 'id' => false];

    protected $_virtual = ['virutal_name'];
}

class Items extends Table
{
    public function initialize(array $config)
    {
        $this->entityClass('ADmad\Sequence\Test\TestCase\Model\Behavior\Item');
        $this->addBehavior('ADmad/Sequence.Sequence', ['start' => 0]);
    }
}

class GroupedItems extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('ADmad/Sequence.Sequence', [
            'start' => 0,
            'scope' => 'group_field',
        ]);
    }
}

class KeywordItems extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('ADmad/Sequence.Sequence', [
            'order' => 'order',
            'start' => 0,
        ]);
    }
}

class SequenceTest extends TestCase
{
    public $fixtures = [
        'plugin.ADmad/Sequence.Items',
        'plugin.ADmad/Sequence.GroupedItems',
        'plugin.ADmad/Sequence.KeywordItems',
    ];

    /**
     * [testSave description].
     *
     * @return void
     */
    public function testSave()
    {
        $Items = TableRegistry::get('Items', [
            'className' => 'ADmad\Sequence\Test\TestCase\Model\Behavior\Items',
        ]);

        // Testing saving a new record (order not specified) sets order to highest + 1
        $entity = $Items->newEntity(['name' => 'Item F']);
        $entity = $Items->save($entity);
        $this->assertOrder([1, 2, 3, 4, 5, 6], $Items);

        // Test saving new record with order specified
        $entity = $Items->newEntity(['name' => 'Item G', 'position' => 3]);
        $entity = $Items->save($entity);
        $this->assertOrder([1, 2, 3, 7, 4, 5, 6], $Items);

        // Test editing record with new order - move up
        $entity = $Items->get(4);
        $entity->set('position', 1);
        $entity = $Items->save($entity);
        $this->assertOrder([1, 4, 2, 3, 7, 5, 6], $Items);

        // Test editing record with new order - move down
        $entity = $Items->get(2);
        $entity->set('position', 6);
        $entity = $Items->save($entity);
        $this->assertOrder([1, 4, 3, 7, 5, 6, 2], $Items);
    }

    /**
     * [testSaveScoped description].
     *
     * @return void
     */
    public function testSaveScoped()
    {
        $GroupedItems = TableRegistry::get('GroupedItems', [
            'table' => 'grouped_items',
            'alias' => 'GroupedItems',
            'className' => 'ADmad\Sequence\Test\TestCase\Model\Behavior\GroupedItems',
        ]);

        // Testing saving a new record (order not specified) sets order to highest + 1
        $entity = $GroupedItems->newEntity([
            'name' => 'Item F',
            'group_field' => 2,
        ]);
        $entity = $GroupedItems->save($entity);
        $this->assertOrder([6, 7, 8, 9, 10, (int)$entity->id], $GroupedItems, ['group_field' => 2]);
        $this->assertOrder([1, 2, 3, 4, 5], $GroupedItems, ['group_field' => 1]);
        $this->assertOrder([11, 12, 13, 14, 15], $GroupedItems, ['group_field' => 3]);

        // Test saving new record with order specified
        $entity = $GroupedItems->newEntity([
            'name' => 'Item G',
            'group_field' => 2,
            'position' => 3,
        ]);
        $entity = $GroupedItems->save($entity);
        $this->assertOrder([6, 7, 8, 17, 9, 10, 16], $GroupedItems, ['group_field' => 2]);
        $this->assertOrder([1, 2, 3, 4, 5], $GroupedItems, ['group_field' => 1]);
        $this->assertOrder([11, 12, 13, 14, 15], $GroupedItems, ['group_field' => 3]);

        // Test editing record with new order
        $entity = $GroupedItems->get(4);
        $entity->position = 2;
        $entity = $GroupedItems->save($entity);
        $this->assertOrder([1, 2, 4, 3, 5], $GroupedItems, ['group_field' => 1]);
        $this->assertOrder([6, 7, 8, 17, 9, 10, 16], $GroupedItems, ['group_field' => 2]);
        $this->assertOrder([11, 12, 13, 14, 15], $GroupedItems, ['group_field' => 3]);

        // Test changing group
        $entity = $GroupedItems->get(2);
        $entity->set('group_field', 2);
        $entity = $GroupedItems->save($entity);
        $this->assertOrder([1, 4, 3, 5], $GroupedItems, ['group_field' => 1]);
        $this->assertOrder([6, 2, 7, 8, 17, 9, 10, 16], $GroupedItems, ['group_field' => 2]);
        $this->assertOrder([11, 12, 13, 14, 15], $GroupedItems, ['group_field' => 3]);
    }

    /**
     * [testSaveNullScoped description].
     *
     * @return void
     */
    public function testSaveNullScoped()
    {
        $GroupedItems = TableRegistry::get('GroupedItems', [
            'table' => 'grouped_items',
            'alias' => 'GroupedItems',
            'className' => 'ADmad\Sequence\Test\TestCase\Model\Behavior\GroupedItems',
        ]);

        // Test group 2 (group_field = 2) as group NULL (group_field = null)
        $GroupedItems->updateAll(['group_field' => null], ['group_field' => 2]);

        // Testing saving a new record (order not specified) sets order to highest + 1
        $entity = $GroupedItems->newEntity([
            'name' => 'Item F',
            'group_field' => null,
        ]);
        $entity = $GroupedItems->save($entity);
        $this->assertOrder([6, 7, 8, 9, 10, (int)$entity->id], $GroupedItems, ['group_field IS' => null]);
        $this->assertOrder([1, 2, 3, 4, 5], $GroupedItems, ['group_field' => 1]);
        $this->assertOrder([11, 12, 13, 14, 15], $GroupedItems, ['group_field' => 3]);

        // Test saving new record with order specified
        $entity = $GroupedItems->newEntity([
            'name' => 'Item G',
            'group_field' => null,
            'position' => 3,
        ]);
        $entity = $GroupedItems->save($entity);
        $this->assertOrder([6, 7, 8, 17, 9, 10, 16], $GroupedItems, ['group_field IS' => null]);
        $this->assertOrder([1, 2, 3, 4, 5], $GroupedItems, ['group_field' => 1]);
        $this->assertOrder([11, 12, 13, 14, 15], $GroupedItems, ['group_field' => 3]);

        // Test editing record with new order
        $entity = $GroupedItems->get(4);
        $entity->position = 2;
        $entity = $GroupedItems->save($entity);
        $this->assertOrder([1, 2, 4, 3, 5], $GroupedItems, ['group_field' => 1]);
        $this->assertOrder([6, 7, 8, 17, 9, 10, 16], $GroupedItems, ['group_field IS' => null]);
        $this->assertOrder([11, 12, 13, 14, 15], $GroupedItems, ['group_field' => 3]);

        // Test changing group
        $entity = $GroupedItems->get(2);
        $entity->set('group_field', null);
        $entity = $GroupedItems->save($entity);
        $this->assertOrder([1, 4, 3, 5], $GroupedItems, ['group_field' => 1]);
        $this->assertOrder([6, 2, 7, 8, 17, 9, 10, 16], $GroupedItems, ['group_field IS' => null]);
        $this->assertOrder([11, 12, 13, 14, 15], $GroupedItems, ['group_field' => 3]);
    }

    /**
     * [testSaveKeyword description].
     *
     * @return void
     */
    public function testSaveKeyword()
    {
        $KeywordItems = TableRegistry::get('KeywordItems', [
            'table' => 'keyword_items',
            'alias' => 'KeywordItems',
            'className' => 'ADmad\Sequence\Test\TestCase\Model\Behavior\KeywordItems',
        ]);

        // Testing saving a new record (order not specified) sets order to highest + 1
        $entity = $KeywordItems->newEntity(['name' => 'Item F']);
        $entity = $KeywordItems->save($entity);
        $this->assertOrder([1, 2, 3, 4, 5, 6], $KeywordItems);
    }

    /**
     * [testDelete description].
     *
     * @return void
     */
    public function testDelete()
    {
        $Items = TableRegistry::get('Items', [
            'className' => 'ADmad\Sequence\Test\TestCase\Model\Behavior\Items',
        ]);

        $entity = $Items->get(3);
        $Items->delete($entity);
        $this->assertOrder([1, 2, 4, 5], $Items);

        $entity = new Entity(['id' => 4]);
        $entity->isNew(false);
        $Items->delete($entity);
        $this->assertOrder([1, 2, 5], $Items);

        $GroupedItems = TableRegistry::get('GroupedItems', [
            'table' => 'grouped_items',
            'alias' => 'GroupedItems',
            'className' => 'ADmad\Sequence\Test\TestCase\Model\Behavior\GroupedItems',
        ]);

        $entity = $GroupedItems->get(3);
        $GroupedItems->delete($entity);
        $this->assertOrder([1, 2, 4, 5], $GroupedItems, ['group_field' => 1]);
        $this->assertOrder([6, 7, 8, 9, 10], $GroupedItems, ['group_field' => 2]);
        $this->assertOrder([11, 12, 13, 14, 15], $GroupedItems, ['group_field' => 3]);
    }

    /**
     * [testSetOrder description].
     *
     * @return void
     */
    public function testSetOrder()
    {
        $Items = TableRegistry::get('Items', [
            'className' => 'ADmad\Sequence\Test\TestCase\Model\Behavior\Items',
        ]);
        $Items->validator()->requirePresence('name');

        // Array of arrays
        $result = $Items->setOrder([
            ['id' => 4],
            ['id' => 3],
            ['id' => 2],
            ['id' => 1],
            ['id' => 5],
        ]);
        $this->assertTrue($result);
        $this->assertOrder([4, 3, 2, 1, 5], $Items);

        // Array of ids
        $result = $Items->setOrder([5, 4, 2, 1, 3]);
        $this->assertTrue($result);
        $this->assertOrder([5, 4, 2, 1, 3], $Items);

        $Items->validator()->requirePresence('name', false);

        // Array of entities
        $entities = $Items->newEntities(
            [
                ['id' => 4],
                ['id' => 3],
                ['id' => 2],
                ['id' => 1],
                ['id' => 5],
            ],
            ['accessibleFields' => ['id' => true]]
        );
        foreach ($entities as &$entity) {
            $entity->isNew(false);
        }
        $result = $Items->setOrder($entities);
        $this->assertTrue($result);
        $this->assertOrder([4, 3, 2, 1, 5], $Items);

        $GroupedItems = TableRegistry::get('GroupedItems', [
            'table' => 'grouped_items',
            'alias' => 'GroupedItems',
            'className' => 'ADmad\Sequence\Test\TestCase\Model\Behavior\GroupedItems',
        ]);

        $result = $GroupedItems->setOrder([
            ['id' => 4],
            ['id' => 3],
            ['id' => 2],
            ['id' => 1],
            ['id' => 5],
        ]);

        $this->assertTrue($result);
        $this->assertOrder([4, 3, 2, 1, 5], $GroupedItems, ['group_field' => 1]);
        $this->assertOrder([6, 7, 8, 9, 10], $GroupedItems, ['group_field' => 2]);
    }

    /**
     * testMoveUp
     *
     * @return void
     */
    public function testMoveUp()
    {
        $Items = TableRegistry::get('Items', [
            'className' => 'ADmad\Sequence\Test\TestCase\Model\Behavior\Items',
        ]);

        $entity = $Items->get(4);
        $result = $Items->moveUp($entity);

        $this->assertTrue($result);
        $this->assertOrder([1, 2, 4, 3, 5], $Items);

        // Move up entity already at first position
        $entity = $Items->get(1);
        $result = $Items->moveUp($entity);

        $this->assertTrue($result);
        $this->assertOrder([1, 2, 4, 3, 5], $Items);

        $GroupedItems = TableRegistry::get('GroupedItems', [
            'table' => 'grouped_items',
            'alias' => 'GroupedItems',
            'className' => 'ADmad\Sequence\Test\TestCase\Model\Behavior\GroupedItems',
        ]);

        $entity = $GroupedItems->get(4);
        $result = $GroupedItems->moveUp($entity);

        $this->assertTrue($result);
        $this->assertOrder([1, 2, 4, 3, 5], $GroupedItems, ['group_field' => 1]);

        // Move up entity already at first position
        $entity = $GroupedItems->get(1);
        $result = $GroupedItems->moveUp($entity);

        $this->assertTrue($result);
        $this->assertOrder([1, 2, 4, 3, 5], $GroupedItems, ['group_field' => 1]);
    }

    /**
     * moveDown
     *
     * @return void
     */
    public function testMoveDown()
    {
        $Items = TableRegistry::get('Items', [
            'className' => 'ADmad\Sequence\Test\TestCase\Model\Behavior\Items',
        ]);

        $entity = $Items->get(2);
        $result = $Items->moveDown($entity);

        $this->assertTrue($result);
        $this->assertOrder([1, 3, 2, 4, 5], $Items);

        // Move down entity already at last position
        $entity = $Items->get(5);
        $result = $Items->moveDown($entity);

        $this->assertTrue($result);
        $this->assertOrder([1, 3, 2, 4, 5], $Items);

        $GroupedItems = TableRegistry::get('GroupedItems', [
            'table' => 'grouped_items',
            'alias' => 'GroupedItems',
            'className' => 'ADmad\Sequence\Test\TestCase\Model\Behavior\GroupedItems',
        ]);

        $entity = $GroupedItems->get(2);
        $result = $GroupedItems->moveDown($entity);

        $this->assertTrue($result);
        $this->assertOrder([1, 3, 2, 4, 5], $GroupedItems, ['group_field' => 1]);

        // Move down entity already at last position
        $entity = $GroupedItems->get(5);
        $result = $GroupedItems->moveDown($entity);

        $this->assertTrue($result);
        $this->assertOrder([1, 3, 2, 4, 5], $GroupedItems, ['group_field' => 1]);
    }

    /**
     * [assertOrder description].
     *
     * @param array $expected
     * @param \Cake\ORM\Table $table
     * @param \Cake\ORM\Query $query
     *
     * @return bool
     */
    public function assertOrder($expected, $table, $query = null)
    {
        if (is_array($query)) {
            $conditions = $query;
            $query = null;
        } else {
            $conditions = [];
        }

        $order = $table->behaviors()->Sequence->config('order');

        $query = $query ?: $table->find();

        $records = $query->find('list', ['keyField' => $order, 'valueField' => 'id'])
            ->where($conditions)
            ->order([$order => 'ASC'])
            ->toArray();

        return $this->assertSame($expected, $records);
    }
}
