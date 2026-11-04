<x-forms::alert type="info" :framework="$framework" :attributes="$attributes->merge(['data-translatable' => 'translation-notice'])">
    {{ $message() }}
</x-forms::alert>
<input type="hidden" name="lang" value="{{ $locale }}">
<input type="hidden" name="lang_parent" value="{{ $parent->getKey() }}">
