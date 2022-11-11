<?php


namespace Foundation\Repository;

use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\QueryException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class QueryBuilderRepository extends Repository
{

    public const TABLE = null;

    /**
     * @var QueryBuilder $query
     */
    protected $query;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->createNewQuery();
    }

    /**
     * @throws \Exception
     */
    public function reset(): Repository
    {
        $this->createNewQuery();

        return $this;
    }


    /**
     * @throws Exception
     */
    private function createNewQuery(): void
    {
        if (is_null(static::TABLE)) {
            throw new Exception('Constant TABLE is mandatory in ' . get_class($this));
        }

        $this->query = DB::table(DB::raw(static::TABLE));
    }



    public function insert(array $values): bool
    {
        return $this->query->insert($values);
    }

    //Simply insert one value avoiding to wrap your data with extra brackets
    public function insertOne(array $attributes): bool
    {
        return $this->query->insert([$attributes]);
    }

    public function select(...$columns)
    {
        return $this->query->select(...$columns);
    }

    public function create(array $attributes)
    {
        return $this->query->create($attributes);
    }

    public function updateOrCreate(array $attributes, array $values)
    {
        return $this->query->updateOrCreate($attributes, $values);
    }

    public function save(array $attributes): bool
    {
        return $this->fill($attributes)->save();
    }

    public function update(array $attributes, int $id): bool
    {
        $entity = $this->query->find($id);

        if ($entity && $entity instanceof QueryBuilder) {
            return $entity->update($attributes);
        }

        return false;
    }

    //Returns number of of updated records
    public function updateWhere(array $values, ...$attributes): int
    {
        return $this->query->where(...$attributes)->update($values);
    }

    //$id - array/int/string
    //returns number of deleted records
    public function delete($id): int
    {
        return $this->query->destroy($id);
    }

    public function fillWithRelations(array $data = [], array $relations = []): QueryBuilder
    {
        $query = $this->fill($data);

        foreach ($relations as $relationName => $object) {
            $query->{$relationName}()->associate($object);
        }

        return $query;
    }

    public function fill(array $attributes): QueryBuilder
    {
        return $this->query->fill($attributes);
    }

    public function with(...$relations)
    {
        return $this->query->with(...$relations);
    }

    public function withCount(...$relations)
    {
        return $this->query->withCount(...$relations);
    }

    public function countWhere(...$attributes): int
    {
        if (is_array($attributes[0]) && count($attributes) === 1 && !is_array($attributes[0][0])) {
            $attributes = $attributes[0];
        }

        return $this->query->where(...$attributes)->count();
    }

    public function getWhere(...$attributes): Collection
    {
        if (is_array($attributes[0]) && count($attributes) === 1 && !is_array($attributes[0][0])) {
            $attributes = $attributes[0];
        }

        return $this->query->where(...$attributes)->get();
    }

    public function getSelection(...$attributes):Collection
    {
        return $this->query->select(...$attributes)->get();
    }

    public function getSelectionWhere(array $selection, array $where):Collection
    {
        if (!is_array($where[0])) {
            $where = [$where];
        }

        return $this->query->select($selection)->where($where)->get();
    }

    public function all(): Collection
    {
        return $this->query->all();
    }

    public function first()
    {
        return $this->query->first();
    }

    public function firstWhere(...$arguments)
    {
        return $this->query->firstWhere(...$arguments);
    }

    public function firstWhereOrFail(...$arguments)
    {
        return $this->query->where(...$arguments)->firstOrFail();
    }

    public function last()
    {
        $sortingKey = $this->query->usesTimestamps() ? $this->query->getCreatedAtColumn() : $this->query->getKeyName();

        return $this->query->latest($sortingKey)->first();
    }

    public function find($id)
    {
        return $this->query->find($id);
    }

    public function findOrFail($id, bool $withTrashed = false)
    {
        $query = $this->query;

        if ($withTrashed) {
            $query = $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function findWith($id, array $relations)
    {
        return $this->query->with($relations)->find($id);
    }

    public function findMany(array $ids, array $columns = ['*']): ?Collection
    {
        if (empty($ids)) {
            return $this->query->newCollection();
        }

        return $this->query
            ->whereIn($this->query->getQualifiedKeyName(), $ids)
            ->get($columns);
    }

    public function chunk(int $count, callable $callback)
    {
        return $this->query->chunk($count, $callback);
    }

    public function count(): int
    {
        return $this->query->count();
    }

    public function exists(int $id): bool
    {
        return $this->query->where($this->query->getKeyName(), $id)->exists();
    }

    public function existsWhere(...$arguments): bool
    {
        return $this->query->where(...$arguments)->exists();
    }

    public function increment(string $column, ...$arguments): int
    {
        return $this->query->where(...$arguments)->increment($column);
    }

    public function build(Builder $query)
    {
        $this->query = $query;

        return $this;
    }

    public function get()
    {
        $collection = clone $this->query;

        $row =  $this->query->get();

        if ($row->isEmpty()) {
            throw new RecordsNotFoundException();
        }

        return $collection->get();
    }
}
