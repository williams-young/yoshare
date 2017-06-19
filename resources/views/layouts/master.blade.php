@extends('layouts.frame')

@section('body')
    <body class="hold-transition skin-green sidebar-mini">
    <div class="wrapper">

        @include('widgets.header')

        @include('widgets.menu')

        @yield('css')

        @yield('content')

        @include('widgets.footer')

        @include('widgets.sidebar')

        @yield('js')

    </div>
    <!-- ./wrapper -->
    </body>
@endsection
