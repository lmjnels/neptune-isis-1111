<?php

namespace Foundation\Repository\QueryBuilder;

use App\Models\V2\BeerRange;
use Foundation\Repository\QueryBuilderRepository;
use Foundation\Repository\IProductRepository;
use App\Models\Beer\Product\ProductStyle;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductRepository extends QueryBuilderRepository implements IProductRepository
{

    public const TABLE = 'bb_products';

    public const BEER_ABV = 'products.abv';
    public const BEER_NAME = 'products.name';
    public const BEER_RATING = 'products.rating';
    public const BEER_COMPANY = 'companies.brewery_name';
    public const BREW_LDN_PRODUCT = 'products.is_brewldn_product';

    public function getProductsQuery()
    {
        return $this->query->select(
            [
                DB::raw('bb_companies.id brewery_id'),
                DB::raw('bb_companies.brewery_name'),
                DB::raw('bb_companies.profile_summary brewery_description'),
                DB::raw('bb_companies.profile_picture'),
                DB::raw('bb_companies.is_brewldn_exhibitor'),
                DB::raw('bb_products.id beer_id'),
                DB::raw('bb_products.description beer_description'),
                DB::raw('bb_products.name beer_name'),
                DB::raw('bb_products.abv beer_abv'),
                DB::raw('bb_products.rating beer_rating'),
                DB::raw('bb_products.image beer_image'),
                DB::raw('bb_products.styles beer_styles'),
                DB::raw('bb_beer_styles.beer_style beer_style'),
                DB::raw('bb_product_packaging.packaging_options'),
                DB::raw('bb_products.image beer_image'),
                DB::raw('bb_product_images.url product_image'),
                // 'beer_products.favourite',
            ]
        )->leftJoin(
            'companies',
            'companies.id',
            '=',
            'products.company_id'
        )->leftJoin(
            'beer_product_styles',
            'beer_product_styles.beer_id',
            '=',
            'products.id',
        )->leftJoin(
            'beer_styles',
            'beer_styles.id',
            '=',
            'beer_product_styles.beer_style_id',
        )->leftJoin(
            'product_images',
            'products.id',
            '=',
            'product_images.product_id',
        )->leftJoin(
            'product_packaging',
            'product_packaging.product_id',
            '=',
            'products.id',
        )->leftJoin(
            'beer_product_packaging',
            'beer_product_packaging.beer_id',
            '=',
            'products.id',
        );
    }

    private function defaultOrderBy($query)
    {
        return $query->orderBy('products.name', 'ASC')
            ->orderBy('products.rating', 'DESC');
    }

    public function getProducts(): Builder
    {
        return $this->query = $this->getSearchProductsQuery();
    }

    public function getProductsWithImages(): Collection
    {
        $query = $this->getProductsQuery();

        $query->whereNotNull('products.rating');

        $query->where('product_images.url', '!=', '');

        $query->orderBy('products.created_at', 'DESC');

        $query->limit(100);

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        $collection = $query->get();

        return $collection->random(96);
    }

    public function getProductLikeName($beer_name): Collection
    {
        $query = $this->getProductsQuery();

        $query->where(self::BEER_NAME, 'ILIKE', "%$beer_name%");

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        return $query->get();
    }

    public function getHighestRatedBeerProducts(): Collection
    {
        $query = $this->getProductsQuery();

        $query->whereNotNull('product_images.url');

        $query->whereNotNull(self::BEER_RATING);

        $query = $query->orderBy(self::BEER_RATING, 'DESC');

        $query = $query->limit(200);

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        return $collection;
    }

    public function getBrewLdnExhibitorProducts(): Collection
    {
        $query = $this->getSearchProductsQuery();
        $query = $query->where(self::BREW_LDN_PRODUCT, '=', true);

        $query->orderBy('products.name', 'ASC')->orderBy('products.rating', 'DESC');

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $query->inRandomOrder()->get();
    }

    /**
     * Retrieve featured products
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getFeaturedProducts(): Collection
    {
        $query = $this->getSearchProductsQuery();

        $query = $this->defaultQueryConditionFilters($query);

        $query = $query->orderBy('products.updated_at', 'DESC');

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }

//            $collection = $collection->map(function ($item) {
//                $item->beer_images[] = $this->getImagesByProductId($item->beer_id)->toArray();
//
//                return $item;
//            });


            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return collect();
    }

    /**
     * Retrieve handpicked products
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getHandpickedProducts(): Collection
    {
        $query = $this->getProductsQuery();

        $query->where('product_images.url', '!=', '');
        $query->whereNotNull('products.rating');
        $query->inRandomOrder();
        $row = $query->limit(10);

        try {
            $collection = $row->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        $collection = $query->get();

        $collection = $collection->map(function ($item) {
        // $item->beer_images[] = $this->getImagesByProductId($item->beer_id)->toArray();

            return $item;
        });

        return $collection;
    }

    /**
     * Retrieve products by category style id
     *
     * @param $category_id
     *
     * @return array
     */
    public function getBeerStyleCategoryId($category_id): array
    {
        return DB::table('beer_styles')
            ->select(['id'])
            ->whereIn('category_id', $category_id)
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * Retrieve parent style category by id
     *
     * @param $name
     *
     * @return array
     */
    public function getParentStyleByCategoryId($name): array
    {
        return DB::table('beer_styles')
            ->where('category_id', '=', $name)
            ->get()
            ->toArray();
    }

    public function getParentStyleCategoryByName($name): array
    {
        return DB::table('beer_styles')
            ->whereIn('category_id', $this->getParentStyleByCategoryId($name))
            ->get()
            ->toArray();
    }

    /**
     * Retrieve products by style name
     *
     * @param $name
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getProductByStyle($name): Collection
    {
        $query = $this->getSearchProductsQuery();


        $catName = DB::table('beer_style_categories')
            ->select('id')
            ->where('name', '=', $name)
            ->get();

        if ($catName->isEmpty()) {
            return collect();
        }

        $catName = $catName->pluck('id')->toArray();

        $category_ids = DB::table('beer_styles')
            ->select('id')->whereIn('category_id', $catName)
            ->pluck('id')
            ->toArray();

        $query = $query->whereIn('beer_product_styles.beer_style_id', $category_ids);

        $query = $query->whereNotNull('companies.profile_picture');

        $query = $query->orderByRaw('bb_product_images.url != ? DESC', ['']);

        $query = $query->orderByRaw('bb_company_subscription_details.id IS NOT NULL');

        $row = $query->orderBy(self::BEER_RATING, 'DESC');

        try {
            $collection = $row->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        return $query->get();
    }

    public function getProductByChildStyle($name): Collection
    {
        $query = $this->getSearchProductsQuery();

        $category_ids = DB::table('beer_styles')
            ->select('id')->where('beer_style', '=', $name)
            ->pluck('id')
            ->toArray();

        $query = $query->whereIn('beer_product_styles.beer_style_id', $category_ids);

        $query = $query->whereNotNull('companies.profile_picture');

        $query = $query->orderByRaw('bb_product_images.url != ? DESC', ['']);

        $query = $query->orderByRaw('bb_company_subscription_details.id IS NOT NULL');

        $row = $query->orderBy(self::BEER_RATING, 'DESC');

        try {
            $collection = $row->get();

            $collection = $collection->each(function ($item) {
                if (null === $item->beer_image && $item->product_image) {
                    $item->beer_image = $item->product_image;
                }

                return $item;
            });


            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        return $query->get();
    }

    public function getChildStyleByName(array$name): Collection
    {
        $query = DB::table('beer_styles')
            ->select('id')->whereIn('beer_style', $name);

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }



        return $collection->pluck('id');
    }

    public function getProductPackagingIdById(array $packaging_id): array
    {
        $collection =  DB::table('beer_product_packaging')
            ->select(['id'])
            ->whereIn('packaging_id', $packaging_id)
            ->get();

        return $collection->pluck('id')->toArray();
    }

    /**
     * Retrieve products by style range
     *
     * @param array $options
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getProductStyleByRange(array $options): Collection
    {
        $query = $this->getProductsQuery();

        $query = $query->whereIn(
            'beer_product_styles.beer_style_id',
            $this->getProductStyleIdById($options)
        );

        $query = $query->limit(500)->orderBy(self::BEER_NAME, 'DESC');

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

                $this->reset();

        return $query->get();
    }

    public function getProductStyleIdById(array $style_id): array
    {
        $collection =  DB::table('beer_styles')
            ->select(['id'])
            ->whereIn('id', $style_id)
            ->get();

        return $collection->pluck('id')->toArray();
    }

    /**
     * @param $query \Illuminate\Database\Query\Builder
     * @param $user_id int|null
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function defaultQueryConditionFilters($query, int $user_id = null): Builder
    {
        $builder = $query->where(
            'products.status',
            '=',
            config('constants.product_status.live')
        );

        $builder = $builder->whereNotNull('products.styles');

        $builder = $builder->whereNotNull('companies.profile_picture');

        $builder = $builder->orderByRaw('bb_product_images.url != ? DESC', ['']);

//        $builder = $builder->orderByRaw('bb_company_subscription_details.id IS NOT NULL');
//        $builder = $builder->orderByRaw('bb_subscriptions.id IS NULL, bb_subscriptions.id DESC,');


        $builder = $builder->orderByRaw('bb_products.updated_at DESC');
        $builder = $builder->orderByRaw('bb_company_subscription_details.end > NOW() DESC');

        return $builder;
    }


    /**
     * Retrieve recently added products
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getRecentlyAddedProducts($user_id = null): Collection
    {
        $query = $this->getSearchProductsQuery();

        $query = $this->defaultQueryConditionFilters($query);

        $query = $query->orderBy('products.updated_at', 'DESC');

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }

            $collection = $collection->unique('beer_id');

            if (null !== $user_id) {
                $favourites = DB::table('favourites')
                    ->where('user_id', '=', $user_id)
                    ->get()
                    ->pluck('product_id')
                    ->toArray();

                $collection = $collection->each(function ($item) use ($favourites) {

                    $item->favourite = 0;
                    if (in_array($item->beer_id, $favourites, true)) {
                        $item->favourite = 1;
                    }

                    if (null === $item->beer_image && $item->product_image) {
                        $item->beer_image = $item->product_image;
                    }

                    return $item;
                });
            }

            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return collect();
    }

    /**
     * Retrieve images by product id
     *
     * @param $product_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getImagesByProductId($product_id): Collection
    {
        $query = DB::table('product_images')
            ->select(['url', 'is_hero'])
            ->where('product_id', '=', $product_id);

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
     * Retrieve product can options
     *
     * @param $id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCansOptions($id): Collection
    {
        $query = DB::table('beer_product_cans')
            ->select([
                'name',
                'value',
                'can_size_id',
                'can_format',
                'can_format_id',
                'list_price',
                'beer_product_packaging_id'
            ])
            ->leftJoin('beer_product_packaging', 'beer_product_cans.beer_product_packaging_id', 'beer_product_packaging.id')
            ->where('beer_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    /**
     * Retrieve product bottle options
     *
     * @param $id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBottlesOptions($id): Collection
    {
        $query = DB::table('beer_product_bottles')
            ->select([
                'name',
                'value',
                'bottle_size_id',
                'bottle_format',
                'bottle_format_id',
                'list_price',
                'beer_product_packaging_id'
            ])
            ->leftJoin('beer_product_packaging', 'beer_product_bottles.beer_product_packaging_id', 'beer_product_packaging.id')
            ->where('beer_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    /**
     * Retrieve product kegs options
     *
     * @param $id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getKegsOptions($id): Collection
    {
        $query = DB::table('beer_product_kegs')
            ->select([
                'name',
                'value',
                'keg_size_id',
                'keg_fitting_id',
                'keg_fitting_name',
                'keg_type_id',
                'keg_type_name',
                'list_price',
                'beer_product_packaging_id'
            ])
            ->leftJoin('beer_product_packaging', 'beer_product_kegs.beer_product_packaging_id', 'beer_product_packaging.id')
            ->where('beer_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    /**
     * Retrieve product cask options
     *
     * @param $id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCasksOptions($id): Collection
    {
        $query = DB::table('beer_product_casks')
            ->select([
                'name',
                'cask_type_id',
                'list_price',
                'beer_product_packaging_id'
            ])
            ->leftJoin('beer_product_packaging', 'beer_product_casks.beer_product_packaging_id', 'beer_product_packaging.id')
            ->where('beer_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    /**
     * Retrieve product taste options
     *
     * @param $id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBeerTastes($id): Collection
    {
        $query = DB::table('beer_product_tastes')
            ->select([
                'beer_tastes.id',
                'name',
            ])
            ->leftJoin('beer_tastes', 'beer_product_tastes.beer_taste_id', 'beer_tastes.id')
            ->where('beer_id', '=', $id);

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect([]);
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return $collection;
    }

    /**
     * Retrieve product sights options
     *
     * @param $id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBeerSights($id): Collection
    {
        $query = DB::table('beer_product_sights')
            ->select([
                'beer_sights.id',
                'name',
            ])
            ->leftJoin('beer_sights', 'beer_product_sights.beer_sight_id', 'beer_sights.id')
            ->where('beer_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    /**
     * Retrieve product smells options
     *
     * @param $id
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBeerSmells($id): Collection
    {
        $query = DB::table('beer_product_smells')
            ->select([
                'beer_smells.id',
                'name',
            ])
            ->leftJoin('beer_smells', 'beer_product_smells.beer_smell_id', 'beer_smells.id')
            ->where('beer_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function getBeerHops($id): Collection
    {
        $query = DB::table('beer_product_hops')
            ->select([
                'beer_hops.id',
                'name',
            ])
            ->leftJoin('beer_hops', 'beer_product_hops.beer_hop_id', 'beer_hops.id')
            ->where('beer_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function getBeerMalts($id): Collection
    {
        $query = DB::table('beer_product_malts')
            ->select([
                'beer_malts.id',
                'name',
            ])
            ->leftJoin('beer_malts', 'beer_product_malts.beer_malt_id', 'beer_malts.id')
            ->where('beer_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function getBeerYeast($id): Collection
    {
        $query = DB::table('beer_product_yeast')
            ->select([
                'beer_yeast.id',
                'name',
            ])
            ->leftJoin('beer_yeast', 'beer_product_yeast.beer_yeast_id', 'beer_yeast.id')
            ->where('beer_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function getBeerAdditionalFeatures($id): Collection
    {
        $query = DB::table('beer_product_additional_features as bpaf')
            ->select([
                'special_requirements.id',
                'special_requirement',
            ])
            ->leftJoin('special_requirements', 'bpaf.special_requirement_id', 'special_requirements.id')
            ->where('beer_product_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function getBeerAllergins($id): Collection
    {
        $query = DB::table('beer_product_allergins as bpal')
            ->select([
                'beer_allergins.id',
                'beer_allergins.name',
            ])
            ->leftJoin('beer_allergins', 'bpal.beer_allergin_id', 'beer_allergins.id')
            ->where('beer_product_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function getBeerAdditives($id): Collection
    {
        $query = DB::table('beer_product_additives as bpa')
            ->select([
                'id',
                'additive',
            ])
            ->where('beer_product_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function getBeerAwards($id): Collection
    {
        $query = DB::table('beer_product_awards as bpa')
            ->select([
                'id',
                'award',
            ])
            ->where('beer_product_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function getBeerRTM($id): Collection
    {
        $query = DB::table('beer_product_rtm as bpr')
            ->select([
                'id',
                'is_sold_directly',
                'is_sold_through_wholesaler',
                'is_white_labeled',
            ])
            ->where('beer_product_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function getBeerWholeSaler($id): Collection
    {
        $query = DB::table('beer_product_wholesalers')
            ->select([
                'id',
                'wholesaler_id',
                'name',
            ])
            ->where('beer_product_id', '=', $id);

        try {
            $collection = $query->get();
            if ($collection->isEmpty()) {
                return collect([]);
            }
            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    public function getProductById($id): Collection
    {
        $query = $this->getProductsQuery();

        $query->where('products.id', '=', $id);
        $query->limit(1);

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        $collection = $query->get();

        return $collection->map(function ($item) {
            $item->beer_images = $this->getImagesByProductId($item->beer_id)->toArray();
            $item->beer_cans = $this->getCansOptions($item->beer_id)->toArray();
            $item->beer_bottles = $this->getBottlesOptions($item->beer_id)->toArray();
            $item->beer_kegs = $this->getKegsOptions($item->beer_id)->toArray();
            $item->beer_casks = $this->getCasksOptions($item->beer_id)->toArray();
            $item->beer_tastes = $this->getBeerTastes($item->beer_id)->toArray();
            $item->beer_sights = $this->getBeerSights($item->beer_id)->toArray();
            $item->beer_smells = $this->getBeerSmells($item->beer_id)->toArray();
            $item->additional_features = $this->getBeerAdditionalFeatures($item->beer_id)->toArray();
            $item->beer_additives = $this->getBeerAdditives($item->beer_id)->toArray();
            $item->beer_allergins = $this->getBeerAllergins($item->beer_id)->toArray();
            $item->beer_awards = $this->getBeerAwards($item->beer_id)->toArray();
            $item->beer_rtm = $this->getBeerRTM($item->beer_id)->toArray();
            $item->beer_wholesalers = $this->getBeerWholeSaler($item->beer_id)->toArray();
            $item->beer_hops = $this->getBeerHops($item->beer_id)->toArray();
            $item->beer_malts = $this->getBeerMalts($item->beer_id)->toArray();
            $item->beer_yeast = $this->getBeerYeast($item->beer_id)->toArray();
            return $item;
        });
    }

    private function getSearchProductsQuery()
    {
        $query = DB::table('products')->select(
            [
                    DB::raw('bb_companies.id AS brewery_id'),
                    DB::raw('bb_companies.brewery_name'),
                    DB::raw('bb_companies.profile_summary AS brewery_description'),
                    DB::raw('bb_companies.profile_picture'),
                    DB::raw('bb_companies.is_brewldn_exhibitor'),
                    DB::raw('bb_products.id AS beer_id'),
                    DB::raw('bb_products.description AS beer_description'),
                    DB::raw('bb_products.name AS beer_name'),
                    DB::raw('bb_products.abv AS beer_abv'),
                    DB::raw('bb_products.rating AS beer_rating'),
                    DB::raw('bb_products.styles AS beer_styles'),
                    DB::raw('bb_beer_style_categories.name AS beer_family'),
                    DB::raw('bb_beer_styles.beer_style AS beer_style'),
                    DB::raw('bb_products.image AS beer_image'),
                    DB::raw('bb_product_images.url AS product_image'),
                    DB::raw('bb_company_subscription_details.id AS subscriber'),
                    DB::raw('bb_subscriptions.title AS subscription'),
                    // 'beer_products.favourite',
                ]
        )->leftJoin(
            'companies',
            'companies.id',
            '=',
            'products.company_id'
        )->leftJoin('product_images', function ($join) {
                $join->on('product_images.product_id', '=', 'products.id')
                    ->where('product_images.is_hero', '=', true)
                ;
        })->leftJoin(
            'beer_product_styles',
            'beer_product_styles.beer_id',
            '=',
            'products.id'
        )
            ->leftJoin(
                'beer_styles',
                'beer_styles.id',
                '=',
                'beer_product_styles.beer_style_id'
            )
            ->leftJoin(
                'beer_style_categories',
                'beer_style_categories.id',
                '=',
                'beer_styles.category_id'
            )
            ->leftJoin(
                'beer_product_ranges',
                'beer_product_ranges.beer_id',
                '=',
                'products.id'
            )
            ->leftJoin(
                'company_subscription_details',
                'company_subscription_details.company_id',
                '=',
                'companies.id'
            )
            ->leftJoin(
                'subscriptions',
                'subscriptions.id',
                '=',
                'company_subscription_details.subscription_id'
            )
            ->leftJoin('beer_product_rtm', function ($join) {
                $join->on('beer_product_rtm.beer_product_id', '=', 'products.id');
            });

        return $query;
    }

    /**
     * @param array $criteria
     * @param integer $user_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function searchProducts(array $criteria = [], $user_id = null)
    {
        $query = $this->getSearchProductsQuery();

        $builder = clone $query;

        try {
            if (isset($criteria['abv_filters']) && false === empty($criteria['abv_filters'])) {
                $abv_buckets = config('constants.buyer_product_search.abv_filters');

                $from = [];
                $to = [];

                foreach ($criteria['abv_filters'] as $bucket_id) {
                    $bucket = $abv_buckets[$bucket_id];

                    if (isset($bucket['from'])) {
                        $from[] = $bucket['from'];
                    } else {
                        $from[] = [0];
                    }

                    if (isset($bucket['to'])) {
                        $to[] = $bucket['to'];
                    } else {
                        $to[] = [100];
                    }
                }

                $values = [
                    $from = collect($from)->min(),
                    $to = collect($to)->max()
                ];

                $builder = $builder->whereBetween('products.abv', $values);
            }

            if (isset($criteria['packaging']) && false === empty($criteria['packaging'])) {
                $builder = $builder
                    ->leftJoin('beer_product_packaging', 'beer_product_packaging.beer_id', '=', 'products.id')
                    ->whereIn('beer_product_packaging.packaging_id', $criteria['packaging']);
            }

            if (isset($criteria['beer_styles']) && false === empty($criteria['beer_styles'])) {
                $beer_styles = collect($criteria['beer_styles'])->each(function ($item) {
                    return (int)$item;
                })->toArray();
                $builder = $builder->whereIn('beer_product_styles.beer_style_id', $beer_styles);
            }

            if (isset($criteria['keyword']) && false === empty($criteria['keyword'])) {
                $keyword = $criteria['keyword'];

                $builder = $builder->where(function ($query) use ($keyword) {
                    $query->where('products.name', 'ILIKE', "%{$keyword}%");
                    $query->where('beer_styles.beer_style', 'ILIKE', "%{$keyword}%");
                });
            }

            if (isset($criteria['producers']) && false === empty($criteria['producers'])) {
                $builder = $builder->whereIn('products.company_id', $criteria['producers']);
            }

            if (isset($criteria['producer_id']) && false === empty($criteria['producer_id'])) {
                $builder = $builder->whereIn('products.company_id', $criteria['producer_id']);
            }

            if (isset($criteria['brewldn_products']) && false === empty($criteria['brewldn_products'])) {
                $builder = $builder->where('companies.is_brewldn_exhibitor', true);
            }

            $beer_range = [];

            if (isset($criteria['core']) && (bool)$criteria['core']) {
                $beer_range[] = BeerRange::CORE_ID;
            }

            if (isset($criteria['seasonal']) && (bool)$criteria['seasonal']) {
                $beer_range[] = BeerRange::SEASONAL_ID;
            }

            if (isset($criteria['limited_edition']) && (bool)$criteria['limited_edition']) {
                $beer_range[] = BeerRange::ONE_OFF_ID;
            }

            if (isset($criteria['collaboration']) && (bool)$criteria['collaboration']) {
                $beer_range[] = BeerRange::COLLABORATION_ID;
            }

            if (false === empty($beer_range)) {
                $builder = $builder->whereIn('beer_product_ranges.beer_range_id', $beer_range);
            }

            if (isset($criteria['award_winning']) && (bool)$criteria['award_winning']) {
                $products = $builder->get()->pluck('beer_id')->toArray();

                $builder = $builder
                    ->leftJoin(
                        'beer_product_awards',
                        'beer_product_awards.beer_product_id',
                        '=',
                        'products.id'
                    )
                    ->whereIn('beer_product_awards.beer_product_id', $products);
            }

            if (isset($criteria['old_to_new']) && (bool)$criteria['old_to_new']) {
                $builder = $builder->orderBy('products.updated_at', 'DESC');
            }

            if (isset($criteria['a_to_z']) && (bool)$criteria['a_to_z']) {
                $builder= $builder->orderBy('products.name', 'ASC');
            }

            if (isset($criteria['highest_rated']) && (bool)$criteria['highest_rated']) {
                $builder= $builder->orderBy('products.updated_at', 'DESC');
                $builder = $builder->orderBy('products.rating', 'DESC');
            }

            if (isset($criteria['on_trade']) && (bool)$criteria['on_trade']) {
                $builder= $builder->whereNotNull('companies.cc_group_id');
            }

            if (isset($criteria['matthew_clark']) && (bool)$criteria['matthew_clark']) {
                $builder = $builder->where('beer_product_rtm.is_sold_through_wholesaler', '=', true);
            }

            if (isset($criteria['white_label']) && (bool)$criteria['white_label']) {
                $builder = $builder->where('beer_product_rtm.is_white_labeled', '=', true);
            }

            if (isset($criteria['order']) && false === empty($criteria['order'])) {
                // $builder = $builder->orderBy(self::BEER_NAME, $criteria['order']);
            }

            if (isset($criteriteria['offset'])) {
//                $builder = $builder->offset($criteria['offset']);
            }

            $builder = $builder->where(
                'products.status',
                '=',
                config('constants.product_status.live')
            );

            $builder = $builder->whereNotNull('products.styles');

            $builder = $builder->whereNotNull('companies.profile_picture');

            $builder = $builder->orderByRaw('bb_product_images.url != ? DESC', ['']);

            $builder = $builder->orderByRaw('bb_company_subscription_details.id IS NOT NULL');

            $collection = $builder->get();

            if ($collection->isEmpty()) {
                return collect();
            }

            $collection = $collection->unique('beer_id');

            $collection = $collection->each(function ($item) use ($user_id) {
                if ($user_id !== null) {
                    $favourites = DB::table('favourites')
                        ->where('user_id', '=', $user_id)
                        ->get();

                    $haystack = $favourites->pluck('product_id')
                        ->toArray();

                    if (in_array($item->beer_id, $haystack, true)) {
                        $item->favourite = 1;
                    } else {
                        $item->favourite = 0;
                    }
                }
                if (null === $item->beer_image && $item->product_image) {
                    $item->beer_image = $item->product_image;
                }

                return $item;
            });

            if (isset($criteria['favourites_only']) && (bool)$criteria['favourites_only']) {
                $collection = $collection->where('favourite', '=', 1);
            }

            return $collection;
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return collect();
    }

    private function filterByAbv($query, $criteria)
    {
        if (isset($criteria['abv_filters']) && false === empty($criteria['abv_filters'])) {
            $abv_buckets = config('constants.buyer_product_search.abv_filters');

            $from = [];
            $to = [];

            foreach ($criteria['abv_filters'] as $bucket_id) {
                $bucket = $abv_buckets[$bucket_id];

                if (isset($bucket['from'])) {
                    $from[] = $bucket['from'];
                } else {
                    $from[] = [0];
                }

                if (isset($bucket['to'])) {
                    $to[] = $bucket['to'];
                } else {
                    $to[] = [100];
                }
            }

            $values = [
                $from = collect($from)->min(),
                $to = collect($to)->max()
            ];

            $query = $query->whereBetween('products.abv', $values);

            return $query;
        }
        return $query;
    }

    private function filterByPackaging($query, $criteria)
    {
        if (isset($criteria['packaging']) && false === empty($criteria['packaging'])) {
            $packaging = collect($criteria['packaging'])->map(function ($item) {
                return (int)$item;
            });

            $query = $query->leftJoin('beer_product_packaging', 'beer_product_packaging.beer_id', '=', 'products.id');
            $query = $query->whereIn('beer_product_packaging.packaging_id', $packaging->toArray());

            return $query;
        }

        return $query;
    }


    private function filterByBeerStyles($query, $criteria)
    {
        if (isset($criteria['beer_styles']) && false === empty($criteria['beer_styles'])) {
            $beer_styles = collect($criteria['beer_styles'])->each(function ($item) use ($query) {
                return (int)$item;
            })->toArray();

            $query = $query->whereIn('beer_product_styles.beer_style_id', $beer_styles);

            return $query;
        }

        return $query;
    }

    private function filterByKeyword($query, $criteria)
    {
        if (isset($criteria['keyword']) && false === empty($criteria['keyword'])) {
            $keyword = $criteria['keyword'];

            return $query->where(function ($builder) use ($keyword) {
                return $builder->where('products.name', 'ILIKE', "%{$keyword}%")
                    ->where('beer_styles.beer_style', 'ILIKE', "%{$keyword}%");
            });
        }

        return $query;
    }

    private function filterByProducers($query, $criteria)
    {
    }

    private function filterByProducerId($query, $criteria)
    {
    }

    private function filterByBrewLondon($query, $criteria)
    {
    }

    private function filterByBeerRange($query, $criteria)
    {
    }

    private function filterByHighestRated($query, $criteria)
    {
    }

    public function getProductFavourites($user_id)
    {
        return $this->getProductsQuery()
            ->leftJoin('favourites', 'products.id', 'favourites.product_id')
            ->where('favourites.user_id', '=', $user_id)
            ->get();
    }

    public function getProductKeywords($keyword): Collection
    {
        $query = $this->getProductsQuery();

        $query->where(self::BEER_NAME, 'ILIKE', "$keyword%");
        $query->where(self::BEER_COMPANY, 'ILIKE', "$keyword%");

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        return $query->get();
    }

    public function getProductStyleCountInRange(array $options): Collection
    {
        $query = $this->getProductsQuery();

        $query = $query->whereIn(
            'beer_product_styles.beer_style_id',
            $options
        );

        $query = $query->limit(500)->orderBy(self::BEER_NAME, 'DESC');

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        return $query->get();
    }

    public function getProductsByBrewery(array $options): Collection
    {
        $query = $this->getSearchProductsQuery();
        $query = $this->defaultQueryConditionFilters($query);

        $query = $query->whereIn(
            'companies.id',
            $options
        );

        $query = $query->orderBy('products.updated_at', 'DESC');
        $query = $query->limit(100);

        try {
            $collection = $query->get();

            if ($collection->isEmpty()) {
                return collect();
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        $this->reset();

        return $query->get();
    }

    public function getProductsByCompanyIdentifier($company_id)
    {
        $query = $this->getSearchProductsQuery()
            ->where('products.status', '=', config('constants.product_status.live'));

        $builder = $this->defaultQueryConditionFilters($query);

        $builder->where('products.company_id', '=', $company_id);

        $collection = $builder->get();

        if ($collection->isEmpty()) {
            return collect();
        }

        $collection->map(function ($item) {
            if (null === $item->beer_image && $item->product_image) {
                $item->beer_image = $item->product_image;
            }

            return $item;
        })->unique('beer_id', true);

        return $collection;
    }

    public function getBeerStyles(int $beer_id): Collection
    {
        $query =  DB::table('beer_product_styles')
            ->select(['beer_styles.beer_style', 'beer_styles.id'])
            ->leftJoin('beer_styles', 'beer_styles.id', '=', 'beer_product_styles.beer_style_id')
            ->where('beer_product_styles.beer_id', '=', $beer_id);

        $collection = $query->get();

        if ($collection->isEmpty()) {
            return collect();
        }

        return $collection;
    }

    public function getProductsByStyleId($style_id, $limit = 10)
    {
        $collection = ProductStyle::where('beer_style_id', '=', $style_id)
            ->limit($limit)
            ->get();

        if ($collection->isEmpty()) {
            return collect();
        }

        return $collection;
    }

    /**
     * @throws \Exception
     */
    public function getProductByStyleId(array $style_ids)
    {
        try {
            $beer_ids = DB::table('products')
                ->select(['products.id'])
                ->leftJoin(
                    'companies',
                    'companies.id',
                    '=',
                    'products.company_id'
                )->where(
                    'products.status',
                    '=',
                    config('constants.product_status.live')
                )->whereNotNull('products.styles')
                ->whereNotNull('companies.profile_picture')
                ->pluck('id')->toArray();

            $query = DB::table('beer_product_styles')
                ->select('beer_id')
                ->whereIn('beer_style_id', $style_ids)
            ->whereIn('beer_id', $beer_ids);

            $collection = $query->get();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        if ($collection->isEmpty()) {
            return false;
        }

        return $collection;
    }

    /**
     * @return false|\Illuminate\Support\Collection
     */
    public function findLimitedEditionProducts()
    {
        $query = $this->getProductsQuery();

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

    /**
     * @param string   $keyword
     * @param int|null $page
     * @param int|null $per_page
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProducersFilterList(string $keyword = '', int $page = null, int $per_page = null)
    {
        $query = DB::table('companies')
            ->select('companies.id AS brewery_id', 'companies.brewery_name', DB::raw('COUNT(*)'))
            ->join('products', 'companies.id', 'products.company_id')
            ->where('products.status', '=', config('constants.product_status.live'))
            ->groupBy('companies.id')
            ->orderBy('count', 'desc')
            ->orderBy('companies.brewery_name', 'asc');

        if ($keyword) {
            $query->where('companies.brewery_name', 'ILIKE', "%{$keyword}%");
        }

        if ($page) {
            return $query
                ->simplePaginate($per_page, ['*'], 'page', $page)
                ->getCollection();
        }

        return $query->get();
    }


    public function getPackagingOptions()
    {
        $query = DB::table('packaging')
            ->select(['id', 'name'])
            ->where('status', '=', true);

        return $query->get();
    }

    public function getProductStyleCategories()
    {
        $query = DB::table('beer_style_categories');
        $query->select(['id', 'name']);
        $query->where('active', '=', true);
        $query->orderBy('name', 'ASC');

        $collection = $query->get();

        if ($collection->isEmpty()) {
            return false;
        }

        $collection->map(function ($item) {
            $childStyles = $this->getProductStyleById($item->id);
            $item->styles[]  = (false === $childStyles) ? [] : $childStyles;
        });

        return $collection->toArray();
    }

    /**
     * Get products by style id
     *
     * @param $identifier
     *
     * @return bool|array
     */
    public function getProductStyleById($identifier)
    {
        $query = DB::table('beer_styles');
        $query->select(['beer_styles.id', 'beer_styles.beer_style']);
        $query->where('category_id', '=', $identifier);
        $query->orderBy('beer_style', 'DESC');

        $collection = $query->get();

        if ($collection->isEmpty()) {
            return false;
        }

        return $collection->toArray();
    }

    public function productSearchQuery(array $filters)
    {
        $query = $this->getProductsQuery();
    }
}
