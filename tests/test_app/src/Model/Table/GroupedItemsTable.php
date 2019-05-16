<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class GroupedItemsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->addBehavior('ADmad/Sequence.Sequence', [
            'startAt' => 0,
            'scope' => 'group_field',
        ]);
    }
}
