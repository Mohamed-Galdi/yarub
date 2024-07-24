@if ($errors->any())
    <div class="text-red-500 text-sm bg-blackp p-3 rounded-lg mt-2">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
