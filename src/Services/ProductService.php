<?php


namespace App\Package\Service;

use App\Models\V2\Packaging;
use App\Repositories\QueryBuilder\ProductRepository;
use App\Transformers\ProductListingTransformer;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductService extends AbstractService
{
    protected ProductRepository $repository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->repository = $productRepository;
    }

    public function getBeerProducts()
    {
        $collection = (new \App\Repositories\Eloquent\ProductRepository())->getBeerProducts();

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    public function getAllProducts()
    {
        $collection = $this->repository->getProductsWithImages();

        if ($collection->isEmpty()) {
            return false;
        }

        $collection = $collection->unique('beer_id', true);

        return (new ProductListingTransformer())->transform($collection->toArray());
    }


    /**
     * Get similar product names
     *
     * @param $product_name
     *
     * @return array|false
     * @throws \Exception
     */
    public function getProductLikeName($product_name)
    {
        if (false === isset($product_name) || empty($product_name)) {
            throw new Exception('Beer product name must be set.');
        }

        $collection = $this->repository->getProductLikeName($product_name);

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    /**
     * Get highest rated products
     *
     * @return false|\Illuminate\Support\Collection
     */
    public function getHighestRatedBeers()
    {
        $collection = $this->repository->getHighestRatedBeerProducts();

        if ($collection->isEmpty()) {
            return false;
        }

        return $collection;
    }

    /**
     * Get Brew London Exhibitor products
     *
     * @return array|false
     */
    public function getBrewLondonExhibitors()
    {
        $collection = $this->repository->getBrewLdnExhibitorProducts();

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    /**
     * Get featured products
     *
     * @return bool|array
     * @throws \Exception
     */
    public function getFeaturedProducts()
    {
        $collection = $this->repository->searchProducts()
            ->sortBy('beer_rating');

        $collection = $collection
            ->unique('brewery_id')
            ->whereNotNull('beer_image')
            ->random(20)
            ->values();

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    /**
     * Get handpicked products
     *
     * @return bool|array
     */
    public function getHandpickedProducts()
    {
        $collection = $this->repository->getHandpickedProducts();

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    private function transformQuery($criteria)
    {
        if (false === isset($criteria) || empty($criteria)) {
            throw new Exception('Product `search_criteria` must be set.');
        }

        if (isset($criteria['abv_filters'])) {
            $tmp = explode(',', str_replace(['[',']'], '', $criteria['abv_filters']));

            $options = [];

            foreach ($tmp as $item) {
                $options[] = (int)$item;
            }

            $criteria['abv_filters'] = $options;
        }

        if (isset($criteria['parent_styles']) && is_string($criteria['parent_styles'])) {
            $criteria['parent_styles'] = $this->transformQueryStringArray($criteria['parent_styles']);
        }

        if (isset($criteria['beer_styles']) && is_string($criteria['beer_styles'])) {
            $criteria['beer_styles'] = $this->transformQueryStringArray($criteria['beer_styles']);
        }

        if (isset($criteria['packaging']) && is_string($criteria['packaging'])) {
            $criteria['packaging'] = $this->transformQueryStringArray($criteria['packaging']);
        }

        if (isset($criteria['producers']) && is_string($criteria['producers'])) {
            $criteria['producers'] = $this->transformQueryStringArray($criteria['producers']);
        }

        if (isset($criteria['producer_id']) && is_string($criteria['producer_id'])) {
            $criteria['producer_id'] = $this->transformQueryStringArray($criteria['producer_id']);
        }

        return $criteria;
    }

    /**
     * Get products by criteria
     *
     * @param $criteria
     *
     * @return bool|array
     * @throws \Exception
     */
    public function getProductsByCriteria($criteria, $user_id)
    {
        $criteria = $this->transformQuery($criteria);

        $items_per_page = 25;

        $offset = ($criteria['page'] - 1) * $items_per_page;

        $criteria['offset'] = $offset;

        // patch: if we don't have these keys set we don't want to bother searching our inventory
        $filterParameters = ['abv_filters', 'packaging', 'beer_styles', 'producer_id'];
        $filtersDontExist = [];

        foreach ($filterParameters as $filter) {
            if (empty($criteria[$filter]) || false === isset($criteria[$filter])) {
                $filtersDontExist[] = $filter;
            }
        }

        if (count($filtersDontExist) === count($filterParameters)) {
            return false;
        }

        $collection = $this->repository->searchProducts($criteria, $user_id);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page'], $items_per_page);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
              'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    /**
     * Get products by style category or style id
     *
     * @param $style
     *
     * @return bool|array
     * @throws \Exception
     */
    public function getProductsByStyle($style)
    {
        if (false === isset($style) || empty($style)) {
            throw new Exception('Product style must be set.');
        }

        if (isset($style['category'])) {
            $collection = $this->repository->getProductByStyle($style);
        } else {
            $collection = $this->repository->getProductStyleByRange($style);
        }

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    /**
     * Get product by style name
     *
     * @param $style
     *
     * @return bool|array
     * @throws \Exception
     */
    public function getProductsByStyleName($style)
    {
        if (false === isset($style) || empty($style)) {
            throw new Exception('Product style must be set.');
        }

        $product_style = trim(ucwords($style));

        $collection = $this->repository->getProductByStyle($product_style)->take(200);

        if ($collection->isEmpty()) {
            return false;
        }

        $collection = $collection->each(function ($item) {
            if (null === $item->beer_image && $item->product_image) {
                $item->beer_image = $item->product_image;
            }

            return $item;
        });

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    public function getChildStyleIdByName(array $style_names)
    {
        if (false === isset($style_names) || empty($style_names)) {
            throw new Exception('Product style must be set.');
        }

        $collection = $this->repository->getChildStyleByName($style_names);

        if ($collection->isEmpty()) {
            return false;
        }

        return $collection->toArray();
    }

    public function getProductsByChildStyleName($style)
    {
        if (false === isset($style) || empty($style)) {
            throw new Exception('Product style must be set.');
        }

        $product_style = trim(ucwords($style));

        $collection = $this->repository->getProductByChildStyle($product_style);

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    public function getSearchFilters()
    {
        $expire = Carbon::now()->addDay(1);

        return Cache::remember('filters', $expire, function () {
            $filterOptions = [];

            $filterOptions['abv_filters'] = config('constants.buyer_product_search.abv_filters');
            $filterOptions['beer_styles'] = $this->getProductStyleCategories();
            $filterOptions['packaging'] = $this->repository->getPackagingOptions();
            $filterOptions['producers'] = $this->repository->getProducersFilterList();

            return $filterOptions;
        });
    }

    /**
     * Get product style categories
     *
     * @return bool|array
     */
    public function getProductStyleCategories()
    {
        return $this->repository->getProductStyleCategories();
    }

    public function getBeerProductRanges()
    {
        $collection = $this->repository->getProductsWithImages();

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }


    /**
     * Get recently added products
     *
     * @return bool|array
     */
    public function getRecentlyAddedProducts()
    {
        $collection = $this->repository->getRecentlyAddedProducts()
            ->unique('brewery_id')
            ->values()
            ->take(20);

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    public function search()
    {
        $collection = $this->repository->searchProducts();

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    /**
     * Get product details by id
     *
     * @param $id
     *
     * @return bool|array
     * @throws \Exception
     */
    public function getProductById($id)
    {
        if (false === isset($id) || empty($id)) {
            throw new Exception('Product `beer_id` must be set.');
        }

        $collection = $this->repository->getProductById($id);

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    /**
     * Get product by keyword
     *
     * @param $keyword
     *
     * @return array|false
     * @throws \Exception
     */
    public function getProductByKeyword($keyword)
    {
        if (false === isset($keyword) || empty($keyword)) {
            throw new Exception('Beer product name must be set.');
        }

        $collection = $this->repository->getProductKeywords($keyword);

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    /**
     * Get products by brewery
     *
     * @param $criteria
     *
     * @return bool|array
     * @throws \Exception
     */
    public function getProductsByBrewery($criteria)
    {
        if (false === isset($criteria) || empty($criteria)) {
            throw new Exception('Product `search_criteria` must be set.');
        }

        $collection = $this->repository->getProductsByBrewery($criteria);

        if ($collection->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    /**
     * Get product details by id
     *
     * @param $id
     *
     * @return bool|\Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getStyleByProductId($id)
    {
        if (false === isset($id) || empty($id)) {
            throw new Exception('Product `beer_id` must be set.');
        }

        $collection = $this->repository->getBeerStyles($id);


        if ($collection->isEmpty()) {
            return collect();
        }

        return $collection;
    }

    /**
     * @throws \Exception
     */
    public function getProductsWithMatchingStyles($id)
    {
        if (false === isset($id) || empty($id)) {
            throw new Exception('Product `beer_id` must be set.');
        }

        $collection = $this->repository->getBeerStyles($id);

        if ($collection->isEmpty()) {
            return false;
        }

        $style_id = $collection->first()->id;

        $rows = $this->repository->getProductsByStyleId($style_id);

        $products = [];

        foreach ($rows as $row) {
            $products[] = $this->repository->getProductById($row->beer_id);
        }

        return $products;
    }

    /**
     * @throws \Exception
     * @return bool|array
     */
    public function getProductByPopularBreweries()
    {

        $criteria = [
            1411, // Brewdog
            1173, // Cloudwater Brew Co
            326, // NORTHERN MONK
            1400, // Abbeydale Brewery
            245, // Tiny Rebel Brewing Co.
        ];

        $collection = $this->repository
            ->getProductsByBrewery($criteria)
            ->random(50)
            ->values();

        if ($collection->isEmpty()) {
            return false;
        }

        $collection  = (new ProductListingTransformer())->transform($collection->toArray());

        return $collection;
    }

    /**
     * @throws \Exception
     */
    public function getProductCountByStyleId(array $style_ids)
    {
        if (false === isset($style_ids) || empty($style_ids)) {
            throw new Exception('Product `style_ids` must be set.');
        }

        $collection = $this->repository->getProductByStyleId($style_ids);

        return $collection->count();
    }

    public function getLimitedEditionProducts()
    {
        $collection = $this->repository->findLimitedEditionProducts();

        if ($collection->isEmpty()) {
            return new Exception('No records found');
        }

        return (new ProductListingTransformer())->transform($collection->toArray());
    }

    public function searchForMatchingStyleByProduct($id)
    {
        if (false === isset($id) || empty($id)) {
            throw new Exception('Product `beer_id` must be set.');
        }

        // search beer pivot table
        // get all of beer styles from pivot
        $styles = $this->repository->getBeerStyles($id);
        if ($styles->isEmpty()) {
            return false;
        }

        $beer_styles = $styles->pluck('id')->toArray();

        // loop through beer styles and get beer family

        // get all children of parent family
        $childrenStyles = DB::table('beer_styles')
            ->select(['beer_style', 'category_id'])
            ->whereIn('id', $beer_styles)
            ->get();

        if ($childrenStyles->isEmpty()) {
            return false;
        }

        $parentIds = $childrenStyles->pluck('category_id');

        $parentStyle = DB::table('beer_style_categories')
            ->select(['id', 'name'])
            ->whereIn('id', $parentIds)
            ->get();

        $childFamilies = DB::table('beer_styles')
            ->whereIn('category_id', $parentIds->toArray())
                ->get();


        if ($childrenStyles->isEmpty()) {
            return false;
        }

        $relatedStyleIds = $childFamilies->pluck('id');

        $criteria['beer_styles'] = $relatedStyleIds;

        // search inventory for products with matching parent style
        $style = $this->repository->searchProducts($criteria);

        if ($style->isEmpty()) {
            return false;
        }

        return (new ProductListingTransformer())->transform($style->take(20)->toArray());
    }

    public function searchByStyleName(array $criteria, $user_id)
    {
        $criteria['limited_edition'] = true;

        $collection = $this->repository->searchProducts($criteria, $user_id);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
            'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchLimitedEdition($criteria, $user_id)
    {
        $criteria['limited_edition'] = true;

        $collection = $this->repository->searchProducts($criteria, $user_id);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
              'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchLimitedEditionBeers(array $criteria = [])
    {
        $criteria['limited_edition'] = true;

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
              'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchAwardWinners(array $criteria = []): array|false
    {
        $criteria['award_winning'] = true;

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
              'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchSeasonalBeers(array $criteria = [])
    {
        $criteria['seasonal'] = true;

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
            'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchCollaborativeBeers(array $criteria = [])
    {
        $criteria['collaboration'] = true;

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
            'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchCaskOptions(array $criteria = [])
    {
        $criteria['packaging'] = [Packaging::CASKS];

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
              'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';
        $data['error'] = false;

        return $data->toArray();
    }

    public function searchKegOptions(array $criteria = [])
    {
        $criteria['packaging'] = [Packaging::KEGS];

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
              'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchFridgeOptions(array $criteria = [])
    {
        $criteria['packaging'] = [Packaging::BOTTLES, Packaging::CANS];

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
              'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchSessionBeers(array $criteria = [])
    {
        $criteria['abv_filters'] = [3];

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
            'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchNoAlcoholBeers(array $criteria = [])
    {
        $criteria['abv_filters'] = [1,2];

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
              'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchOnTradeBeers(array $criteria = [])
    {
        $criteria['on_trade'] = true;

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
            'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchMatthewClarkBeers(array $criteria = [])
    {
        $criteria['matthew_clark'] = true;

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
            'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchWhiteLabelBeers(array $criteria = [])
    {
        $criteria['white_label'] = true;

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
            'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }

    public function searchLogicNotEstablished()
    {
        $collection = collect([]);

        $favourites = clone $collection;

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
            'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($collection->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'No data found found';
        $data['error'] = false;

        return $data->toArray();
    }

    public function searchOrganicAndVeganBeers(array $criteria = [])
    {
        return $this->searchLogicNotEstablished();
    }

    public function searchUnfilteredHazyBeers(array $criteria = [])
    {
        return $this->searchLogicNotEstablished();
    }

    public function searchByBeerStyle(array $styles = [], array $criteria): array|false
    {
//        $style_ids = $this->getChildStyleIdByName($styles);

        $criteria['beer_styles'] = $styles;

        $collection = $this->repository->searchProducts($criteria, $criteria['user']);

        if ($collection->isEmpty()) {
            return false;
        }

        $items = (new ProductListingTransformer())->transform($collection->toArray());

        $paginator = Pagination::create($items, $criteria['page']);

        $favourites = collect($items)->where('favourite', '=', 1)->values();

        $count = [
            'products'  => $collection->count(),
            'favourites'  => $favourites->count(),
            'my_favorite_products'  => $favourites->toArray(),
            'producers'  => $collection->unique('brewery_name')->count()
        ];

        $data = collect($paginator->toArray());
        $data->prepend($count, 'counts');
        $data['message'] = 'Data found successfully';

        return $data->toArray();
    }
}
