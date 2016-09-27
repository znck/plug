<?php namespace Znck\Plug\Eloquent\Traits;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

trait UuidKey
{
    /**
     * The "booting" method of the model.
     *
     * @codeCoverageIgnore
     */
    protected static function bootUuidKey()
    {
        static::creating(
            function (Model $model) {
                if (! $model->incrementing) {
                    $key = $model->getKeyName();
                    if (empty($model->$key)) {
                        $model->attributes[$key] = $model->generateNewUuid();
                    }
                }
            }
        );
    }

    /**
     * Get a new version 4 (random) UUID.
     */
    public function generateNewUuid()
    {
        return Uuid::uuid4()->toString();
    }
}
