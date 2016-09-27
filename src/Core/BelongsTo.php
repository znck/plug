<?php namespace Znck\Plug\Eloquent\Core;

use Illuminate\Database\Eloquent\Relations\BelongsTo as OriginalBelongsTo;
use Ramsey\Uuid\Uuid;

class BelongsTo extends OriginalBelongsTo
{

    /**
     * @codeCoverageIgnore
     */
    protected function getEagerModelKeys(array $models)
    {
        $keys = [];

        // First we need to gather all of the keys from the parent models so we know what
        // to query for via the eager loading query. We will add them to an array then
        // execute a "where in" statement to gather up all of those related records.
        foreach ($models as $model) {
            if (! is_null($value = $model->{$this->foreignKey})) {
                $keys[] = $value;
            }
        }

        if (count($keys) === 0) {
            return [$this->related->getIncrementing() ? 0 : Uuid::NIL];
        }

        return array_values(array_unique($keys));
    }
}
