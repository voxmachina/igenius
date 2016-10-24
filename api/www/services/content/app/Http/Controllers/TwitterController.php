<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Request;
use App\Http\Middleware\FileCache;
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterController extends Controller
{
    /**
     * Gets Medium data
     * @return mixed
     */
    public static function getData()
    {
        $consumerKey = $_ENV['TWITTER_CONSUMER_TOKEN'];
        $consumerSecret = $_ENV['TWITTER_CONSUMER_SECRET'];
        $consumerAccessToken = $_ENV['TWITTER_ACCESS_TOKEN'];
        $consumerAccessSecret = $_ENV['TWITTER_ACCESS_SECRET'];
        $connection = new TwitterOAuth($consumerKey, $consumerSecret, $consumerAccessToken, $consumerAccessSecret);
        $data = $connection->get("statuses/user_timeline", ["count" =>3]);
        $cacheFile = dirname(__FILE__) . "/../../../storage/twitter.json";

        if (!FileCache::cached($cacheFile)) {
          $content = self::parseData($data);
          file_put_contents($cacheFile, json_encode($content));
        } else {
          $content = json_decode(file_get_contents($cacheFile), true);
        }

        return response()->json($content);
    }

    /**
     * Parses medium json data
     * @param  mixed $data
     * @return array
     */
    private static function parseData($data)
    {
        $posts = $data;
        $content = [];
        $index = 0;

        foreach($posts as $post) {
            if ($index > 2) {
                break;
            }

            $content[$index] = [
                    'id' => $post->id,
                    'title' => $post->text,
                    'url' => "https://twitter.com/atomzboy/status/"  . $post->id,
                    'createdAt' => $post->created_at,
            ];

            $index++;
        }

        return $content;
    }
}
