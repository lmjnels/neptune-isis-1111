<?php

namespace Foundation\Http\Client;

use Foundation\Http\Client\Contract\Twitter;
use Client\Twitter\Query\TweetSearch;
use Foundation\Http\Client\Response;

final class TwitterClient extends HttpClient implements Twitter
{
    private $auth = 'AAAAAAAAAAAAAAAAAAAAAPmWcwEAAAAAJ7HxSJHOgZWCbJIOz%2BeuK9gRDRY%3DCIxn0I616aCij7EyEKvKzUstalHy7RICnFwUy78sohk4O7jOdY';

    public function getAuthBearerToken()
    {
        return $this->post('/oauth2/token',['grant_type' => 'client_credentials']);
    }

    public function exampleRequest($token){
        return $this
            ->withToken(env('TWITTER_AUTH_BEARER', $this->auth))
            ->get('/1.1/statuses/user_timeline.json?count=100&screen_name=twitterapi');
    }

    /**
     * @throws \Exception
     */
    public function search_tweets(TweetSearch $twitter)
    {
        $url = Twitter::SEARCH_RECENT.'?'.$twitter->getQueryParams();

        $response = $this
            ->withToken($this->auth)
            ->get($url);

        $data = collect($response);

        if(isset($data->get('meta')->next_token) && $nextToken = $data->get('meta')->next_token){
            // @todo: get next paginated page if it exists, most queries results show null because max results are set to 100
        }

        return $response;
    }

    public function full_archive_search(TweetSearch $twitter)
    {
        $url = Twitter::SEARCH_ALL.'?'.$twitter->getQueryParams();

        return $this
            ->withToken(env('TWITTER_AUTH_BEARER', $this->auth))
            ->get($url);
    }

    public function add_rule(TweetSearch $twitter, $tag)
    {
        $url = Twitter::STREAM_RULE.'?'.$twitter->getQueryParams();

        $rule = $twitter->buildAddRulePayload($tag);

        dd($rule);

        return $this
            ->withToken(env('TWITTER_AUTH_BEARER', $this->auth))
            ->post($url, $rule);
    }

    /**
     * @inheritDoc
     */
    protected function dispatchResponseReceivedEvent(Response $response)
    {
        // TODO: Implement dispatchResponseReceivedEvent() method.
    }

    /**
     * @inheritDoc
     */
    protected function dispatchConnectionFailedEvent()
    {
        // TODO: Implement dispatchConnectionFailedEvent() method.
    }

    private function createAddRulePayload($query)
    {
        return [
            'add'        => [
                'value' => $query,
                'tag' => uniqid('', true),
            ]
        ];
    }
}
