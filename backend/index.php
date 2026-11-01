<?php
// Załączenie głównego pliku frameworka PRADO 3.3.3 z folderu vendor
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/vendor/pradosoft/prado/framework/Prado.php';

// Utworzenie i uruchomienie aplikacji
$application = new \Prado\TApplication('protected');
$application->run();