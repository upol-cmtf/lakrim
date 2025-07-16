<div wire:click="loadQuestion" @class([
       'bg-white cursor-pointer outline outline-1 outline-gray-300' => $hide,
       'p-4 aspect-square w-20 md:w-32 text-center justify-center flex items-center'
   ])
>
    @if ($hide)
        <span class="text-2xl font-sans">{{ $position }}</span>
    @endif
</div>
