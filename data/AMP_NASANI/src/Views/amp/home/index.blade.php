@extends('layout')
@section('content')

    @if (!empty($listProductNB) && $listProductNB->isNotEmpty())
        <div class="wrap-product-list">
            <div class="wrap-content">
                <amp-carousel width="auto" height="234" layout="fixed-height" type="carousel">
                    @foreach ($listProductNB as $v)
                        <div class="box-product-list">
                            <a class="img-product-list scale-img" href="{{ config('app.amp_prefix') }}/{{ $v[$sluglang] }}"
                                title="{{ $v['name' . $lang] }}">
                                <amp-img class="w-100"
                                    src="{{ assets_photo($v->img->photo->thumb, $v['photo'], 'thumbs') }}"
                                    alt="{{ $v['name' . $lang] }}" width="{{ $v->img->photo->width ?? 150 }}"
                                    height="{{ $v->img->photo->height ?? 120 }}" layout="responsive">
                                </amp-img>
                            </a>
                            <div class="info-product-list">
                                <h2><a class="text-split" href="{{ config('app.amp_prefix') }}/{{ $v[$sluglang] }}"
                                        title="{{ $v['name' . $lang] }}">{{ $v['name' . $lang] }}</a>
                                </h2>
                            </div>
                        </div>
                    @endforeach
                </amp-carousel>
            </div>
        </div>
    @endif

    @if (!empty($about))
        <div class="wrap-about">
            <div class="wrap-content d-flex justify-content-between align-items-start flex-wrap">
                <div class="left-about">
                    <div class="title-about">
                        <strong>About us</strong>
                        <span>{{ $about['name' . $lang] }}</span>
                        <p>{{ $about->options2['slogan'] }}</p>
                    </div>
                    <div class="desc-about text-split">{{ $about['desc' . $lang] }}</div>
                    <a href="{{ url('gioi-thieu') }}" class="view-about">Xem chi tiết</a>
                </div>
                <div class="right-about">
                    <div class="img-about">
                        <amp-img class="w-100"
                            src="{{ assets_photo($about->img->photo->thumb, $about['photo'], 'thumbs') }}"
                            alt="{{ $about['name' . $lang] }}" width="{{ $about->img->photo->width ?? 400 }}"
                            height="{{ $about->img->photo->height ?? 300 }}" layout="responsive">
                        </amp-img>
                    </div>
                    <div class="video-about">
                        @if (!empty($video) && $video->isNotEmpty())
                            <amp-carousel width="{{ $video[0]->img->photo->width ?? 200 }}"
                                height="{{ $video[0]->img->photo->height ?? 150 }}" layout="responsive" type="slides">
                                @foreach ($video as $v)
                                    <a href="{{ $v->options2['link-youtube'] ?? '' }}" target="_blank" class="box-video">
                                        <amp-img class="w-100"
                                            src="{{ assets_photo($v->img->photo->thumb, $v['photo'], 'thumbs') }}"
                                            alt="{{ $v['name' . $lang] }}" width="{{ $v->img->photo->width ?? 200 }}"
                                            height="{{ $v->img->photo->height ?? 150 }}" layout="responsive">
                                        </amp-img>
                                    </a>
                                @endforeach
                            </amp-carousel>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (!empty($productNB))
        @php
            $productsNB = \NASANICORE\Models\ProductModel::select([
                'name' . $lang,
                'photo',
                'desc' . $lang,
                $sluglang,
                'regular_price',
                'sale_price',
                'discount',
                'id',
                'type',
            ])
                ->where('type', 'san-pham')
                ->whereRaw('FIND_IN_SET(?,status)', ['noibat'])
                ->whereRaw('FIND_IN_SET(?,status)', ['hienthi'])
                ->datePublish()
                ->orderBy('numb', 'asc')
                ->orderBy('id', 'desc')
                ->take(8)
                ->get();
        @endphp
        @if ($productsNB->isNotEmpty())
            <div class="wrap-product">
                <div class="wrap-content">
                    <div class="title-main">
                        <span>Sản Phẩm Nổi Bật</span>
                        <p>Một số dòng sản phẩm nổi bật của công ty chúng tôi đang kinh doanh hiện nay trên thị trường
                        </p>
                    </div>
                    <div class="row row-product row-cols-2 row-cols-sm-2 row-cols-md-3">
                        @foreach ($productsNB as $v)
                            <div class="col col-product">
                                @component('component.itemProduct', ['product' => $v])
                                @endcomponent
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if (!empty($solieu) && $solieu->isNotEmpty())
        <div class="wrap-solieu">
            <div class="wrap-content">
                @foreach ($solieu as $v)
                    <div class="box-solieu">
                        <div class="number-solieu">
                            <span
                                class="counter">{{ $v->options2['solieu'] }}</span><strong>{{ $v->options2['unit'] }}</strong>
                        </div>
                        <div class="desc-solieu">{{ $v['desc' . $lang] }}</div>
                        <h3>{{ $v['name' . $lang] }}</h3>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (!empty($projectNB) && $projectNB->isNotEmpty())
        <div class="wrap-news">
            <div class="wrap-content">
                <div class="title-news">
                    <div class="title-news-left">
                        <strong>{{ $setting['name' . $lang] }}</strong>
                        <span>Dự án thi công</span>
                    </div>
                    <a href="{{ url('thi-cong') }}" class="view-news">Xem tất cả<i class="fa fa-angles-right"></i></a>
                </div>
                <div class="row row-news  row-cols-2 row-cols-sm-2 row-cols-md-3">
                    @foreach ($projectNB->take(6) as $v)
                        <div class="col col-news">
                            @component('component.itemNews', ['v' => $v])
                            @endcomponent
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="wrap-newsletter">
        <div class="wrap-content">
            <div class="right-newsletter">
                <div class="title-newsletter">
                    <span><amp-img src="assets/images/title_nhantin.webp" alt="Đăng ký nhận báo giá" width="30"
                            height="30" layout="intrinsic"></amp-img>Đăng ký nhận báo giá</span>
                    <p>* Điền thông tin của bạn và các ô bên dưới để nhận được tư vấn từ chúng tôi *</p>
                </div>
                <form id="form-newsletter" class="form-newsletter" method="POST"
                    action-xhr="{{ url('api', ['method' => 'newsletter-post']) }}" target="_top">
                    <div class="newsletter-input">
                        <span>Họ & tên *</span>
                        <input type="text" name="dataNewsletter[fullname]" class="form-control" required />
                    </div>
                    <div class="newsletter-input">
                        <span>Điện thoại *</span>
                        <input type="text" name="dataNewsletter[phone]" class="form-control" required />
                    </div>
                    <div class="newsletter-input">
                        <span>Email *</span>
                        <input type="email" name="dataNewsletter[email]" class="form-control" required />
                    </div>
                    <div class="newsletter-input">
                        <span>Địa chỉ *</span>
                        <input type="text" name="dataNewsletter[address]" class="form-control" />
                    </div>
                    <div class="newsletter-input">
                        <span>Nội dung ...</span>
                        <textarea name="dataNewsletter[content]" class="form-control"></textarea>
                    </div>
                    <div class="newsletter-footer">
                        <div class="newsletter-check-wrap">
                            <p class="newsletter-note">* Mọi thông tin sẽ được bảo mật.</p>
                            <label class="newsletter-check form-check">
                                <input class="form-check-input" type="checkbox" required />
                                <span class="form-check-label">Tôi chấp nhận điều khoản</span>
                            </label>
                        </div>
                        <button type="submit" class="newsletter-submit">Đăng ký ngay</button>
                    </div>
                    <input type="hidden" name="is_amp" value="1">
                    <input type="hidden" name="dataNewsletter[type]" value="dang-ky-bao-gia">
                    <input type="hidden" name="csrf_token" value="{{ csrf_token() }}">
                </form>
            </div>
        </div>
    </div>

@endsection
