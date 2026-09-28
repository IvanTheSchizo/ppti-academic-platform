@extends('layout')

@section('title', 'Student Profile - PPTI Academic Platform')
@section('menu_student', 'active')

@section('content')
    <style>
        /* Breadcrumb & Header */
        .breadcrumb { font-size: 13px; color: #6b7280; margin-bottom: 20px; }
        .breadcrumb a { color: #9ca3af; text-decoration: none; }
        .profile-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; }
        .profile-info { display: flex; gap: 15px; align-items: center; }
        .profile-avatar { width: 60px; height: 60px; border-radius: 50%; border: 2px solid #e5e7eb; display: flex; justify-content: center; align-items: center; font-size: 30px; color: #9ca3af; background: white; }
        .profile-name { font-size: 20px; font-weight: 600; color: #1f2937; margin-bottom: 4px; }
        .profile-id { font-size: 13px; color: #6b7280; }
        .profile-actions { display: flex; gap: 10px; }
        .btn-edit { background: white; border: 1px solid #d1d5db; color: #374151; padding: 8px 16px; border-radius: 4px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .btn-edit:hover { background-color: #f3f4f6; }
        
        /* Tabs */
        .tabs { display: flex; gap: 20px; border-bottom: 1px solid #e5e7eb; margin-bottom: 20px; }
        .tab-link { padding: 10px 0; font-size: 14px; font-weight: 600; color: #9ca3af; cursor: pointer; border-bottom: 3px solid transparent; transition: 0.2s; }
        .tab-link:hover { color: #4b5563; }
        .tab-link.active { color: #f97316; border-bottom-color: #f97316; }

        /* Cards & Grid */
        .info-card { background: white; border: 1px solid #e5e7eb; border-radius: 4px; padding: 20px; margin-bottom: 20px; }
        .card-header { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f3f4f6; padding-bottom: 12px; }
        .card-header i { color: #0f4a73; }
        .card-grid { display: grid; grid-template-columns: 1fr 1fr; gap: y-20px x-40px; row-gap: 20px; }
        .info-label { color: #9ca3af; margin-bottom: 4px; font-size: 11px; }
        .info-value { color: #374151; font-weight: 600; font-size: 13px; }

        /* Chart Bars */
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .chart-header .card-header { border: none; padding: 0; margin: 0; }
        .avg-gpa { font-size: 12px; color: #4b5563; font-weight: 600; }
        .bar-row { display: flex; align-items: center; gap: 15px; margin-bottom: 12px; font-size: 11px; color: #6b7280; }
        .bar-label { width: 70px; }
        .bar-track { flex: 1; background: #f3f4f6; height: 24px; display: flex; align-items: center; }
        .bar-fill { background: #0ea5e9; height: 100%; display: flex; align-items: center; justify-content: flex-end; padding-right: 10px; color: white; font-weight: 700; font-size: 11px; }
        
        /* Utility */
        .tab-content { display: none; animation: fadeIn 0.3s ease; }
        .tab-content.active { display: block; }
        .text-blue { color: #3b82f6; text-decoration: none; font-weight: 600; }
        .text-blue:hover { text-decoration: underline; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    <!-- Breadcrumb & Header -->
    <div class="breadcrumb">
        <i class="fas fa-angle-left"></i> <a href="/student">Student</a> / Rusdi
    </div>

    <div class="profile-header">
        <div class="profile-info">
            <div class="profile-avatar"><i class="far fa-user"></i></div>
            <div>
                <div class="profile-name">Rusdi</div>
                <div class="profile-id">2702302864</div>
            </div>
        </div>
        <div class="profile-actions">
            <button class="btn-edit"><i class="fas fa-pen"></i> Edit</button>
            <button class="download-btn"><i class="fas fa-download"></i> Download</button>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs">
        <div class="tab-link active" onclick="switchTab('overview', this)">Overview</div>
        <div class="tab-link" onclick="switchTab('grades', this)">Grades</div>
    </div>

    <!-- TAB 1: OVERVIEW -->
    <div id="overview" class="tab-content active">
        <!-- Academic Info Card -->
        <div class="info-card">
            <div class="card-header"><i class="fas fa-graduation-cap"></i> Academic Info</div>
            <div class="card-grid">
                <div>
                    <div class="info-label">Student ID</div>
                    <div class="info-value">2702302864</div>
                </div>
                <div>
                    <div class="info-label">Batch</div>
                    <div class="info-value">PPTI23</div>
                </div>
                <div>
                    <div class="info-label">Current Semester</div>
                    <div class="info-value">Semester 5 (2025 - Odd)</div>
                </div>
                <div>
                    <div class="info-label">Class</div>
                    <div class="info-value">1A</div>
                </div>
                <div>
                    <div class="info-label">Cumulative GPA</div>
                    <div class="info-value">3.85</div>
                </div>
                <div>
                    <div class="info-label">Academic Status</div>
                    <div class="info-value">Active</div>
                </div>
            </div>
        </div>

        <!-- Personal Information Card -->
        <div class="info-card">
            <div class="card-header"><i class="far fa-user"></i> Personal Information</div>
            <div class="card-grid">
                <div>
                    <div class="info-label">Full Name</div>
                    <div class="info-value">Rusdi</div>
                </div>
                <div>
                    <div class="info-label">Gender</div>
                    <div class="info-value">Male</div>
                </div>
                <div>
                    <div class="info-label">Email</div>
                    <div class="info-value">rusdi@ppti.ac.id</div>
                </div>
                <div>
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value">17 August 2004</div>
                </div>
                <div>
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">+62 812-9988-7766</div>
                </div>
                <div>
                    <div class="info-label">Address</div>
                    <div class="info-value">Jakarta, Indonesia</div>
                </div>
            </div>
        </div>

        <!-- Grade Point Chart Card -->
        <div class="info-card">
            <div class="chart-header">
                <div class="card-header"><i class="fas fa-chart-line"></i> Grade Point</div>
                <div class="avg-gpa">Average GPA : <b>3.85</b></div>
            </div>
            
            <div class="bar-row">
                <div class="bar-label">2023 - Odd</div>
                <div class="bar-track"><div class="bar-fill" style="width: 88%;">3.75</div></div>
            </div>
            <div class="bar-row">
                <div class="bar-label">2023 - Even</div>
                <div class="bar-track"><div class="bar-fill" style="width: 90%;">3.80</div></div>
            </div>
            <div class="bar-row">
                <div class="bar-label">2024 - Odd</div>
                <div class="bar-track"><div class="bar-fill" style="width: 92%;">3.90</div></div>
            </div>
            <div class="bar-row">
                <div class="bar-label">2024 - Even</div>
                <div class="bar-track"><div class="bar-fill" style="width: 93%;">3.95</div></div>
            </div>
        </div>
    </div>

    <!-- TAB 2: GRADES -->
    <div id="grades" class="tab-content">
        <div class="filter-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Search...">
            </div>
            <button class="btn-edit"><i class="fas fa-pen"></i> Edit</button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Course ID</th>
                        <th>Course</th>
                        <th>Lecturer</th>
                        <th>SCU</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2025 - Odd</td>
                        <td>COMP6062001</td>
                        <td>Compilation Techniques</td>
                        <td><a href="#" class="text-blue">Dr. Hendra Wijaya</a></td>
                        <td>4/0</td>
                        <td>A</td>
                    </tr>
                    <tr>
                        <td>2025 - Odd</td>
                        <td>COMP7116001</td>
                        <td>Computer Vision</td>
                        <td><a href="#" class="text-blue">Dr. Hendra Wijaya</a></td>
                        <td>2/0</td>
                        <td>A</td>
                    </tr>
                    <tr>
                        <td>2024 - Even</td>
                        <td>COMP6058001</td>
                        <td>Software Engineering</td>
                        <td><a href="#" class="text-blue">Prof. Agus Setiawan</a></td>
                        <td>2/1</td>
                        <td>A-</td>
                    </tr>
                    <tr>
                        <td>2024 - Even</td>
                        <td>COMP6023001</td>
                        <td>Database Systems</td>
                        <td><a href="#" class="text-blue">Dr. Rina Kusuma</a></td>
                        <td>4/2</td>
                        <td>B+</td>
                    </tr>
                    <tr>
                        <td>2024 - Even</td>
                        <td>COMP6091001</td>
                        <td>Mobile Development</td>
                        <td><a href="#" class="text-blue">Dr. Sarah Tanuwijaya</td>
                        <td>2/0</td>
                        <td>A</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Script Tab Switcher -->
    <script>
        function switchTab(tabId, element) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-link').forEach(link => link.classList.remove('active'));
            
            document.getElementById(tabId).classList.add('active');
            element.classList.add('active');
        }
    </script>
@endsection