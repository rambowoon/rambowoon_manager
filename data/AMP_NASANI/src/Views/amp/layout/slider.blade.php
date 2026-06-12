@if (!empty($slideshow->toArray()))
    <div class="slide-wrap position-relative">
        <amp-carousel width="{{ $slideshow->first()->img->width ?? 390 }}"
            height="{{ $slideshow->first()->img->height ?? 200 }}" layout="responsive" type="slides" autoplay
            delay="5000">
            @foreach ($slideshow as $v)
                <a class="d-block" href="{{ $v['link'] }}" target="_blank">
                    @php
                        $width = $v->img->width ?? 390;
                        $height = $v->img->height ?? 200;
                        $size = $width . 'x' . $height . 'x1';
                    @endphp
                    <amp-img src="{{ assets_photo($size, $v['photo'], 'thumbs') }}" alt="{{ $v['name' . $lang] ?? '' }}"
                        width="{{ $width }}" height="{{ $height }}" layout="responsive">
                    </amp-img>
                </a>
            @endforeach
        </amp-carousel>
    </div>
@endif
