<?php

namespace Tests\Feature;

use App\Transformers\DataHandler\Csv\LegacyBrewBrokerCompanies;
use App\Transformers\Migration\Import\CompanyToBreweryImport;
use Tests\TestCase;

/**
 * Class MigrateLegacyBrewBrokerTableTest
 *
 * @package Tests\Feature
 */
class MigrateLegacyBrewBrokerTableTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        (new LegacyBrewBrokerCompanies())->run();
//        (new LegacyBrewBrokerProducts())->run();
    }

    public function testExampleing()
    {
        $legacy = new CompanyToBreweryImport();
//        $legacy = new LegacyBrewBrokerCompanies();

        $legacy->handle();
    }
}