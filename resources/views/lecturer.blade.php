@extends('layout')

@section('title', 'Lecturer List - PPTI Academic Platform')
@section('menu_lecturer', 'active')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Lecturer List</h1>
        <button class="download-btn"><i class="fas fa-download"></i> Download</button>
    </div>

    <div class="stats-bar">
        <div class="stat-item"><span class="dot dot-total"></span> Total <b>50</b></div>
        <div class="stat-item"><span class="dot dot-active"></span> Active <b>30</b></div>
        <div class="stat-item"><span class="dot dot-inactive"></span> Inactive <b>20</b></div>
    </div>

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
                    <th>Lecturer Code</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr class="clickable-row" onclick="window.location='/lecturer/profile'">
                    <td>D010101</td>
                    <td>Dr. Hendra Wijaya</td>
                    <td>hendra.wijaya@binus.edu</td>
                    <td><span class="badge badge-active">Active</span></td>
                </tr>
                <tr>
                    <td>D010101</td>
                    <td>Dr. Sarah Tanuwijaya</td>
                    <td>sarah.tanuwijaya@binus.edu</td>
                    <td><span class="badge badge-active">Active</span></td>
                </tr>
                <tr>
                    <td>D010101</td>
                    <td>Prof. Agus Setiawan</td>
                    <td>agus.setiawan@binus.edu</td>
                    <td><span class="badge badge-active">Active</span></td>
                </tr>
                <tr>
                    <td>D010101</td>
                    <td>Dr. Rina Kusuma</td>
                    <td>rina.kusuma@binus.edu</td>
                    <td><span class="badge badge-active">Active</span></td>
                </tr>
                <tr>
                    <td>D010101</td>
                    <td>Dr. Ahmad Fauzi</td>
                    <td>ahmad.fauzi@binus.edu</td>
                    <td><span class="badge badge-inactive">Inactive</span></td>
                </tr>
                <tr>
                    <td>D010101</td>
                    <td>Dr. Budi Santoso</td>
                    <td>budi.santoso@binus.edu</td>
                    <td><span class="badge badge-active">Active</span></td>
                </tr>
                <tr>
                    <td>D010101</td>
                    <td>Prof. Maya Indah</td>
                    <td>maya.indah@binus.edu</td>
                    <td><span class="badge badge-active">Active</span></td>
                </tr>
                <tr>
                    <td>D010101</td>
                    <td>Dr. Kevin Gunawan</td>
                    <td>kevin.gunawan@binus.edu</td>
                    <td><span class="badge badge-active">Active</span></td>
                </tr>
                <tr>
                    <td>D010101</td>
                    <td>Dewi Lestari, M.Kom.</td>
                    <td>dewi.lestari@binus.edu</td>
                    <td><span class="badge badge-active">Active</span></td>
                </tr>
                <tr>
                    <td>D010101</td>
                    <td>Dr. Eko Prasetyo</td>
                    <td>eko.prasetyo@binus.edu</td>
                    <td><span class="badge badge-inactive">Inactive</span></td>
                </tr>
            </tbody>
        </table>

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