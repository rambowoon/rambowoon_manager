@if (!empty($val) && empty(session()->get('popup')))
    <div class="modal fade show" id="popup" tabindex="-1" aria-labelledby="popupLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="popupLabel">{{ $val['namevi'] }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if (!empty($val['contentvi']))
                        <div class="mb-3">
                            {!! $val['contentvi'] !!}
                        </div>
                    @endif
                    <a href="{{ $val['link'] }}">
                        <img data-src="{{ upload('photo', $val['photo']) }}" alt="{{ $val['namevi'] }}"
                            class="lazy loaded w-100" src="{{ upload('photo', $val['photo']) }}"
                            data-was-processed="true">
                    </a>
                </div>
         
            </div>
        </div>
    </div>
@endif
@if (!empty(strpos($val['status'], 'repeat')))
    @php session()->unset('popup'); @endphp
@else
    @php session()->set('popup', true); @endphp
@endif
