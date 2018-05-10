<?php
declare(strict_types=1);

namespace Acme\Test\Account\Domain\Models;

use Acme\Account\Domain\Models\Balance;
use PHPUnit\Framework\TestCase;

final class BalanceTest extends TestCase
{
    /**
     * @test
     */
    public function of()
    {
        $sut = Balance::of(0);
        $this->assertSame(0, $sut->asInt());
    }

    /**
     * @test
     * @expectedException \Acme\Account\Domain\Exceptions\InvariantException
     */
    public function of_with_invalid_value()
    {
        Balance::of(-1);
    }
}
