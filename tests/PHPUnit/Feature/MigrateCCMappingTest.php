<?php

namespace Tests\Feature;

use App\Console\Commands\Migration\MigrateCCMapping;
use App\Package\Migration\ImportCCMapping;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MigrateCCMappingTest extends TestCase
{
    private function Mapping()
    {
        return new ImportCCMapping();
    }

    public function test_import_cc_data_mapping()
    {
        $fetch = $this->Mapping()->fetch;
        $count = $fetch->getCount();
        $counted = 0;
        foreach ($fetch->getRecords() as $record) {
            $counted++;
            if ($record[$this->Mapping()->country_of_origin()] !== 'United Kingdom') {
                continue;
            }
            //dump($record);
            $this->Mapping()->importDataMapping($record);
        }

        dd($count, $counted);
    }

    /**
     * get parent brand and compare to bb company brewery_name with exact match
     */
    public function test_get_imported_brand_names()
    {
        // get parent brand and compare to bb company brewery_name with exact match
        $parent_names = $this->Mapping()->getProductParentBrandNames()->pluck('parent_brand')->unique();;

        $this->assertInstanceOf(Collection::class, $parent_names);

        //dd($parent_names);
    }

    public function test_get_imported_brand_names_and_find_matches()
    {
        // get parent brand and compare to bb company brewery_name with exact match
        $collection = $this->Mapping()->getProductParentBrandNames()->pluck('parent_brand')->unique();;

        $this->assertInstanceOf(Collection::class, $collection);

        $collection->each(function ($parent_name) {
            if ($matches = $this->Mapping()->findBreweriesWithExactMatch($parent_name)) {
                if (empty($matches)) {
                    // @todo we have a problemm
                }
                [$name, $weight] = $matches[0];

                $tmp = ['dartmoor brewery ltd.', 'fourpure brewing co.'];

                if (str_ends_with($name, '.')) {
                    $parent_name = $name;
                }

                // match of 100 found, now get bb_id and update portfolio reference with it
                $breweries = $this->Mapping()->getBreweriesByName($parent_name);

                if ($breweries->count() === 1) {
                    $values = [
                        'bb_company_id' => $breweries->first()->id,
                    ];
                    $query = $this->Mapping()->updateProductPortfolioByParentBrand($parent_name, $values);
                }
                if ($breweries->count() > 1) {
                    // @todo present user with options on what choice to import
                    dump($breweries);
                }
            }
        });
    }

    public function test_getParentBrandAndMatchWithBrewery()
    {
        $handle = (new MigrateCCMapping())->getParentBrandAndMatchWithBrewery();

        dd($handle);
    }

    // @todo fuzzy match against each item in the inventory that has a company id
}
