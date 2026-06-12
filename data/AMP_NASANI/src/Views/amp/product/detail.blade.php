@extends('layout')
@section('content')
    @php
        $index = $rowDetail['id'] . '_' . uniqid(rand(), true);
        $thumb_size = $rowDetail->img->photo->width . 'x' . $rowDetail->img->photo->height . 'x1';
        $deviceType = device();
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $isIOS = false;
        if (
            strpos($userAgent, 'iPhone') !== false ||
            strpos($userAgent, 'iPad') !== false ||
            strpos($userAgent, 'iPod') !== false
        ) {
            $isIOS = true;
        }
    @endphp

    <div class="wrap-page">
        <div class="container-custom">
            <div class="wrap-content">
                <div class="prodetail">
                    <div class="prodetail__header">
                        <div class="prodetail__gallery">
                            <amp-carousel width="{{ $rowDetail->img->photo->width ?? 590 }}"
                                height="{{ $rowDetail->img->photo->height ?? 650 }}" layout="responsive" type="slides">
                                <amp-img src="{{ assets_photo($thumb_size, $rowDetail['photo'], 'watermarks') }}"
                                    width="{{ $rowDetail->img->photo->width ?? 590 }}"
                                    height="{{ $rowDetail->img->photo->height ?? 650 }}" layout="responsive"
                                    alt="{{ $rowDetail['name' . $lang] }}"></amp-img>
                                @if (!empty($rowDetailPhoto->toArray()))
                                    @foreach ($rowDetailPhoto as $gallery)
                                        <amp-img src="{{ assets_photo($thumb_size, $gallery['photo'], 'watermarks') }}"
                                            width="{{ $rowDetail->img->photo->width ?? 590 }}"
                                            height="{{ $rowDetail->img->photo->height ?? 650 }}" layout="responsive"
                                            alt="{{ $rowDetail['name' . $lang] }}"></amp-img>
                                    @endforeach
                                @endif
                            </amp-carousel>
                        </div>

                        <div class="prodetail__info">
                            <h2 class="prodetail__title">{{ $rowDetail['name' . $lang] }}</h2>

                            <ul class="prodetail__option">
                                @if (!empty(config('type.product.' . $rowDetail->type . '.code')))
                                    <li>
                                        <label>{{ __('web.masanpham') }}:</label>
                                        <span>{{ $rowDetail['code'] != '' ? $rowDetail['code'] : __('web.dangcapnhat') }}</span>
                                    </li>
                                    <li class="line">|</li>
                                @endif
                                <li>
                                    <label>{{ __('web.luotxem') }}:</label>
                                    <span>{{ $rowDetail['view'] }}</span>
                                </li>
                            </ul>

                            <div class="prodetail__price">
                                @if (empty($rowDetail['sale_price']))
                                    @if (empty($rowDetail['regular_price']))
                                        <span class="prodetail__price-new">{{ __('web.lienhe') }}</span>
                                    @else
                                        <span
                                            class="prodetail__price-new">{{ Func::formatMoney($rowDetail['regular_price']) }}</span>
                                    @endif
                                @else
                                    <span
                                        class="prodetail__price-new">{{ Func::formatMoney($rowDetail['sale_price']) }}</span>
                                    <span
                                        class="prodetail__price-old">{{ Func::formatMoney($rowDetail['regular_price']) }}</span>
                                    <span
                                        class="prodetail__price-discount">-{{ round((($rowDetail['regular_price'] - $rowDetail['sale_price']) / $rowDetail['regular_price']) * 100) }}%</span>
                                @endif
                            </div>

                            <div class="prodetail__desc content-ckeditor">{!! nl2br(Func::ampify($rowDetail['desc' . $lang])) !!}</div>

                            <div class="prodetail__actions">
                                @if (config('type.order'))
                                    @if (!empty($optSetting['hotline']))
                                        <a class="prodetail__btn prodetail__btn--add"
                                            href="tel:{{ Func::parsePhone($optSetting['hotline']) }}">
                                            <span class="prodetail__btn-text">{{ __('web.themvaogio') }}</span>
                                        </a>
                                        <a class="prodetail__btn prodetail__btn--buy"
                                            href="tel:{{ Func::parsePhone($optSetting['hotline']) }}">
                                            <span class="prodetail__btn-text">{{ __('web.muangay') }}</span>
                                        </a>
                                    @endif
                                @endif
                            </div>

                            <div class="prodetail-btn-detail">
                                @if (!empty($zalo))
                                    <a class="prodetail-btn-item"
                                        href="{{ !empty($zalo) ? (!empty($zalo->idzalo) && strpos($zalo->status, 'qrcode') ? Func::checkLinkZalo($zalo->idzalo, $zalo->phone, $deviceType, $isIOS) : 'https://zalo.me/' . Func::parsePhone($zalo->phone)) : '' }}"
                                        target="_blank">
                                        <i class="fa fa-file-text-o"></i> Yêu cầu báo giá
                                    </a>
                                @endif
                                @if (!empty($optSetting['hotline']))
                                    <a class="prodetail-btn-item prodetail-btn-hotline"
                                        href="tel:{{ Func::parsePhone($optSetting['hotline']) }}"><i
                                            class="fa fa-phone"></i> Hotline:
                                        {{ Func::formatPhone($optSetting['hotline']) }}</a>
                                @endif
                                <a class="prodetail-btn-item" href="{{ url('lien-he') }}"><i class="fa fa-briefcase"></i>
                                    Liên hệ đại lý</a>
                                @if (!empty($rowDetail->options2['file']))
                                    <a class="prodetail-btn-item" href="{{ upload($rowDetail->options2['file']) }}"
                                        download><i class="fa fa-download"></i> Tài liệu kỹ thuật</a>
                                @endif
                                <a class="prodetail-btn-item" href="{{ url('mau-sac') }}"><i class="fa fa-paint-brush"></i>
                                    Xem
                                    mã màu sơn</a>
                            </div>

                        </div>
                    </div>

                    <div class="prodetail__tabs">
                        <div class="prodetail__tab-container">
                            <amp-accordion>
                                @if ($rowDetail['content' . $lang] != '')
                                    <section expanded>
                                        <h4 class="accordion-tabProdetail">{{ __('web.thongtinchitiet') }}</h4>
                                        <div class="content-tabProdetail">
                                            <div class="content-ckeditor">{!! Func::ampify(Func::insertKeywordLinks(Func::decodeHtmlChars($rowDetail['content' . $lang]), $keyw)) !!}</div>
                                        </div>
                                    </section>
                                @endif
                            </amp-accordion>
                        </div>
                    </div>

                    @if (!empty($product) && $product->isNotEmpty())
                        <div class="prodetail__related">
                            <div class="prodetail__related-title">
                                <h2>{{ __('web.cothebanquantam') }}</h2>
                            </div>
                            <div class="row-product row row-cols-2 row-cols-sm-2 row-cols-md-3">
                                @foreach ($product as $v)
                                    <div class="col-product col">
                                        @component('component.itemProduct', ['product' => $v])
                                        @endcomponent
                                    </div>
                                @endforeach
                            </div>
                            {!! $product->appends(request()->query())->onEachSide(0)->links() !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
