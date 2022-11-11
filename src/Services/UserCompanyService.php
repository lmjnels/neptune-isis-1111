<?php
/**
 * Copyright (c) 1989-2019. TS Lombard and its licensors. All Rights Reserved.
 * TS Lombard, the TS Lombard logo, and TS Lombard App are either registered
 * trademarks or trademarks of TS Lombard in the United Kingdom and/or other
 * countries. All other trademarks are the property of their respective owners.
 */

namespace Service;

use Model\DynamicsTslRelationship;
use Model\UserCompany;
use RepositoryInterface\UserCompanyRepositoryInterface;

class UserCompanyService extends BaseService
{
    /**
     * @var DynamicsTslRelationshipService
     */
    private $relationshipService;

    public function __construct(
        UserCompanyRepositoryInterface $repository,
        DynamicsTslRelationshipService $relationshipService
    )
    {
        $this->repository = $repository;
        $this->relationshipService = $relationshipService;
    }

    public function find(int $userId): ?UserCompany
    {
        return $this->repository()->find($userId);
    }

    public function findOrFail(int $userId): UserCompany
    {
        return $this->repository()->findOrFail($userId);
    }

    public function getDynamicsRelationship(?UserCompany $userCompany): ?DynamicsTslRelationship
    {
        if ($userCompany === null) {
            return null;
        }
        return $this->relationshipService->getRelationship('UserCompany', $userCompany->id, false);
    }

}
