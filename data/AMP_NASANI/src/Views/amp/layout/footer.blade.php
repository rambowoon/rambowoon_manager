<footer>
    <div class="footer-article">
        <div class="container-custom">
            <div class="wrap-content d-flex flex-wrap justify-content-between">
                <div class="footer-news">
                    <div class="footer-title">Thông tin công ty</div>
                    <div class="footer-info content-ckeditor">{!! Func::ampify(Func::decodeHtmlChars($footer['content' . $lang] ?? '')) !!}</div>
                    @if (!empty($social) && $social->isNotEmpty())
                        <div class="social-footer d-flex align-items-center flex-wrap">
                            <span>Follow us:</span>
                            @foreach ($social as $k => $v)
                                <a href="{{ $v['link'] }}" target="_blank">
                                    <amp-img class="mw-100"
                                        src="{{ assets_photo($v->img->thumb, $v['photo'], 'thumbs') }}"
                                        alt="{{ $v['name' . $lang] }}" width="{{ $v->img->width ?? 35 }}"
                                        height="{{ $v->img->height ?? 35 }}" layout="intrinsic">
                                    </amp-img>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="footer-news">
                    <div class="footer-title">Chính sách công ty</div>
                    @if (!empty($policy) && $policy->isNotEmpty())
                        <ul class="footer-ul">
                            @foreach ($policy as $v)
                                <li>
                                    <a
                                        href="{{ url('slugweb', ['slug' => $v[$sluglang]]) }}">{{ $v['name' . $lang] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="footer-news">
                    <div class="footer-title">Về chúng tôi</div>
                    <a href="{{ url('cau-hoi-thuong-gap') }}" class="footer-item">Những câu hỏi thường gặp!</a>
                    <div class="footer-title footer-title-thongke">Thống kê truy cập</div>
                    <div class="footer-statistic ">
                        <p>Online: <span>{{ Statistic::getOnline() }}</span></p>
                        <p>Ngày: <span>{{ Statistic::getTodayRecord() }}</span></p>
                        <p>Tuần: <span>{{ Statistic::getWeekRecord() }}</span></p>
                        <p>Tổng: <span>{{ Statistic::getTotalRecord() }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-powered">
        <div class="container-custom">
            <div class="wrap-content d-flex justify-content-center align-items-center flex-wrap">
                <div class="footer-copyright">Copyright © 2026 <span>{{ $copyright['name' . $lang] ?? '' }}</span>.
                    Designed
                    by <a href="https://nasani.vn" class=" text-decoration-none" title="nasani.vn">nasani.vn</a></div>
            </div>
        </div>
    </div>
</footer>
