<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Request;
use App\Http\Middleware\FileCache;

class ImageController extends Controller
{
    public static function optimize($file, $w, $mime = 'image/jpeg')
    {
        list($width, $height) = getimagesize($file);
        $newwidth = $w;
        $newheight = $w * $height / $width;

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

        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($dst, $file, 75);
                break;
            case 'image/png':
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                imagepng($dst, $file, 5);
                break;
            case 'image/bmp':
                imagewbmp($dst, $file);
                break;
            case 'image/gif':
                imagegif($dst, $file);
                break;
        }

        imagedestroy($dst);
    }

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
            $info = getimagesize($cacheFile);
            self::optimize($cacheFile, $info[0]/2, $info['mime']);
        } else {
            $info = getimagesize($cacheFile);
        }

        // always serve the cache optimized version
        $content = file_get_contents($cacheFile);

        return response($content)
            ->header('Content-Type', $info['mime'])
            ->header('Pragma', 'public')
            ->header('Content-Disposition', 'inline; filename="'.$systemFilename.'"');
    }
}
