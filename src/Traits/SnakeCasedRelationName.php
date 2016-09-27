<?php namespace Znck\Plug\Eloquent\Traits;

use Illuminate\Support\Str;

/**
 * Class SnakeCasedRelationName
 *
 * @package Znck\Plug\Eloquent\Traits
 * @deprecated
 */
trait SnakeCasedRelationName
{
    /**
     * Get a relationship.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getRelationValue($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        // If the "attribute" is snake cased version of a method on the model, we will
        // convert it to camel case and will handle it as above.
        $key = Str::camel($key);
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }
    }

    public function getRelationNameForError($key)
    {
        return Str::snake($key);
    }
}