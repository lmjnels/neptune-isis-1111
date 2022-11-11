<?php

namespace Tests\PHPUnit\Integration;


use App\Domain\Admin\SearchTwitter;
use Foundation\Http\Client\ApiClient;
use Foundation\Http\Client\ApiHandler;
use Foundation\Http\Client\HttpClient;
use Foundation\Http\Client\PendingRequest;
use Foundation\Http\Client\TwitterClient;
use Client\Twitter\Query\TweetSearch;
use Client\Twitter\Query\TwitterQuery;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TwitterClientTest extends TestCase
{

    public function test_client()
    {
        $client = $this->get_client();

        $this->assertInstanceOf(ApiHandler::class, $client);
        $this->assertInstanceOf(HttpClient::class, $client);
        $this->assertInstanceOf(\GuzzleHttp\Client::class, $client);
    }

    public function get_client()
    {
        return new TwitterClient(config('client.twitter'));
    }

    public function test_twitter_client_config()
    {
        $config = config('client.twitter');

        $this->assertArrayHasKey('base_uri', $config);
        $this->assertArrayHasKey('auth_key', $config);
        $this->assertArrayHasKey('auth_secret', $config);
        $this->assertArrayHasKey('auth_token', $config);
    }

    public function test_twittter_get_recent_tweets()
    {
        $twitter = $this->get_client();

        $response = $twitter->withToken(env('TWITTER_AUTH_BEARER'))
            ->get('2/tweets/search/recent?query=from:TwitterDev&tweet.fields=created_at&expansions=author_id&user.fields=created_at');


        $this->assertArrayHasKey('data', $response = (array)$response);

        $data = (array)$response['data'][0];

        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('author_id', $data);
        $this->assertArrayHasKey('text', $data);
    }

    public function test_twittter_tweets_lookup()
    {
        $twitter = $this->get_client();

        $response = $twitter->withToken(env('TWITTER_AUTH_BEARER'))
            ->get('https://api.twitter.com/2/tweets?ids=1228393702244134912,1227640996038684673,1199786642791452673&tweet.fields=created_at&expansions=author_id&user.fields=created_at');

        $this->assertArrayHasKey('data', $response = (array)$response);

        $data = (array)$response['data'][0];

        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('author_id', $data);
        $this->assertArrayHasKey('text', $data);
    }

    public function test_twitter_client_search_tweets()
    {
        $query_str = '"google" -is:retweet (has:media)';
        $query = TweetSearch::create()->allOfTheseWords('"google"')
            ->isNotRetweet()
            ->hasMedia();
        $this->assertSame($query_str, $query->getQuery());

        // check that all default parameters are appended to the query string

        // tweet.fields
        $tweet_fields = 'created_at,conversation_id,geo,lang,public_metrics,attachments,source,context_annotations';
        $this->assertSame($tweet_fields, $query->getTweetFields());

        // expansions
        $expansion_fields = 'author_id,attachments.media_keys,geo.place_id';
        $this->assertSame($expansion_fields, $query->getExpansionFields());

        // user.fields
        $user_fields = 'description,username,location,profile_image_url,verified,url,public_metrics,created_at';
        $this->assertSame($user_fields, $query->getUserFields());

        // media_fields
        $media_fields = 'url,duration_ms,preview_image_url,public_metrics,alt_text,variants';
        $this->assertSame($media_fields, $query->getMediaFields());

        $place_fields = 'id,full_name,country,country_code,geo,name,place_type';
        $this->assertSame($place_fields, $query->getPlaceFields());

        $client = $this->get_client();

        $payload = $client->search_tweets($query);

        $this->assertArrayHasKey('data', $payload = (array)$payload);

        $data = (array)$payload['data'][0];


        $this->assertArrayHasKey('conversation_id', $data);
        $this->assertArrayHasKey('author_id', $data);
        $this->assertArrayHasKey('source', $data);
        $this->assertArrayHasKey('context_annotations', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('text', $data);
        $this->assertArrayHasKey('public_metrics', $data);
        $this->assertArrayHasKey('lang', $data);
    }


    public function test_twitter_save_search_query_results()
    {
        $results = $this->test_twitter_search_query();

        $collection = collect($results);

        $tweets = $collection->get('data');
        $includes = $collection->get('includes');

        $media = collect($includes)->get('media');
        $users = collect($includes)->get('users');

        foreach ($tweets as $key => $data) {
            $media_keys = $data['attachments']['media_keys'];

            $media_key = $media_keys[0];

            $author_id = $data['author_id'];

            $author = collect($users)
                ->where('id', '=', $author_id)->first();

            $media_data = collect($media)->where('media_key', '=', $media_key);

            $debug = [
                //'users' => $users,
                'author_id' => $author_id,
                'author' => $author,

                //'media_key' => $media_key,
                //'media_data' => $media_data,

                //'media' => $media,

                'data' => $data,
            ];


            $attributes = [
                'author_screen_name' => $author['name'],
                'author_handle' => $author['username'],
                'author_id' => $data['author_id'],
                'tweet_id' => $data['id'],
                'content' => $data['text'],
                'conversation_id' => $data['conversation_id'],
                'lang' => $data['lang'],
                'location' => '',
                'retweets' => $data['public_metrics']['retweet_count'],
                'likes' => $data['public_metrics']['like_count'],
                'replies' => $data['public_metrics']['reply_count'],
                'quotes' => $data['public_metrics']['quote_count'],
                'source' => $data['source'],
                'verified' => $author['verified'],
                'listed' => $author['public_metrics']['listed_count'],
                'author_since' => $author['created_at'],
                'author_profile_image_url' => $author['profile_image_url'],
                'author_bio' => $author['description'],
                'author_followers' => $author['public_metrics']['followers_count'],
                'author_following' => $author['public_metrics']['following_count'],
                'posted_at' => $data['created_at'],
            ];

            $query = DB::table('twitter_feed')->insert($attributes);

            dd($query, $attributes, $debug);
        }
    }


    public function test_twitter_search_query()
    {
        // https://api.twitter.com/2/tweets/search/recent?query=cat has:media&tweet.fields=created_at,conversation_id,geo,lang,referenced_tweets,public_metrics,attachments,source&expansions=author_id&user.fields=description,username,location,profile_image_url,verified,url,public_metrics

        $auth = 'AAAAAAAAAAAAAAAAAAAAAPmWcwEAAAAAJ7HxSJHOgZWCbJIOz%2BeuK9gRDRY%3DCIxn0I616aCij7EyEKvKzUstalHy7RICnFwUy78sohk4O7jOdY';

        // query
        $query_str = 'google -is:retweet (has:media)';
        $query = TweetSearch::create()->allOfTheseWords('google')
            ->isNotRetweet()
            ->hasMedia();
        $this->assertSame($query_str, $query->getQuery());

        // tweet.fields
        $tweet_fields = 'created_at,conversation_id,geo,lang,public_metrics,attachments,source';
        $tweet = [
            'created_at', 'conversation_id', 'geo', 'lang',
            'public_metrics', 'attachments', 'source'
        ];
        $query = $query->withTweetFields($tweet);

        $this->assertSame($tweet_fields, $query->getTweetFields());

        // expansions
        $expansion_fields = "author_id,attachments.media_keys,geo.place_id";
        $expansions = [
            'author_id', 'attachments', 'media_keys', 'geo', 'place_id'
        ];
        $query = $query->withExpansionFields($expansions);
        $this->assertSame($expansion_fields, $query->getExpansionFields());

        // user.fields
        $user_fields = 'description,username,location,profile_image_url,verified,url,public_metrics,created_at';
        $user = [
            'description', 'username', 'location', 'profile_image_url', 'verified', 'url',
            'public_metrics', 'created_at'
        ];
        $query = $query->withUserFields($user);
        $this->assertSame($user_fields, $query->getUserFields());

        // media_fields
        $media_fields = 'url,duration_ms,preview_image_url,public_metrics,alt_text,variants';
        $media = [
            'url', 'duration_ms', 'preview_image_url', 'public_metrics', 'alt_text', 'variants'
        ];
        $query = $query->withMediaFields($media);
        $this->assertSame($media_fields, $query->getMediaFields());

        $place_fields = 'id,full_name,country,country_code,geo,name,place_type';
        $place = ['id', 'full_name', 'country', 'country_code', 'geo', 'name', 'place_type'];
        $query = $query->withPlaceFields($place);


        $query = TweetSearch::create();

        $query_fields = [
            'query' => $query->allOfTheseWords('google')
                ->isNotRetweet()
                ->hasMedia()
                ->getQuery(),
            'tweet.fields' => $query->withTweetFields($tweet)->getTweetFields(),
            'user.fields' => $query->withUserFields($user)->getUserFields(),
            'media.fields' => $query->withMediaFields($media)->getMediaFields(),
//            'place.fields'  => $query->withPlaceFields($place)->getPlaceFields(),
        ];

        $query_array = array();

        foreach ($query_fields as $key => $key_value) {
            $query_array[] = $key . '=' . $key_value;
        }
        $http_query_string = implode('&', $query_array);

        $base_uri = 'https://api.twitter.com/';

        $base_path = '2/tweets/search/recent';

        $endpoint = $base_uri . $base_path . '?' . $http_query_string;

        $request = (new PendingRequest());

        $response = $request->withToken($auth)
            ->get($endpoint);

        $this->assertArrayHasKey('data', $payload = (array)$response);

        $data = (array)$payload['data'][0];

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('source', $data);
        $this->assertArrayHasKey('text', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('conversation_id', $data);
        $this->assertArrayHasKey('attachments', $data);
        $this->assertArrayHasKey('public_metrics', $data);
        $this->assertArrayHasKey('lang', $data);
    }

    public function test_twitter_search_query_with_defaults()
    {
        $auth = 'AAAAAAAAAAAAAAAAAAAAAPmWcwEAAAAAJ7HxSJHOgZWCbJIOz%2BeuK9gRDRY%3DCIxn0I616aCij7EyEKvKzUstalHy7RICnFwUy78sohk4O7jOdY';

        $query = TweetSearch::create();

        $query_fields = [
            'query' => $query->allOfTheseWords('google')
                ->isNotRetweet()
                ->hasMedia()
                ->getQuery(),
            'tweet.fields' => $query->getTweetFields(),
            'user.fields' => $query->getUserFields(),
            'media.fields' => $query->getMediaFields(),
            'place.fields' => $query->getPlaceFields(),
        ];

        $query_array = [];

        foreach ($query_fields as $key => $key_value) {
            $query_array[] = $key . '=' . $key_value;
        }
        $http_query_string = implode('&', $query_array);

        $base_uri = 'https://api.twitter.com/';

        $base_path = '2/tweets/search/recent';

        $endpoint = $base_uri . $base_path . '?' . $http_query_string;

        $request = (new PendingRequest());

        $response = $request->withToken($auth)
            ->get($endpoint);

        $this->assertArrayHasKey('data', $payload = (array)$response);

        $data = (array)$payload['data'][0];

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('source', $data);
        $this->assertArrayHasKey('text', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('conversation_id', $data);
        $this->assertArrayHasKey('attachments', $data);
        $this->assertArrayHasKey('public_metrics', $data);
        $this->assertArrayHasKey('lang', $data);
    }

    public function test_updateLocalRepositories()
    {
        $collection = [
            'all' => 'war weapons',
            'any' => '', //
            'exact' => '', //
            'none' => '', //
            'from' => 'bbc, rtnews, nbc',
            'to' => '', //
            'mentioning' => '', //
            'raw' => ' (has:media)',
        ];

        $arr = collect($collection)->toArray();

        $searchTwitter = SearchTwitter::fromArray($arr);

        $searchResults = $searchTwitter->search_tweets();

        $searchTwitter->updateLocalRepositories($searchTwitter, $searchResults);
    }

    public function test_addTwitterRule()
    {
        $collection = [
            'all' => 'war weapons',
            'any' => '', //
            'exact' => '', //
            'none' => '', //
            'from' => '',
            'to' => '', //
            'mentioning' => '', //
            'raw' => ' (has:media)',
        ];

        $arr = collect($collection)->toArray();

        $tag = 'War Weapons';

        $searchTwitter = SearchTwitter::fromArray($arr);

        $searchResults = $searchTwitter->add_rule($tag);

        $words = $searchResults->words;
        $people = $searchResults->people;

        dd($words, $people);
    }
}
