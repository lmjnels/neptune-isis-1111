<?php


namespace App\Package\Service;

use App\Models\V2\User;
use App\Repositories\QueryBuilder\InsightsRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class InsightService
{
    public function __construct(InsightsRepository $insightsRepository)
    {
        $this->repository = $insightsRepository;
    }

    /**
     * @return \Illuminate\Support\Collection|false
     * @throws \Exception
     */
    public function tryGetLatestBenchmarkReport(int $user_id): bool|\Illuminate\Support\Collection
    {
        if (false === $collection = $this->repository->getLatestInsightsBenchmarkByUserId($user_id)) {
            throw new Exception('Benchmark report not found');
        }

        $benchmarks = $collection->benchmarks;

        if (!isset($benchmarks)) {
            throw new Exception('Insights service not unavailable');
        }

        if (false === $breweries = $this->repository->getPintPleaseBreweryIds($benchmarks)) {
            throw new Exception('Insights report cannot be fully generated');
        }

        return $breweries;
    }

    /**
     * @throws \Exception
     */
    public function trySaveBenchmarkReport(int $user_id, array $data)
    {
        $collection = User::where('id', '=', $user_id)->get();

        if ($collection->isEmpty()) {
            return false;
        }

        try {
            $query = DB::table('insights_benchmarks')->insert([
                'user_id'       =>  $collection->first()->id,
                'company_id'    =>  $collection->first()->company_id,
                'benchmarks'    =>  json_encode($data['benchmarks'])
            ]);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        return $query;
    }

    public function getHeroBrandComparisons($user_id)
    {
        $user = \App\Models\V2\User::where('id', '=', $user_id)->get()->first();

        $breweries = $this->tryGetLatestBenchmarkReport($user_id);

        $data = [];


        foreach ($breweries->toArray() as $k => $brewery){
            $report = (new Cumulio\CumulioAuthorization())->authorizeWithHero($user, [$brewery->pp_brewery_id]);

            $data[$k]['index'] = $k+1;
            $data[$k]['id'] = $k+1;
            $data[$k]['text'] = $brewery->brewery_name;
            $data[$k]['token'] = $report['token'];
            $data[$k]['uid'] = $report['id'];
            $data[$k]['pp_id'] = $brewery->pp_brewery_id;
        }

        return $data;
    }
}
