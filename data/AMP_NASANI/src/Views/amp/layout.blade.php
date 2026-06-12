@extends('master_amp')
@section('contentmaster')
    @include('layout.header')
    @include('layout.menu')
    @includeWhen(!empty($slideshow), 'layout.slider')
    @includeWhen(\NASANICORE\Core\Support\Str::isNotEmpty(BreadCrumbs::get()), 'layout.breadcrumbs')
    @yield('content')
    @include('layout.footer')
@endsection
