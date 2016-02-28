<?php
namespace Znck\Tests\Plug\Eloquent;

use GrahamCampbell\TestBench\AbstractTestCase;
use Znck\Plug\Eloquent\Core\DecoratorFactory;
use Znck\Plug\Eloquent\Exceptions\UnknownDecoratorException;

class DecoratorFactoryTest extends AbstractTestCase
{

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DecoratorFactory
     */
    protected function get()
    {
        return $this->getMock(DecoratorFactory::class);
    }

    public function test_it_can_decorate()
    {
        $decorator = new DecoratorFactory();

        $this->assertTrue(method_exists($decorator, 'decorate'));
    }

    public function test_it_throws_for_unknown_decorator()
    {
        $decorator = new DecoratorFactory();

        $this->expectException(UnknownDecoratorException::class);

        $decorator->decorate('random_decorator_that_should_not_exist', 'val');
    }

    public function test_it_can_register_a_custom_decorator()
    {
        $decorator = new DecoratorFactory();

        $this->assertTrue(method_exists($decorator, 'register'));
    }

    public function test_it_can_use_custom_decorator()
    {
        $decorator = new DecoratorFactory();

        $decorator->register('trim', function ($value) {
            return trim($value);
        });
        $this->assertEquals('hello', $decorator->decorate('trim', ' hello '));
    }

    public function test_it_can_decorate_name()
    {
        $decorator = new DecoratorFactory();

        $this->assertEquals('Rahul Kadyan', $decorator->decorate('name', 'rahul kadyan'));
    }
}
