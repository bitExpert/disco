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
    public function setUp(): void
    {
        parent::setUp();

        $this->beanStore = new SerializableBeanStore();
    }

    /**
     * @test
     * @dataProvider beanProvider
     * @param mixed $bean
     */
    public function addingAndRetrievingBeansSucceeds(mixed $bean): void
    {
        $this->beanStore->add('bean', $bean);
        $beanFromStore = $this->beanStore->get('bean');

        self::assertSame($bean, $beanFromStore);
    }

    /**
     * @test
     */
    public function addingBeanWithSameBeanIdMultipleTimeWillNotTriggerError(): void
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
    public function gettingNonExistentBeanWillThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->beanStore->get('some-random-bean-instance');
    }

    /**
     * @test
     * @dataProvider beanProvider
     * @param mixed $bean
     */
    public function beanStoreCanBeSerialized(mixed $bean): void
    {
        $this->beanStore->add('bean', $bean);

        $beanStore       = serialize($this->beanStore);
        $this->beanStore = unserialize($beanStore);

        $beanFromStore = $this->beanStore->get('bean');
        self::assertEquals($bean, $beanFromStore);
    }

    /**
     * @return array<mixed>
     */
    public function beanProvider(): array
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
