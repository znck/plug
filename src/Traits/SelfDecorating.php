<?php namespace Znck\Plug\Eloquent\Traits;

use Illuminate\Container\Container;
use Znck\Plug\Eloquent\Core\DecoratorFactory;

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
    public static $decorator;

    public static function bootSelfDecoratingModel()
    {
        static::$decorator = Container::getInstance()->make(DecoratorFactory::class);
    }

    private function getDecorations(string $attribute)
    {
        if (array_key_exists($attribute, $this->decorations)) {
            if (is_string($this->decorations[$attribute])) {
                $this->decorations[$attribute] = explode('|', $this->decorations[$attribute]);
            }

            return $this->decorations[$attribute];
        }

        return [];
    }

    public function setAttribute($key, $value)
    {
        if (array_key_exists($key, $this->decorations)) {
            $decorations = $this->getDecorations($key);

            $value = array_reduce($decorations, function ($input, $decoration) {
                return static::$decorator->decorate($decoration, $input);
            }, $value);
        }

        return parent::setAttribute($key, $value);
    }
}
