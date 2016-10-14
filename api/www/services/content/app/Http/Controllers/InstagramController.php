<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Request;
use App\Http\Middleware\FileCache;

class InstagramController extends Controller
{
    /**
     * Gets Medium data
     * @return mixed
     */
    public static function getData()
    {
        $cacheFile = dirname(__FILE__) . "/../../../storage/instagram.json";

        if (!FileCache::cached($cacheFile)) {
          $url = "https://api.instagram.com/v1/users/".$_ENV['INSTAGRAM_USER_ID']."/media/recent/?access_token=".$_ENV['INSTAGRAM_ACCESS_TOKEN'] . "&count=3";
          $data = Request::get($url);
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
        $data = json_decode($data);
        $posts = $data->data;
        $content = [];
        $index = 0;
        $filename = "";

        foreach($posts as $post) {
            if ($index > 2) {
                break;
            }

            $filename = substr($post->images->standard_resolution->url, 8);
            $filenameTokens = explode("?", $filename);
            $filename = "/services/content/public/img/" . str_replace("/", ":", $filenameTokens[0]);

            if ($_ENV['APP_ENV'] === 'local') {
                $filename = "http://localhost:8080" . $filename;
            } else {
                $filename = "/api/www" . $filename; 
            } 

            $content[$index] = [
                    'id' => $post->id,
                    'title' => '',
                    'subTitle' => '',
                    'url' => $post->link,
                    'createdAt' => date('d M Y', $post->created_time),
                    'thumbnail' => $filename,
            ];

            if ($post->caption !== null && $post->caption->text !== null) {
                $content[$index]['title'] = $post->caption->text;
            }

            if ($post->location !== null && $post->location->name !== null) {
                $content[$index]['subTitle'] = $post->location->name;
            }

            $index++;
        }

        return $content;
    }
}
