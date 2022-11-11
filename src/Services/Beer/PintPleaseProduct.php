<?php


namespace Services\Beer;


use App\Models\Product;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class PintPleaseProduct extends ProductService
{

    /**
     * @param $id
     *
     * @return array
     */
    public function getBeerProductByPintPlease($id): array
    {
        try {
            /** @var \Illuminate\Support\Collection $row */
            $row = Product::where('pp_product_id', '=', $id)->with('product_tag')->with('company')->get();

            if ($row->isNotEmpty()) {
                return $this->transformBeerProductInformation($row->toArray()[0]);
            }
        } catch (QueryException | Exception $exception) {
            Log::error($exception->getMessage());
        }

        return [];
    }

    /**
     * Transform results from database query
     *
     * @param array $beer
     *
     * @return array
     */
    public function transformBeerProductInformation(array $beer): array
    {
        $data = [];
        $data['brewbroker_example_id'] = $beer['id'];
        $data['pp_product_id'] = $beer['pp_product_id'];
        $data['name'] = $beer['name'];
        $data['description'] = $beer['description'];
        $data['abv'] = $beer['abv'];
        $data['ibu'] = $beer['ibu'];
        $data['image'] = $beer['image'];
        $data['packaging'] = $beer['packaging'];
        $data['rating'] = $beer['rating'];

        $tags = json_decode($beer['product_tag']['product_tags'], true);

        $data['SRM_scale'] = $tags['SRM_scale'];

        $data['beer_ranges'] = [];
        if (false === empty($tags['beer_ranges'])) {
            foreach ($tags['beer_ranges'] as $key) {
                $data['beer_ranges'][] = $key['name'];
            }
        }

        $data['beer_sights'] = [];
        if (false === empty($tags['beer_sights'])) {
            foreach ($tags['beer_sights'] as $key) {
                $data['beer_sights'][] = $key['name'];
            }
        }

        $data['beer_smells'] = [];
        if (false === empty($tags['beer_smells'])) {
            foreach ($tags['beer_smells'] as $key) {
                $data['beer_smells'][] = $key['name'];
            }
        }

        $data['beer_styles'] = [];
        if (false === empty($tags['beer_styles'])) {
            foreach ($tags['beer_styles'] as $key) {
                $data['beer_styles'][] = $key['name'];
            }
        }


        $tags['beer_tastes'] = [];
        if (false === (empty($tags['beer_tastes']))) {
            foreach ($tags['beer_tastes'] as $key) {
                $data['beer_tastes'][] = $key['name'];
            }
        }

        $data['brewery'] = [
            'name'  => $beer['company']['brewery_name'],
            'summary'  => $beer['company']['profile_summary'],
            'website'  => $beer['company']['website'],
            'profile_picture'  => $beer['company']['profile_picture'],
            'feature_image'  => $beer['company']['feature_image'],
            'latitude'  => $beer['company']['latitude'],
            'longitude'  => $beer['company']['longitude'],
        ];

        $tags['additional_features'] = [];
        if (false === (empty($tags['additional_features']))) {
            foreach ($tags['additional_features'] as $key) {
                $data['additional_features'][] = $key;
            }
        }

        $data['typical_ABV'] = $tags['typical_ABV'];

        $data['ingredients'] = $beer['product_tag']['product_ingredients'];
        $data['allergens'] = $beer['product_tag']['product_allergins'];
        $data['awards'] = $beer['product_tag']['product_awards'];
        $data['rtm'] = $beer['product_tag']['product_rtm'];

        return $data;
    }
}
