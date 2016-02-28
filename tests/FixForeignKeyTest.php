<?php namespace Znck\Tests\Plug\Eloquent;

use GrahamCampbell\TestBench\AbstractTestCase;
use Znck\Plug\Eloquent\Traits\FixForeignKey;

class FixForeignKeyTest extends AbstractTestCase
{

    public function test_it_works()
    {
        $fixer = $this->get();

        $fixer->expects($this->once())->method('getTable')->with()->willReturn('users');

        $this->assertEquals('user_id', $fixer->getForeignKey());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FixForeignKey
     */
    protected function get()
    {
        return $this->getMockBuilder(FixForeignKey::class)->setMethods(['getTable'])->getMockForTrait();
    }
}
