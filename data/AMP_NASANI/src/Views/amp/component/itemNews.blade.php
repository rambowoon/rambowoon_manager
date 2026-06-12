<div class="news-box">
    <a class="news-pic position-relative scale-img" href="{{ url('amp.slugweb', ['slug' => $v[$sluglang]]) }}"
        title="{{ $v['name' . $lang] }}">
        <amp-img class="w-100" src="{{ assets_photo($v->img->photo->thumb, $v['photo'], 'thumbs') }}"
            alt="{{ $v['name' . $lang] }}" width="{{ $v->img->photo->width ?? 200 }}"
            height="{{ $v->img->photo->height ?? 150 }}" layout="responsive">
        </amp-img>
    </a>
    <div class="news-info">
        <h3 class="news-name"><a class="text-split" href="{{ url('amp.slugweb', ['slug' => $v[$sluglang]]) }}"
                title="{{ $v['name' . $lang] }}">{{ $v['name' . $lang] }}</a></h3>
        <div class="news-desc text-split">{{ $v['desc' . $lang] }}</div>
        <div class="news-view">
            <a href="{{ url('amp.slugweb', ['slug' => $v[$sluglang]]) }}" title="{{ $v['name' . $lang] }}">
                /Xem chi tiết <span>+</span>
            </a>
        </div>
    </div>
</div>
