@extends('layout')

@section('content')
    <h1>Admin Dashboard</h1>

    <p>Authentication is working.</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button type="submit">Logout</button>
    </form>
@endsection