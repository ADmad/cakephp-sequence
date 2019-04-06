<?php
declare(strict_types=1);
namespace TestApp\Model\Table;

use Cake\ORM\Table;

class KeywordItemsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->addBehavior('ADmad/Sequence.Sequence', [
            'order' => 'order',
            'start' => 0,
        ]);
    }
}
