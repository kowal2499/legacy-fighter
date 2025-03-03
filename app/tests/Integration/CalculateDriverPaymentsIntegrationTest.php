<?php

namespace LegacyFighter\Cabs\Tests\Integration;

use LegacyFighter\Cabs\Entity\Driver;
use LegacyFighter\Cabs\Entity\DriverFee;
use LegacyFighter\Cabs\Entity\Transit;
use LegacyFighter\Cabs\Repository\DriverFeeRepository;
use LegacyFighter\Cabs\Repository\TransitRepository;
use LegacyFighter\Cabs\Service\DriverService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CalculateDriverPaymentsIntegrationTest extends KernelTestCase
{
    private DriverService $driverService;
    private TransitRepository $transitRepository;
    private DriverFeeRepository $feeRepository;

    protected function setUp(): void
    {
        $this->driverService = $this->getContainer()->get(DriverService::class);
        $this->transitRepository = $this->getContainer()->get(TransitRepository::class);
        $this->feeRepository = $this->getContainer()->get(DriverFeeRepository::class);
    }

    /** @test */
    public function calculateMonthlyPayment(): void
    {
        //given
        $driver = $this->aDriver();
        //and
        $this->aTransit($driver, 60, new \DateTimeImmutable('2000-10-01 06:30'));
        $this->aTransit($driver, 70, new \DateTimeImmutable('2000-10-10 02:30'));
        $this->aTransit($driver, 80, new \DateTimeImmutable('2000-10-30 06:30'));
        $this->aTransit($driver, 60, new \DateTimeImmutable('2000-11-10 01:30'));
        $this->aTransit($driver, 30, new \DateTimeImmutable('2000-11-10 01:30'));
        $this->aTransit($driver, 15, new \DateTimeImmutable('2000-12-10 02:30'));

        //and
        $this->driverHasFee($driver, DriverFee::TYPE_FLAT, 10);

        //when
        $feeOctober = $this->driverService->calculateDriverMonthlyPayment($driver->getId(), 2000, 10);
        //then
        self::assertEquals(180, $feeOctober);

        //when
        $feeNovember = $this->driverService->calculateDriverMonthlyPayment($driver->getId(), 2000, 11);
        //then
        self::assertEquals(70, $feeNovember);

        //when
        $feeDecember = $this->driverService->calculateDriverMonthlyPayment($driver->getId(), 2000, 12);
        //then
        self::assertEquals(5, $feeDecember);
    }

    /** @test */
    public function calculateYearlyPayment(): void
    {
        //given
        $driver = $this->aDriver();
        //and
        $this->aTransit($driver, 60, new \DateTimeImmutable('2000-10-01 06:30'));
        $this->aTransit($driver, 70, new \DateTimeImmutable('2000-10-10 02:30'));
        $this->aTransit($driver, 80, new \DateTimeImmutable('2000-10-30 06:30'));
        $this->aTransit($driver, 60, new \DateTimeImmutable('2000-11-10 01:30'));
        $this->aTransit($driver, 30, new \DateTimeImmutable('2000-11-10 01:30'));
        $this->aTransit($driver, 15, new \DateTimeImmutable('2000-12-10 02:30'));

        //and
        $this->driverHasFee($driver, DriverFee::TYPE_FLAT, 10);

        //when
        $payments = $this->driverService->calculateDriverYearlyPayment($driver->getId(), 2000);

        //then
        self::assertEquals(0, $payments[1]);
        self::assertEquals(0, $payments[2]);
        self::assertEquals(0, $payments[3]);
        self::assertEquals(0, $payments[4]);
        self::assertEquals(0, $payments[5]);
        self::assertEquals(0, $payments[6]);
        self::assertEquals(0, $payments[7]);
        self::assertEquals(0, $payments[8]);
        self::assertEquals(0, $payments[9]);
        self::assertEquals(180, $payments[10]);
        self::assertEquals(70, $payments[11]);
        self::assertEquals(5, $payments[12]);
    }

    private function aDriver(): Driver
    {
        return $this->driverService->createDriver('FARME100165AB5EW', 'Kowalski', 'Janusz', Driver::TYPE_REGULAR, Driver::STATUS_ACTIVE, '');
    }

    private function driverHasFeeWithMin(Driver $driver, string $feeType, int $amount, int $min): DriverFee
    {
        $driverFee = new DriverFee($feeType, $driver, $amount, $min);
        return $this->feeRepository->save($driverFee);
    }

    private function driverHasFee(Driver $driver, string $feeType, int $amount): DriverFee
    {
        return $this->driverHasFeeWithMin($driver, $feeType, $amount, 0);
    }

    private function aTransit(Driver $driver, int $price, \DateTimeImmutable $when): Transit
    {
        $transit = new Transit();
        $transit->setStatus(Transit::STATUS_DRAFT);
        $transit->setPrice($price);
        $transit->setDriver($driver);
        $transit->setDateTime($when);
        return $this->transitRepository->save($transit);
    }
}