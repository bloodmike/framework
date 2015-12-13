<?php
/**
 * Точка входа для запуска консольных команд
 *
 * @author mkoshkin
 */

use Framework\Service\Container;
use Framework\Command\Console\ConsoleCommandExecutor;

chdir(__DIR__);
require_once('./bootstrap.php');

$Container = new Container('', [], []);

$ConsoleCommandExecutor = new ConsoleCommandExecutor($Container);
$ConsoleCommandExecutor->execute($argv);
