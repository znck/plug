<?php namespace Znck\Plug\Eloquent\Traits;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Znck\Plug\Eloquent\Contracts\SelfValidating as SelfValidatingInterface;

/**
 * Class SelfValidating
 *
 * @package Znck\Plug\Eloquent\Traits
 * @deprecated
 */
trait SelfValidating //extends \Illuminate\Database\Eloquent\Model
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
     * @param int             $priority
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
     * @param int             $priority
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

            foreach ($this->getRelations() as $key => $relation) {
                if ($this->isSelfValidating($relation)) {
                    if ($relation->hasErrors()) {
                        $this->errors->merge([$this->getRelationNameForError($key) => $relation->getErrors()->toArray()]);
                    }
                } elseif ($relation instanceof Collection) {
                    $localErrors = [];
                    foreach ($relation as $index => $model) {
                        if ($this->isSelfValidating($model)) {
                            if ($model->hasErrors()) {
                                $localErrors[$index] = $model->getErrors()->toArray();
                            }
                        }
                    }
                    if (count($localErrors)) {
                        $this->errors->merge([$this->getRelationNameForError($key) => $localErrors]);
                    }
                }
            }
        }

        return $this->errors;
    }

    public function getRelationNameForError ($key) {
        return $key;
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

        return ! $fails;
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
        return $this->fixRules($this->rules);
    }

    protected function fixRules($rules)
    {
        foreach ($rules as $key => $rule) {
            $subRules = is_string($rule) ? explode('|', $rule) : $rule;
            foreach ($subRules as $index => $subRule) {
                if (str_contains($subRule, 'unique')) {
                    $fields = explode(',', substr($subRule, 7));
                    $table = array_shift($fields);
                    $column = array_shift($fields) ?: $key;
                    array_shift($fields); // id
                    $primary = array_shift($fields);
                    $where = [];
                    for ($i = 0; $i < count($fields); $i += 2) {
                        $where[$fields[$i]] = array_get($fields, $i + 1, null);
                    }
                    $fields = [];
                    foreach ($where as $k => $v) {
                        $fields[] = $k;
                        $fields[] = ($v === null or 0 === strcmp($v, 'null')) ? array_get($this->attributes, $k) : $v;
                    }
                    if (count($fields)) {
                        array_unshift($fields, $primary);
                    }
                    array_unshift($fields, $this->exists ? $this->getKey() : 'NULL');
                    array_unshift($fields, $column);
                    $subRule = 'unique:'.$table.','.implode(',', $fields);
                    $subRules[$index] = $subRule;
                    $rules[$key] = $subRules;
                }
            }
        }
        return $rules;
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

    /**
     * @param $relation
     *
     * @return bool
     */
    private function isSelfValidating($relation)
    {
        return $relation instanceof SelfValidatingInterface;
    }
}
