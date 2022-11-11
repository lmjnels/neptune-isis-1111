<?php


namespace App\Package\Service;

use App\Package\Repositories\RepositoryInterface;
use App\Package\Validators\BaseValidator;

/**
 * Class Service
 *
 * @package \App\Package\Service
 */
class Service
{
    /**
     * @var RepositoryInterface|null $repository
     */
    protected $repository = null;

    /**
     * @var null
     */
    protected ?BaseValidator $validator = null;

    /**
     * @return RepositoryInterface|null
     */
    public function getRepository(): ?RepositoryInterface
    {
        return $this->repository ?? null;
    }

    /**
     * @return RepositoryInterface|null
     */
    public function repository(): ?RepositoryInterface
    {
        return $this->getRepository();
    }

    /**
     * @return BaseValidator|null
     */
    public function getValidator(): ?BaseValidator
    {
        return $this->validator ?? null;
    }

    /**
     * @return BaseValidator|null
     */
    public function validator(): ?BaseValidator
    {
        return $this->getValidator();
    }
}