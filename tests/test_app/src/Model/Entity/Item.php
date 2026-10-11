<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

class Item extends Entity
{
    protected array $patchable = ['*' => true, 'id' => false];

    protected array $virtual = ['virutal_name'];
}
