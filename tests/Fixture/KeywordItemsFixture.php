<?php
namespace Sequence\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class KeywordItemsFixture extends TestFixture
{

    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string', 'null' => true],
        'order' => ['type' => 'integer', 'null' => true],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['name' => 'Item A', 'order' => 0],
        ['name' => 'Item B', 'order' => 1],
        ['name' => 'Item C', 'order' => 2],
        ['name' => 'Item D', 'order' => 3],
        ['name' => 'Item E', 'order' => 4]
    ];
}
