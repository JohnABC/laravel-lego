<?php /** @var \JA\Lego\Widget\Button\Button $button */ ?>

@if($link = $button->getLink())
    <a href="{{ $link }}" {!! $attributes !!}>{{ $button->getText() }}</a>
@else
    <button {!! $attributes !!}>{{ $button->getText() }}</button>
@endif

@if($button->getPrevent())
    @push('ja-lego-scripts')
    <script>
        $('#{{ $button->getId() }}').on('click', function () {
            var button = this;
            setTimeout(function () {
                $(button).attr('disabled', true).attr("data-href", $(button).attr("href")).attr('href', 'javascript:;');
            }, 0);
        });
    </script>
    @endpush
@endif
