<?php
/**
 * Copyright (c) 1989-2019. TS Lombard and its licensors. All Rights Reserved.
 * TS Lombard, the TS Lombard logo, and TS Lombard App are either registered
 * trademarks or trademarks of TS Lombard in the United Kingdom and/or other
 * countries. All other trademarks are the property of their respective owners.
 */

namespace Service;

use App\Notifications\PremiumAccessRequestNotification;
use DB;
use Job\Freemium\UpdateFreemiumUserOnHubSpotJob;
use Log;
use Model\DynamicsTslRelationship;
use Model\Report;
use Model\TokenBasket;
use Model\TokenType;
use Model\User;
use RepositoryInterface\TokenBasketRepositoryInterface;
use RepositoryInterface\UserRepositoryInterface;
use Service\Token\ContactTokenBasketConfigService;
use Throwable;

/**
 * Class UserService
 * @package Service
 */
class UserService extends BaseService
{

    /**
     * @var ContactTokenBasketConfigService
     */
    private $tokenBasketConfigService;

    /**
     * @var TokenBasketRepositoryInterface
     */
    private $tokenBasketRepository;

    /**
     * @var ServiceService
     */
    private $serviceService;

    /**
     * @var DynamicsTslRelationshipService
     */
    private $relationshipService;

    /**
     * @var UserAccessService
     */
    private $userAccessService;

    public function __construct(
        UserRepositoryInterface $repository,
        TokenBasketRepositoryInterface $tokenBasketRepository,
        ContactTokenBasketConfigService $tokenBasketConfigService,
        ServiceService $serviceService,
        DynamicsTslRelationshipService $relationshipService,
        UserAccessService $userAccessService)
    {
        $this->repository = $repository;
        $this->tokenBasketConfigService = $tokenBasketConfigService;
        $this->tokenBasketRepository = $tokenBasketRepository;
        $this->serviceService = $serviceService;
        $this->relationshipService = $relationshipService;
        $this->userAccessService = $userAccessService;
    }

    public function find(int $userId): ?User
    {
        return $this->repository()->find($userId);
    }

    public function getTestUser(): ?User
    {
        return $this->repository()->find(config('tsl.debug.testUserId'));
    }

    public function findLatestUserByEmail(string $email): ?User
    {
        return $this->repository()->findLatestUserByEmail($email);
    }

    public function findOrFail(int $userId): User
    {
        return $this->repository()->findOrFail($userId);
    }

    public function isUserActive(int $userId): bool
    {
        $user = $this->repository()->find($userId);

        return $user->userAccess->status ?? false;
    }

    public function userExistsByEmail(string $email): bool
    {
        return $this->repository()->existsWhere('UserDetails_Email', $email);
    }

    /**
     * Returns this user's allowance in the form of a TokenBasket object (that may or may not exist in DB).
     * This method could be used for instance to show users how many tokens they could have, before they actually use
     * one. This is an important distinction because a basket expiry depends on the time the first token is actually
     * used.
     *
     * @param int $userId
     * @param int $tokenTypeId
     *
     * @return TokenBasket|null Null if user does not exist
     */
    public function getUserTokenAllowance(int $userId, int $tokenTypeId): ?TokenBasket
    {
        return $this->tokenBasketRepository->findActiveBasketForUser($userId, $tokenTypeId) ?? $this->tokenBasketConfigService->createBasketObjFromConfig($userId, $tokenTypeId);
    }

    /**
     * Returns an array of TokenBaskets under key = TokenType's name
     *
     * @param int $userId
     *
     * @return array
     */
    public function getUserTokenAllowancesForAllTokenTypes(int $userId): array
    {
        $retArray = [];
        $tokenTypes = TokenType::all()->pluck('id', 'name');
        foreach ($tokenTypes as $name => $ID) {
            $retArray[$name] = $this->getUserTokenAllowance($userId, $ID);
        }
        return $retArray;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function getFilteredUsersList($companyId = null, array $options): array
    {
        $includeDisabled = (bool)$options['includeDisabled'];

        //datatables stuff
        $start = $options['start'];
        $length = $options['length'];
        $search = $options['search']['value'];
        $orderCol = $options['order'][0]['column'] ?? null;
        $orderDirection = $options['order'][0]['dir'] ?? 'asc';

        $orderBy = $options['columns'][$orderCol]['data'] ?? null;

        $foundRecords = $this->repository()->usersSearch($includeDisabled, [
            'start'          => $start,
            'length'         => $length,
            'orderBy'        => $orderBy,
            'orderDirection' => $orderDirection
        ], $search, $companyId);

        $recordsTotal = $this->repository()->count();

        //send datatables some info back
        $draw = $request['draw'] ?? 0;

        return [
            'data'            => $foundRecords['records'],
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $foundRecords['records_no']
        ];
    }

    public function loginExists($userName): bool
    {
        return $this->repository()->userExists($userName);
    }

    /**
     * @param $id
     *
     * @return mixed|null
     */
    public function getFullSubscriptionList($id)
    {
        $user = $this->repository()->find($id);

        if ($user === null) {
            return null;
        }

        return $this->serviceService->getFullSubscriptionList($id);
    }

    public function getDynamicsRelationship(?User $user): ?DynamicsTslRelationship
    {
        if ($user === null) {
            return null;
        }
        return $this->relationshipService->getRelationship('User', $user->id, false);
    }

    public function toggleSubscriptionStatus(User $user, bool $status): bool
    {
        $user->research_status = $status;

        return $user->save();
    }

    public function premiumAccessRequest(User $user): void
    {
        //Update "Trial request" property of the user on HubSpot
        if($user->isUserType(User::USERTYPES['Freemium'])) {
            UpdateFreemiumUserOnHubSpotJob::dispatch($user, [
                'trial_request' => true
            ]);
        }

        //Notify account manager
        $user->notify(new PremiumAccessRequestNotification('Premium access requested', $user));
    }

    public function chunkSubscribedEmailUsersToReport(callable $callback, Report $report, bool $emailAlertSubscriptions = null)
    {
        return $this->repository()->chunkSubscribedEmailUsersToService($report->service->id, $callback, $report->is_excluded, $emailAlertSubscriptions);
    }

    public function chunkSubscribedEmailUsersToService(callable $callback, $serviceId, bool $excludeBlockedCountries = false, bool $emailAlertSubscriptions = null)
    {
        return $this->repository()->chunkSubscribedEmailUsersToService($serviceId, $callback, $excludeBlockedCountries, $emailAlertSubscriptions);
    }

    /**
     * @param string $email
     * @return User|null null if user was NOT created (already existed, or any exception)
     */
    public function createTemporaryUser(string $email): ?User
    {
        if (!$this->userExistsByEmail($email)) {
            try {
                return DB::lsrConnection()->transaction(function () use ($email) {
                    $userAccess = $this->userAccessService->getRepository()->create([
                        'UserAccess_Status' => true,
                        'UserAccess_Login'  => $email
                    ]);

                    $user = $this->repository->create([
                        'UserDetails_UserAccess_Id'        => $userAccess->id,
                        'UserDetails_Company'              => null,
                        'UserDetails_Country'              => null,
                        'UserDetails_SysAccountManager_Id' => null,
                        'UserDetails_Email'                => $email,
                        'UserDetails_DateRegister'         => now(),
                        'last_activity'                    => now()
                    ]);

                    $user->assignRole('temporary');

                    //push: save model and all of its associated relationships.
                    $user->push();
                    return $user;
                });
            } catch (Throwable $e) {
                Log::error('Error while creating a Temporary User: ' . $e->getMessage());
            }
        }
        return null;
    }
}
