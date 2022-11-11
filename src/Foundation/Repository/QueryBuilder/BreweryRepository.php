<?php

namespace Foundation\Repository\QueryBuilder;

use Foundation\Repository\IBreweryRepository;
use Exception;
use Foundation\Repository\QueryBuilderRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BreweryRepository extends QueryBuilderRepository implements IBreweryRepository
{
    public const TABLE = 'bb_companies';

    public const BREWERY_NAME = 'companies.brewery_name';

    /**
     * Base brewery query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function getBreweryQuery(): Builder
    {
        return $this->query->select(
            [
                DB::raw('bb_companies.id AS brewery_id'),
                DB::raw('bb_companies.brewery_name AS brewery_name'),
                DB::raw('bb_companies.profile_picture'),
                DB::raw('bb_companies.profile_summary'),
                DB::raw('bb_companies.address'),
                DB::raw('bb_companies.brewery_name'),
                DB::raw('bb_companies.profile_summary'),
                DB::raw('bb_companies.profile_picture'),
                DB::raw('bb_companies.feature_image'),
                DB::raw('bb_companies.video_title'),
                DB::raw('bb_companies.video_url'),

            ]
        );
    }

    /**
     * @param null $keyword
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     * @throws \Exception
     */
    public function list($keyword = null)
    {
        $row = Company::select([
            DB::raw('bb_companies.id AS brewery_id'),
            DB::raw('bb_companies.brewery_name'),
            DB::raw('bb_companies.profile_picture'),
            DB::raw('COUNT(*) AS product_count'),
        ])
        ->when($keyword, function ($query) use ($keyword) {
            return $query->where(self::BREWERY_NAME, 'ILIKE', "%$keyword%");
        })
        ->leftJoin('products', 'companies.id', '=', 'products.company_id')
            ->whereNotNull('companies.profile_picture')
            ->whereNotNull('companies.pp_brewery_id')
            ->groupBy(['companies.id'])
            ->orderBy('brewery_name', 'ASC')
            ->orderBy('product_count', 'DESC');


        try {
            $collection = $row->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        return $row->paginate(12);
    }

    /**
     * Get breweries similar to name
     *
     * @param $brewery_name
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getBreweryLikeName($brewery_name): Collection
    {
        $query = $this->getBreweryQuery();

        $row = $query->where(self::BREWERY_NAME, 'LIKE', "%$brewery_name%");

        try {
            $collection = $row->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        return $row->get();
    }

    /**
     * Retrieve most popular breweries
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getMostPopularBreweries(): Collection
    {
        $query = $this->query->select([
                                          DB::raw('bb_companies.id AS brewery_id'),
                                          DB::raw('bb_companies.brewery_name AS brewery_name'),
                                          DB::raw('COUNT(*) AS product_count'),
                                          DB::raw('bb_companies.profile_picture'),
                                          DB::raw('bb_companies.profile_summary'),
                                      ])->leftJoin('products', 'companies.id', '=', 'products.company_id')
            ->whereNotNull('companies.profile_picture')
            ->groupBy(['companies.id'])
            ->orderBy('product_count', 'DESC');

        $row = $query->limit(100);

        try {
            $collection = $row->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        $collection = $row->get();

        // $collection = $this->getImagesAndProductsById($collection);

        return $collection;
    }

    /**
     * Retrieve random most popular breweries
     *
     * @return \Illuminate\Support\Collection|mixed
     * @throws \Exception
     */
    public function getRandomMostPopularBreweries()
    {
        $query = $this->query->select([
                                          DB::raw('bb_companies.id AS brewery_id'),
                                          DB::raw('bb_companies.brewery_name AS brewery_name'),
                                          DB::raw('COUNT(*) AS product_count'),
                                          DB::raw('bb_companies.profile_picture'),
                                          DB::raw('bb_companies.profile_summary'),
                                      ])->leftJoin('products', 'companies.id', '=', 'products.company_id')
            ->whereNotNull('companies.profile_picture')
            ->groupBy(['companies.id'])
            ->orderBy('product_count', 'DESC');

        $row = $query->limit(100);

        try {
            $collection = $row->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        $collection = $row->get();

        // $collection = $this->getImagesAndProductsById($collection);

        return $collection->random(10);
    }

    public function getImagesAndProductsById(Collection $collection)
    {
        return $collection->map(function ($item) {
            $item->brewery_images[] = $this->getImagesByBreweryId($item->brewery_id)->toArray();
            $item->brewery_products[] = $this->getProductsByBreweryId($item->brewery_id)->toArray();

            return $item;
        });
    }

    /**
     * Brewery default where clause
     *
     * @param $query
     *
     * @return mixed
     */
    private function defaultWhereClauses($query)
    {
        $query->whereNotNull('profile_picture');
        $query->whereNotNull('key_contact_user_id');
        $query->whereNotNull('latitude');
        $query->whereNotNull('longitude');

        return $query;
    }

    /**
     * Retrieve recently added Breweries
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getRecentlyAddedBreweries(): Collection
    {
        $query = $this->getBreweryQuery();

        $query->where('companies.profile_picture', '!=', '');
        $query->whereNotNull('companies.profile_picture');
        $query->orderBy('companies.created_at', 'DESC');

        $row = $this->defaultWhereClauses($query)->limit(10);

        try {
            $collection = $row->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        return $row->get();
    }

    private function defaultOrderBy(Builder $query)
    {
        return $query->orderBy('companies.brewery_name', 'DESC')->orderBy('companies.created_at', 'DESC');
    }

    /**
     * Retrieve brewery by ID with images
     *
     * @param $company_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getImagesByBreweryId($company_id): Collection
    {
        $query = DB::table('breweries_images')
            ->select(['image'])
            ->where('company_id', '=', $company_id)
            ->where('image', '!=', '');

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect([]);
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $query->get();
    }

    /**
     * Retrieve products by brewery id
     *
     * @param $company_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProductsByBreweryId($company_id): Collection
    {
        $query = (new ProductRepository())->getProductsQuery()
            ->where('products.company_id', '=', $company_id);

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect([]);
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $query->get();
    }

    /**
     * Retrieve brewery meta data
     *
     * @param $company_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBreweryMeta($company_id): Collection
    {
        $query = $this->query
            ->select([
                DB::raw('bb_companies.id AS brewery_id'),
                DB::raw('bb_companies.brewery_name'),
                DB::raw('bb_companies.profile_picture'),
                DB::raw('bb_companies.profile_summary'),
                DB::raw('bb_companies.address'),
                DB::raw('bb_companies.latitude'),
                DB::raw('bb_companies.longitude'),

                DB::raw('bb_user_address.address company_address'),
                DB::raw('bb_user_address.address_line_2 company_address_line_2'),
                DB::raw('bb_user_address.city company_city'),
                DB::raw('bb_user_address.postcode company_postcode'),
                DB::raw('bb_user_address.postcode company_postcode'),
                DB::raw('bb_user_address.latitude company_latitude'),
                DB::raw('bb_user_address.longitude company_longitude'),
                DB::raw('bb_countries.name company_country'),

                DB::raw('bb_companies.is_brewldn_exhibitor'),
                DB::raw('bb_companies.capacity_id'),
                DB::raw('bb_companies.production_capacity AS annual_capacity'),
                DB::raw('bb_production_capacity_options.option AS capacity_option'),
                'feature_image',
                DB::raw('bb_companies.background_title'),
                DB::raw('bb_companies.background_image'),
                DB::raw('bb_companies.signpost_image'),
                DB::raw('bb_companies.signpost_title'),
                'video_title',
                'video_url',
                'website',
                'twitter_handle',
                'facebook_handle',
                'instagram_handle',
                'youtube_handle',
                'pintplease_handle',
            ])
            ->leftJoin(
                'production_capacity_options',
                'production_capacity_options.id',
                '=',
                'companies.capacity_id'
            )
            ->leftJoin(
                'user_address',
                'user_address.company_id',
                '=',
                'companies.id'
            )
            ->leftJoin(
                'countries',
                'countries.id',
                '=',
                'user_address.country_id'
            )
            ->where('companies.id', '=', $company_id);

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect([]);
            }
        } catch (QueryException | Exception $exception) {
        }

        return $query->get();
    }

    public function getBreweryProductionServices($company_id)
    {
        $services = [];

        if ($brewing = $this->getBreweryBrewingServices($company_id)) {
            $services['brewing_services'] = $brewing;
        }

        if ($packaging = $this->getBreweryPackagingServices($company_id)) {
            $services['packaging_services'] = $packaging;
        }

        $collection = collect($services);

        if ($collection->isEmpty()) {
            return false;
        }

        return $collection->toArray();
    }

    public function getBreweryBrewingServices($company_id)
    {

        $query = DB::table('brewing_services')
            ->where('company_id', '=', $company_id)
            ->where('status', '=', 'publish');

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return false;
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $query->get();
    }

    public function getBreweryPackagingServices($company_id)
    {
        $columns = ['title', 'description', 'images', 'packagings'];

        $query = DB::table('packaging_services')
            ->select($columns)
            ->where('company_id', '=', $company_id)
            ->where('status', '=', 'publish');

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return false;
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $query->get();
    }

    public function getBreweryAccreditations($company_id)
    {
        $query = DB::table('user_standards')
            ->where('company_id', '=', $company_id);

        $accreditations = [];

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return false;
            }

            foreach ($collection as $item) {
                $accreditations[] = $this->getAccreditationStandardsById($item->standard_id);
            }

            return collect($accreditations)->unique()->values()->toArray();
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return collect()->toArray();
    }

    public function getAccreditationStandardsById($identifier)
    {

        $query = DB::table('standards')
            ->select('standard')
            ->where('id', '=', $identifier);

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return false;
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $collection->first()->standard;
    }

    public function updateBreweryMeta($company_id, $payload)
    {
        // if params are in fillable
        $fillable = (new Company())->getFillable();

        // create payload to send to database table
        $data = [];

        foreach ($payload as $key => $value) {
            if (in_array($key, $fillable, true)) {
                $data[$key] = $value;
            }
        }

        if (empty($data)) {
            return false; // no fillable data to insert
        }

        $query = DB::table('companies')->where('id', '=', $company_id);

        return $query->update($data);
    }

    public function updateBreweryAddress($address)
    {
        // if params are in fillable
        $fillable = ['address', 'address_line_2', 'city', 'state', 'postcode', 'country'];

        // create payload to send to database table
        $data = [];

        foreach ($address as $key => $arr) {
            foreach ($arr as $k => $value) {
                if (in_array($k, $fillable, true)) {
                    $data[$key][$k] = $value;
                }
            }
        }

        if (empty($data)) {
            return false; // no fillable data to insert
        }

        $query = DB::table('user_address');

        $update = $query->insert($data);

        if (false === (bool)$update) {
            return false;
        }

        return $query->get();
    }

    public function insertBreweryAccreditations($standards, $company_id, $user_id)
    {
        /*// if params are in fillable
        $fillable = ['user_id', 'company_id', 'standard_id'];

        // create payload to send to database table
        $data = [];

        foreach ($standards as $key => $arr) {
            foreach ($arr as $k => $value) {
                if (in_array($k, $fillable, true)) {
                    $data[$key][$k] = $value;
                }
            }
        }*/
        $this->removeBreweryAccreditations($company_id);

        $data = $this->transformAccreditations($standards, $user_id);

        if (empty($data)) {
            return false; // no fillable data to insert
        }

        $query = DB::table('user_standards');

        $update = $query->where('company_id', '=', $company_id)->insert($data);

        if (false === (bool)$update) {
            return false;
        }

        return $query->get();
    }

    private function transformAccreditations($standards, $user_id)
    {
        // if params are in fillable
        $fillable = ['company_id', 'standard_id'];

        // create payload to send to database table
        $data = [];

        foreach ($standards as $key => $arr) {
            foreach ($arr as $k => $value) {
                if (in_array($k, $fillable, true)) {
                    $data[$key][$k] = $value;
                    $data[$key]['user_id'] = $user_id;
                }
            }
        }

        return $data; // no fillable data to insert
    }

    public function removeBreweryAccreditations($company_id)
    {
//        dd($company_id);

        $query = DB::table('user_standards');
//        dd($query);
        $query = $query->whereIn('company_id', [$company_id]);
//        dd($query);

//        dd(__LINE__);
        $query = $query->delete();

        return [];
    }

    public function getBenchmarkableBreweries(): Collection
    {
        $query = $this->query->select([
            DB::raw('bb_companies.id AS brewery_id'),
            DB::raw('bb_companies.brewery_name AS brewery_name'),
            DB::raw('COUNT(*) AS product_count'),
            DB::raw('bb_companies.profile_picture'),
            DB::raw('bb_companies.profile_summary'),
        ])->leftJoin('products', 'companies.id', '=', 'products.company_id')
            ->whereNotNull('companies.profile_picture')
            ->whereNotNull('companies.pp_brewery_id')
            ->groupBy(['companies.id'])
            ->orderBy('product_count', 'DESC');

        $row = $query->limit(100);

        try {
            $collection = $row->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        $collection = $row->get();

        return $collection;
    }

    public function findPremiumMembers()
    {
        $query = $this->getProductsQuery();

        $query->where('companies.premium_user', '=', 1);
        $query->where('product_images.url', '!=', '');
        $query->whereNotNull('product_tags.product_tags');
        $query->orderBy('products.rating', 'DESC');

        try {
            $collection = $query->get()->each(function ($item) {
                $tags = json_decode($item->product_tags);

                if (false === empty($tags->beer_ranges[0]->id) && $tags->beer_ranges[0]->id == 3) {
                    $item->is_limited = 1;
                }

                unset($item->product_tags);
            });

            if ($collection->isEmpty()) {
                return false ;
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $collection;
    }
}
