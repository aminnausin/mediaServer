<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header
        name="Log Files"
        title="Log Files"
        details="Track Application Log Files"
    >
        <x-slot:icon>
            <x-pulse::icons.circle-stack />
        </x-slot:icon>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        <div class="min-h-full flex flex-col">
            @if ($servers->isNotEmpty())
                @foreach ($servers as $server)
                    <x-pulse::table>
                        <x-pulse::thead>
                            <tr>
                                <x-pulse::th>Logfile</x-pulse::th>
                                <x-pulse::th class="text-right">Size</x-pulse::th>
                            </tr>
                        </x-pulse::thead>
                        <tbody>
                            @foreach ($server->logFiles as $logFile)
                                <tr class="h-2 first:h-0"></tr>
                                <tr wire:key="log-file-{{ $logFile->name }}">
                                    <x-pulse::td>
                                        <div class="flex items-center" title="{{ $logFile->name }}">
                                            <div>{{ $logFile->name }}</div>
                                        </div>
                                    </x-pulse::td>
                                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                        {{ $logFile->readableSize }}
                                    </x-pulse::td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-pulse::table>
                @endforeach
            @else
                <x-pulse::no-results />
            @endif
        </div>
    </x-pulse::scroll>
</x-pulse::card>
