<x-layout title="Feeling lucky?">
    <div>
        <label for="link">
            {{ $link->user->username }}'s unique link
            @if ($link->expiresAt())
                — valid until {{ $link->expiresAt()->toDayDateTimeString() }}
            @endif
        </label>
        <a id="link" href="{{ route('luck', $link) }}">{{ route('luck', $link) }}</a>
    </div>

    <div class="actions">
        <form method="POST" action="{{ route('link.regenerate', $link) }}">
            @csrf
            <button type="submit">Regenerate link</button>
        </form>

        <form method="POST" action="{{ route('link.deactivate', $link) }}">
            @csrf
            <button type="submit">Deactivate link</button>
        </form>
    </div>

    <hr>

    @if ($attempt)
        <div class="result">
            <p class="number">{{ $attempt['number'] }}</p>
            <p>{{ $attempt['result'] }}</p>
            <p>Prize: {{ $attempt['prize'] }}</p>
        </div>
    @endif

    <div class="actions">
        <form method="POST" action="{{ route('luck.generate', $link) }}">
            @csrf
            <button type="submit">Imfeelinglucky</button>
        </form>

        <form method="GET" action="{{ route('luck.history', $link) }}">
            <button type="submit">History</button>
        </form>
    </div>

    @isset($history)
        <div>
            <h2>Last {{ $history->count() }} tries</h2>
            <ul>
                @forelse ($history as $item)
                    <li>
                        <span>{{ $item->number }}</span>
                        <span>{{ $item->result->label() }}</span>
                        <span>Prize: {{ $item->prize }}</span>
                    </li>
                @empty
                    <li>No tries yet</li>
                @endforelse
            </ul>
        </div>
    @endisset
</x-layout>
