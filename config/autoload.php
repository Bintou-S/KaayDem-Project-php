<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = ROOT_PATH . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

foreach (glob(ROOT_PATH . '/app/Core/*.php') as $file) {
    require_once $file;
}
