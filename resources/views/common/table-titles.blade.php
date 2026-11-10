@foreach($languages as $language)
    <x-forms::table.heading :framework="$framework" :label="$language->native_name" class="text-center" lang="{{ $language->locale }}" data-locale="{{ $language->code }}" />
@endforeach
