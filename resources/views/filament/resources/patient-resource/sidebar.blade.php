<div>
    <h2>{{ $title }}</h2>
    <p>{{ $description }}</p>

    <ul>
        @foreach ($navigationItems as $item)
        <li>
            <a href="{{ $item->getUrl() }}" class="{{ $item->isActive() ? 'active' : '' }}">
                {{ $item->getLabel() }}
                @if ($item->getBadge())
                <span class="badge">{{ $item->getBadge() }}</span>
                @endif
            </a>
        </li>
        @endforeach
    </ul>

    <div class="card">
        <div class="card-header">
            <h3>Patient Details</h3>
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> {{ $record->firstname }} {{ $record->lastname }}</p>
            <p><strong>DOB:</strong> {{ $record->dob }}</p>
            <p><strong>Gender:</strong> {{ $record->gender }}</p>
            <p><strong>Age:</strong> {{ $record->age }}</p>
        </div>
    </div>
</div>