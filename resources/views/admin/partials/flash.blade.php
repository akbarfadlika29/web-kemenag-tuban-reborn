@if (session('success'))
    <x-ui.alert
        variant="success"
        dismissible
    >
        {{ session('success') }}
    </x-ui.alert>
@endif

@if (session('error'))
    <x-ui.alert
        variant="danger"
        dismissible
    >
        {{ session('error') }}
    </x-ui.alert>
@endif

@if ($errors->any())
    <x-ui.alert variant="danger">
        <strong>
            Periksa kembali data yang dimasukkan.
        </strong>

        <ul style="margin: 8px 0 0; padding-left: 18px;">
            @foreach ($errors->all() as $error)
                <li>
                    {{ $error }}
                </li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif