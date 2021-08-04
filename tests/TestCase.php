<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('passport:install');
    }

    protected function assertHasManyRelationship(Model $entity, string $relationKey)
    {
        /** @var HasMany $relation */
        $relation = $entity->{$relationKey}();
        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals($entity->getForeignKey(), $relation->getForeignKeyName());
        $customerTable = $entity->getTable();
        $customerTablePrimaryKeyName = $entity->getKeyName();
        $parentKey = $relation->getQualifiedParentKeyName();
        $this->assertEquals("$customerTable.$customerTablePrimaryKeyName", $parentKey);
    }

    protected function assertBelongsToRelationship(Model $entity, string $relationKey, Model $ownerModel)
    {
        /** @var BelongsTo $relation */
        $relation = $entity->{$relationKey}();
        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals($ownerModel->getKeyName(), $relation->getOwnerKeyName());
    }

    protected function assertHasOneRelationship(Model $entity, string $relationKey)
    {
        /** @var HasOne $relation */
        $relation = $entity->{$relationKey}();
        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertEquals($entity->getForeignKey(), $relation->getForeignKeyName());
    }
}
