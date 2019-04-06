<?php
declare(strict_types=1);
namespace TestApp\Model\Table;

use Cake\ORM\Table;
use TestApp\Model\Entity\Item;

class ItemsTable extends Table
{
    protected $_entityClass = Item::class;

    public function initialize(array $config): void
    {
        $this->addBehavior('ADmad/Sequence.Sequence', ['startAt' => 0]);
    }
}
