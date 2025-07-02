@foreach($languages as $language)
    <x-forms::table.heading class="text-center">
        <img src="{{ $language->flag_url }}" class="flag-thumb" alt="{{ $language->code }}">
    </x-forms::table.heading>
@endforeach
