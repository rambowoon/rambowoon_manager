@if (device() == 'mobile')
    @php
        $bottom = $val['mobile']['bottom'] ?? '';
        $left = !empty($val['mobile']['left']) ? $val['mobile']['left'] . 'px' : '';
        $right = !empty($val['mobile']['right']) ? $val['mobile']['right'] . 'px' : '';
    @endphp
@else
    @php
        $bottom = $val['destop']['bottom'] ?? '';
        $left = !empty($val['destop']['left']) ? $val['destop']['left'] . 'px' : '';
        $right = !empty($val['destop']['right']) ? $val['destop']['right'] . 'px' : '';
    @endphp
@endif

@php
    $background = $val['background'] ?? '';
    $backgroundText = $val['background-text'] ?? '';
    $color = $val['color'] ?? '';
    $location = !empty($left) ? 'left' : 'right';
    $destop = !empty($val['destop']['device']) && device() == 'destop' ? true : false;
    $mobile = !empty($val['mobile']['device']) && device() == 'mobile' ? true : false;
@endphp

@if (!empty($destop) || !empty($mobile))
    <div id="social"
        style="
    --background: #{{ $background }};
    --bottom: {{ $bottom }}px;
    --left: {{ $left }};
    --right: {{ $right }};
">
        @foreach ($mxh as $mxh)
            <a class="btn-phone btn-frame text-decoration-none" href="{{ $mxh['link'] }}">
                <div class="animated infinite zoomIn kenit-alo-circle"></div>
                <div class="animated infinite pulse kenit-alo-circle-fill"></div>
                <i><img onerror="this.src='assets/images/noimage.png';" src="{{ upload('photo', $mxh['photo']) }}"
                        alt="{{ $mxh['namevi'] }}" title="{{ $mxh['namevi'] }}" width="35" /></i>
            </a>
        @endforeach
    </div>
    @push('styles')
        <link href="assets/css/social.css" rel="stylesheet">
    @endpush

    @push('scripts')
        <script type="text/javascript">
            $('#social').show(500);
        </script>
    @endpush
@endif