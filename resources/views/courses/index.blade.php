@extends('layouts.app')

@section('title', 'Courses')

@php
    $courses = [
        ['code' => 'COMP6047', 'name' => 'Algorithm and Programming', 'sks' => 4],
        ['code' => 'COMP6048', 'name' => 'Data Structures', 'sks' => 4],
        ['code' => 'COMP6049', 'name' => 'Database Systems', 'sks' => 4],
        ['code' => 'CHAR6013', 'name' => 'Character Building', 'sks' => 2],
    ];
@endphp

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Courses</h1>
        <x-ui.button type="button">Add Course</x-ui.button>
    </div>

    <x-ui.card>
        <x-ui.table :headings="['Code', 'Course Name', 'SKS']">
            @foreach ($courses as $course)
                <tr>
                    <td>{{ $course['code'] }}</td>
                    <td>{{ $course['name'] }}</td>
                    <td>{{ $course['sks'] }}</td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.card>
@endsection
