<?php

namespace Znck\Plug\Eloquent;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Support\MessageBag;

trait ValidationTrait
{

    /**
     * Validation rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Custom validation messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Custom attributes for validation messages.
     *
     * @var array
     */
    protected $customAttributes = [];

    /**
     * Error messages.
     *
     * @var \Illuminate\Contracts\Support\MessageBag
     */
    private $errors = null;

    /**
     * Validator factory.
     *
     * @var \Illuminate\Contracts\Validation\Factory
     */
    private $validationFactory = null;

    /**
     * Register a validating model event with the dispatcher.
     *
     * @param  \Closure|string $callback
     * @param  int $priority
     * @return void
     * @codeCoverageIgnore
     */
    public static function validating($callback, $priority = 0)
    {
        static::registerModelEvent('validating', $callback, $priority);
    }

    /**
     * Register a validated model event with the dispatcher.
     *
     * @param  \Closure|string $callback
     * @param  int $priority
     * @return void
     * @codeCoverageIgnore
     */
    public static function validated($callback, $priority = 0)
    {
        static::registerModelEvent('validated', $callback, $priority);
    }

    /**
     * Does it have errors?
     *
     * @return bool
     */
    public function hasErrors()
    {
        return 0 !== $this->getErrors()->count();
    }

    /**
     * Failed validation messages.
     *
     * @return \Illuminate\Contracts\Support\MessageBag|null
     */
    public function getErrors()
    {
        return $this->errors ?: new MessageBag;
    }

    /**
     * Validate model.
     *
     * @return bool
     */
    public function validate()
    {
        if (false === $this->fireValidationEvent('validating')) {
            $this->errors = $this->getErrors()->add('::validating', 'validation.pre_validation');

            return false;
        }

        $validator = $this->getValidationFactory()->make($this->getAttributes(), $this->getValidationRules(),
            $this->getValidationMessages(), $this->getValidationCustomAttributes());
        if ($fails = $validator->fails()) {
            $this->errors = $validator->getMessageBag();
        }

        if (false === $this->fireValidationEvent('validated')) {
            $this->errors = $this->getErrors()->add('::validated', 'validation.post_validation');

            return false;
        }

        return ! $fails;
    }

    /**
     * Fire pre/post validation events.
     *
     * @codeCoverageIgnore
     * @param string $event
     * @return mixed
     */
    protected function fireValidationEvent($event)
    {
        return static::fireModelEvent($event);
    }

    /**
     * Validation Factory.
     *
     * @codeCoverageIgnore
     * @return Factory
     */
    protected function getValidationFactory()
    {
        if (! ($this->validationFactory instanceof Factory)) {
            $this->validationFactory = app(Factory::class);
        }

        return $this->validationFactory;
    }

    /**
     * Validation rules.
     *
     * @return array
     */
    public function getValidationRules()
    {
        return $this->rules;
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function getValidationMessages()
    {
        return $this->messages;
    }

    /**
     * Custom attributes for validation messages.
     *
     * @return array
     */
    public function getValidationCustomAttributes()
    {
        return $this->customAttributes;
    }

    /**
     * @codeCoverageIgnore
     * @return void
     * @static
     */
    protected static function bootValidation()
    {
        static::saving(function ($model) {
            if (method_exists($model, 'validate')) {
                return $model->validate();
            }

            return true;
        });

        static::updating(function ($model) {
            if (method_exists($model, 'validate')) {
                return $model->validate();
            }

            return true;
        });
    }
}
