<?php namespace Znck\Plug\Eloquent\Contracts;

interface DecoratorFactory
{

    public function decorate(string $decorator, $value);

    public function register(string $decorator, \Closure $handler);
}
