<?php namespace GeneaLabs\LaravelModelCaching\Tests\Integration\CachedBuilder;

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Comment;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Post;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedComment;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPost;
use GeneaLabs\LaravelModelCaching\Tests\IntegrationTestCase;
use Illuminate\Database\Eloquent\Builder;

class WhereHasMorphTest extends IntegrationTestCase
{
    public function testWithSingleMorphModel()
    {
        $comments = (new Comment)
            ->whereHasMorph('commentable', Post::class)
            ->get();
        $uncachedComments = (new UncachedComment())
            ->whereHasMorph('commentable', Post::class)
            ->get();

        $cacheResults = $this->cache()->tags([
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturescomment",
        ])
            ->get(sha1(
                "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:comments:genealabslaravelmodelcachingtestsfixturescomment-nested-nested-comments.commentable_type_=_GeneaLabs\LaravelModelCaching\Tests\Fixtures\Post-exists-and_comments.commentable_id_=_posts.id"
            ))['value'];

        $this->assertCount(5, $comments);
        $this->assertEquals($comments->pluck("id"), $uncachedComments->pluck("id"));
        $this->assertEquals($uncachedComments->pluck("id"), $cacheResults->pluck("id"));
    }

    public function testWithMultipleMorphModels()
    {
        $comments = (new Comment)
            ->whereHasMorph('commentable', [Post::class, UncachedPost::class])
            ->get();
        $uncachedComments = (new UncachedComment())
            ->whereHasMorph('commentable', [Post::class, UncachedPost::class])
            ->get();

        $cacheResults = $this->cache()->tags([
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturescomment",
        ])
            ->get(sha1(
                "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:comments:genealabslaravelmodelcachingtestsfixturescomment-nested-nested-comments.commentable_type_=_GeneaLabs\LaravelModelCaching\Tests\Fixtures\Post-exists-and_comments.commentable_id_=_posts.id-nested-comments.commentable_type_=_GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPost-exists-and_comments.commentable_id_=_posts.id"
            ))['value'];

        $this->assertCount(10, $comments);
        $this->assertEquals($comments->pluck("id"), $uncachedComments->pluck("id"));
        $this->assertEquals($uncachedComments->pluck("id"), $cacheResults->pluck("id"));
    }

    public function testWithMultipleMorphModelsWithClosure()
    {
        $comments = (new Comment)
            ->whereHasMorph('commentable', [Post::class, UncachedPost::class], function (Builder $query) {
                return $query->where('subject', 'like',  '%uncached post');
            })
            ->get();
        $uncachedComments = (new UncachedComment())
            ->whereHasMorph('commentable', [Post::class, UncachedPost::class], function (Builder $query) {
                return $query->where('subject', 'like',  '%uncached post');
            })
            ->get();

        $cacheResults = $this->cache()->tags([
            "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:genealabslaravelmodelcachingtestsfixturescomment",
        ])
            ->get(sha1(
                "genealabs:laravel-model-caching:testing:{$this->testingSqlitePath}testing.sqlite:comments:genealabslaravelmodelcachingtestsfixturescomment-nested-nested-comments.commentable_type_=_GeneaLabs\LaravelModelCaching\Tests\Fixtures\Post-exists-and_comments.commentable_id_=_posts.id-subject_like_%uncached post-nested-comments.commentable_type_=_GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedPost-exists-and_comments.commentable_id_=_posts.id-subject_like_%uncached post"
            ))['value'];

        $this->assertCount(5, $comments);
        $this->assertEquals($comments->pluck("id"), $uncachedComments->pluck("id"));
        $this->assertEquals($uncachedComments->pluck("id"), $cacheResults->pluck("id"));
    }
}
