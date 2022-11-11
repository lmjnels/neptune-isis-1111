<?php


namespace Service;

use RepositoryInterface\BaseRepositoryInterface;
use Validator\BaseValidator;

/**
 * Class BaseService
 *
 * @package Service
 */
class BaseService
{
    /**
     * @var null
     */
    protected $repository = null;

    /**
     * @var null
     */
    protected $validator = null;

    /**
     * @return BaseRepositoryInterface|null
     */
    public function getRepository(): ?BaseRepositoryInterface
    {
        return $this->repository ?? null;
    }

    /**
     * @return BaseRepositoryInterface|null
     */
    public function repository(): ?BaseRepositoryInterface
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
