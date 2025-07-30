@foreach($languages as $language)
    <x-forms::table.cell>
        <div class="{{ config('translatable.styles.table-cell-wrapper') }}">
            @if($model->hasTranslation($language->code))
                <a class="actions__item {{ config('translatable.styles.icons.edit') }}"
                   href="{{ $getUrl('edit', $language->code) }}"
                   title="{{ _d('Edit Translation') }}">
                </a>
            @else
                <a class="actions__item text-decoration-none {{ config('translatable.styles.icons.add') }}"
                   href="{{ $getUrl($isModelDbTranslatable() ? 'create' : 'edit', $language->code) }}"
                   title="{{ _d('Add Translation') }}">
                </a>
            @endif
        </div>
    </x-forms::table.cell>
@endforeach
