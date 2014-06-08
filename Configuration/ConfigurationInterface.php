<?php

/*
 * This file is part of the WobbleCodeRestBundle package.
 *
 * (c) WobbleCode <http://www.wobblecode.com/>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace WobbleCode\RestBundle\Configuration;

interface ConfigurationInterface
{
    /**
     * Returns the alias name for an annotated configuration.
     *
     * @return string
     */
    public function getAliasName();

    /**
     * Returns whether multiple annotations of this type are allowed
     *
     * @return Boolean
     */
    public function allowArray();
}
