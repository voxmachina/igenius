<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Request;
use App\Http\Middleware\FileCache;

class ImageController extends Controller
{
    public static function optimize($file, $mime = 'image/jpeg')
    {
        switch ($mime) {
            case 'image/jpeg':
                $src = imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $src = imagecreatefrompng($file);
                break;
            case 'image/bmp':
                $src = imagecreatefromwbmp($file);
                break;
            case 'image/gif':
                $src = imagecreatefromgif($file);
                break;
        }

        imageinterlace($src, 1);
        imagejpeg($src, $file, 75);
    }

    public static function getImage($filename)
    {
        $filename = str_replace(":", "/", $filename);
        $filenameTokens = explode("/", $filename);
        $fileUrl  = "https://" . $filename;

        $systemFilename = $filenameTokens[count($filenameTokens) - 1];
        
        $cacheFile = dirname(__FILE__) . "/../../../storage/" . $systemFilename . ".cache.jpeg";

        if (!FileCache::cached($cacheFile)) {
            $content = file_get_contents($fileUrl);
            file_put_contents($cacheFile, $content);
            $info = getimagesize($cacheFile);
            self::optimize($cacheFile, $info['mime']);
        } else {
            $info = getimagesize($cacheFile);
        }

        // always serve the cache optimized version
        $content = file_get_contents($cacheFile);

        return response($content)
            ->header('Content-Type', 'image/jpeg')
            ->header('Pragma', 'public')
            ->header('Content-Disposition', 'inline; filename="'.$systemFilename.'"');
    }
}
