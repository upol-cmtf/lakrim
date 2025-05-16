<div>
    @if(!$completed)
        <div id="main-grid" class="grid grid-cols-4 mt-5">
            @for ($i = 1; $i <= 16; $i++)
                <livewire:web.revealing-image-item :position="$i" :key="'tile-' . $i"/>
            @endfor
        </div>

        <div class="text-center mt-2">
            Vyřešeno: <span class="font-semibold">{{ $this->solved }}</span>
        </div>
    @endif

    @if($completed)
        <div class="flex items-center justify-center max-w-2xl aspect-square">
            <video controls autoPlay>
                <source
                        src="{{ asset('videos/policie.mp4') }}"
                        type="video/mp4"
                />
                Your browser does not support the video tag.
            </video>
        </div>
    @endif
</div>
