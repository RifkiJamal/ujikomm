<aside class="left-sidebar with-vertical">
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <a href="{{ Auth::user()->isAdmin ? route('admin.quiz-terbaru') : route('dashboard') }}" class="text-nowrap logo-img">
                <img src="{{ asset('/assets/backend/images/logos/logo-TestHivee.png') }}" class="dark-logo m-1" alt="Logo-Dark" style="width: 180px;" />
                <img src="{{ asset('/assets/backend/images/logos/logo-TestHivee.png') }}" class="light-logo m-1" alt="Logo-Light" style="width: 180px;" />
            </a>
            <a href="javascript:void(0)" class="sidebartoggler ms-auto text-decoration-none fs-5 d-block d-xl-none">
                <i class="ti ti-x"></i>
            </a>
        </div>

        <nav class="sidebar-nav scroll-sidebar" data-simplebar>
            @if (Auth::user()->isAdmin == '1' || Auth::user()->isAdmin == '2')
                @php
                    $pendingEssayCount = 0;
                    try {
                        $pendingEssayCount = App\Models\HasilUjianDetail::whereHas('soal', function($query) {
                            $query->where('tipe', 'essay');
                        })
                        ->whereHas('hasilUjian.quiz', function($query) {
                            $query->where('user_id', Auth::id());
                        })
                        ->where('status_jawaban', 'pending')
                        ->count();
                    } catch (\Exception $e) {
                        $pendingEssayCount = 0;
                    }
                @endphp

                <ul id="sidebarnav">
                    <li class="nav-small-cap">
                        <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                        <span class="hide-menu">Dashboard</span>
                    </li>
                    
                    <li class="sidebar-item {{ request()->routeIs('admin.quiz-terbaru') ? 'active' : '' }}">
                        <a class="sidebar-link justify-content-between" href="{{ route('admin.quiz-terbaru') }}">
                            <div class="d-flex align-items-center gap-3">
                                <span class="d-flex"><i class="ti ti-home"></i></span>
                                <span class="hide-menu">Beranda</span>
                            </div>
                            <span class="badge rounded-pill border border-primary text-primary fs-2 py-1 px-2">★</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('quiz.hasil.*') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('quiz.hasil.keseluruhan') }}">
                            <span class="d-flex"><i class="ti ti-chart-bar"></i></span>
                            <span class="hide-menu">Hasil Quiz</span>
                        </a>
                    </li>


                    <li class="nav-small-cap">
                        <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                        <span class="hide-menu">Kelola Data</span>
                    </li>

                    @if (Auth::user()->isAdmin == '2')
                    <li class="sidebar-item {{ request()->routeIs('users.index') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('users.index') }}">
                            <span class="d-flex"><i class="ti ti-users"></i></span>
                            <span class="hide-menu">User</span>
                        </a>
                    </li>
                    @endif

                    <li class="sidebar-item {{ request()->routeIs('kelas.index') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('kelas.index') }}">
                            <span class="d-flex"><i class="ti ti-school"></i></span>
                            <span class="hide-menu">Kelas</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('siswa.index') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('siswa.index') }}">
                            <span class="d-flex"><i class="ti ti-user-circle"></i></span>
                            <span class="hide-menu">Siswa</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('kategori.index') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('kategori.index') }}">
                            <span class="d-flex"><i class="ti ti-tags"></i></span>
                            <span class="hide-menu">Kategori</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('matapelajaran.index') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('matapelajaran.index') }}">
                            <span class="d-flex"><i class="ti ti-book"></i></span>
                            <span class="hide-menu">Mata Pelajaran</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow {{ request()->is('admin/quiz*') && !request()->routeIs('quiz.essay.*') ? 'active' : '' }}" 
                           data-bs-toggle="collapse" href="#quizSubmenu" aria-expanded="false">
                            <span class="d-flex align-items-center">
                                <i class="ti ti-chart-donut-3"></i>
                                <span class="hide-menu ms-3">Kelola Quiz</span>
                            </span>
                        </a>
                        <ul id="quizSubmenu" class="collapse first-level {{ request()->is('admin/quiz*') && !request()->routeIs('quiz.essay.*') ? 'show' : '' }}">
                            <li class="sidebar-item">
                                <a href="{{ route('quiz.index') }}" class="sidebar-link {{ request()->routeIs('quiz.index') ? 'active' : '' }}">
                                    <i class="ti ti-circle"></i>
                                    <span class="hide-menu">Lihat Quiz</span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a href="{{ route('quiz.create') }}" class="sidebar-link {{ request()->routeIs('quiz.create') ? 'active' : '' }}">
                                    <i class="ti ti-circle"></i>
                                    <span class="hide-menu">Buat Quiz</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>

            @else
                <ul id="sidebarnav">
                    <li class="nav-small-cap">
                        <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                        <span class="hide-menu">Beranda Quiz</span>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a class="sidebar-link justify-content-between" href="{{ route('dashboard') }}">
                            <div class="d-flex align-items-center gap-3">
                                <span class="d-flex"><i class="ti ti-home"></i></span>
                                <span class="hide-menu">Quiz Terbaru</span>
                            </div>
                            <span class="badge rounded-pill border border-primary text-primary fs-2 py-1 px-2">★</span>
                        </a>
                    </li>

                    <li class="sidebar-item {{ request()->routeIs('histori-pengerjaan') ? 'active' : '' }}">
                        <a class="sidebar-link" href="{{ route('histori-pengerjaan') }}">
                            <span class="d-flex"><i class="ti ti-history"></i></span>
                            <span class="hide-menu">Riwayat Pengerjaan</span>
                        </a>
                    </li>
                </ul>
            @endif
        </nav>
    </div>
</aside>

<style>
    /* Sidebar Active States */
    .sidebar-item.active > .sidebar-link {
        background-color: rgba(13, 110, 253, 0.05);
        font-weight: 600;
        color: #0d6efd !important;
        border-radius: 8px;
    }

    .sidebar-item.active i {
        color: #0d6efd;
    }

    /* Notification Badge Styling */
    .sidebar-link .badge {
        font-size: 0.7rem;
        min-width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Animations */
    .badge.bg-danger { animation: pulse 2s infinite; }
    .badge.bg-warning { animation: glow 2s infinite alternate; }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    @keyframes glow {
        from { box-shadow: 0 0 4px rgba(255, 193, 7, 0.4); }
        to { box-shadow: 0 0 10px rgba(255, 193, 7, 0.7); }
    }
</style>