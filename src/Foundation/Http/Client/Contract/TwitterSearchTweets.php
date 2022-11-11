<?php


namespace Foundation\Http\Client\Contract;


use Client\Twitter\Query\TweetSearch;

interface TwitterSearchTweets
{
    public const SEARCH_RECENT = '2/tweets/search/recent';

    public const SEARCH_ALL = '2/tweets/search/all';

    public const STREAM_RULE = '2/tweets/search/stream/rules';


    public function search_tweets(TweetSearch $criteria);

    public function full_archive_search(TweetSearch $criteria);

    public function add_rule(TweetSearch $criteria, $tag);
}
