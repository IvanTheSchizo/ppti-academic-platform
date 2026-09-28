<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List - PPTI Academic Platform</title>
    
    <!-- Manggil Google Fonts: Open Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Open Sans', sans-serif; }
        body { display: flex; height: 100vh; background-color: #f8fafc; overflow: hidden; }
        
        /* Sidebar Kiri */
        .sidebar { width: 250px; background-color: #0098d9; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-header { height: 70px; background-color: white; display: flex; align-items: center; padding: 0 12px; gap: 10px; border-bottom: 1px solid #e5e7eb; border-right: none; }
        
        /* Styling Logo Binus & Garis Pemisah */
        .sidebar-logo { height: 34px; width: auto; object-fit: contain; }
        .header-divider { width: 2px; height: 28px; background-color: #9ca3af; }
        .brand-text { font-size: 12px; font-weight: 600; color: #374151; letter-spacing: 0.3px; white-space: nowrap; }
        
        /* Navbar Menu (Font Regular / Tidak Bold) */
        .nav-menu { padding-top: 20px; display: flex; flex-direction: column; }
        .nav-link { padding: 16px 24px; color: white; text-decoration: none; font-size: 14px; font-weight: 400; display: flex; align-items: center; gap: 16px; transition: 0.2s; }
        .nav-link:hover { background-color: rgba(0,0,0,0.1); }
        .nav-link.active { background-color: #0f4a73; border-left: 4px solid white; font-weight: 600; }
        .nav-icon { width: 20px; text-align: center; font-size: 16px; }
        
        /* Layout Kanan */
        .main-wrapper { flex: 1; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        .topbar { height: 70px; background-color: white; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: flex-end; padding: 0 30px; flex-shrink: 0; }
        .profile-btn { color: #4b5563; font-size: 24px; cursor: pointer; }
        
        .content-area { padding: 30px; overflow-y: auto; flex: 1; }
        
        /* Header Title & Download Button */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .page-title { font-size: 22px; color: #1f2937; font-weight: 600; }
        .download-btn { background-color: #0f4a73; color: white; border: none; padding: 8px 16px; border-radius: 4px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .download-btn:hover { background-color: #0c3a5a; }

        /* Statistik Bar */
        .stats-bar { background: white; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 30px; font-size: 13px; color: #4b5563; font-weight: 600; }
        .stat-item { display: flex; align-items: center; gap: 8px; }
        .stat-item b { font-weight: 700; color: #1f2937; }
        .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .dot-total { background-color: #1f2937; }
        .dot-active { background-color: #22c55e; }
        .dot-graduated { background-color: #3b82f6; }
        .dot-leave { background-color: #ef4444; }

        /* Search & Filter Bar */
        .filter-bar { background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 15px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .search-box { position: relative; flex: 1; margin-right: 15px; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 13px; }
        .search-input { width: 100%; padding: 8px 12px 8px 35px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; outline: none; font-family: 'Open Sans', sans-serif; }
        .search-input:focus { border-color: #3b82f6; }
        
        .filter-btn { background: white; border: 1px solid #d1d5db; color: #374151; padding: 8px 16px; border-radius: 4px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .filter-btn:hover { background-color: #f3f4f6; }

        /* Tabel Data */
        .table-container { background: white; border: 1px solid #e5e7eb; border-radius: 6px; overflow: hidden; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
        th { background-color: #f3f4f6; color: #374151; font-weight: 700; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: #4b5563; font-weight: 400; }
        tr:hover { background-color: #f8fafc; }

        /* Badge Status */
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 14px; font-size: 12px; font-weight: 600; background-color: #ffffff; color: #374151; border: 1px solid #f3f4f6; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05); }
        .badge-active { background-color: #ffffff; color: #374151; border-color: #e5e7eb; }
        .badge-active::before { content: ""; width: 6px; height: 6px; background-color: #22c55e; border-radius: 50%; }
        
        .badge-graduated { background-color: #ffffff; color: #374151; border-color: #e5e7eb; }
        .badge-graduated::before { content: ""; width: 6px; height: 6px; background-color: #3b82f6; border-radius: 50%; }

        .badge-leave { background-color: #ffffff; color: #374151; border-color: #e5e7eb; }
        .badge-leave::before { content: ""; width: 6px; height: 6px; background-color: #ef4444; border-radius: 50%; }
        
        /* Pagination Footer */
        .table-footer { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: white; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; font-weight: 600; }
        .pagination { display: flex; align-items: center; gap: 6px; }
        .page-btn { border: 1px solid #d1d5db; background: white; padding: 4px 8px; border-radius: 4px; cursor: pointer; color: #374151; font-weight: 600; }
        .page-btn:hover { background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <!-- Sesuaikan nama file gambar logo kamu di sini -->
            <img src="{{ asset('images/binus.png') }}" alt="Binus Logo" class="sidebar-logo">
            <div class="brand-text">PPTI ACADEMIC PLATFORM</div>
        </div>
        <div class="nav-menu">
            <a href="#" class="nav-link active"><i class="fas fa-user nav-icon"></i> Student</a>
            <a href="#" class="nav-link"><i class="fas fa-chalkboard-teacher nav-icon"></i> Lecturer</a>
            <a href="#" class="nav-link"><i class="fas fa-history nav-icon"></i> Audit Log</a>
            <a href="#" class="nav-link"><i class="fas fa-graduation-cap nav-icon"></i> Course Records</a>
        </div>
    </div>

    <div class="main-wrapper">
        <div class="topbar">
            <i class="fas fa-user-circle profile-btn"></i>
        </div>

        <div class="content-area">
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
                        <tr>
                            <td>PPTI23</td>
                            <td>2702302864</td>
                            <td>Ayasi Kahfi</td>
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
        </div>
    </div>
</body>
</html>