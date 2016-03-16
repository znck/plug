<?php namespace Znck\Plug\Eloquent\Contracts;

interface SelfValidating
{

    /**
     * Check if model has validation errors.
     *
     * @return bool
     */
    public function hasErrors();

    /**
     * Get error message bag.
     *
     * @return \Illuminate\Contracts\Support\MessageBag
     */
    public function getErrors();

    /**
     * Validate model.
     *
     * @return bool
     */
    public function validate();
}
