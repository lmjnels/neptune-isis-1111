<?php

namespace Tests\PHPUnit\Integration;


use App\Http\Client\ApiClient;
use App\Http\Client\TwitterClient;
use Tests\PHPUnit\TestCase;
use App\Domain\Admin\SearchTwitter;

class TwitterIntegrationTest extends TestCase
{
    public function test_search_twitter_form_handle_with_array()
    {
        $collection = [
            'all' =>    'war weapons',
            'any' =>    '', //
            'exact' =>    '', //
            'none' =>    '', //
            'from' =>    'bbc, rtnews, nbc',
            'to' =>    '', //
            'mentioning' =>    '', //
            'raw' =>    ' (has:media)',
        ];

        $arr = collect($collection)->toArray();


        $twitter = SearchTwitter::fromArray($arr);

        $qs = $twitter->createQueryString();

        $twitter = new TwitterClient(config('client.twitter'));

        $payload = (array)$twitter->search_tweets($qs);

        $tweets = (array)collect($payload['data'])->toArray()[0];

        $this->assertArrayHasKey('author_id', $tweets);
        $this->assertArrayHasKey('created_at', $tweets);
        $this->assertArrayHasKey('lang', $tweets);
        $this->assertArrayHasKey('source', $tweets);
        $this->assertArrayHasKey('public_metrics', $tweets);
        $this->assertArrayHasKey('id', $tweets);
        $this->assertArrayHasKey('conversation_id', $tweets);
        $this->assertArrayHasKey('text', $tweets);
        $this->assertArrayHasKey('attachments', $tweets);
        $this->assertArrayHasKey('attachments', $tweets);

        $includes = $payload['includes'];

        $users  = (array)collect($includes->users)->toArray()[0];

        if($users){
            $this->assertArrayHasKey('location', $users);
            $this->assertArrayHasKey('public_metrics', $users);
            $this->assertArrayHasKey('username', $users);
            $this->assertArrayHasKey('profile_image_url', $users);
            $this->assertArrayHasKey('verified', $users);
            $this->assertArrayHasKey('description', $users);
            $this->assertArrayHasKey('name', $users);
            $this->assertArrayHasKey('created_at', $users);
            $this->assertArrayHasKey('url', $users);
            $this->assertArrayHasKey('id', $users);
        }

        $media  = (array)collect($includes->media)->toArray()[0];

        if($media){
            $this->assertArrayHasKey('type', $media);
            $this->assertArrayHasKey('url', $media);
            $this->assertArrayHasKey('media_key', $media);
        }






    }
}
