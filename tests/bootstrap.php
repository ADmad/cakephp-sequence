<?php
declare(strict_types=1);

/*
 * Test suite bootstrap for Sequence
 */

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;

/*
 * This function is used to find the location of CakePHP whether CakePHP
 * has been installed as a dependency of the plugin, or the plugin is itself
 * installed as a dependency of an application.
 */
$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);

    throw new Exception('Cannot find the root of the application, unable to run tests');
};
$root = $findRoot(__FILE__);
unset($findRoot);

chdir($root);
if (file_exists($root . '/config/bootstrap.php')) {
    require $root . '/config/bootstrap.php';

    return;
}

require dirname(__DIR__) . '/vendor/cakephp/cakephp/tests/bootstrap.php';

ConnectionManager::get('test')->getDriver()->enableAutoQuoting(true);

Configure::write('Error.ignoredDeprecationPaths', [
    'src/TestSuite/Fixture/FixtureInjector.php',
]);
