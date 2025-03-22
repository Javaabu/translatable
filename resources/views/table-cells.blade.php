@foreach($languages as $language)
    <x-forms::table.cell>
        <div class="actions show-always">
            @if($model->hasTranslation($language->code))
                <a class="actions__item zmdi zmdi-edit"
                   href="{{ $model->getAdminLocalizedUrl($language->code) }}"
                   title="{{ _d('Edit Translation') }}">
                </a>
            @else
                <a class="actions__item zmdi zmdi-plus"
                   href="{{ $model->getAdminLocalizedUrl($language->code) }}"
                   title="{{ _d('Add Translation') }}">
                </a>
            @endif
        </div>
    </x-forms::table.cell>
@endforeach
