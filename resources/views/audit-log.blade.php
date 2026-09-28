@extends('layout')

@section('title', 'Audit Log - PPTI Academic Platform')
@section('menu_audit', 'active')

@section('content')
    <style>
        /* Tambahan styling khusus halaman Audit Log */
        .page-header-alt { display: flex; justify-content: space-between; align-items: center; padding-bottom: 20px; border-bottom: 1px solid #e5e7eb; margin-bottom: 20px; }
        .audit-filters { display: flex; gap: 15px; margin-bottom: 20px; align-items: center; }
        .filter-select { padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; outline: none; font-family: 'Open Sans', sans-serif; color: #4b5563; min-width: 140px; background: white; }
        .action-pill { background-color: #f3f4f6; color: #6b7280; font-size: 11px; font-weight: 600; padding: 4px 12px; border-radius: 12px; border: 1px solid #e5e7eb; display: inline-block; }
        .target-entity { color: #3b82f6; font-weight: 600; }
        .mono-text { font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #4b5563; }
        
        /* Modifikasi tabel khusus Audit biar nampung teks dua baris */
        .table-container td { vertical-align: top; line-height: 1.5; }
    </style>

    <div class="page-header-alt">
        <h1 class="page-title">Audit Log</h1>
        <button class="download-btn"><i class="fas fa-download"></i> Download</button>
    </div>

    <div class="audit-filters">
        <div class="search-box" style="flex: none; width: 220px; margin-right: 0;">
            <i class="far fa-calendar-alt"></i>
            <input type="text" class="search-input" placeholder="Select date range">
        </div>
        <select class="filter-select">
            <option>All Admins</option>
        </select>
        <select class="filter-select">
            <option>All Actions</option>
        </select>
        <button class="filter-btn" style="background: white; border: 1px solid #e5e7eb;">
            <i class="fas fa-filter"></i> Reset
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Target Entity</th>
                    <th>Target ID</th>
                    <th>Old Value</th>
                    <th>New Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>2026-09-28<br>14:32</td>
                    <td>admin_budi</td>
                    <td><span class="action-pill">Update</span></td>
                    <td><span class="target-entity">Student</span></td>
                    <td class="mono-text">2201581234</td>
                    <td class="mono-text">grade: 3.5</td>
                    <td class="mono-text">grade: 3.8</td>
                </tr>
                <tr>
                    <td>2026-09-28<br>11:15</td>
                    <td>admin_sari</td>
                    <td><span class="action-pill">Create</span></td>
                    <td><span class="target-entity">Lecturer</span></td>
                    <td class="mono-text">EMP-2018-827</td>
                    <td class="mono-text">-</td>
                    <td class="mono-text">name: Dr. Rina Kusuma</td>
                </tr>
                <tr>
                    <td>2026-09-19<br>16:48</td>
                    <td>admin_budi</td>
                    <td><span class="action-pill">Update</span></td>
                    <td><span class="target-entity">CourseAssignment</span></td>
                    <td class="mono-text">88</td>
                    <td class="mono-text">lecturer: EMP-2012-<br>009</td>
                    <td class="mono-text">lecturer: EMP-2013-<br>008</td>
                </tr>
                <tr>
                    <td>2026-09-19<br>09:20</td>
                    <td>admin_dewi</td>
                    <td><span class="action-pill">Update</span></td>
                    <td><span class="target-entity">Student</span></td>
                    <td class="mono-text">2201581237</td>
                    <td class="mono-text">status: active</td>
                    <td class="mono-text">status: graduated</td>
                </tr>
                <tr>
                    <td>2026-09-18<br>13:05</td>
                    <td>admin_sari</td>
                    <td><span class="action-pill">Create</span></td>
                    <td><span class="target-entity">Course</span></td>
                    <td class="mono-text">15</td>
                    <td class="mono-text">-</td>
                    <td class="mono-text">courseName: Mobile<br>Development</td>
                </tr>
                <tr>
                    <td>2026-09-18<br>08:42</td>
                    <td>admin_budi</td>
                    <td><span class="action-pill">Update</span></td>
                    <td><span class="target-entity">Student</span></td>
                    <td class="mono-text">2201581235</td>
                    <td class="mono-text">cachedGpa: 3.40</td>
                    <td class="mono-text">cachedGpa: 3.45</td>
                </tr>
                <tr>
                    <td>2026-09-17<br>17:50</td>
                    <td>admin_sari</td>
                    <td><span class="action-pill">Update</span></td>
                    <td><span class="target-entity">Lecturer</span></td>
                    <td class="mono-text">EMP-2015-814</td>
                    <td class="mono-text">department: CS</td>
                    <td class="mono-text">department: Information<br>Systems</td>
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