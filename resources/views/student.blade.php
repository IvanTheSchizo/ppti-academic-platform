@extends('layout')

@section('title', 'Student List - PPTI Academic Platform')
@section('menu_student', 'active')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Student List</h1>
        <button class="download-btn"><i class="fas fa-download"></i> Download</button>
    </div>

    <!-- Statistik Atas -->
    <div class="stats-bar">
        <div class="stat-item"><span class="dot dot-total"></span> Total <b>1087</b></div>
        <div class="stat-item"><span class="dot dot-active"></span> Active <b>167</b></div>
        <div class="stat-item"><span class="dot dot-graduated"></span> Graduated <b>567</b></div>
        <div class="stat-item"><span class="dot dot-leave"></span> On Leave <b>67</b></div>
    </div>

    <!-- Search & Filter -->
    <div class="filter-bar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" class="search-input" placeholder="Search...">
        </div>
        <button class="filter-btn"><i class="fas fa-filter"></i> Filter</button>
    </div>

    <!-- Tabel Mahasiswa -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Batch</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>GPA</th>
                </tr>
            </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td>{{ $student->classGroup->batch->batch_name }}</td>
                                <td>{{ $student->nim }}</td>
                                <td>{{ $student->name }}</td>
                                <td>
                                    <span class="badge">
                                        {{ $student->status }}
                                    </span>
                                </td>
                                <td>{{ $student->cumulative_gpa }}</td>
                            </tr>
                        @endforeach
        </tbody>
        </table>

        <!-- Footer Tabel & Pagination -->
        <div class="table-footer">
            <span>10 Result(s) Show: <select style="padding: 2px 4px; border: 1px solid #d1d5db; border-radius: 3px; font-family: 'Open Sans', sans-serif;"><option>10</option></select></span>
            <div class="pagination">
                <button class="page-btn"><i class="fas fa-angle-double-left"></i></button>
                <button class="page-btn"><i class="fas fa-angle-left"></i></button>
                <button class="page-btn"><i class="fas fa-angle-right"></i></button>
                <button class="page-btn"><i class="fas fa-angle-double-right"></i></button>
                <span style="margin-left: 8px;">Page: <select style="padding: 2px 4px; border: 1px solid #d1d5db; border-radius: 3px; font-family: 'Open Sans', sans-serif;"><option>1</option></select></span>
            </div>
        </div>
    </div>
@endsection