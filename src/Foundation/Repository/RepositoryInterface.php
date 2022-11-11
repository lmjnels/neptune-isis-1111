<?php


namespace Foundation\Repository;

use Illuminate\Support\Collection;

/**
 * Interface RepositoryInterface
 *
 * @package RepositoryInterface
 */
interface RepositoryInterface
{
    public function reset();

    public function insert(array $values): bool;

    public function insertOne(array $attributes): bool;

    public function select(...$columns);

    public function create(array $attributes);

    public function updateOrCreate(array $attributes, array $values);

    public function save(array $attributes): bool;

    public function update(array $attributes, int $id): bool;

    public function updateWhere(array $values, ...$attributes): int;

    public function delete($id): int;

    public function fillWithRelations(array $data = [], array $relations = []);

    public function fill(array $attributes);

    public function with(...$relations);

    public function withCount(...$relations);

    public function countWhere(...$attributes): int;

    public function getWhere(...$attributes): Collection;

    public function getSelection(...$attributes): Collection;

    public function getSelectionWhere(array $selection, array $where): Collection;

    public function all(): Collection;

    public function first();

    public function firstWhere(...$arguments);

    public function firstWhereOrFail(...$arguments);

    public function last();

    public function find($id);

    public function findOrFail($id, bool $withTrashed = false);

    public function findWith($id, array $relations);

    public function findMany(array $ids, array $columns): ?Collection;

    public function chunk(int $count, callable $callback);

    public function count(): int;

    public function exists(int $id): bool;

    public function existsWhere(...$arguments): bool;

    public function increment(string $column, ...$arguments): int;
}
