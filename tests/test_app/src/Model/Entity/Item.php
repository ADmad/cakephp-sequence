<?php
declare(strict_types=1);
namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

class Item extends Entity
{
    protected $_accessible = ['*' => true, 'id' => false];

    protected $_virtual = ['virutal_name'];
}
