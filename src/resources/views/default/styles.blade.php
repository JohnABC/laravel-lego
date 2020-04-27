@foreach(\JA\Lego\Facades\Asset::styles() as $style)
    {!! \Collective\Html\HtmlFacade::style($style) !!}
@endforeach

@stack('ja-lego-styles')
