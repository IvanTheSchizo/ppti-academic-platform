@extends('layouts.app')

@section('title', 'Grades')

@php
    $grades = [
        ['nim' => '2702347250', 'student' => 'Nicholas Victorio', 'course' => 'Algorithm and Programming', 'numeric' => '3.90', 'letter' => 'A'],
        ['nim' => '2702347250', 'student' => 'Nicholas Victorio', 'course' => 'Data Structures', 'numeric' => '3.80', 'letter' => 'A-'],
        ['nim' => '2702347251', 'student' => 'Budi Santoso', 'course' => 'Algorithm and Programming', 'numeric' => '3.40', 'letter' => 'B+'],
        ['nim' => '2702347252', 'student' => 'Siti Rahma', 'course' => 'Database Systems', 'numeric' => '3.70', 'letter' => 'A-'],
    ];
@endphp

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Grades</h1>
        <x-ui.button type="button">Add Grade</x-ui.button>
    </div>

    <x-ui.card>
        <x-ui.table :headings="['NIM', 'Student', 'Course', 'Numeric', 'Letter']">
            @foreach ($grades as $grade)
                <tr>
                    <td>{{ $grade['nim'] }}</td>
                    <td>{{ $grade['student'] }}</td>
                    <td>{{ $grade['course'] }}</td>
                    <td>{{ $grade['numeric'] }}</td>
                    <td>{{ $grade['letter'] }}</td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.card>
@endsection
