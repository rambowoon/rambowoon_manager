@extends('layout')
@section('content')
    <section>
        <div class="wrap-page">
            <div class="wrap-content">
                <h3 class="title-main">{{ $titleMain }}</h3>
                @if (!empty($product))
                    <div class="row-product row row-cols-2 row-cols-sm-2 row-cols-md-3">
                        @foreach ($product as $v)
                            <div class="col-product col">
                                @component('component.itemProduct', ['product' => $v])
                                @endcomponent
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="pagination">{!! $product->appends(request()->query())->onEachSide(2)->links() !!}</div>
                <div class="clearfix"></div>
            </div>
        </div>
    </section>
@endsection
