<?php
namespace ADmad\Sequence\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UniqueItemsFixture extends TestFixture
{
    public array $records = [
        ['name' => 'Item A', 'position' => 0],
        ['name' => 'Item B', 'position' => 1],
        ['name' => 'Item C', 'position' => 2],
        ['name' => 'Item D', 'position' => 3],
        ['name' => 'Item E', 'position' => 4],
    ];
}
