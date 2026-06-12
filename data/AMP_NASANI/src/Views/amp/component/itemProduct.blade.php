<div class="product-item">
    <div class="product-item__pic">
        <a href="{{ url('amp.slugweb', ['slug' => $product[$sluglang]]) }}" class="d-block scale-img">
            <amp-img srcset="{{ assets_photo($product->img->photo->thumb, $product['photo'], 'watermarks') }}"
                alt="{{ $product['name' . $lang] }}" width="{{ $product->img->photo->width }}"
                height="{{ $product->img->photo->height }}" layout="responsive">
        </a>
    </div>
    <div class="product-item__info">
        <h3 class="product-item__name"><a class="text-split"
                href="{{ url('amp.slugweb', ['slug' => $product[$sluglang]]) }}">{{ $product['name' . $lang] }}</a>
        </h3>
        <div class="product-item__desc">{{ nl2br($product['desc' . $lang]) }}</div>
        <div class="product-item__price">
            Giá:
            @if (empty($product['sale_price']))
                @if (empty($product['regular_price']))
                    <span class="product-item__price--new">Liên Hệ</span>
                @else
                    <span class="product-item__price--new">{{ Func::formatMoney($product['regular_price']) }}</span>
                @endif
            @else
                <span class="product-item__price--new">{{ Func::formatMoney($product['sale_price']) }}</span>
                <span class="product-item__price--old">{{ Func::formatMoney($product['regular_price']) }}</span>
            @endif
        </div>
    </div>
    @if (config('type.order'))
        <div class="product-item__cart">
            <button class="product-item__btn product-item__btn--addnow addcart addnow" data-id="{{ $product['id'] }}"
                data-action="addnow">{{ __('web.themvaogio') }}</button>
            <button class="product-item__btn product-item__btn--buynow addcart buynow" data-id="{{ $product['id'] }}"
                data-action="buynow">{{ __('web.muangay') }}</button>
            <button class="product-item__btn product-popup" data-id="{{ $product['id'] }}">Mua hàng Popup</button>
        </div>
    @endif
</div>
