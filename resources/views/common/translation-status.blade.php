@php($items = $items())
@if($items)
    <ul {{ $attributes->merge(['class' => 'translation-status list-none p-0 m-0 ' . $layoutClasses('inline-list')]) }} aria-label="{{ __('translatable::strings.translation_status') }}" data-translatable="translation-status">
        @foreach($items as $item)
            <li data-locale="{{ $item['locale'] }}" data-translated="{{ $item['translated'] ? 'true' : 'false' }}">
                @if($item['url'])
                    <a href="{{ $item['url'] }}" title="{{ $description($item) }}" aria-label="{{ $description($item) }}">
                @else
                    <span title="{{ $description($item) }}">
                @endif
                    <x-forms::status :color="$color($item)" :framework="$framework" :lang="$item['language']['locale']">
                        <x-forms::icon :name="$item['translated'] ? 'check' : 'plus'" :framework="$framework" />
                        {{ $item['language']['native_name'] }}
                    </x-forms::status>
                @if($item['url'])
                    </a>
                @else
                    </span>
                @endif
            </li>
        @endforeach
    </ul>
@endif
