@php($items = $items())
@if(count($items) > 1)
    <x-forms::nav-tabs
        :tabs="$items"
        :framework="$framework"
        :attributes="$attributes->merge(['class' => 'language-switcher', 'data-translatable' => 'language-switcher'])"
    />
@endif
