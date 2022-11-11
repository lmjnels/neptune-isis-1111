<?php


namespace Foundation\Repository;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class EloquentRepository extends Repository
{
    //This constant needs to be declared in any child class
    public const MODEL = null;

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * BaseRepository constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->createNewModel();
    }

    /**
     * @throws Exception
     */
    public function reset(): Repository
    {
        $this->createNewModel();

        return $this;
    }

    public function modelClass(): ?string
    {
        return static::MODEL;
    }

    /**
     * @throws Exception
     */
    private function createNewModel(): void
    {
        if (is_null(static::MODEL)) {
            throw new Exception("Constant MODEL is mandatory in " . get_class($this));
        }

        $modelClassName = static::MODEL;

        $this->model = new $modelClassName();
    }

    public function insert(array $values): bool
    {
        return $this->model->insert($values);
    }

    //Simply insert one value avoiding to wrap your data with extra brackets
    public function insertOne(array $attributes): bool
    {
        return $this->model->insert([$attributes]);
    }

    public function select(...$columns)
    {
        return $this->model->select(...$columns);
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function updateOrCreate(array $attributes, array $values)
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function save(array $attributes): bool
    {
        return $this->fill($attributes)->save();
    }

    public function update(array $attributes, int $id): bool
    {
        $entity = $this->model->find($id);

        if ($entity && $entity instanceof Model) {
            return $entity->update($attributes);
        }

        return false;
    }

    //Returns number of of updated records
    public function updateWhere(array $values, ...$attributes): int
    {
        return $this->model->where(...$attributes)->update($values);
    }

    //$id - array/int/string
    //returns number of deleted records
    public function delete($id): int
    {
        return $this->model->destroy($id);
    }

    public function fillWithRelations(array $data = [], array $relations = []): Model
    {
        $model = $this->fill($data);

        foreach ($relations as $relationName => $object) {
            $model->{$relationName}()->associate($object);
        }

        return $model;
    }

    public function fill(array $attributes): Model
    {
        return $this->model->fill($attributes);
    }

    public function with(...$relations)
    {
        return $this->model->with(...$relations);
    }

    public function withCount(...$relations)
    {
        return $this->model->withCount(...$relations);
    }

    public function countWhere(...$attributes): int
    {
        if (is_array($attributes[0]) && count($attributes) === 1 && !is_array($attributes[0][0])) {
            $attributes = $attributes[0];
        }

        return $this->model->where(...$attributes)->count();
    }

    public function getWhere(...$attributes): Collection
    {
        if (is_array($attributes[0]) && count($attributes) === 1 && !is_array($attributes[0][0])) {
            $attributes = $attributes[0];
        }

        return $this->model->where(...$attributes)->get();
    }

    public function getSelection(...$attributes): Collection
    {
        return $this->model->select(...$attributes)->get();
    }

    public function getSelectionWhere(array $selection, array $where): Collection
    {
        if (!is_array($where[0])) {
            $where = [$where];
        }

        return $this->model->select($selection)->where($where)->get();
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function first()
    {
        return $this->model->first();
    }

    public function firstWhere(...$arguments)
    {
        return $this->model->firstWhere(...$arguments);
    }

    public function firstWhereOrFail(...$arguments)
    {
        return $this->model->where(...$arguments)->firstOrFail();
    }

    public function last()
    {
        $sortingKey = $this->model->usesTimestamps() ? $this->model->getCreatedAtColumn() : $this->model->getKeyName();

        return $this->model->latest($sortingKey)->first();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findOrFail($id, bool $withTrashed = false)
    {
        $model = $this->model;

        if ($withTrashed) {
            $model = $model->withTrashed();
        }

        return $model->findOrFail($id);
    }

    public function findWith($id, array $relations)
    {
        return $this->model->with($relations)->find($id);
    }

    public function findMany(array $ids, array $columns = ['*']): ?Collection
    {
        if (empty($ids)) {
            return $this->model->newCollection();
        }

        return $this->model
            ->whereIn($this->model->getQualifiedKeyName(), $ids)
            ->get($columns);
    }

    public function chunk(int $count, callable $callback)
    {
        return $this->model->chunk($count, $callback);
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function exists(int $id): bool
    {
        return $this->model->where($this->model->getKeyName(), $id)->exists();
    }

    public function existsWhere(...$arguments): bool
    {
        return $this->model->where(...$arguments)->exists();
    }

    public function increment(string $column, ...$arguments): int
    {
        return $this->model->where(...$arguments)->increment($column);
    }
}
