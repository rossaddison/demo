<?php

declare(strict_types=1);

namespace App\User;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Cycle\Writer\EntityWriter;
use Yiisoft\Data\Reader\Sort;

final class UserRepository extends Select\Repository implements IdentityWithTokenRepositoryInterface, IdentityRepositoryInterface
{
    public function __construct(
        Select $select,
        private ORMInterface $orm,
        private EntityWriter $entityWriter,
    ) {
        parent::__construct($select);
    }

    /**
     * @psalm-return EntityReader<array-key, User>
     */
    public function findAllOrderByLogin(): EntityReader
    {
        /** @psalm-var EntityReader<array-key, User> */
        return (new EntityReader($this->select()))
            ->withSort(
                Sort::only(['login'])->withOrderString('login')
            );
    }

    public function findIdentity(string $id): ?IdentityInterface
    {
        return $this->findIdentityBy('id', $id);
    }

    public function findIdentityByToken(string $token, string $type = null): ?IdentityInterface
    {
        return $this->findIdentityBy('token', $token);
    }

    public function findByLogin(string $login): ?IdentityInterface
    {
        return $this->findIdentityBy('login', $login);
    }

    public function save(IdentityInterface $user): void
    {
        $this->entityWriter->write([$user]);
    }

    private function findIdentityBy(string $field, string $value): ?IdentityInterface
    {
        /**
         * @var $identity IdentityInterface|null
         */
        return $this->findOne([$field => $value]);
    }
}
