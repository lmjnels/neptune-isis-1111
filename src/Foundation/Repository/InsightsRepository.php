<?php


namespace App\Repositories\QueryBuilder;

use App\Package\Repositories\QueryBuilderRepository;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InsightsRepository extends QueryBuilderRepository
{
    public const TABLE = 'insights_benchmarks';

    public function getInsightsBenchmarkByName($name)
    {
        $query = DB::table(self::TABLE)->where('name', '=', $name);

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $query->get();
    }

    public function getInsightsBenchmarkById($id)
    {
        $query = DB::table(self::TABLE)->where('id', '=', $id);

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $this->mapBenchmarksToArray($query->get());
    }

    /**
     * @param $user_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getInsightsBenchmarkByUserId($user_id)
    {
        $query = DB::table(self::TABLE)->where('user_id', '=', $user_id);

        try {
            $collection = clone $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
            throw new $exception($exception->getMessage());
        }

        return $this->mapBenchmarksToArray($collection);
    }

    /**
     * @param $user_id
     *
     * @return mixed
     */
    public function getLatestInsightsBenchmarkByUserId($user_id)
    {
        $collection = $this->getInsightsBenchmarkByUserId($user_id);

        if ($collection->isEmpty()) {
            return false;
        }

        return $collection->last();
    }

    private function mapBenchmarksToArray(Collection $insights): Collection
    {
        return $insights->each(function ($item) {
            if ($item->benchmarks) {
                $item->benchmarks = json_decode($item->benchmarks);
            }

            return $item;
        });
    }

    public function getPintPleaseBreweryIds(array $benchmarks)
    {
        $query = DB::table('companies')
            ->select(['brewery_name', 'pp_brewery_id'])
            ->whereNotNull('pp_brewery_id')
            ->whereIn('id', $benchmarks);

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return false;
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $collection;
    }
}
