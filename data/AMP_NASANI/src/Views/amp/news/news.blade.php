@extends('layout')
@section('content')
    <section>
        <div class="wrap-page">
            <div class="wrap-content">
                <h1 class="title-main">{{ $titleMain }}</h1>
                @if (!empty($news))
                    <div class="row row-news  row-cols-2 row-cols-sm-2 row-cols-md-3">
                        @foreach ($news as $v)
                            <div class="col col-news">
                                @component('component.itemNews', ['v' => $v])
                                @endcomponent
                            </div>
                        @endforeach
                    </div>
                @endif
                {!! $news->links() !!}
            </div>
        </div>
    </section>
@endsection
