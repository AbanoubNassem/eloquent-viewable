<?php

declare(strict_types=1);

namespace CyrildeWit\EloquentViewable\Tests;

use Session;
use Carbon\Carbon;
use CyrildeWit\EloquentViewable\ViewSessionHistory;
use CyrildeWit\EloquentViewable\Tests\TestClasses\Models\Post;

class ViewSessionHistoryTest extends TestCase
{
    /** @test */
    public function push_can_add_an_item()
    {
        $post = factory(Post::class)->create();
        $viewHistory = app(ViewSessionHistory::class);
        $postSessionKey = config('eloquent-viewable.session.key').'.'.strtolower(str_replace('\\', '-', $post->getMorphClass())).'.'.$post->getKey();

        $this->assertFalse(Session::has($postSessionKey));

        $viewHistory->push($post, Carbon::tomorrow());

        $this->assertTrue(Session::has($postSessionKey));
    }

    /** @test */
    public function push_can_add_an_item_with_collection()
    {
        $post = factory(Post::class)->create();
        $viewHistory = app(ViewSessionHistory::class);
        $postSessionKey = config('eloquent-viewable.session.key').'.'.strtolower(str_replace('\\', '-', $post->getMorphClass())).':some-collection'.'.'.$post->getKey();

        $this->assertFalse(Session::has($postSessionKey));

        $viewHistory->push($post, Carbon::tomorrow(), 'some-collection');

        $this->assertTrue(Session::has($postSessionKey));
    }

    /** @test */
    public function push_does_not_add_an_item_if_already_added()
    {
        $post = factory(Post::class)->create();
        $postBaseKey = config('eloquent-viewable.session.key').'.'.strtolower(str_replace('\\', '-', $post->getMorphClass()));
        $viewHistory = app(ViewSessionHistory::class);

        $viewHistory->push($post, Carbon::tomorrow());
        $viewHistory->push($post, Carbon::tomorrow());
        $viewHistory->push($post, Carbon::tomorrow());

        $this->assertCount(1, Session::get($postBaseKey));
    }

    /** @test */
    public function it_can_forget_expired_views()
    {
        $post = factory(Post::class)->create();
        $postNamespacKey = config('eloquent-viewable.session.key').'.'.strtolower(str_replace('\\', '-', $post->getMorphClass()));
        $viewHistory = app(ViewSessionHistory::class);

        $viewHistory->push($post, Carbon::today());
        $viewHistory->push($post, Carbon::today()->addHours(1));
        $viewHistory->push($post, Carbon::today()->addHours(2));

        Carbon::setTestNow(Carbon::tomorrow());

        $viewHistory->push($post, Carbon::today()->addHours(2));

        $this->assertCount(1, Session::get($postNamespacKey));
    }

    /** @test */
    public function it_can_forget_expired_views_with_collection()
    {
        $post = factory(Post::class)->create();
        $postNamespacKey = config('eloquent-viewable.session.key').'.'.strtolower(str_replace('\\', '-', $post->getMorphClass()));
        $viewHistory = app(ViewSessionHistory::class);

        $viewHistory->push($post, Carbon::today());
        $viewHistory->push($post, Carbon::today(), 'some-collection');
        $viewHistory->push($post, Carbon::today()->addHours(1));
        $viewHistory->push($post, Carbon::today()->addHours(2));
        $viewHistory->push($post, Carbon::today()->addHours(2), 'some-collection');

        Carbon::setTestNow(Carbon::tomorrow());

        $viewHistory->push($post, Carbon::today()->addHours(2));

        $this->assertCount(1, Session::get($postNamespacKey));
    }
}
