<?php namespace Znck\Tests\Plug\Eloquent;

use GrahamCampbell\TestBench\AbstractTestCase;
use Znck\Plug\Eloquent\Traits\SelfDecorating;

class SelfDecoratingTest extends AbstractTestCase
{
    public function test_it_decorates()
    {
        $decorating = $this->getMockBuilder(SelfDecorating::class)->getMockForTrait();

        $decorating->expects($this->once())->method('getDecorations');

        $decorating->setAttribute('name', 'rahul kadyan');
    }
}
