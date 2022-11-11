<?php

namespace Tests\Feature;

use App\Package\Migration\DataHandler\Reader\CsvReader;
use App\Package\Migration\ImportCCMapping;
use Carbon\Carbon;
use Exception;
use FuzzyWuzzy\Fuzz;
use FuzzyWuzzy\Process;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FuzzCCDataTest extends TestCase
{
    public const COUNTRY = 'COO';
    public const COMPANY = 'Parent Brand';

    public function test_can_read_csv_data()
    {
        $filePath = storage_path('migration/cc/beer and cider product portfolio_export_uk.csv');

        $reader = (new CsvReader())->read($filePath, ',');

        foreach ($reader->getRecords() as $record) {
            if ($record[self::COUNTRY] !== 'United Kingdom') {
                continue;
            }
            dd($record);
        }
    }

    public function test_fuzzy_matching()
    {
        $fuzz = new Fuzz();
        $process = new Process($fuzz); // $fuzz is optional here, and can be omitted.

        $cc_data = 'Aspall Cyder';
//        $company = 'Aspall Cyder';
//        $bb_data = 'Aspall Cyder Ltd';

        $company = 'Yonder Brewing';
//        $company = 'CRATE Brewery';
//        $company = 'Aspall Cyder';

//        $company = 'Williams Bros Brewing Co';

        // remove Brewing Co, Brewing Company, Brewery, Brewing

        $breweries = DB::table('companies')
            ->whereNotNull('key_contact')
            ->pluck('brewery_name')
            ->toArray();

        $collection = $process->extract(
            $company,
            $breweries,
            $processor = null,
            $scorer = null
        );

        $matches = $collection->filter(
            function ($p) {
                [$name, $weight] = $p;
                if ($weight >= 70) {
                    return $p;
                }
                return false;
            }
        );

        foreach ($matches as $match) {
            [$name, $weight] = $match;
            $this->assertIsString($name);
            $this->assertIsInt($weight);
//            $this->assertGreaterThanOrEqual(89, $weight);
        }

        dd($matches);
    }

    public function test_read_csv_data_and_fuzzy_match()
    {
        $filePath = storage_path('migration/cc/beer and cider product portfolio_export_uk.csv');

        $reader = (new CsvReader())->read($filePath, ',');

        foreach ($reader->getRecords() as $record) {
            if ($record[self::COUNTRY] !== 'United Kingdom') {
                continue; // skip
            }

            $search = $record['Parent Brand'];
            $search = 'Bedlam Brewery';

            $fuzz = new Fuzz();
            $process = new Process($fuzz); // $fuzz is optional here, and can be omitted.

            $breweries = DB::table('companies')
                ->whereNotNull('key_contact')
                ->pluck('brewery_name')
                ->toArray();

            $collection = $process->extract(
                $search,
                $breweries,
                $processor = null,
                $scorer = null
            );

            $weight = 91;

            dd($collection);

            $matches = $collection->filter(
                function ($p) use ($weight) {
                    // if we have more than one match
                    dd($p);

                    [$name, $size] = $p;

                    // check match weight
                    if ($size >= $weight) {
                        return $p;
                    }


                    return false;
                }
            );

            dd($matches);

            foreach ($collection as $match) {
                [$name] = $match;
                dd(['search' => $search, 'record' => $record, 'breweries' => $breweries, ['collection' => ['match' => $match]]]);
            }
        }
    }

    public function test_read_csv_data_and_exact_fuzzy_match()
    {
        $filePath = storage_path('migration/cc/beer and cider product portfolio_export_uk.csv');

        $reader = (new CsvReader())->read($filePath, ',');

        foreach ($reader->getRecords() as $record) {
            if ($record[self::COUNTRY] !== 'United Kingdom') {
                continue;
            }

            $company = $record[self::COMPANY];

            $fuzz = new Fuzz();
            $process = new Process($fuzz); // $fuzz is optional here, and can be omitted.

            $breweries = DB::table('companies')
            //    ->whereNotNull('key_contact')
                ->pluck('brewery_name')
                ->toArray();

            $collection = $process->extractBests(
                $company,
                $breweries,
                $processor = null,
                $scorer = null
            );

            $matches = $collection->filter(
                function ($p) {
                    [$name, $weight] = $p;
                    if ($weight >= 90) {
                        return $p;
                    }
                    return false;
                }
            );



            if ($matches->count() > 1) {
                foreach ($matches as $match) {
                    [$name, $weight] = $match;

                    dd($record, $company, $match);

                    $this->assertIsString($name);
                    $this->assertIsInt($weight);
                    $this->assertGreaterThanOrEqual(89, $weight);
                }
            }
        }
    }

    public function test_handle_import_cc_brewery_exact_match()
    {
        $import = new ImportCCMapping();

        $search = 'Bedlam Brewery';

        $match = $import->extractExactMatch($search);

        [$name, $weight] = $match;

        $this->assertEquals($expected = $search, $actual = $name);
        $this->assertEquals($expected = 100, $actual = $weight);
    }

    public function test_handle_import_data_mapping()
    {
        $import = new ImportCCMapping();

        $records = $import->fetch->getRecords();

        foreach ($records as $record) {
            if ($record[$import->country_of_origin()] !== 'United Kingdom') {
                continue;
            }


            // @todo test how many records versus how many imported
            $import->importDataMapping($record);
        }
    }

    public function importing_products_by_exact_brand_name()
    {
        $import = new ImportCCMapping();

//        $import->findBrandNameWithExactMatch($search)


    }

    public function importing_products_by_exact_brand_name_n()
    {
        $import = new ImportCCMapping();

        foreach ($import->fetch->getRecords() as $record) {
            if ($record[$import->country_of_origin()] !== 'United Kingdom') {
                continue;
            }

            $search = $record['Product'];

            $import->findBrandNameWithExactMatch($search);
        }
    }

    public function test_handle_import_cc_brewery_data()
    {
        $import = new ImportCCMapping();

        foreach ($import->fetch->getRecords() as $record) {
            if ($record[$import->country_of_origin()] !== 'United Kingdom') {
                continue;
            }
            // log record being migrated
            $import->logRecord($record);


            $search = $brewery_name = $record[self::COMPANY];

            // find exact matches for brewery
            if (false === empty($match = $import->findBreweriesWithExactMatch($search))) {
                [$name, $weight] = $match[0];

                // dd($match, $name, $weight);

                // find breweries that match name
                $breweries = $import->getBreweriesByName($name);

                if(empty($breweries)){
                    dd('this is strange');
                }

                if($breweries->count() === 1){
                    $brewery = $breweries->first();

                    try {
                        $values = [
                            'name'  => $record['Product Name'],
                            'company_id'  => $brewery->id,
                            'cc_group_id'  => $record['Grouping ID']
                        ];

                        $query = DB::table('products')->insert($values);
                    } catch (Exception $exception){
                        dd($exception->getMessage());
                    }
                }

                // determine what brewery to assign the recorded product to

                // before doing this we must check if we have more than one instance of brewery in our databae
            }

            // return count of all products with grouping_id

            $query = DB::table('products')->whereNotNull('cc_group_id');
        }


        dd($query->get()->count());
    }
}
