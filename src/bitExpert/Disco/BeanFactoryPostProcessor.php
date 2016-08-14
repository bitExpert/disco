<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace bitExpert\Disco;

/**
 * The {@link \bitExpert\Disco\BeanFactoryPostProcessor} is an beanFactory hook allowing you to
 * inject the {@link \bitExpert\Disco\BeanFactory} instance into classes implementing the
 * {@link \bitExpert\Disco\BeanFactoryAware} interface.
 */
class BeanFactoryPostProcessor implements BeanPostProcessor
{
    /**
     * {@inheritdoc}
     */
    public function postProcess($bean, $beanName)
    {
        if ($bean instanceof BeanFactoryAware) {
            $beanFactory = BeanFactoryRegistry::getInstance();
            if ($beanFactory instanceof BeanFactory) {
                $bean->setBeanFactory($beanFactory);
            }
        }
    }
}
