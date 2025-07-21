--TEST--
Configured deprecation triggers are filtered when displaying deprecation details in process isolation
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/deprecation-in-data-provider/phpunit.xml';
$_SERVER['argv'][] = '--display-deprecations';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

D..                                                                 3 / 3 (100%)

Time: 00:00.001, Memory: 16.00 MB

1 test triggered 1 deprecation:

1) %s/deprecation-in-data-provider/tests/TriggersDeprecationInDataProviderTest.php:%d
%s

OK, but there were issues!
Tests: 3, Assertions: 3, Deprecations: 1.
