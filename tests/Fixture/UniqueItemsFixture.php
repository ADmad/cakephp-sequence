<?php
namespace ADmad\Sequence\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UniqueItemsFixture extends TestFixture
{
    /**
     * fields property.
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string', 'null' => true],
        'position' => ['type' => 'integer', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'position' => ['type' => 'unique', 'columns' => ['position']],
        ],
    ];

    /**
     * records property.
     *
     * @var array
     */
    public $records = [
        ['name' => 'Item A', 'position' => 0],
        ['name' => 'Item B', 'position' => 1],
        ['name' => 'Item C', 'position' => 2],
        ['name' => 'Item D', 'position' => 3],
        ['name' => 'Item E', 'position' => 4],
    ];
}
