<div class="header-bottom">
    <div class="wrap-content">
        <div class="header-logo">
            <a href="{{ url('amp.home') }}">
                <amp-img src="{{ assets_photo($logoPhoto->img->thumb, $logoPhoto['photo'], 'thumbs') }}"
                    width="{{ $logoPhoto->img->width ?? 65 }}" height="{{ $logoPhoto->img->height ?? 60 }}"
                    layout="intrinsic" alt="{{ $setting['name' . $lang] ?? '' }}">
                </amp-img>
            </a>
        </div>
        <div class="header-right">
            <div class="search-popup">
                <input type="checkbox" id="toggle-search" />
                <label for="toggle-search" class="icon-search transition"><i class="fa fa-search"></i></label>
                <div class="search-grid w-clear">
                    <form method="GET" action="{{ url('tim-kiem') }}" target="_top">
                        <input type="text" name="keyword" id="keyword" placeholder="{{ __('web.nhaptukhoa') }}"
                            required />
                        <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
            </div>
            <span id="hamburger" on="tap:sidebar.toggle" role="button" tabindex="0"><span></span></span>
        </div>
    </div>
</div>
