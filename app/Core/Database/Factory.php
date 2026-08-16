<?php

declare(strict_types=1);

namespace Flex\Core\Database;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

abstract class Factory
{
    /**
     * @var class-string<Model>
     */
    protected string $model;

    protected int $count = 1;

    protected array $state = [];

    private ?Generator $faker = null;

    abstract public function definition(): array;

    public static function new(): static
    {
        return new static();
    }

    public function count(int $count): static
    {
        if ($count < 1) {
            throw new InvalidArgumentException(
                'Factory count must be greater than zero.'
            );
        }

        $factory = clone $this;
        $factory->count = $count;

        return $factory;
    }

    public function state(array $attributes): static
    {
        $factory = clone $this;

        $factory->state = [
            ...$factory->state,
            ...$attributes,
        ];

        return $factory;
    }

    public function make(array $attributes = []): Model|array
    {
        if ($this->count === 1) {
            return $this->makeModel($attributes);
        }

        $models = [];

        for ($i = 0; $i < $this->count; $i++) {
            $models[] = $this->makeModel($attributes);
        }

        return $models;
    }

    public function create(array $attributes = []): Model|array
    {
        $models = $this->make($attributes);

        if ($models instanceof Model) {
            $models->save();

            return $models;
        }

        foreach ($models as $model) {
            $model->save();
        }

        return $models;
    }

    protected function makeModel(array $attributes = []): Model
    {
        $modelClass = $this->model;

        if (!class_exists($modelClass)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Factory model [%s] does not exist.',
                    $modelClass
                )
            );
        }

        if (!is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Factory model [%s] must extend [%s].',
                    $modelClass,
                    Model::class
                )
            );
        }

        return new $modelClass([
            ...$this->definition(),
            ...$this->state,
            ...$attributes,
        ]);
    }

    protected function faker(): Generator
    {
        return $this->faker ??= FakerFactory::create('bg_BG');
    }
}
