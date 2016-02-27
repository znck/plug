<?php namespace Znck\Plug\Eloquent\Traits;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

trait UuidKey
{

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "booting" method of the model.
     */
    protected static function bootUuidKeyModel()
    {
        static::creating(function (Model $model) {
            $key = $model->getKeyName();
            if (empty($model->$key)) {
                $model->attributes[$key] = $model->generateNewUuid();
            }
        });
    }

    /**
     * Get a new version 5 (random) UUID.
     */
    public function generateNewUuid()
    {
        return Uuid::uuid4()->toString();
    }
}