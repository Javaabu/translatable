@foreach($languages as $language)
    <x-forms::table.cell :framework="$framework" data-locale="{{ $language->code }}">
        <div class="{{ $layoutClasses('center') }}">
            @php($url = $translationUrl($language))
            @if($url)
                <a href="{{ $url }}" title="{{ $linkTitle($language) }}" aria-label="{{ $linkTitle($language) }}" data-translated="{{ $isTranslated($language) ? 'true' : 'false' }}">
                    <x-forms::icon :name="$isTranslated($language) ? 'pencil' : 'plus'" :framework="$framework" />
                </a>
            @else
                <span title="{{ $linkTitle($language) }}" data-translated="{{ $isTranslated($language) ? 'true' : 'false' }}">
                    <x-forms::icon :name="$isTranslated($language) ? 'check' : 'plus'" :framework="$framework" />
                </span>
            @endif
        </div>
    </x-forms::table.cell>
@endforeach
