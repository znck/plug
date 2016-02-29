<?php namespace Znck\Plug\Eloquent\Traits;

trait FixForeignKey
{
    /**
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return str_singular($this->getTable()).'_id';
    }
}
