@extends('layout')

@section('title', 'Lecturer Profile - PPTI Academic Platform')
@section('menu_lecturer', 'active')

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

        /* Utility */
        .tab-content { display: none; animation: fadeIn 0.3s ease; }
        .tab-content.active { display: block; }
        .text-blue { color: #3b82f6; text-decoration: none; font-weight: 600; }
        .text-blue:hover { text-decoration: underline; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    <!-- Breadcrumb & Header -->
    <div class="breadcrumb">
        <i class="fas fa-angle-left"></i> <a href="/lecturer">Lecturer</a> / Dr. Hendra Wijaya
    </div>

    <div class="profile-header">
        <div class="profile-info">
            <div class="profile-avatar"><i class="far fa-user"></i></div>
            <div>
                <div class="profile-name">Dr. Hendra Wijaya</div>
                <div class="profile-id">D6767</div>
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
        <div class="tab-link" onclick="switchTab('courses', this)">Courses</div>
    </div>

    <!-- TAB 1: OVERVIEW -->
    <div id="overview" class="tab-content active">
        <!-- Academic Info Card -->
        <div class="info-card">
            <div class="card-header"><i class="fas fa-graduation-cap"></i> Academic Info</div>
            <div class="card-grid">
                <div>
                    <div class="info-label">Lecturer Code</div>
                    <div class="info-value">D6767</div>
                </div>
                <div>
                    <div class="info-label">Key Performance Index</div>
                    <div class="info-value">3.67</div>
                </div>
                <div>
                    <div class="info-label">Academic Functional Rank</div>
                    <div class="info-value">Lecturer Specialist - Professor</div>
                </div>
                <div>
                    <div class="info-label">Latest Education</div>
                    <div class="info-value">S3</div>
                </div>
                <div>
                    <div class="info-label">Status</div>
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
                    <div class="info-value">Dr. Hendra Wijaya, S.Kom., M.T.</div>
                </div>
                <div>
                    <div class="info-label">Gender</div>
                    <div class="info-value">Male</div>
                </div>
                <div>
                    <div class="info-label">Email 1</div>
                    <div class="info-value">hendra.wijaya@binus.edu</div>
                </div>
                <div>
                    <div class="info-label">Email 2</div>
                    <div class="info-value">hendra.wijaya@binus.ac.id</div>
                </div>
                <div>
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">+62 811-2345-6789</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: COURSES -->
    <div id="courses" class="tab-content">
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
                        <th>PERIOD</th>
                        <th>COURSE ID</th>
                        <th>COURSE NAME</th>
                        <th>CLASS</th>
                        <th>IKADQ</th>
                        <th>STUDENTS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2025 - Odd</td>
                        <td>COMP6047</td>
                        <td>Algorithm and Programming</td>
                        <td>L4BC</td>
                        <td>3.60</td>
                        <td>18</td>
                    </tr>
                    <tr>
                        <td>2025 - Odd</td>
                        <td>COMP6047</td>
                        <td>Algorithm and Programming</td>
                        <td>L4CC</td>
                        <td>3.80</td>
                        <td>24</td>
                    </tr>
                    <tr>
                        <td>2025 - Odd</td>
                        <td>COMP6048</td>
                        <td>Data Structures</td>
                        <td>LAB1</td>
                        <td>3.00</td>
                        <td>18</td>
                    </tr>
                    <tr>
                        <td>2024 - Even</td>
                        <td>COMP6056</td>
                        <td>Database Systems</td>
                        <td>T4BC</td>
                        <td>4.00</td>
                        <td>35</td>
                    </tr>
                    <tr>
                        <td>2024 - Even</td>
                        <td>COMP6100</td>
                        <td>Software Engineering</td>
                        <td>L3AC</td>
                        <td>3.50</td>
                        <td>40</td>
                    </tr>
                    <tr>
                        <td>2024 - Even</td>
                        <td>COMP6115</td>
                        <td>Object-Oriented Programming</td>
                        <td>L2QC</td>
                        <td>3.80</td>
                        <td>35</td>
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