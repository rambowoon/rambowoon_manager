<?php

NASANIRouter::group(['namespace' => 'Web', 'prefix' => config('app.amp_prefix'), 'middleware' => [\NASANICORE\Middlewares\LangRequest::class, \NASANICORE\Middlewares\CheckRedirect::class]], function ($language = 'vi') {
    NASANIRouter::get('/change-lang/{lang}', function ($lang) {
        if (\Illuminate\Support\Arr::has(config('app.langs'), $lang)) {
            session()->set('locale', $lang);
            app()->make('config')->set('app.seo_default', $lang);
            response()->redirect(linkReferer() ? linkReferer() : url('home', ['language' => $lang]));
        }
    });
    NASANIRouter::get('/', 'HomeController@index')->name('amp.home');
    NASANIRouter::get('/blog', 'NewsController@index')->name('amp.blog');
    NASANIRouter::get('/san-pham', 'ProductController@index')->name('amp.san-pham');
    NASANIRouter::get('/{slug}', 'SlugController@handle')->name('amp.slugweb');
});
