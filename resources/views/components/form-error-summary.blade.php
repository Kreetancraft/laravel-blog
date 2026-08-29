@if ($errors->any())
    <flux:callout variant="danger" icon="exclamation-triangle" {{ $attributes }}>
        <flux:callout.heading>
            {{ trans_choice('{1}:count field needs attention|[2,*]:count fields need attention', $errors->count(), ['count' => $errors->count()]) }}
        </flux:callout.heading>
        <flux:callout.text>
            <ul class="list-disc ps-4">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </flux:callout.text>
    </flux:callout>
@endif
