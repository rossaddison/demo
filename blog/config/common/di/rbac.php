<?php

declare(strict_types=1);

use Yiisoft\Access\AccessCheckerInterface;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Php\AssignmentsStorage;
use Yiisoft\Rbac\Php\ItemsStorage;

/**
 * @see $params['yiisoft/aliases']['aliases']['@root']
 * @var array $params
 * @var string $root
 */

$root = array_search('@root', $params);
return [
    ItemsStorageInterface::class => [
        'class' => ItemsStorage::class,
        '__construct()' => [
            'directory' => $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'rbac',
        ],
    ],
    AssignmentsStorageInterface::class => [
        'class' => AssignmentsStorage::class,
        '__construct()' => [
            'directory' => $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'rbac',
        ],
    ],
    AccessCheckerInterface::class => Manager::class,
];
