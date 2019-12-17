<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class UniqueItemsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->addBehavior('ADmad/Sequence.Sequence', ['start' => 0]);
    }
}
