<?php

namespace Tests\Feature;

use App\Migration\Domain\BreweryTable;
use App\Migration\Domain\CompanyTable;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Throwable;

class MigrateCompanyTableToBreweryTest extends TestCase
{
    public const SOURCE_TABLE = 'companies';

    public const TARGET_TABLE = 'beer_breweries';

    public int $count = 0;

    public int $parsedCount = 0;

    protected CompanyTable $Source;

    protected BreweryTable $Target;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->Source = $this->company();

        $this->Target = $this->brewery();
    }

    public function company(): CompanyTable
    {
        return new CompanyTable();
    }

    public function brewery(): BreweryTable
    {
        return new BreweryTable();
    }

    /**
     * @param string $tableName
     *
     * @return false|Collection
     * @throws \Exception
     */
    public function getSourceCollection(string $tableName)
    {
        $srcTable = DB::table($tableName);
        $collection = $srcTable->get();

//        $this->assertInstanceOf(Builder::class, $srcTable);
//        $this->assertInstanceOf(Collection::class, $collection);

        if ($collection->isEmpty()) {
            return false;
        }

        return $collection;
    }

    /**
     * @param string $tableName
     *
     * @return Collection
     * @throws \Exception
     */
    public function getTargetTable(string $tableName): Collection
    {
        $targetTable = DB::table($tableName);

        $collection = $targetTable->get();

//        $this->assertInstanceOf(Builder::class, $targetTable);
//        $this->assertInstanceOf(Collection::class, $collection);

        return $collection;
    }

    /**
     * A basic feature test example.
     *
     * @return void
     * @throws \Exception
     */
    public function testExample()
    {
        // define source table - company
        if (false === $companies = $this->getSourceCollection(self::SOURCE_TABLE)) {
            throw new Exception('Source Table is empty');
        }
        $this->assertInstanceOf(Collection::class, $companies);

        // define target table - brewery
        $brewery = $this->getTargetTable(self::TARGET_TABLE);
        $this->assertInstanceOf(Collection::class, $brewery);


        // cache target table id's
//        if ($brewery->isNotEmpty()) {
        // cache
//        }

        $errors = [];

        foreach ($companies as $company) {
            $company = $this->Source->setAttributes(collect($company)->toArray());
            $this->assertInstanceOf(CompanyTable::class, $company);

            // build/transform source table row for target schema
            $data = $this->transform();

            $brewery = $this->Target->setAttributes($data);
            $this->assertInstanceOf(BreweryTable::class, $data);
            $this->assertInstanceOf(BreweryTable::class, $brewery);

            $data = $brewery;



            // begin transaction
            DB::beginTransaction();

            try {
                DB::table('beer_breweries')->insert(collect($data)->toArray());
            } catch (Exception | Throwable $exception){
                DB::rollBack();
                $errors[] = $exception;
            }
        }


        dd($errors);
    }

    /**
     * @param $brewery
     */
    public function parse($brewery)
    {
        // check if company already exists;1 - skip we already have imported ? 0 - import this company
        // transform source table row for target schema
        // begin transaction
        // insert in to target table
    }

    public function setAttributes(object $attributes): CompanyTable
    {
        foreach ((array)$attributes as $key => $value) {
            if ($key === 'id') {
                $key = $this->Source->getForeignKey();
            }

            if (in_array($key, array_values($this->Source->getTableColumns()), true)) {
                if (false === is_null($value)) {
                    $this->Source->set($key, $value);
                }
            }
        }

        return $this->Source;
    }

    public function transform()
    {
        if($company_id = $this->Source->getCompanyId()){
            $this->Target->setCompanyId($company_id);
        }

        if($name = $this->Source->getBreweryName()){

            $name = ucwords($name);

            $this->Target->setName($name);
            $this->Target->setDisplayName($name);
        }

        if($description = $this->Source->getProfileSummary()){
            $this->Target->setDescription($description);
        }

        if($profile_picture = $this->Source->getProfilePicture()){
            $this->Target->setProfilePicture($profile_picture);
        }

        if($address = $this->Source->getAddress()){
            $this->Target->setAddress($address);
        }

        if($is_warehousing_needed = $this->Source->getIsWarehousingNeeded()){
            $this->Target->setIsWarehousingNeeded($is_warehousing_needed);
        }

        if($latitude = $this->Source->getLatitude()){
            $this->Target->setLatitude($latitude);
        }

        if($longitude = $this->Source->getLongitude()){
            $this->Target->setLongitude($longitude);
        }

        if($minimum_capacity_liters = $this->Source->getMinimumCapacityLiters()){
            $this->Target->setMinimumCapacityLiters($minimum_capacity_liters);
        }

        if($maximum_capacity_liters = $this->Source->getMaximumCapacityLiters()){
            $this->Target->setMaximumCapacityLiters($maximum_capacity_liters);
        }

        if($phone = $this->Source->getPhone()){
            $this->Target->setPhone($phone);
        }

        if($premium_user = $this->Source->getPremiumUser()){
            $this->Target->setPremiumUser($premium_user);
        }

        if($producer_type_id = $this->Source->getProducerTypeId()){
            $this->Target->setProducerTypeId($producer_type_id);
        }

        if($status = $this->Source->getStatus()){
            $this->Target->setStatus($status);
        }

        if($website_url = $this->Source->getWebsite()){
            $this->Target->setFacebookUrl(null);
            $this->Target->setTwitterUrl(null);
            $this->Target->setInstagramUrl(null);
            $this->Target->setWebsiteUrl($website_url);
        }

        if($hs_company_id = $this->Source->getHsCompanyId()){
            $this->Target->setHsCompanyId($hs_company_id);
        }

        if($company_number = $this->Source->getCompanyNumber()){
            $this->Target->setCompanyNumber($company_number);
        }

        if($key_contact_user_id = $this->Source->getKeyContactUserId()){
            $this->Target->setKeyContactUserId($key_contact_user_id);
        }

        if($is_brewldn_exhibitor = $this->Source->getIsBrewldnExhibitor()){
            $this->Target->setIsBrewldnExhibitor($is_brewldn_exhibitor);
        }

        if($stand_number = $this->Source->getStandNumber()){
            $this->Target->setStandNumber($stand_number);
        }

        if($is_sponsor = $this->Source->getIsSponsor()){
            $this->Target->setIsSponsor($is_sponsor);
        }

        if($sponsor_summary = $this->Source->getSponsorSummary()){
            $this->Target->setSponsorSummary($sponsor_summary);
        }

        if($external_link = $this->Source->getExternalLink()){
            $this->Target->setExternalLink($external_link);
        }

        if($pp_brewery_id = $this->Source->getPpBreweryId()){
            $this->Target->setPpBreweryId($pp_brewery_id);
        }

        if($created_at = $this->Source->getCreatedAt()){
            $this->Target->setCreatedAt($created_at);
            $this->Target->setUpdatedAt(null);
            $this->Target->setPpCreatedAt(null);
            $this->Target->setPpUpdatedAt(null);
        }

        return $this->Target;
    }
}