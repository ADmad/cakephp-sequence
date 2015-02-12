<?php
namespace Sequence\Test\TestCase\Model\Behavior;

use Cake\ORM\Table as CakeTable;

/**
 * Test Table class
 *
 * The sole purpose of this class is to hijack behavior loading to substitude
 * The translate behavior with the shadow translate behavior. This allows the
 * core test case to be used to verify as close as possible that the shadow
 * translate behavior is functionally equivalent to the core behavior.
 */
class Table extends CakeTable
{
    public function addBehavior($name, array $options = [])
    {
        if ($name === 'Translate') {
            $name = 'ShadowTranslate.ShadowTranslate';
        }
        $this->_behaviors->load($name, $options);
    }
}
