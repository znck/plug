<?php namespace Znck\Tests\Plug\Eloquent\Traits;

use GrahamCampbell\TestBench\AbstractTestCase;
use Ramsey\Uuid\Uuid;
use Znck\Plug\Eloquent\Traits\UuidKey;

class UuidKeyTest extends AbstractTestCase
{
    public function test_it_has_uuid_key()
    {
        $uuid = $this->getMockForTrait(UuidKey::class);

        $this->assertTrue(Uuid::isValid($uuid->generateNewUuid()));
    }
}
