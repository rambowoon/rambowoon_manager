<?php

namespace NASANICORE\Helpers;

use NASANICORE\Core\Singleton;
use NASANICORE\Core\Support\Str;
use NASANICORE\Core\Support\Facades\Func;

class BreadCrumbs
{
    use Singleton;
    private $data = array();
    protected $lang;
    protected $seolang;
    public function __construct()
    {
        $this->seolang = app()->getSeoLang();
        $this->lang = session()->get('locale');
    }
    public function set($slug = '', $name = '')
    {
        $bre = Func::nameRouter($slug);

        if (!empty($bre)) {
            $slugUrl = url($bre);
        } else {
            $slugUrl = $slug;
        }

        $slugUrl = $this->ampifyUrl($slugUrl);

        $this->data[] = array('slug' => $slugUrl, 'name' => $name);
    }
    private function ampifyUrl($url)
    {
        $path = '/' . ltrim(request()->getPathInfo(), '/');
        $ampPrefix = '/' . trim(config('app.amp_prefix'), '/');
        $isAMP = (strpos($path, $ampPrefix . '/') === 0 || $path === $ampPrefix);
        
        $ampPrefixSegment = trim(config('app.amp_prefix'), '/');
        $root = request()->root();

        if ($isAMP) {
            if (preg_match('#^(' . preg_quote($root, '#') . ')?/' . preg_quote($ampPrefixSegment, '#') . '(/|$)#', $url)) {
                return $url;
            }

            if (strpos($url, $root) === 0) {
                $pathPart = substr($url, strlen($root));
                return $root . '/' . $ampPrefixSegment . '/' . ltrim($pathPart, '/');
            } elseif (strpos($url, '/') === 0) {
                return '/' . $ampPrefixSegment . '/' . ltrim($url, '/');
            } else {
                return '/' . $ampPrefixSegment . '/' . $url;
            }
        } else {
            // Non-AMP pages: strip AMP prefix if it was incorrectly added (e.g. from route name conflicts)
            $absoluteAmp = $root . '/' . $ampPrefixSegment;
            if (strpos($url, $absoluteAmp) === 0) {
                $pathPart = substr($url, strlen($absoluteAmp));
                if (trim($pathPart, '/') === '') {
                    return url('home');
                }
                return $root . '/' . ltrim($pathPart, '/');
            }
            $relativeAmp = '/' . $ampPrefixSegment;
            if (strpos($url, $relativeAmp) === 0) {
                $pathPart = substr($url, strlen($relativeAmp));
                if (trim($pathPart, '/') === '') {
                    return url('home');
                }
                return '/' . ltrim($pathPart, '/');
            }

            return $url;
        }
    }
    public function get(): string
    {
        $json = array();
        $breadcumb = '';
        $path = '/' . ltrim(request()->getPathInfo(), '/');
        $ampPrefix = '/' . trim(config('app.amp_prefix'), '/');
        $isAMP = (strpos($path, $ampPrefix . '/') === 0 || $path === $ampPrefix);
        if ($isAMP) {
            $home = url(config('app.amp_prefix'));
        } elseif (count(config('app.slugs')) > 1) {
            $home = url('home.' . $this->lang);
        } else {
            $home = url('home');
        }
        
        if ($this->data) {
            $breadcumb .= '<ol class="breadcrumb">';
            $breadcumb .= '<li class="home">
            <a class="text-decoration-none" href="' . $home . '"><span>' . __('web.trangchu') . '</span></a>
            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chevron-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="svg-inline--fa fa-chevron-right fa-w-10"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z" class=""></path></svg>
            </li>';
            $k = 1;
            foreach ($this->data as $key => $value) {
                if ($value['name'] != '') {
                    $slug = $value['slug'];
                    $name = $value['name'];
                    $active = ($key == count($this->data) - 1) ? "active" : "";
                    $breadcumb .= '<li class="' . $active . '"><a class="text-decoration-none" href="' . $slug . '"><span>' . $name . '</span></a>';
                    if (!$active) {
                        $breadcumb .= '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chevron-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="svg-inline--fa fa-chevron-right fa-w-10"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z" class=""></path></svg>';
                    }
                    $breadcumb .= '</li>';
                    $itemUrl = (strpos($slug, 'http') === 0) ? $slug : request()->root() . $slug;
                    $json[] = array("@type" => "ListItem", "position" => $k, "name" => $name, "item" => $itemUrl);
                    $k++;
                }
            }
            $breadcumb .= '</ol>';
            $breadcumb .= '<script type="application/ld+json">{"@context": "https://schema.org","@type": "BreadcrumbList","itemListElement": ' . ((json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))) . '}</script>';
        }
        return $breadcumb;
    }
    public function setBreadcrumb($type = '', $title = '', $detail = '', $list = '', $cat = '', $item = '', $sub = ''): string
    {
        // $bre =Func::nameRouter($type);
        if (Str::isNotEmpty($title ?? '')) $this->set(url($type), $title);
        $this->extracted($list, $cat);
        if (!empty($item)) $this->set(url('slugweb', ['slug' => $item['slug' . $this->seolang]]), $item['name' . $this->lang]);
        $this->extracted($sub, $detail);
        return $this->get();
    }
    /**
     * @param mixed $list
     * @param mixed $cat
     * @return void
     */
    public function extracted(mixed $list, mixed $cat): void
    {
        if (!empty($list)) $this->set(url('slugweb', ['slug' => $list['slug' . $this->seolang]]), $list['name' . $this->lang]);
        if (!empty($cat)) $this->set(url('slugweb', ['slug' => $cat['slug' . $this->seolang]]), $cat['name' . $this->lang]);
    }
}
