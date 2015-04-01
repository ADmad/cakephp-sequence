<?php
namespace Sequence\Test\TestCase\Model\Behavior;

use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Sequence\Model\Behavior\SequenceBehavior;

class Items extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Sequence.Sequence', ['start' => 0]);
    }
}

class GroupedItems extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Sequence.Sequence', [
            'start' => 0,
            'scope' => 'group_field'
        ]);
    }
}

class KeywordItems extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Sequence.Sequence', [
            'order' => 'order',
            'start' => 0,
        ]);
    }
}

class SequenceTest extends TestCase
{
    public $fixtures = ['plugin.Sequence.Items', 'plugin.Sequence.GroupedItems', 'plugin.Sequence.KeywordItems'];

    /**
     * [testSave description]
     *
     * @return void
     */
    public function testSave()
    {
        $Items = TableRegistry::get('Items', [
            'className' => 'Sequence\Test\TestCase\Model\Behavior\Items'
        ]);

        // Testing saving a new record (order not specified) sets order to highest + 1
        $entity = $Items->newEntity(['name' => 'Item F']);
        $entity = $Items->save($entity);
        $this->assertOrder([1, 2, 3, 4, 5, 6], $Items);

        // Test saving new record with order specified
        $entity = $Items->newEntity(['name' => 'Item G', 'position' => 3]);
        $entity = $Items->save($entity);
        $this->assertOrder([1, 2, 3, 7, 4, 5, 6], $Items);

        // Test editing record with new order
        $entity = $Items->get(4);
        $entity->set('position', 1);
        $entity = $Items->save($entity);
        $this->assertOrder([1, 4, 2, 3, 7, 5, 6], $Items);
    }

    /**
     * [testSaveScoped description]
     *
     * @return void
     */
    public function testSaveScoped()
    {
        $GroupedItems = TableRegistry::get('GroupedItems', [
            'table' => 'grouped_items',
            'alias' => 'GroupedItems',
            'className' => 'Sequence\Test\TestCase\Model\Behavior\GroupedItems'
        ]);

        // Testing saving a new record (order not specified) sets order to highest + 1
        $entity = $GroupedItems->newEntity([
            'name' => 'Item F',
            'group_field' => 2
        ]);
        $entity = $GroupedItems->save($entity);
        $this->assertOrder([6, 7, 8, 9, 10, (int)$entity->id], $GroupedItems, ['group_field' => 2]);
        $this->assertOrder([1, 2, 3, 4, 5], $GroupedItems, ['group_field' => 1]);
        $this->assertOrder([11, 12, 13, 14, 15], $GroupedItems, ['group_field' => 3]);

        // Test saving new record with order specified
        $entity = $GroupedItems->newEntity([
            'name' => 'Item G',
            'group_field' => 2,
            'position' => 3
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
     * [testSaveKeyword description]
     *
     * @return void
     */
    public function testSaveKeyword()
    {
        $KeywordItems = TableRegistry::get('KeywordItems', [
            'table' => 'keyword_items',
            'alias' => 'KeywordItems',
            'className' => 'Sequence\Test\TestCase\Model\Behavior\KeywordItems'
        ]);

        // Testing saving a new record (order not specified) sets order to highest + 1
        $entity = $KeywordItems->newEntity(['name' => 'Item F']);
        $entity = $KeywordItems->save($entity);
        $this->assertOrder([1, 2, 3, 4, 5, 6], $KeywordItems);
    }

    /**
     * [testDelete description]
     *
     * @return void
     */
    public function testDelete()
    {
        $Items = TableRegistry::get('Items', [
            'className' => 'Sequence\Test\TestCase\Model\Behavior\Items'
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
            'className' => 'Sequence\Test\TestCase\Model\Behavior\GroupedItems'
        ]);

        $entity = $GroupedItems->get(3);
        $GroupedItems->delete($entity);
        $this->assertOrder([1, 2, 4, 5], $GroupedItems, ['group_field' => 1]);
        $this->assertOrder([6, 7, 8, 9, 10], $GroupedItems, ['group_field' => 2]);
        $this->assertOrder([11, 12, 13, 14, 15], $GroupedItems, ['group_field' => 3]);
    }

    /**
     * [testSetOrder description]
     *
     * @return void
     */
    public function testSetOrder()
    {
        $Items = TableRegistry::get('Items', [
            'className' => 'Sequence\Test\TestCase\Model\Behavior\Items'
        ]);

        $result = $Items->setOrder([
            ['id' => 4],
            ['id' => 3],
            ['id' => 2],
            ['id' => 1],
            ['id' => 5],
        ]);
        $this->assertTrue($result);
        $this->assertOrder([4, 3, 2, 1, 5], $Items);

        $GroupedItems = TableRegistry::get('GroupedItems', [
            'table' => 'grouped_items',
            'alias' => 'GroupedItems',
            'className' => 'Sequence\Test\TestCase\Model\Behavior\GroupedItems'
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
     * [assertOrder description]
     *
     * @param array $expected
     * @param \Cake\ORM\Table $table
     * @param \Cake\ORM\Query $query
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
