<?php

/*
 * This file is part of the Techno package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace bitExpert\Techno;

/**
 * Callback interface for an initialized bean. This method is the last method
 * being called during bean instantiation process. It allows the bean to check,
 * if it is configured correctly.
 *
 * @api
 */
interface InitializedBean
{
    /**
     * Callback method to check bean configuration.
     *
     * @throws \bitExpert\Techno\BeanException
     */
    public function postInitialization();
}
