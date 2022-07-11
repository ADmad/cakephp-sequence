<?php
declare(strict_types=1);

return [
    [
        'table' => 'grouped_items',
        'columns' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string', 'null' => true],
            'group_field' => ['type' => 'integer', 'null' => true],
            'position' => ['type' => 'integer', 'null' => true],
        ],
        'constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ],
    [
        'table' => 'items',
        'columns' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string', 'null' => true],
            'position' => ['type' => 'integer', 'null' => true],
        ],
        'constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ],
    [
        'table' => 'keyword_items',
        'columns' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string', 'null' => true],
            'order' => ['type' => 'integer', 'null' => true],
        ],
        'constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ],
    [
        'table' => 'unique_items',
        'columns' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string', 'null' => true],
            'position' => ['type' => 'integer', 'null' => true],
        ],
        'constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'position' => ['type' => 'unique', 'columns' => ['position']],
        ],
    ],
];
