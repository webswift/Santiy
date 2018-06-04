@extends('layouts.dashboard')
@section("title")
    Payment
@endsection
@section('css')
    <style type="text/css">
        .mainpanel{
            min-height: 1000px;
        }
    </style>
@endsection
@section('content')
    <iframe src="{{ route("user.payment.show") }}" frameborder="0" style="position: absolute;top: 50px;left: 0;z-index: 9999;width: 100%;"></iframe>
@endsection
@section('javascript')
    {!! Html::script('assets/js/custom.js') !!}
    <script type="text/javascript">
        $(document).ready(function() {
            var h = document.body.offsetHeight - 50;
            $("iframe").css("height", h + "px");
        });
    </script>
@stop