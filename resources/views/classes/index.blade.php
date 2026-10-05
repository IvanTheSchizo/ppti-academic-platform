@extends('layouts.app')

@section('title', 'Classes')

@php
    $classes = [
        ['batch' => 'Batch 2027', 'code' => 'LA01', 'students' => 32],
        ['batch' => 'Batch 2027', 'code' => 'LB02', 'students' => 30],
        ['batch' => 'Batch 2028', 'code' => 'LA01', 'students' => 35],
    ];
@endphp

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Classes</h1>
        <x-ui.button type="button">Add Class</x-ui.button>
    </div>

    <x-ui.card>
        <x-ui.table :headings="['Batch', 'Class Code', 'Students']">
            @foreach ($classes as $class)
                <tr>
                    <td>{{ $class['batch'] }}</td>
                    <td>{{ $class['code'] }}</td>
                    <td>{{ $class['students'] }}</td>
                </tr>
            @endforeach
        </x-ui.table>
    </x-ui.card>
@endsection
