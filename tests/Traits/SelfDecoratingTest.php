<?php namespace Znck\Tests\Plug\Eloquent\Traits;

use GrahamCampbell\TestBench\AbstractTestCase;
use Znck\Plug\Eloquent\Core\DecoratorFactory;
use Znck\Plug\Eloquent\Traits\SelfDecorating;

class SelfDecoratingTest extends AbstractTestCase
{
    public function test_it_decorates()
    {
        $decorating = $this->getMockBuilder(SelfDecorating::class)->getMockForTrait();

        $this->assertEquals('rahul kadyan', $decorating->decorateAttribute('name', 'rahul kadyan'));
    }

    public function test_it_can()
    {
        $decorating = $this->getMockBuilder(SelfDecorating::class)->getMockForTrait();

        $reflection = (new \ReflectionClass($decorating));
        $property = $reflection->getProperty('decorations');
        $property->setAccessible(true);
        $property->setValue($decorating, ['name' => 'name|name']);

        $decorator = $reflection->getProperty('decorator');
        $decorator->setAccessible(true);
        $decorator->setValue($decorating, new DecoratorFactory());

        $this->assertEquals('Rahul Kadyan', $decorating->decorateAttribute('name', 'rahul kadyan'));
    }

}
