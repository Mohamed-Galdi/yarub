
 <button
     {{ $attributes->merge(['class' => 'relative py-2 rounded-md text-gray-200 bg-primary hover:text-primary isolation-auto z-10 border-2 border-pr-500 before:absolute before:w-full before:transition-all before:duration-700 before:hover:w-full before:-left-full before:hover:left-0 before:rounded-full before:bg-gray-200 before:-z-10 before:aspect-square before:hover:scale-150 overflow-hidden before:hover:duration-700 transition-all duration-700']) }}>
     @if ($slot->isEmpty())
        click here
     @else
         {{ $slot }}
     @endif
 </button>
