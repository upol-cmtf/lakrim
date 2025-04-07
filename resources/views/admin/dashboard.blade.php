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
    </div>
@endsection
