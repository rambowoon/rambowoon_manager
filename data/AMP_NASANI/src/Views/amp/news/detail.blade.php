@extends('layout')
@section('content')
    <div class="wrap-page">
        <div class="wrap-content">
            <div class="title-detail">
                <h1>{{ $rowDetail['name' . $lang] }}</h1>
            </div>
            <div class="date mb-3">{{ $rowDetail['created_at'] }} - {{ $rowDetail['view'] }} lượt xem</div>

            @if (!empty($rowDetailPhoto) && $rowDetailPhoto->isNotEmpty())
                <amp-image-lightbox id="lightbox-gallery" layout="nodisplay"></amp-image-lightbox>
                <div class="row-news row row-cols-2 row-cols-sm-2 row-cols-md-3">
                    @foreach ($rowDetailPhoto as $gallery)
                        @php
                            $width = $configType->news->{$gallery['type']}->images->photo->width ?? 400;
                            $height = $configType->news->{$gallery['type']}->images->photo->height ?? 300;
                            $size = $width . 'x' . $height . 'x1';
                        @endphp
                        <div class="col-news col">
                            <div class="d-block scale-img" role="button" tabindex="0" on="tap:lightbox-gallery">
                                <amp-img class="w-100" src="{{ assets_photo($size, $gallery['photo'], 'news') }}"
                                    alt="{{ $setting['name' . $lang] ?? '' }}" width="{{ $width }}"
                                    height="{{ $height }}" layout="responsive">
                                </amp-img>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="content-ckeditor">
                {!! Func::ampify(Func::decodeHtmlChars($rowDetail['content' . $lang] ?? '')) !!}
            </div>

            @if (!empty($news) && $news->isNotEmpty())
                <div class="title-main mt-4">
                    <span>{{ __('web.tinlienquan') }}</span>
                </div>
                <div class="row row-news  row-cols-2 row-cols-sm-2 row-cols-md-3">
                    @foreach ($news as $v)
                        <div class="col col-news">
                            @component('component.itemNews', ['v' => $v])
                            @endcomponent
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
