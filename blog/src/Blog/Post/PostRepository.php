<?php

declare(strict_types=1);

namespace App\Blog\Post;

use App\Blog\Entity\Post;
use Cycle\ORM\Select;
use DateTimeImmutable;
use DateTimeInterface;
use Throwable;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Cycle\Writer\EntityWriter;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

/**
 * @template TEntity of Post
 * @extends Select\Repository<TEntity>
 */
final class PostRepository extends Select\Repository
{
    public function __construct(private EntityWriter $entityWriter, Select $select)
    {
        parent::__construct($select);
    }

    /**
     * Get posts without filter with preloaded Users and Tags.
     *
     * @psalm-return DataReaderInterface<int, Post>
     */
    public function findAllPreloaded(): DataReaderInterface
    {
        $query = $this
            ->select()
            ->load(['user', 'tags']);

        return $this->prepareDataReader($query);
    }

    /**
     * @psalm-return DataReaderInterface<int, Post>
     */
    public function findByTag($tagId): DataReaderInterface
    {
        $query = $this
            ->select()
            ->where(['tags.id' => $tagId])
            ->load('user', ['method' => Select::SINGLE_QUERY]);

        return $this->prepareDataReader($query);
    }

    public function fullPostPage(string $slug): ?Post
    {
        $query = $this
            ->select()
            ->where(['slug' => $slug])
            ->load('user', ['method' => Select::SINGLE_QUERY])
            ->load(['tags'])
            // force loading in single query with comments
            ->load('comments.user', ['method' => Select::SINGLE_QUERY])
            ->load('comments', ['method' => Select::OUTER_QUERY]);

        return  $query->fetchOne();
    }

    public function getMaxUpdatedAt(): DateTimeInterface
    {
        return new DateTimeImmutable($this
                ->select()
                ->max('updated_at') ?? 'now');
    }

    public function findBySlug(string $slug): ?Post
    {
        return $this
            ->select()
            ->where(['slug' => $slug])
            ->fetchOne();
    }

    /**
     * @throws Throwable
     */
    public function save(Post $post): void
    {
        $this->entityWriter->write([$post]);
    }

    private function prepareDataReader($query): EntityReader
    {
        return (new EntityReader($query))->withSort(
            Sort::only(['id', 'title', 'public', 'updated_at', 'published_at', 'user_id'])
                ->withOrder(['published_at' => 'desc'])
        );
    }
}
