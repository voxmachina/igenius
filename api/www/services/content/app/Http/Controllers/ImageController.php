<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Request;
use App\Http\Middleware\FileCache;

class ImageController extends Controller
{
    private static function compress($source, $destination, $quality)
    {
        $info = getimagesize($source);

        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
        }

        imagejpeg($image, $destination, $quality);

        return $destination;
    }

    public static function getImage($filename)
    {
        $filename = str_replace(":", "/", $filename);
        $filenameTokens = explode("/", $filename);
        $fileUrl  = "https://" . $filename;

        $systemFilename = $filenameTokens[count($filenameTokens) - 1];
        
        $cacheFile = dirname(__FILE__) . "/../../../storage/" . $systemFilename;

        if (!FileCache::cached($cacheFile)) {
            $content = file_get_contents($fileUrl);
            file_put_contents($cacheFile, $content);
            self::compress($cacheFile, $cacheFile, 75);
        } else {
            $content = file_get_contents($cacheFile);
        }

        $info = getimagesize($cacheFile);

        return response($content)
            ->header('Content-Type', $info['mime'])
            ->header('Pragma', 'public')
            ->header('Content-Disposition', 'inline; filename="'.$systemFilename.'"');
    }
}
