@extends('layouts.backend')
@section('content')
@include('layouts.components-backend.css')

@php
    $essayAnswers   = $essayAnswers ?? collect();
    $groupedByUser  = $essayAnswers->groupBy('hasilUjian.user_id');
    $totalUsers     = $groupedByUser->count();
    $pendingCount   = $essayAnswers->count();

    $baseQuery = fn($q) => $q->whereHas('soal', fn($q) => $q->where('tipe', 'essay'))
                             ->whereHas('hasilUjian.quiz', fn($q) => $q->where('user_id', Auth::id()));

    $gradedQuery  = App\Models\HasilUjianDetail::query();
    $baseQuery($gradedQuery);
    $gradedQuery->whereIn('status_jawaban', ['benar', 'salah', 'sebagian']);

    $gradedCount     = (clone $gradedQuery)->count();
    $totalEssays     = App\Models\HasilUjianDetail::query()->tap($baseQuery)->count();
    $progressPercent = $totalEssays > 0 ? round(($gradedCount / $totalEssays) * 100, 1) : 0;

    $gradedUsers = (clone $gradedQuery)->with('hasilUjian.user')
                    ->get()->groupBy('hasilUjian.user_id')->count();

    $gradedEssays = (clone $gradedQuery)->with(['hasilUjian.user', 'hasilUjian.quiz', 'soal'])
                    ->orderByDesc('updated_at')->get()->groupBy('hasilUjian.user_id');
@endphp

<div class="container-fluid">

    {{-- Header --}}
    <div class="card bg-gradient-primary shadow-sm position-relative overflow-hidden mb-5">
        <div class="card-body px-4 py-4">
            <div class="row align-items-center">
                <div class="col-9">
                    <h3 class="fw-bold mb-2 text-white">Penilaian Jawaban Esai</h3>
                    <p class="text-white-75 mb-3">Pilih peserta untuk menilai jawaban esai mereka.</p>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-light mb-0">
                            <li class="breadcrumb-item">
                                <a class="text-white text-decoration-none" href="{{ route('admin.quiz-terbaru') }}">
                                    <i class="ti ti-home me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-white-75">Penilaian Esai</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-3 text-center">
                    <img src="{{ asset('assets/backend/images/breadcrumb/ChatBc.png') }}"
                         alt="essay" class="img-fluid" style="max-height:120px;">
                </div>
            </div>
        </div>
        <div class="position-absolute top-0 end-0 opacity-25">
            <div class="bg-white rounded-circle" style="width:200px;height:200px;transform:translate(50px,-50px);"></div>
        </div>
        <div class="position-absolute bottom-0 start-0 opacity-25">
            <div class="bg-white rounded-circle" style="width:150px;height:150px;transform:translate(-75px,75px);"></div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-2">
        @foreach([
            ['warning', 'ti-users',     $totalUsers,     'Peserta Perlu Dinilai'],
            ['info',    'ti-clock',     $pendingCount,   'Esai Menunggu'],
            ['success', 'ti-check',     $gradedCount,    'Esai Sudah Dinilai'],
            ['primary', 'ti-chart-pie', $progressPercent.'%', 'Progress Penilaian'],
        ] as [$color, $icon, $val, $label])
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm stats-card text-center py-4">
                <div class="rounded-circle bg-{{ $color }}-subtle d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:60px;height:60px;">
                    <i class="ti {{ $icon }} text-{{ $color }}" style="font-size:24px;"></i>
                </div>
                <h4 class="fw-bold text-{{ $color }} mb-1">{{ $val }}</h4>
                <p class="text-muted mb-0">{{ $label }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Tab Nav --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <ul class="nav nav-pills" id="gradingTabs">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pending-content">
                                <i class="ti ti-clock me-2"></i>Perlu Dinilai
                                <span class="badge bg-warning ms-2">{{ $totalUsers }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="graded-tab" data-bs-toggle="pill" data-bs-target="#graded-content">
                                <i class="ti ti-check me-2"></i>Sudah Dinilai
                                <span class="badge bg-success ms-2">{{ $gradedUsers }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('quiz.essay.stats') }}" class="btn btn-info me-2">
                        <i class="ti ti-chart-bar me-2"></i>Statistik
                    </a>
                    <a href="{{ route('quiz.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @foreach(['success', 'error' => 'danger'] as $type => $bsType)
        @if(session(is_string($type) ? $type : $bsType))
        <div class="alert alert-{{ $bsType }} alert-dismissible fade show">
            <i class="ti ti-{{ $bsType === 'danger' ? 'alert-circle' : 'check' }} me-2"></i>
            {{ session(is_string($type) ? $type : $bsType) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
    @endforeach

    {{-- Tab Content --}}
    <div class="tab-content">

        {{-- Pending Tab --}}
        <div class="tab-pane fade show active" id="pending-content">
            @if($essayAnswers->count() > 0)
                <div class="row">
                    @foreach($groupedByUser as $userId => $userEssays)
                        @php
                            $user       = $userEssays->first()->hasilUjian->user;
                            $essayCount = $userEssays->count();
                            $quizCount  = $userEssays->groupBy('hasilUjian.quiz_id')->count();
                            $totalBobot = $userEssays->sum('bobot_soal');
                            $uniqueQuizzes = $userEssays->groupBy('hasilUjian.quiz_id');

                            $priorityColor = $essayCount > 5 ? 'danger' : ($essayCount > 2 ? 'warning' : 'success');
                            $priorityText  = $essayCount > 5 ? 'HIGH'   : ($essayCount > 2 ? 'MEDIUM'  : 'LOW');
                        @endphp
                        <div class="col-lg-6 mb-4">
                            <div class="user-card">
                                {{-- Header --}}
                                <div class="uc-header">
                                    <div class="d-flex gap-2 flex-grow-1">
                                        <div class="uc-avatar">{{ strtoupper(substr($user->name,0,2)) }}</div>
                                        <div>
                                            <h5 class="uc-name">{{ $user->name }}</h5>
                                            <p class="uc-email">{{ $user->email }}</p>
                                            <small class="text-muted">
                                                <i class="ti ti-calendar me-1"></i>
                                                Sejak {{ \Carbon\Carbon::parse($user->created_at)->locale('id')->isoFormat('MMMM YYYY') }}
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ $priorityColor }}">{{ $priorityText }}</span>
                                </div>

                                {{-- Stats --}}
                                <div class="uc-stats">
                                    @foreach([
                                        ['ti-file-text','essays-icon', $essayCount,  'ESAI'],
                                        ['ti-clipboard-list','quizzes-icon', $quizCount,  'QUIZ'],
                                        ['ti-star','points-icon',  $totalBobot, 'POIN'],
                                    ] as [$icon, $cls, $num, $lbl])
                                    <div class="uc-stat">
                                        <div class="uc-stat-icon {{ $cls }}"><i class="ti {{ $icon }}"></i></div>
                                        <div class="fw-bold fs-5">{{ $num }}</div>
                                        <div class="uc-stat-label">{{ $lbl }}</div>
                                    </div>
                                    @endforeach
                                </div>

                                {{-- Latest Quiz --}}
                                @foreach($uniqueQuizzes->take(1) as $quizId => $quizEssays)
                                    @php
                                        $quiz     = $quizEssays->first()->hasilUjian->quiz;
                                        $tanggal  = $quizEssays->first()->hasilUjian->tanggal_ujian;
                                        $isRecent = \Carbon\Carbon::parse($tanggal)->isAfter(\Carbon\Carbon::now()->subDays(7));
                                    @endphp
                                    <div class="uc-timeline">
                                        <div class="tl-dot"></div>
                                        <div class="ms-3">
                                            <div class="d-flex gap-1 flex-wrap mb-1">
                                                <span class="qbadge essays-badge">{{ $quizEssays->count() }} ESAI</span>
                                                <span class="qbadge points-badge">{{ $quizEssays->sum('bobot_soal') }}PT</span>
                                                @if($isRecent)<span class="qbadge new-badge">BARU</span>@endif
                                            </div>
                                            <div class="d-flex gap-3" style="font-size:10px;color:#6c757d;">
                                                <span># {{ $quiz->kode_quiz }}</span>
                                                <span><i class="ti ti-clock me-1"></i>{{ \Carbon\Carbon::parse($tanggal)->locale('id')->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                        <small class="ms-auto text-muted">{{ $quizCount }} total</small>
                                    </div>
                                @endforeach

                                {{-- Footer --}}
                                <div class="uc-footer">
                                    <div class="uc-footer-info">
                                        <div class="d-flex align-items-center gap-1" style="font-size:11px;color:#6c757d;">
                                            <i class="ti ti-clock"></i>
                                            <span>{{ $essayCount }} esai belum dinilai</span>
                                        </div>
                                        <div style="font-size:9px;" class="mt-1">
                                            @if($essayCount > 5)
                                                <span class="text-danger"><i class="ti ti-alert-triangle me-1"></i>Mendesak</span>
                                            @elseif($essayCount > 2)
                                                <span class="text-warning"><i class="ti ti-clock me-1"></i>Prioritas Sedang</span>
                                            @else
                                                <span class="text-success"><i class="ti ti-info-circle me-1"></i>Prioritas Rendah</span>
                                            @endif
                                        </div>
                                    </div>
                                    @foreach($uniqueQuizzes->take(1) as $quizId => $quizEssays)
                                        <a href="{{ $essayCount == 1
                                                ? route('quiz.essay.grade', $quizEssays->first()->id)
                                                : route('quiz.essay.grade-user', $userId) }}"
                                           class="action-btn grade-btn">
                                            <i class="ti ti-{{ $essayCount == 1 ? 'edit' : 'list-check' }}"></i>
                                            <div>
                                                <div class="btn-title">{{ $essayCount == 1 ? 'Beri Nilai' : 'Mulai Penilaian' }}</div>
                                                <div class="btn-sub">{{ $essayCount }} esai menunggu</div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($essayAnswers->hasPages())
                    <div class="d-flex justify-content-center mt-4">{{ $essayAnswers->links() }}</div>
                @endif
            @else
                <div class="card text-center py-5">
                    <div class="card-body">
                        <i class="ti ti-check-circle display-1 text-success mb-4"></i>
                        <h3 class="mb-3">Semua jawaban esai sudah dinilai!</h3>
                        <p class="text-muted mb-4">Tidak ada jawaban esai yang perlu dinilai saat ini.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('quiz.index') }}" class="btn btn-primary btn-lg">
                                <i class="ti ti-arrow-left me-2"></i>Kembali ke Daftar Quiz
                            </a>
                            <a href="{{ route('quiz.essay.stats') }}" class="btn btn-outline-primary btn-lg">
                                <i class="ti ti-chart-bar me-2"></i>Lihat Statistik
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Graded Tab --}}
        <div class="tab-pane fade" id="graded-content">
            @if($gradedEssays->count() > 0)
                <div class="row">
                    @foreach($gradedEssays as $userId => $userGradedEssays)
                        @php
                            $user          = $userGradedEssays->first()->hasilUjian->user;
                            $total         = $userGradedEssays->count();
                            $bobotDiperoleh= $userGradedEssays->sum('bobot_diperoleh');
                            $bobotSoal     = $userGradedEssays->sum('bobot_soal');
                            $avgScore      = $bobotSoal > 0 ? round(($bobotDiperoleh / $bobotSoal) * 100, 1) : 0;
                            $quizCount     = $userGradedEssays->groupBy('hasilUjian.quiz_id')->count();
                            $lastGraded    = $userGradedEssays->max('updated_at');
                            $correctCount  = $userGradedEssays->where('status_jawaban','benar')->count();
                            $partialCount  = $userGradedEssays->where('status_jawaban','sebagian')->count();
                            $incorrectCount= $userGradedEssays->where('status_jawaban','salah')->count();
                            $perfColor     = $avgScore >= 80 ? 'success' : ($avgScore >= 60 ? 'warning' : 'danger');
                            $perfText      = $avgScore >= 80 ? 'SANGAT BAIK' : ($avgScore >= 60 ? 'BAIK' : 'PERLU PERBAIKAN');
                        @endphp
                        <div class="col-lg-6 mb-4">
                            <div class="user-card graded-card">
                                {{-- Header --}}
                                <div class="uc-header">
                                    <div class="d-flex gap-2 flex-grow-1">
                                        <div class="uc-avatar graded-av">{{ strtoupper(substr($user->name,0,2)) }}</div>
                                        <div>
                                            <h5 class="uc-name">{{ $user->name }}</h5>
                                            <p class="uc-email">{{ $user->email }}</p>
                                            <small class="text-muted">
                                                <i class="ti ti-calendar me-1"></i>
                                                Dinilai: {{ \Carbon\Carbon::parse($lastGraded)->locale('id')->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                    <span class="badge bg-{{ $perfColor }}">{{ $perfText }}</span>
                                </div>

                                {{-- Stats --}}
                                <div class="uc-stats">
                                    @foreach([
                                        ['ti-file-check','graded-essays-icon', $total,      'EVALUASI'],
                                        ['ti-trophy',     'score-icon',         $avgScore.'%','RATA-RATA'],
                                        ['ti-star',       'points-icon',         $bobotDiperoleh.'/'.$bobotSoal,'POIN'],
                                    ] as [$icon, $cls, $num, $lbl])
                                    <div class="uc-stat">
                                        <div class="uc-stat-icon {{ $cls }}"><i class="ti {{ $icon }}"></i></div>
                                        <div class="fw-bold fs-5">{{ $num }}</div>
                                        <div class="uc-stat-label">{{ $lbl }}</div>
                                    </div>
                                    @endforeach
                                </div>

                                {{-- Performance --}}
                                <div class="uc-perf">
                                    <div class="uc-perf-label">Rincian Performa</div>
                                    <div class="perf-grid">
                                        @foreach([
                                            ['correct',   $correctCount,   'Benar'],
                                            ['partial',   $partialCount,   'Sebagian'],
                                            ['incorrect', $incorrectCount, 'Salah'],
                                        ] as [$cls, $cnt, $lbl])
                                        <div class="perf-item {{ $cls }}">
                                            <div class="perf-num">{{ $cnt }}</div>
                                            <div class="perf-lbl">{{ $lbl }}</div>
                                            <div class="perf-bar">
                                                <div class="perf-fill" style="width:{{ $total > 0 ? round($cnt/$total*100) : 0 }}%"></div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Footer --}}
                                <div class="uc-footer">
                                    <div class="uc-footer-info">
                                        <div style="font-size:11px;color:#6c757d;">
                                            <i class="ti ti-check-circle me-1"></i>{{ $total }} esai dinilai
                                        </div>
                                        <div style="font-size:11px;color:#6c757d;">
                                            <i class="ti ti-clipboard-list me-1"></i>{{ $quizCount }} quiz
                                        </div>
                                    </div>
                                    <button class="action-btn review-btn"
                                        onclick="showUserDetails({{ $userId }},'{{ $user->name }}','{{ $user->email }}',{{ $total }},{{ $avgScore }},{{ $correctCount }},{{ $partialCount }},{{ $incorrectCount }})">
                                        <i class="ti ti-eye"></i>
                                        <div>
                                            <div class="btn-title">Lihat Detail</div>
                                            <div class="btn-sub">Tampilkan ringkasan</div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card text-center py-5">
                    <div class="card-body">
                        <i class="ti ti-file-search display-1 text-muted mb-4"></i>
                        <h3 class="mb-3">Belum ada jawaban esai yang dinilai</h3>
                        <p class="text-muted mb-4">Mulai menilai jawaban esai untuk melihat hasil di sini.</p>
                        <button class="btn btn-primary" onclick="document.querySelector('[data-bs-target=\'#pending-content\']').click()">
                            <i class="ti ti-arrow-left me-2"></i>Mulai Menilai
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Detail --}}
<div class="modal fade" id="userDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title"><i class="ti ti-user me-2"></i>Detail Penilaian Peserta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center mb-4">
                        <div class="modal-avatar mx-auto mb-3" id="modalUserAvatar"></div>
                        <h4 id="modalUserName" class="mb-1"></h4>
                        <p class="text-muted" id="modalUserEmail"></p>
                    </div>
                    <div class="col-md-8">
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="stat-card-sm">
                                    <div class="stat-icon-sm bg-success-subtle"><i class="ti ti-file-check text-success"></i></div>
                                    <div><h3 id="modalTotalEssays" class="mb-0"></h3><small class="text-muted">Total Esai Dinilai</small></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card-sm">
                                    <div class="stat-icon-sm bg-warning-subtle"><i class="ti ti-trophy text-warning"></i></div>
                                    <div><h3 id="modalAvgScore" class="mb-0"></h3><small class="text-muted">Rata-rata Skor</small></div>
                                </div>
                            </div>
                        </div>
                        <h6 class="mb-3">Rincian Performa</h6>
                        @foreach([
                            ['success','ti-check-circle','Jawaban Benar','modalCorrectCount','modalCorrectBar'],
                            ['warning','ti-clock','Jawaban Sebagian','modalPartialCount','modalPartialBar'],
                            ['danger','ti-x-circle','Jawaban Salah','modalIncorrectCount','modalIncorrectBar'],
                        ] as [$color,$icon,$label,$countId,$barId])
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-{{ $color }}"><i class="ti {{ $icon }} me-1"></i>{{ $label }}</span>
                                <span id="{{ $countId }}" class="fw-bold"></span>
                            </div>
                            <div class="progress" style="height:8px;">
                                <div id="{{ $barId }}" class="progress-bar bg-{{ $color }}" role="progressbar"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-2"></i>Tutup
                </button>
                <button type="button" class="btn btn-primary" onclick="exportUserReport()">
                    <i class="ti ti-download me-2"></i>Export Report
                </button>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary: #5d87ff;
    --success: #13deb9;
    --warning: #ffae1f;
    --danger:  #fa896b;
    --info:    #539bff;
}

/* Utilities */
.text-white-75 { color: rgba(255,255,255,.75) !important; }
.bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
.bg-primary-subtle { background-color: rgba(93,135,255,.1) !important; }
.bg-success-subtle { background-color: rgba(19,222,185,.1) !important; }
.bg-warning-subtle { background-color: rgba(255,174,31,.1) !important; }
.bg-info-subtle    { background-color: rgba(83,155,255,.1) !important; }
.bg-danger-subtle  { background-color: rgba(250,137,107,.1) !important; }
.breadcrumb-light .breadcrumb-item+.breadcrumb-item::before { color: rgba(255,255,255,.7); }

/* Stats cards */
.stats-card { border-radius: 15px; transition: transform .3s, box-shadow .3s; }
.stats-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,.15); }

/* Tabs */
.nav-pills .nav-link { border-radius: 25px; padding: 10px 20px; margin-right: 10px; transition: all .3s; }
.nav-pills .nav-link.active { background: var(--primary); }
.nav-pills .nav-link:hover:not(.active) { background: rgba(93,135,255,.1); }

/* Tab animation */
.tab-pane { opacity: 0; transform: translateY(10px); transition: all .3s; }
.tab-pane.show.active { opacity: 1; transform: translateY(0); }

/* User Card */
.user-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
    transition: transform .3s, box-shadow .3s;
}
.user-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.12); }
.user-card.graded-card { border: 1px solid #e9ecef; }

.uc-header { padding: 16px; display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #f0f0f0; }
.uc-avatar {
    width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; font-size: 14px;
}
.uc-avatar.graded-av { background: linear-gradient(135deg, #13deb9, #0bb5a0); }
.uc-name  { font-size: 15px; font-weight: 600; color: #2c3e50; margin-bottom: 2px; }
.uc-email { font-size: 12px; color: #6c757d; margin-bottom: 2px; }

/* Stats row */
.uc-stats { display: grid; grid-template-columns: repeat(3,1fr); border-bottom: 1px solid #f0f0f0; }
.uc-stat  { padding: 14px 10px; text-align: center; border-right: 1px solid #f0f0f0; }
.uc-stat:last-child { border-right: none; }
.uc-stat-icon { width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 13px; margin: 0 auto 6px; }
.uc-stat-label { font-size: 9px; color: #6c757d; font-weight: 600; letter-spacing: .5px; }
.essays-icon        { background: #e3f2fd; color: #1976d2; }
.graded-essays-icon { background: #e8f5e9; color: #2e7d32; }
.quizzes-icon       { background: #e8f5e9; color: #2e7d32; }
.score-icon         { background: #fff8e1; color: #f57c00; }
.points-icon        { background: #fff3e0; color: #f57c00; }

/* Timeline */
.uc-timeline { display: flex; align-items: flex-start; gap: 0; padding: 12px 16px; border-bottom: 1px solid #f0f0f0; position: relative; }
.tl-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--success); border: 2px solid #fff; box-shadow: 0 0 0 1px var(--success); margin-top: 5px; flex-shrink: 0; }
.qbadge { padding: 2px 6px; border-radius: 10px; font-size: 8px; font-weight: 600; letter-spacing: .5px; }
.essays-badge { background: #e3f2fd; color: #1976d2; }
.points-badge { background: #fff3e0; color: #f57c00; }
.new-badge    { background: var(--success); color: #fff; }

/* Performance */
.uc-perf { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; }
.uc-perf-label { font-size: 11px; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; }
.perf-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; }
.perf-item { text-align: center; }
.perf-num  { font-size: 18px; font-weight: 700; }
.perf-lbl  { font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
.perf-bar  { height: 4px; background: #f0f0f0; border-radius: 2px; overflow: hidden; }
.perf-fill { height: 100%; transition: width .4s ease; }
.correct .perf-num,.correct .perf-lbl { color: var(--success); }
.correct .perf-fill { background: var(--success); }
.partial .perf-num,.partial .perf-lbl { color: var(--warning); }
.partial .perf-fill { background: var(--warning); }
.incorrect .perf-num,.incorrect .perf-lbl { color: var(--danger); }
.incorrect .perf-fill { background: var(--danger); }

/* Footer */
.uc-footer { padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; gap: 12px; background: #f8f9fa; }
.uc-footer-info { flex: 1; }

/* Buttons */
.action-btn {
    display: flex; align-items: center; gap: 8px;
    padding: 7px 14px; border: none; border-radius: 8px; color: #fff;
    font-size: 12px; font-weight: 500; text-decoration: none; cursor: pointer;
    transition: transform .2s, box-shadow .2s;
}
.action-btn:hover { transform: translateY(-1px); color: #fff; }
.grade-btn  { background: linear-gradient(135deg, #667eea, #764ba2); }
.grade-btn:hover  { box-shadow: 0 4px 10px rgba(102,126,234,.35); }
.review-btn { background: linear-gradient(135deg, #13deb9, #0bb5a0); }
.review-btn:hover { box-shadow: 0 4px 10px rgba(19,222,185,.35); }
.btn-title { font-size: 12px; font-weight: 600; line-height: 1.2; display: block; }
.btn-sub   { font-size: 9px; opacity: .8; line-height: 1; display: block; }

/* Modal */
.modal-avatar {
    width: 80px; height: 80px; border-radius: 50;
    background: linear-gradient(135deg, #13deb9, #0bb5a0);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 24px; font-weight: 700;
    box-shadow: 0 4px 12px rgba(19,222,185,.3);
}
.stat-card-sm { display: flex; align-items: center; gap: 10px; padding: 14px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef; }
.stat-icon-sm { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.stat-card-sm h3 { font-size: 22px; font-weight: 700; color: #2c3e50; }

/* Responsive */
@media (max-width: 768px) {
    .uc-header { flex-direction: column; gap: 10px; }
    .uc-footer { flex-direction: column; }
    .action-btn { width: 100%; justify-content: center; }
    .nav-pills { flex-direction: column; }
    .nav-pills .nav-link { margin-right: 0; margin-bottom: 8px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => bootstrap.Alert.getOrCreateInstance(el).close());
    }, 5000);

    // Loading state on grade buttons
    document.querySelectorAll('.grade-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.innerHTML = `<i class="ti ti-loader-2 spin"></i><div><div class="btn-title">Memuat...</div><div class="btn-sub">Harap tunggu</div></div>`;
            this.style.pointerEvents = 'none';
        });
    });

    // Animate perf bars when graded tab opens
    document.querySelector('#graded-content')?.addEventListener('shown.bs.tab', () => {
        setTimeout(() => {
            document.querySelectorAll('.perf-fill').forEach(el => {
                const w = el.style.width;
                el.style.width = '0%';
                setTimeout(() => el.style.width = w, 100);
            });
        }, 200);
    });

    // URL hash sync
    const hash = window.location.hash;
    if (hash === '#graded') document.querySelector('[data-bs-target="#graded-content"]')?.click();

    document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', e => {
            const t = e.target.getAttribute('data-bs-target');
            history.replaceState(null, null, t === '#graded-content' ? '#graded' : '#pending');
        });
    });
});

function showUserDetails(userId, name, email, total, avg, correct, partial, incorrect) {
    document.getElementById('modalUserAvatar').textContent = name.substring(0, 2).toUpperCase();
    document.getElementById('modalUserName').textContent   = name;
    document.getElementById('modalUserEmail').textContent  = email;
    document.getElementById('modalTotalEssays').textContent = total;
    document.getElementById('modalAvgScore').textContent    = avg + '%';
    document.getElementById('modalCorrectCount').textContent   = correct;
    document.getElementById('modalPartialCount').textContent   = partial;
    document.getElementById('modalIncorrectCount').textContent = incorrect;

    const sum = correct + partial + incorrect;
    [['modalCorrectBar', correct], ['modalPartialBar', partial], ['modalIncorrectBar', incorrect]]
        .forEach(([id, val]) => {
            document.getElementById(id).style.width = (sum > 0 ? (val / sum * 100) : 0) + '%';
        });

    bootstrap.Modal.getOrCreateInstance(document.getElementById('userDetailsModal')).show();
    window._currentUserId = userId;
}

function exportUserReport() {
    if (window._currentUserId) {
        alert('Fitur ekspor akan diimplementasikan sesuai kebutuhan backend.');
    }
}
</script>

<style>.spin { animation: spin 1s linear infinite; } @keyframes spin { to { transform: rotate(360deg); } }</style>
@endsection