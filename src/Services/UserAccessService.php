<?php
/**
 * Copyright (c) 1989-2019. TS Lombard and its licensors. All Rights Reserved.
 * TS Lombard, the TS Lombard logo, and TS Lombard App are either registered
 * trademarks or trademarks of TS Lombard in the United Kingdom and/or other
 * countries. All other trademarks are the property of their respective owners.
 */

namespace Service;

use App\Http\Controllers\Auth\ForgotPasswordController;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Model\UserAccess;
use RepositoryInterface\UserAccessRepositoryInterface;

class UserAccessService extends BaseService
{
    public function __construct(UserAccessRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function findUserByLoginName(string $username): ?UserAccess
    {
        return $this->repository()->findUserByLoginName($username);
    }

    public function findEmailByUsername(string $username): ?string
    {
        return $this->repository()->findEmailByUsername($username);
    }

    /**
     * @param UserAccess $userAccess
     * @param string $signInName
     * @param string $password
     * @param bool $rememberMe
     *
     * @return string - redirect url
     * @throws \Exception
     */
    public function authenticateUser(UserAccess $userAccess, string $signInName, string $password, bool $rememberMe = false): string
    {
        $userAccessPassword = $userAccess->password ?? NULL;

        //Authentication passed
        if ($userAccess !== null && $this->passwordMatches($password, $userAccessPassword)) {
            if ($rememberMe) {
                app('SignInNameCookie')->queueMakeCookie($signInName);
            } else {
                app('SignInNameCookie')->queueForgetCookie();
            }

            Auth::login($userAccess->user, $rememberMe);

            $redirectUrl = app('RedirectToPageCookie')->getAndForgetCookie();

            return $redirectUrl ?: route('home');
        }

        abort_json('Your credentials were incorrect. Please try again');
    }

    /**
     * @param $value
     * @param $hashedValue
     *
     * @return bool
     */
    private function passwordMatches($value, $hashedValue): bool
    {
        return Hash::check($value, $hashedValue);
    }

    public function setSubDomainSharedCookie(int $userId, string $userSecurityToken): void
    {
        $jsonCookie = json_encode([
            'id'    => $userId,
            'token' => $userSecurityToken
        ]);

        app('SubDomainSharedCookie')->queueMakeCookie($jsonCookie);
    }

    public function sentSetPasswordEmail(string $email): JsonResponse
    {
        $request = request();
        $request->merge(['UserDetails_Email' => $email]);

        return app(ForgotPasswordController::class)->sendSetLinkEmail($request);
    }
}
