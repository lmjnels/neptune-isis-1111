<?php

namespace Tests\Feature;

use App\Services\PintPlease\ConsumeBeerData;
use App\Services\PintPlease\ConsumeBreweryData;
use App\Services\PintPlease\ConsumeBreweryStats;
use App\Services\PintPlease\ConsumeInsightStats;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConsumeDataStatsTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get(route('insights.get.stats'));

        dd($response->decodeResponseJson());

        $response->assertStatus(200);
    }

    public function test_fetch_data_stats()
    {
        $response = ConsumeInsightStats::fetch();

        $handler = $response->status();

        $this->assertSame(200, $response->status());

        dd($response->object());
    }

    public function test_fetch_brewery_stats()
    {
        $response = ConsumeBreweryStats::fetch(1);


        $this->assertSame(200, $response->status());

        dd($response->object());
    }

    public function test_fetch_brewery_stats_loop()
    {
        $ids = [1, 3361];

        $data = [];

        foreach ($ids as $k => $id) {
            $response = ConsumeBreweryStats::fetch($id);

            $data[$k]['pp_id'] = $id;
            $data[$k]['review_count'] = $response->object()->data->reviewCount;
            $data[$k]['average_rating'] = $response->object()->data->averageRating;
        }

        dd($data);
    }

    public function test_fetch_brewery_stats_loop_update()
    {
        $start = microtime(true);

        $query = DB::table('companies')
            ->whereNull('average_rating')
            ->whereNull('review_count')
            ->whereNotNull('pp_brewery_id')
            ->orderBy('id', 'DESC');

        $ids = $query->pluck('pp_brewery_id');

        $data = [];

        $total = $ids->count();
        $count = 0;
        $error = [];

        try {
            foreach ($ids as $k => $id) {
                $response = ConsumeBreweryStats::fetch($id);

                 $payload = $response->object();

                 $payload = json_decode(json_encode($payload), true);

                if (!empty($item['data'])) {
           /*         if ((false === is_int($payload->data->reviewCount)) || (false === is_float($payload->data->averageRating))) {
                        continue;
                    }*/

                    $query = DB::table('companies')
                        ->where('pp_brewery_id', '=', $id)
                        ->update([
                                     'average_rating' => $item['data']['averageRating'],
                                     'review_count' => $item['data']['reviewCount'],
                                 ]);

                    $count++;
                    dump(['count' => $count, 'left' => $total - $count, 'pp_id' => $id]);

                    /*  if(!$query){
                          $error[] = $id;
                      }*/
                }/* else {
                    $error[] = $id;
                }*/
            }
        } catch (Exception $exception) {
            $error[] = [$id => $exception->getMessage()];
        }
        $time_elapsed_secs = microtime(true) - $start;

        dd($count, ['time_elapsed_secs' => $time_elapsed_secs, 'pp_id_errors' => $error]);
    }

    public function test_fetch_brewery_data()
    {
        $pint_please_brewery_start = '2015-06-03';

        $start = microtime(true);

        $ppStartDate = Carbon::parse($pint_please_brewery_start);

        do {
            $day = $ppStartDate->addDays(1);

            $query_string = sprintf('?from=%s&to=%s&format=json', $day->format('Y-m-d'), $day->format('Y-m-d'));

            dump($day->format('Y-m-d'));

            $response = ConsumeBreweryData::fetch($query_string);

            $error = [];

            $payload = json_decode(json_encode($response->object()), true);

            $count = 0;

            $skipped = 0;

            $data = $payload['list'];

            if (empty($data)) {
                $skipped++;
                continue;
            }

            DB::beginTransaction();

            foreach ($data as $k => $item) {
                $values = [
                    'brewery_id'  =>  $item['id'],
                    'name'  =>  $item['name'],
                    'description'  =>  $item['description'],
                    'email'  =>  $item['email'],
                    'web'  =>  $item['web'],
                    'facebook'  =>  $item['facebook'],
                    'twitter'  =>  $item['twitter'],
                    'instagram'  =>  $item['instagram'],
                    'latitude'  =>  $item['latitude'],
                    'longitude'  =>  $item['longitude'],
                    'country'  =>  $item['country'],
                    'brewery_type'  =>  ($item['brewery_type'])??null,
                    'pp_created_at'  =>  $item['created_at'],
                    'pp_updated_at'  =>  $item['updated_at'],
                    'created_at'  =>  date('Y-m-d H:i:s'),
                    'updated_at'  =>  null,
                ];

                try {
                    DB::table('pp_brewery_portfolio')
                        ->insert($values);

                    $count++;
                } catch (Exception $exception) {
                    $error[] = [
                        'batch' => $day->format('Y-m-d'),
                        'brewery_id' => $item['id'],
                        'brewery_name' => $item['name'],
                        'exception'=> $exception->getMessage()
                    ];
                    dump($error);
                }
            }

            DB::commit();

            dump(
                [
                    'count' => $count,
                    'skipped' => $skipped,
                    $error
                ]
            );
        } while ($ppStartDate->format('Y-m-d') !== Carbon::now()->format('Y-m-d'));

        dd(['time_elapsed_in_seconds' => microtime(true) - $start]);
    }

    public function test_fetch_beer_data()
    {
        $query = DB::table('pp_product_portfolio')
            ->select('pp_created_at')
            ->orderBy('pp_created_at', 'DESC');

        if ($query->get()->isEmpty()) {
            $pint_please_date = '2014/04/04';
        } else {
            // '2018-11-04'
            // in this case cache all that match date and compare against payload before inserting to prevent dupes
            $pint_please_date = $query->pluck('pp_created_at')->first();
        }


        $start = microtime(true);

        $ppStartDate = Carbon::parse($pint_please_date);

        do {
            $day = $ppStartDate->addDays(1);

            $query_string = sprintf('?from=%s&to=%s&format=json', $day->format('Y-m-d'), $day->format('Y-m-d'));

            dump($day->format('Y-m-d'));

            $response = ConsumeBeerData::fetch($query_string);

            $error = [];

            $payload = json_decode(json_encode($response->object()), true);

            $count = 0;

            $skipped = 0;

            $data = $payload['list'];

            if (empty($data)) {
                $skipped++;
                continue;
            }

            DB::beginTransaction();

            foreach ($data as $k => $item) {
                // check cache here first

                $values = [
                    'beer_id'  =>  $item['id'],
                    'name'  =>  $item['name'],
                    'description'  =>  $item['description'],
                    'average_rating'  =>  $item['average_rating'],
                    'abv'  =>  $item['abv'],
                    'ibu'  =>  $item['ibu'],
                    'beer_type'  =>  $item['beer_type'],
                    'beer_type_id'  =>  $item['beer_type_id'],
                    'brewery_id'  =>  $item['brewery_id'],
                    'pp_created_at'  =>  $item['created_at'],
                    'pp_updated_at'  =>  $item['updated_at'],
                    'created_at'  =>  date('Y-m-d'),
                    'updated_at'  =>  null,
                ];

                try {
                    DB::table('pp_product_portfolio')
                        ->insert($values);

                    $count++;
                } catch (Exception $exception) {
                    $error[] = [
                        'batch' => $day->format('Y-m-d'),
                        'beer_id' => $item['id'],
                        'beer_name' => $item['name'],
                        'exception'=> $exception->getMessage()
                    ];
                    dump($error);
                }
            }

            DB::commit();

            dump(
                [
                    'count' => $count,
                    'skipped' => $skipped,
                    $error
                ]
            );
        } while ($ppStartDate->format('Y-m-d') !== Carbon::now()->format('Y-m-d'));

        dd(['time_elapsed_in_seconds' => microtime(true) - $start]);
    }

    public function test_update_brewery_social()
    {
        $companies = DB::connection('DB_UAT')
        ->table('companies')
            ->select(['pp_brewery_id'])
            ->whereNotNull('pp_brewery_id')
            ->get()
            ->pluck('pp_brewery_id');

//        dd($companies);

        $portfolio = DB::table('pp_brewery_portfolio')
            ->select([
                'brewery_id',
                'web',
                'facebook',
                'twitter',
                'instagram'
        ])
            ->whereIn('brewery_id', $companies)
            ->get()->unique('brewery_id');

//        dd(count($portfolio));

        foreach ($portfolio as $key => $company) {
            DB::beginTransaction();

            $values = [];

            if (isset($company->web) && false === empty($company->web)) {
                $values['website'] = $company->web;
            }

            if (isset($company->facebook) && false === empty($company->facebook)) {
                $values['facebook_handle'] = $company->facebook;
            }

            if (isset($company->twitter) && false === empty($company->twitter)) {
                $values['twitter_handle'] = $company->twitter;
            }

            if (isset($company->instagram) && false === empty($company->instagram)) {
                $values['instagram_handle'] = $company->instagram;
            }

//            dd($company->brewery_id, $values->toArray());

            try {
                DB::connection('DB_UAT')
                ->table('companies')
                    ->where('companies.pp_brewery_id', '=', $company->brewery_id)
                    ->insert($values);
            } catch (Exception $exception) {
                $error[] = [
                    'values'     => $values,
                    'message'     => $exception->getMessage(),
                ];
                dump($error);
            }

            DB::commit();
        }
    }
}
