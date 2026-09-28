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
                <tr class="clickable-row" onclick="window.location='/student/profile'">
                    <td>PPTI23</td>
                    <td>2702302864</td>
                    <td>Rusdi</td>
                    <td><span class="badge badge-active">Active</span></td>
                    <td>3.85</td>
                </tr>
                <tr>
                    <td>PPTI23</td>
                    <td>2201581235</td>
                    <td>Budi Santoso</td>
                    <td><span class="badge badge-active">Active</span></td>
                    <td>3.45</td>
                </tr>
                <tr>
                    <td>PPTI22</td>
                    <td>2201581236</td>
                    <td>Citra Dewi</td>
                    <td><span class="badge badge-graduated">Graduated</span></td>
                    <td>3.90</td>
                </tr>
                <tr>
                    <td>PPTI24</td>
                    <td>2201581237</td>
                    <td>Dimas Prasetyo</td>
                    <td><span class="badge badge-active">Active</span></td>
                    <td>3.10</td>
                </tr>
                <tr>
                    <td>PPTI24</td>
                    <td>2201581238</td>
                    <td>Eka Putri</td>
                    <td><span class="badge badge-active">Active</span></td>
                    <td>3.55</td>
                </tr>
                <tr>
                    <td>PPTI23</td>
                    <td>2201581239</td>
                    <td>Fajar Nugraha</td>
                    <td><span class="badge badge-active">Active</span></td>
                    <td>3.82</td>
                </tr>
                <tr>
                    <td>PPTI22</td>
                    <td>2201581240</td>
                    <td>Gita Savitri</td>
                    <td><span class="badge badge-graduated">Graduated</span></td>
                    <td>3.95</td>
                </tr>
                <tr>
                    <td>PPTI23</td>
                    <td>2201581241</td>
                    <td>Hendra Gunawan</td>
                    <td><span class="badge badge-leave">On Leave</span></td>
                    <td>3.40</td>
                </tr>
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