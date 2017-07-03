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

namespace bitExpert\Techno\Store;

use bitExpert\Techno\Helper\MasterService;
use bitExpert\Techno\Helper\SampleService;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for {@link \bitExpert\Techno\Store\SerializableBeanStoreUnitTest}.
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
     * @expectedException InvalidArgumentException
     */
    public function gettingNonExistentBeanWillThrowException()
    {
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
