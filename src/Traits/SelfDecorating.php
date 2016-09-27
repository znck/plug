<?php namespace Znck\Plug\Eloquent\Traits;

use Illuminate\Container\Container;
use Znck\Plug\Eloquent\Core\DecoratorFactory;

/**
 * Class SelfDecorating
 *
 * @package Znck\Plug\Eloquent\Traits
 * @deprecated
 */
trait SelfDecorating
{
    /**
     * Mutation rules.
     *
     * @var array
     */
    protected $decorations = [];

    /**
     * @var DecoratorFactory
     */
    protected static $decorator;

    /**
     * @codeCoverageIgnore
     */
    public static function bootSelfDecorating()
    {
        static::$decorator = Container::getInstance()->make(DecoratorFactory::class);
    }

    protected function getDecorations(string $attribute)
    {
        if (array_key_exists($attribute, $this->decorations)) {
            if (is_string($this->decorations[$attribute])) {
                $this->decorations[$attribute] = explode('|', $this->decorations[$attribute]);
            }

            return $this->decorations[$attribute];
        }

        return [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function setAttribute($key, $value)
    {
        if (array_key_exists($key, $this->decorations)) {
            $value = $this->decorateAttribute($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function decorateAttribute($key, $value)
    {
        $decorations = $this->getDecorations($key);

        $value = array_reduce(
            $decorations,
            function ($input, $decoration) {
                return static::$decorator->decorate($decoration, $input);
            },
            $value
        );

        return $value;
    }
}
