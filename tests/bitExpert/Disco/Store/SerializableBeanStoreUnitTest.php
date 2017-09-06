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

namespace bitExpert\Disco\Store;

use bitExpert\Disco\Helper\MasterService;
use bitExpert\Disco\Helper\SampleService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@link \bitExpert\Disco\Store\SerializableBeanStoreUnitTest}.
 */
class SerializableBeanStoreUnitTest extends TestCase
{
    /**
     * @var SerializableBeanStore
     */
    private $beanStore;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->beanStore = new SerializableBeanStore();
    }

    /**
     * @test
     * @dataProvider beanProvider
     */
    public function addingAndRetrievingBeansSucceeds($bean)
    {
        $this->beanStore->add('bean', $bean);
        $beanFromStore = $this->beanStore->get('bean');

        self::assertSame($bean, $beanFromStore);
    }

    /**
     * @test
     */
    public function addingBeanWithSameBeanIdMultipleTimeWillNotTriggerError()
    {
        $service = new SampleService();
        $bean = new MasterService($service);

        $this->beanStore->add('bean', $service);
        $this->beanStore->add('bean', $bean);
        $beanFromStore = $this->beanStore->get('bean');

        self::assertSame($bean, $beanFromStore);
    }

    /**
     * @test
     */
    public function gettingNonExistentBeanWillThrowException()
    {
        self::expectException(InvalidArgumentException::class);

        $this->beanStore->get('some-random-bean-instance');
    }

    /**
     * @test
     * @dataProvider beanProvider
     */
    public function beanStoreCanBeSerialized($bean)
    {
        $this->beanStore->add('bean', $bean);

        $this->beanStore = serialize($this->beanStore);
        $this->beanStore = unserialize($this->beanStore);

        $beanFromStore = $this->beanStore->get('bean');
        self::assertEquals($bean, $beanFromStore);
    }

    public function beanProvider()
    {
        return [
            [new SampleService()],
            [1],
            [1.23],
            [false],
            ['some string']
        ];
    }
}
