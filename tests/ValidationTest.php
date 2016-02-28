<?php

namespace Znck\Tests\Plug\Eloquent;

use GrahamCampbell\TestBench\AbstractTestCase;
use Illuminate\Validation\Factory;
use Symfony\Component\Translation\Translator;
use Znck\Plug\Eloquent\Traits\SelfValidating;

class ValidationTest extends AbstractTestCase
{
    /**
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Znck\Plug\Eloquent\ValidationTrait
     */
    protected function prepareStub($methods = [])
    {
        $stub = $this->getMockBuilder(SelfValidating::class)->setMethods(array_unique(array_merge($methods, [
            'getAttributes',
            'fireValidationEvent',
            'getValidationFactory',
        ])))->getMockForTrait();

        $translator = new Translator('en');
        $factory = new Factory($translator);

        if (!array_has(array_flip($methods), 'fireValidationEvent')) {
            $stub->expects($this->atLeast(0))->method('fireValidationEvent')->willReturn(true);
        }

        if (!array_has(array_flip($methods), 'getAttributes')) {
            $stub->expects($this->atLeast(0))->method('getAttributes')->willReturn(['email' => 'foo@example.com']);
        }

        if (!array_has(array_flip($methods), 'getValidationFactory')) {
            $stub->expects($this->atLeast(0))->method('getValidationFactory')->willReturn($factory);
        }

        return $stub;
    }

    public function test_stub()
    {
        $stub = $this->prepareStub(['getAttributes']);

        $stub->expects($this->once())->method('getAttributes')->willReturn(['email' => 'foo@example.com']);

        $this->assertArrayHasKey('email', $stub->getAttributes());
    }

    public function test_it_has_no_rules()
    {
        $this->assertEmpty($this->prepareStub()->getValidationRules());
    }

    public function test_it_has_no_errors_before_validation()
    {
        $this->assertFalse($this->prepareStub()->hasErrors());
    }

    public function test_it_passes_with_correct_input()
    {
        $stub = $this->prepareStub(['getValidationRules']);

        $stub->expects($this->once())->method('getValidationRules')->willReturn(['email' => 'required|email']);

        $this->assertTrue($stub->validate());
    }

    public function test_it_fails_with_invalid_input()
    {
        $stub = $this->prepareStub(['getValidationRules']);

        $stub->expects($this->once())->method('getValidationRules')->willReturn(['name' => 'required']);

        $this->assertFalse($stub->validate());
    }

    public function test_it_fails_if_prevalidation_events_fails()
    {
        $stub = $this->prepareStub(['getValidationRules', 'fireValidationEvent']);

        $stub->expects($this->once())->method('fireValidationEvent')->willReturn(false);
        $stub->expects($this->never())->method('getValidationRules');

        $this->assertFalse($stub->validate());
    }

    public function test_it_has_no_error_if_validation_passes()
    {
        $stub = $this->prepareStub(['getValidationRules']);

        $stub->expects($this->once())->method('getValidationRules')->willReturn(['email' => 'required|email']);

        $stub->validate();

        $this->assertFalse($stub->hasErrors());
    }

    public function test_it_has_error_if_validation_fails()
    {
        $stub = $this->prepareStub(['getValidationRules']);

        $stub->expects($this->once())->method('getValidationRules')->willReturn(['name' => 'required']);

        $stub->validate();

        $this->assertTrue($stub->hasErrors());
    }

    public function test_it_has_error_if_prevalidation_events_fails()
    {
        $stub = $this->prepareStub(['getValidationRules', 'fireValidationEvent']);

        $stub->expects($this->once())->method('fireValidationEvent')->willReturn(false);
        $stub->expects($this->never())->method('getValidationRules');

        $stub->validate();

        $this->assertTrue($stub->hasErrors());
    }

    public function test_it_has_error_message_for_prevalidation()
    {
        $stub = $this->prepareStub(['getValidationRules', 'fireValidationEvent']);

        $stub->expects($this->once())->method('fireValidationEvent')->willReturn(false);
        $stub->expects($this->never())->method('getValidationRules');

        $stub->validate();

        $this->assertArrayHasKey('::validating', $stub->getErrors()->toArray());
    }

    public function test_it_has_error_if_postvalidation_events_fails()
    {
        $stub = $this->prepareStub(['getValidationRules', 'fireValidationEvent']);

        $stub->expects($this->exactly(2))->method('fireValidationEvent')->willReturn(true, false);
        $stub->expects($this->once())->method('getValidationRules')->willReturn([]);

        $stub->validate();

        $this->assertTrue($stub->hasErrors());
    }

    public function test_it_has_error_message_for_postvalidation()
    {
        $stub = $this->prepareStub(['getValidationRules', 'fireValidationEvent']);

        $stub->expects($this->exactly(2))->method('fireValidationEvent')->willReturn(true, false);
        $stub->expects($this->once())->method('getValidationRules')->willReturn([]);

        $stub->validate();

        $this->assertArrayHasKey('::validated', $stub->getErrors()->toArray());
    }
}
