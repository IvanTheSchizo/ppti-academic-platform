@extends('layout')

@section('title', 'Course Records - PPTI Academic Platform')
@section('menu_course', 'active')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Course Records</h1>
        <button class="download-btn"><i class="fas fa-download"></i> Download</button>
    </div>

    <!-- Search & Filter tanpa Stats Bar -->
    <div class="filter-bar">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" class="search-input" placeholder="Search...">
        </div>
        <button class="filter-btn"><i class="fas fa-filter"></i> Filter</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Record Code</th>
                    <th>Period</th>
                    <th>Course ID</th>
                    <th>Class</th>
                    <th>Lecturer Code</th>
                    <th>Students</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>CR-2025O-001</td>
                    <td>2025 - Odd</td>
                    <td>COMP6047</td>
                    <td>L4BC</td>
                    <td>D5821</td>
                    <td>18</td>
                </tr>
                <tr>
                    <td>CR-2025O-002</td>
                    <td>2025 - Odd</td>
                    <td>COMP6047</td>
                    <td>L4CC</td>
                    <td>D5821</td>
                    <td>24</td>
                </tr>
                <tr>
                    <td>CR-2025O-003</td>
                    <td>2025 - Odd</td>
                    <td>COMP6048</td>
                    <td>LAB1</td>
                    <td>D5821</td>
                    <td>18</td>
                </tr>
                <tr>
                    <td>CR-2025O-004</td>
                    <td>2025 - Odd</td>
                    <td>COMP6056</td>
                    <td>T4BC</td>
                    <td>D6124</td>
                    <td>35</td>
                </tr>
                <tr>
                    <td>CR-2024E-011</td>
                    <td>2024 - Even</td>
                    <td>COMP6100</td>
                    <td>L3AC</td>
                    <td>D4902</td>
                    <td>40</td>
                </tr>
            </tbody>
        </table>

        <div class="table-footer">
            <span>10 Result(s) Show: <select style="padding: 2px 4px; border: 1px solid #d1d5db; border-radius: 3px; font-family: 'Open Sans', sans-serif;"><option>5</option></select></span>
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