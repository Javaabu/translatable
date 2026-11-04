@if($isTranslatable())
    @if($layout === 'stacked')
        <div {{ $attributes->only(['class', 'id'])->merge(['class' => 'translatable-field']) }} data-translatable-field="{{ $name }}">
            @foreach($inputs as $input)
                <x-dynamic-component
                    :component="$inputComponent()"
                    :name="$input->name"
                    :label="$inputLabel($input)"
                    :model="$model"
                    :required="$isRequired($input)"
                    :framework="$framework"
                    :attributes="$attributes->except(['class', 'id'])->merge($inputAttributes($input))"
                />
            @endforeach
        </div>
    @else
        <x-forms::tabs
            :tabs="$tabs()"
            :active="$activeTab()"
            :framework="$framework"
            :attributes="$attributes->only(['class', 'id'])->merge(['class' => 'translatable-field', 'data-translatable-field' => $name])"
        >
            @foreach($inputs as $input)
                <x-slot :name="$slotName($input)">
                    <x-dynamic-component
                        :component="$inputComponent()"
                        :name="$input->name"
                        :label="$inputLabel($input)"
                        :model="$model"
                        :required="$isRequired($input)"
                        :framework="$framework"
                        :attributes="$attributes->except(['class', 'id'])->merge($inputAttributes($input))"
                    />
                </x-slot>
            @endforeach
        </x-forms::tabs>
    @endif
@else
    {{-- Not translatable (or translations are off): a plain field --}}
    <x-dynamic-component
        :component="$inputComponent()"
        :name="$name"
        :label="$label"
        :model="$model"
        :required="$required"
        :framework="$framework"
        :attributes="$attributes"
    />
@endif
