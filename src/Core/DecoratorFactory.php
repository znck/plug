<?php namespace Znck\Plug\Eloquent\Core;

use Znck\Plug\Eloquent\Contracts\DecoratorFactory as DecoratorInterface;
use Znck\Plug\Eloquent\Contracts\string;
use Znck\Plug\Eloquent\Exceptions\UnknownDecorator;

class DecoratorFactory implements DecoratorInterface
{

    protected $decorators = [];

    public function decorate(string $decoration, $value)
    {
        $method = 'decorate'.studly_case($decoration);
        if (array_key_exists($decoration, $this->decorators)) {
            $value = call_user_func_array($this->decorators[$decoration], [$value]);
        } elseif (method_exists($this, $method)) {
            $value = call_user_func_array([$this, $method], [$value]);
        } else {
            throw new UnknownDecorator("There is no ${decoration} sanitizer.");
        }

        return $value;
    }

    public function decorateName($value)
    {
        return ucwords($value);
    }

    public function decoratePhone($value)
    {
        return str_replace('[\s]+', '', $value);
    }

    public function register(string $decorator, \Closure $handler)
    {
        $this->decorators[$decorator] = $handler;

        return $this;
    }
}
