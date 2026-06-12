<amp-sidebar id="sidebar" layout='nodisplay'>
    <label class="menu-title">Menu</label>
    <ul class="menu-ul">
        <li><a class="{{ ($com ?? '') == 'trang-chu' ? 'active' : '' }}"
                href="{{ url('amp.home') }}">{{ __('web.trangchu') }}</a></li>
        <li>
            @if (!empty($productListMenu) && $productListMenu->isNotEmpty())
                <amp-accordion class="menu-accordion" expand-single-section>
                    <section>
                        <h4 class="menu-title-acc {{ ($com ?? '') == 'san-pham' ? 'active' : '' }}">
                            {{ __('web.sanpham') }}
                        </h4>
                        <div class="menu-content-acc">
                            <ul>
                                <li>
                                    <a class="transition" href="{{ url('amp.san-pham') }}">Tất cả
                                        {{ __('web.sanpham') }}</a>
                                </li>
                                @foreach ($productListMenu as $vlist)
                                    <li>
                                        @if ($vlist->getCategoryCats->isNotEmpty())
                                            <amp-accordion class="menu-accordion-sub" expand-single-section>
                                                <section>
                                                    <h5 class="menu-title-subacc">
                                                        {{ $vlist['name' . $lang] }}
                                                    </h5>
                                                    <div class="menu-content-subacc">
                                                        <ul>
                                                            <li>
                                                                <a class="transition"
                                                                    href="{{ url('amp.slugweb', ['slug' => $vlist[$sluglang]]) }}">Tất
                                                                    cả {{ $vlist['name' . $lang] }}</a>
                                                            </li>
                                                            @foreach ($vlist->getCategoryCats as $vcat)
                                                                <li>
                                                                    @if ($vcat->getCategoryItems->isNotEmpty())
                                                                        <amp-accordion class="menu-accordion-item"
                                                                            expand-single-section>
                                                                            <section>
                                                                                <h6 class="menu-title-itemacc">
                                                                                    {{ $vcat['name' . $lang] }}
                                                                                </h6>
                                                                                <div class="menu-content-itemacc">
                                                                                    <ul>
                                                                                        <li>
                                                                                            <a class="transition"
                                                                                                href="{{ url('amp.slugweb', ['slug' => $vcat[$sluglang]]) }}">Tất
                                                                                                cả
                                                                                                {{ $vcat['name' . $lang] }}</a>
                                                                                        </li>
                                                                                        @foreach ($vcat->getCategoryItems as $vitem)
                                                                                            <li>
                                                                                                <a class="transition"
                                                                                                    href="{{ url('amp.slugweb', ['slug' => $vitem[$sluglang]]) }}">
                                                                                                    {{ $vitem['name' . $lang] }}
                                                                                                </a>
                                                                                            </li>
                                                                                        @endforeach
                                                                                    </ul>
                                                                                </div>
                                                                            </section>
                                                                        </amp-accordion>
                                                                    @else
                                                                        <a class="transition"
                                                                            href="{{ url('amp.slugweb', ['slug' => $vcat[$sluglang]]) }}">
                                                                            {{ $vcat['name' . $lang] }}
                                                                        </a>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </section>
                                            </amp-accordion>
                                        @else
                                            <a class="transition"
                                                href="{{ url('amp.slugweb', ['slug' => $vlist[$sluglang]]) }}">
                                                {{ $vlist['name' . $lang] }}
                                            </a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </section>
                </amp-accordion>
            @else
                <a class="{{ ($com ?? '') == 'san-pham' ? 'active' : '' }}"
                    href="{{ url('amp.san-pham') }}">{{ __('web.sanpham') }}</a>
            @endif
        </li>
        <li><a class="{{ ($com ?? '') == 'mau-sac' ? 'active' : '' }}" href="{{ url('mau-sac') }}">Màu sắc</a></li>
        <li><a class="{{ ($com ?? '') == 'thi-cong' ? 'active' : '' }}" href="{{ url('thi-cong') }}">Thi công</a></li>
        <li><a class="{{ ($com ?? '') == 'dich-vu' ? 'active' : '' }}" href="{{ url('dich-vu') }}">Dịch vụ</a></li>
        <li><a class="{{ ($com ?? '') == 'gioi-thieu' ? 'active' : '' }}"
                href="{{ url('gioi-thieu') }}">{{ __('web.gioithieu') }}</a></li>
        <li>
            @if (!empty($newsList) && $newsList->isNotEmpty())
                <amp-accordion class="menu-accordion" expand-single-section>
                    <section>
                        <h4 class="menu-title-acc {{ ($com ?? '') == 'blog' ? 'active' : '' }}">
                            Blog
                        </h4>
                        <div class="menu-content-acc">
                            <ul>
                                <li>
                                    <a class="transition" href="{{ url('blog') }}">Tất cả Blog</a>
                                </li>
                                @foreach ($newsList as $vlist)
                                    <li><a class="transition"
                                            href="{{ $vlist[$sluglang] }}">{{ $vlist['name' . $lang] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </section>
                </amp-accordion>
            @else
                <a class="{{ ($com ?? '') == 'blog' ? 'active' : '' }}" href="{{ url('blog') }}">Blog</a>
            @endif
        </li>
        <li><a class="{{ ($com ?? '') == 'lien-he' ? 'active' : '' }}"
                href="{{ url('lien-he') }}">{{ __('web.lienhe') }}</a></li>
    </ul>
</amp-sidebar>
