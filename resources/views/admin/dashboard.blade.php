@extends('layouts.admin')

@section('content')
    <div class="container mx-5 my-5">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
        </div>

        <ul>
            <li>
                <a href="{{ route('admin.export.completed-questionnaires-by-student-id') }}"
                   class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600"
                >
                    Export vyplněných dotazníků dle ID studenta
                </a>
            </li>

            <li>
                <a href="{{ route('admin.export.questions-results') }}"
                   class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600"
                >
                    Export výsledků dle otázek
                </a>
            </li>
        </ul>

        <form method="get" action="{{ route('admin.export.questions-results') }}" class="mt-6 flex items-end gap-3">
            <label class="block">
                <span class="block text-sm font-medium">Export výsledků dle otázek jen pro událost (kurz)</span>
                <select name="event" class="mt-1 block rounded border border-gray-300 px-2 py-1" required>
                    <option value="" disabled selected>– vyberte název události –</option>
                    @foreach ($eventNames as $eventName)
                        <option value="{{ $eventName }}">{{ $eventName }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="rounded bg-blue-600 px-3 py-1 text-white hover:bg-blue-800">
                Exportovat
            </button>
        </form>
    </div>
@endsection
