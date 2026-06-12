<?php

namespace NASANICORE\Helpers;

class Func
{
    public function getCurrentPageURLAMP()
    {
        $pageURL = 'http';
        if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        $pageURL .= $_SERVER["SERVER_NAME"] . "/amp" . $_SERVER["REQUEST_URI"];
        $urlpos = strpos($pageURL, "?p");
        $pageURL = ($urlpos) ? explode("?p=", $pageURL) : explode("&p=", $pageURL);

        return $pageURL[0];
    }

    public function getCurrentPageURL_NOAMP()
    {
        $pageURL = 'http';
        if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $urlpos = strpos($pageURL, "?p");
        $pageURL = ($urlpos) ? explode("?p=", $pageURL) : explode("&p=", $pageURL);
        $pageURL = explode("/amp", $pageURL[0]);

        return $pageURL[0] . $pageURL[1];
    }

    public function getCurrentUrl()
    {
        $pageURL = 'http';
        if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        return $pageURL;
    }

    public function genAMPVideo($id)
    {
        return '<amp-youtube data-videoid="' . $id . '" layout="responsive" width="480" height="270"></amp-youtube>';
    }

    public function LinkConvert($str)
    {
        $pattern = '|<a.+?href\="(.+?)".*?>(.+?)</a>|i';

        return preg_replace_callback($pattern, function ($matches) {
            $matches[2] = strip_tags($matches[0]);
            $link = $matches[1];
            $text = $matches[2];
            return "<a href='$link'>$text</a>";
        }, $str);
    }

    public function VidConvert($iframeCode, $check = false)
    {
        $pattern = '/<iframe\s+.*?\s+src=(".*?").*?<\/iframe>/';
        if ($check) {
            return preg_match_all($pattern, $iframeCode, $matches);
        }
        return preg_replace_callback($pattern, function ($matches) {
            $youtubeUrl = $matches[1];
            $youtubeUrl = trim($youtubeUrl, '"');
            $youtubeUrl = trim($youtubeUrl, "'");
            preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $youtubeUrl, $videoId);
            return $youtubeVideoId = isset($videoId[1]) ? $this->genAMPVideo($videoId[1]) : "";
        }, $iframeCode);
    }

    public function MapConvert($iframeCode)
    {
        $output = htmlspecialchars_decode($iframeCode);
        preg_match_all('/<iframe[^>]+src="([^"]+)"/', $output, $match);
        $urls = $match[1];
        return '<amp-iframe src="' . $urls[0] . '" width="600" height="400" title="Bản đồ" layout="responsive" sandbox="allow-scripts allow-same-origin allow-popups" frameborder="0" >
                        </amp-iframe>';
    }

    public function ampify($html)
    {
        $html = $this->LinkConvert($html);
        $html = $this->VidConvert($html);
        $html = str_ireplace(array('<img', '<video', '/video>', '<audio', '/audio>', '<iframe', '/iframe>'), array('<amp-img', '<amp-video', '/amp-video>', '<amp-audio', '/amp-audio>', '<amp-iframe', '/amp-iframe>'), $html);
        $html = preg_replace_callback('/<amp-img([^>]*)\/?>/i', [$this, 'ampifyImageTag'], $html);
        $html = preg_replace('/<span(.*?)\/?>/', '<span>', $html);
        $html = preg_replace('/<h3(.*?)\/?>/', '<h3>', $html);
        $html = preg_replace('/<p(.*?)\/?>/', '<p>', $html);
        $html = preg_replace('/<h2(.*?)\/?>/', '<h2>', $html);
        $html = preg_replace('/<table(.*?)\/?>/', '<table>', $html);
        $html = preg_replace('/<table(.*?)\/?>/', '<table>', $html);
        $html = preg_replace('/<td(.*?)\/?>/', '<td>', $html);
        $html = preg_replace('/<tr(.*?)\/?>/', '<tr>', $html);
        $html = preg_replace('/<a style(.*?)\/?>/', '</a>', $html);
        $html = preg_replace('/alt=".*?"/', '', $html);
        $html = preg_replace('/longdesc=".*?"/', '', $html);
        $html = preg_replace('/<strong(.*?)\/?>/', '<strong>', $html);
        $html = strip_tags($html, '<h1><h2><h3><h4><h5><h6><a><p><ul><ol><li><blockquote><q><cite><ins><del><strong><em><code><pre><svg><table><thead><tbody><tfoot><th><tr><td><dl><dt><dd><article><section><header><footer><aside><figure><time><abbr><div><span><hr><small><br><amp-img><amp-audio><amp-video><amp-ad><amp-anim><amp-carousel><amp-fit-rext><amp-image-lightbox><amp-instagram><amp-lightbox><amp-twitter><amp-youtube>');
        $html = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $html);
        return $html;
    }

    public function ampifyImageTag($matches)
    {
        $attrs = $matches[1];
        $width = null;
        $height = null;

        if (preg_match('/width=["\'](\d+)(?:px)?["\']/i', $attrs, $w_match)) {
            $width = (int)$w_match[1];
            $attrs = preg_replace('/width=["\']\d+(?:px)?["\']/i', '', $attrs);
        } elseif (preg_match('/style=["\'][^"\']*width\s*:\s*(\d+)px/i', $attrs, $w_style_match)) {
            $width = (int)$w_style_match[1];
        }

        if (preg_match('/height=["\'](\d+)(?:px)?["\']/i', $attrs, $h_match)) {
            $height = (int)$h_match[1];
            $attrs = preg_replace('/height=["\']\d+(?:px)?["\']/i', '', $attrs);
        } elseif (preg_match('/style=["\'][^"\']*height\s*:\s*(\d+)px/i', $attrs, $h_style_match)) {
            $height = (int)$h_style_match[1];
        }

        if (empty($width) || empty($height)) {
            if (preg_match('/src=["\']([^"\']+)["\']/i', $attrs, $src_match)) {
                $src = $src_match[1];
                $imagePath = '';
                if (strpos($src, 'http') === 0) {
                    $imagePath = $src;
                } else {
                    $srcClean = ltrim($src, '/');
                    if (function_exists('public_path')) {
                        $localPath = public_path($srcClean);
                        if (file_exists($localPath)) {
                            $imagePath = $localPath;
                        }
                    }
                }
                if (empty($imagePath)) {
                    $imagePath = $this->getCurrentUrl() . $_SERVER["SERVER_NAME"] . '/' . $srcClean;
                }
            }

            if (!empty($imagePath)) {
                $size = @getimagesize($imagePath);
                if ($size) {
                    $width = $width ?: $size[0];
                    $height = $height ?: $size[1];
                }
            }
        }

        $width = $width ?: 700;
        $height = $height ?: 500;

        return '<amp-img' . $attrs . ' layout="responsive" width="' . $width . '" height="' . $height . '"></amp-img>';
    }
}
