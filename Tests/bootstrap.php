<?php

/*
 * This file is part of the WobbleCodeRestBundle package.
 *
 * (c) WobbleCode <http://www.wobblecode.com/>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

use Doctrine\Common\Annotations\AnnotationRegistry;

call_user_func(function () {

    if (is_file($autoloadFile = __DIR__.'/../vendor/autoload.php') == false) {
        throw new \RuntimeException('Did not find vendor/autoload.php. Did you run "composer install --dev"?');
    }

    require_once $autoloadFile;

    AnnotationRegistry::registerLoader('class_exists');
});
