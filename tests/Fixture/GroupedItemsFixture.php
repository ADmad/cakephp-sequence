<?php
namespace ADmad\Sequence\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class GroupedItemsFixture extends TestFixture
{
    public array $records = [
        ['name' => 'Group 1 Item A', 'group_field' => 1, 'position' => 0],
        ['name' => 'Group 1 Item B', 'group_field' => 1, 'position' => 1],
        ['name' => 'Group 1 Item C', 'group_field' => 1, 'position' => 2],
        ['name' => 'Group 1 Item D', 'group_field' => 1, 'position' => 3],
        ['name' => 'Group 1 Item E', 'group_field' => 1, 'position' => 4],
        ['name' => 'Group 2 Item A', 'group_field' => 2, 'position' => 0],
        ['name' => 'Group 2 Item B', 'group_field' => 2, 'position' => 1],
        ['name' => 'Group 2 Item C', 'group_field' => 2, 'position' => 2],
        ['name' => 'Group 2 Item D', 'group_field' => 2, 'position' => 3],
        ['name' => 'Group 2 Item E', 'group_field' => 2, 'position' => 4],
        ['name' => 'Group 3 Item A', 'group_field' => 3, 'position' => 0],
        ['name' => 'Group 3 Item B', 'group_field' => 3, 'position' => 1],
        ['name' => 'Group 3 Item C', 'group_field' => 3, 'position' => 2],
        ['name' => 'Group 3 Item D', 'group_field' => 3, 'position' => 3],
        ['name' => 'Group 3 Item E', 'group_field' => 3, 'position' => 4],
    ];
}
