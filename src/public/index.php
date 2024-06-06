<?php

require_once(dirname(__DIR__, 2) . '/vendor/autoload_runtime.php');

return function (array $context) {
    $kernelClass = $_ENV['KERNEL_CLASS'];
    return new $kernelClass($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};