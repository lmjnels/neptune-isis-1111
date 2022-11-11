<?php


namespace App\Package\Service\Cumulio;

use App\Models\V2\User;
use Cumulio\Cumulio;
use League\Csv\Exception;

class CumulioAuthorization
{
    /**
     * @var \Cumulio\Cumulio
     */
    private Cumulio $Client;

    private string $expiry = '24 hours';

    private string $inactivity_interval = '15 minutes';


    private const DEV_INTEGRATION_ID = '2a2fe902-d36d-4a30-8125-012eedf8acf3';

    public function __construct()
    {
        $this->Client = Cumulio::initialize(env('CUMULIO_KEY'), env('CUMULIO_TOKEN'));
    }

    public function authorize(User $user, array $breweries)
    {
        $userName = $user->id;
        $fullName = $user->first_name . ' '. $user->last_name;
        $email = $user->email;
        $company = $user->company()->first()->brewery_name ?? 'BrewBroker UAT';

        $parameters = [
            'type'                => 'sso',
            'expiry'              => $this->expiry,
            'inactivity_interval' => $this->inactivity_interval,
            'username'            => $userName,
            'name'                => $fullName,
            'email'               => $email,
            'suborganization'     => $company,
            'integration_id'      => self::DEV_INTEGRATION_ID,
            'role'                => 'viewer',
        ];

        $parameters['metadata']['SelectedBrewIds'] = $breweries;

        return $this->Client->create(
            'authorization',
            $parameters
        );
    }

    public function authorizeWithHero(User $user, array $breweries)
    {
        $userName = $user->id;
        $fullName = $user->first_name . ' '. $user->last_name;
        $email = $user->email;
        $company = $user->company()->first()->brewery_name ?? 'BrewBroker UAT';

        $parameters = [
            'type'                => 'sso',
            'expiry'              => $this->expiry,
            'inactivity_interval' => $this->inactivity_interval,
            'username'            => $userName,
            'name'                => $fullName,
            'email'               => $email,
            'suborganization'     => $company,
            'integration_id'      => self::DEV_INTEGRATION_ID,
            'role'                => 'viewer',
        ];

        $parameters['metadata']['HeroBrand'] = (integer)$breweries[0];

        return $this->Client->create(
            'authorization',
            $parameters
        );
    }
}
