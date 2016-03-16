<?php namespace Znck\Plug\Eloquent\Traits;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Support\MessageBag;
use Znck\Plug\Eloquent\Contracts\SelfValidating as SelfValidatingInterface;

trait SelfValidating #extends \Illuminate\Database\Eloquent\Model
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
     * @var bool
     */
    private $validationDirty = true;

    /**
     * Register a validating model event with the dispatcher.
     *
     * @param \Closure|string $callback
     * @param int $priority
     * @codeCoverageIgnore
     */
    public static function validating($callback, $priority = 0)
    {
        static::registerModelEvent('validating', $callback, $priority);
    }

    /**
     * Register a validated model event with the dispatcher.
     *
     * @param \Closure|string $callback
     * @param int $priority
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
        if (! $this->errors) {
            $this->errors = new MessageBag();
        }

        if ($this->validationDirty) {
            $this->validationDirty = false;

            foreach($this->getRelations() as $key => $relation) {
                if ($relation instanceof SelfValidatingInterface) {
                    if ($relation->hasErrors()) {
                        $this->errors->merge([$key => $relation->getErrors()->toArray()]);
                    }
                }
            }
        }

        return $this->errors;
    }

    /**
     * Validate model.
     *
     * @return bool
     */
    public function validate()
    {
        $this->validationDirty = true;

        if (false === $this->fireValidationEvent('validating')) {
            $this->errors = $this->errors ?? new MessageBag();
            $this->errors->add('::validating', 'Pre-validation event returned false.');
            return false;
        }

        $validator = $this->getValidationFactory()->make(
            $this->getAttributes(),
            $this->getValidationRules(),
            $this->getValidationMessages(),
            $this->getValidationCustomAttributes()
        );

        if ($fails = $validator->fails()) {
            $this->errors = $validator->getMessageBag();
        }

        if (false === $this->fireValidationEvent('validated')) {
            $this->errors = $this->errors ?? new MessageBag();
            $this->errors->add('::validated', 'Post-validation event returned false.');
            return false;
        }

        return !$fails;
    }

    /**
     * Fire pre/post validation events.
     *
     * @codeCoverageIgnore
     *
     * @param string $event
     *
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
     *
     * @return Factory
     */
    protected function getValidationFactory()
    {
        if (!($this->validationFactory instanceof Factory)) {
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
     * @static
     */
    protected static function bootSelfValidating()
    {
        static::saving(
            function ($model) {
                if (method_exists($model, 'validate')) {
                    return $model->validate();
                }

                return true;
            }
        );

        static::updating(
            function ($model) {
                if (method_exists($model, 'validate')) {
                    return $model->validate();
                }

                return true;
            }
        );
    }
}
