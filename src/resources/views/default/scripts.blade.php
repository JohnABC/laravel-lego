@foreach(\JA\Lego\Facades\Asset::scripts() as $script)
    {!! \Collective\Html\HtmlFacade::script($script) !!}
@endforeach

@stack('ja-lego-scripts')
