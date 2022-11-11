<?php

namespace App\Package\Service;

use App\Models\V2\Company;
use App\Package\Repositories\QueryBuilderRepository;
use App\Package\Service\Google\GoogleMaps;
use App\Repositories\QueryBuilder\BreweryRepository;
use App\Repositories\QueryBuilder\ProductRepository;
use App\Transformers\BreweryListingTransformer;
use App\Transformers\ProductListingTransformer;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\HttpCache\Esi;

class BreweryService
{
    public function __construct(BreweryRepository $breweryRepository)
    {
        $this->repository = $breweryRepository;
    }

    /**
     * Get similar brewery names
     *
     * @throws \Exception
     *
     * @return bool|array
     */
    public function getBreweryLikeName($brewery_name)
    {
        if (false === isset($brewery_name) || empty($brewery_name)) {
            throw new Exception('Brewery name must be set.');
        }

        $collection = $this->repository->getBreweryLikeName($brewery_name);

        if ($collection->isEmpty()) {
            return false;
        }

        return (new BreweryListingTransformer())->transform($collection->toArray());
    }

    /**
     * Get most popular brewery producers
     *
     * @return bool|array
     */
    public function getMostPopularProducers()
    {
        $collection = $this->repository
            ->getMostPopularBreweries()
            ->random(50)
            ->sortByDesc('product_count');

        if ($collection->isEmpty()) {
            return false;
        }

        return (new BreweryListingTransformer())->transform($collection->toArray());
    }

    /**
     * Get recently added breweries
     *
     * @return bool|array
     */
    public function getRecentBreweries()
    {
        $collection = $this->repository->getRecentlyAddedBreweries();

        if ($collection->isEmpty()) {
            return false;
        }

        return (new BreweryListingTransformer())->transform($collection->toArray());
    }

    /**
     * Get handpicked breweries
     *
     * @return bool|array
     */
    public function getHandpickedBreweries()
    {
        $collection = $this->repository->getRandomMostPopularBreweries();

        if ($collection->isEmpty()) {
            return false;
        }

        return (new BreweryListingTransformer())->transform($collection->toArray());
    }

    /**
     * Get brewery details by id
     *
     * @param $id
     *
     * @return bool|array
     * @throws \Exception
     */
    public function getBreweryDetails($id)
    {
        if (false === isset($id) || empty($id)) {
            throw new Exception('Brewery id must be set.');
        }

        try {
            // get brewery details
            $meta = $this->repository->getBreweryMeta($id);

            if ($meta->isEmpty()) {
                return false;
            }

            $company = (new BreweryListingTransformer())->transform($meta->toArray());

            // services
            if ($services = $this->repository->getBreweryProductionServices($id)) {
                $company[0]['services'] = $services;
            }

            // accreditations
            if ($accreditations = $this->repository->getBreweryAccreditations($id)) {
                $company[0]['accreditations'] = $accreditations;
            }

            // get brewery products
            if ($products = (new ProductRepository())->getProductsByCompanyIdentifier($id)) {
                $company[0]['products'] = (new ProductListingTransformer())->transform($products->toArray());
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return collect($company[0])->toArray();
    }

    /**
     * @throws \Exception
     */
    public function updateBreweryProfile($company_id, $data, $user_id)
    {
        if (isset($data['annual_capacity'])) {
            $data['production_capacity'] = $data['annual_capacity'];
        }

        $this->repository->updateBreweryMeta($company_id, $data);

        $standards = [];

        if (isset($data['accreditations']) && false === empty($data['accreditations'])) {
            $accreditations = collect($data['accreditations'])
                ->map(function ($item) use ($standards, $company_id, $data) {

                    if (null === $item) {
                        $this->repository->removeBreweryAccreditations($company_id);

                        return collect();
                    }

                    $query = DB::table('standards')->where('standard', 'ILIKE', "%$item%")->get();

                    if (null === $query->first()) {
                        throw new Exception('Cannot update accreditations');
                    }

                    $standards['standard_id'] = (int)$query->first()->id;
                    $standards['company_id'] = (int)$company_id;
                    $standards['user_id'] = (int)$data['user_id'];

                    return $standards;
                });

            // update with accreditations
            if($accreditations->isNotEmpty()){
                $this->repository->insertBreweryAccreditations($accreditations->toArray(), $company_id, $user_id);
            }

        }

         return $this->getBreweryDetails($company_id);
    }

    public function getBenchmarkableBreweries()
    {
        $collection = $this->repository->getBenchmarkableBreweries();

        if ($collection->isEmpty()) {
            return false;
        }

        return (new BreweryListingTransformer())->transform($collection->toArray());
    }

    public function getPremiumMembers()
    {
        $collection = $this->repository->findPremiumMembers();

        if ($collection->isEmpty()) {
            return new Exception('Record not found! :)');
        }

        return (new BreweryListingTransformer())->transform($collection->toArray());
    }

    /**
     * @param $keyword
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getBrewery($keyword)
    {
        return $this->repository->list($keyword);
    }

    public function getHighestRatedConsumers()
    {
        $collection = $this->repository->getRandomMostPopularBreweries();

        if ($collection->isEmpty()) {
            return false;
        }

        return (new BreweryListingTransformer())->transform($collection->toArray());
    }
}
