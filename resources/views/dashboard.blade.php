<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Monitoring & Evaluasi Diklat</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-900">Dashboard Penjagaan Diklat</h2>
                <p class="mt-1 text-sm text-slate-500">Sistem monitoring berbasis data untuk pelaksanaan dan evaluasi pelatihan</p>
                <p class="mt-1 text-xs text-slate-400">Terakhir diperbarui: {{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
    </x-slot>

    @php
        $metrics = $dashboard['metrics'];
        $programs = ($dashboard['programs_filtered'] ?? $dashboard['programs']);
        $bidangBreakdown = $dashboard['bidang_breakdown'];
        $bidangChartData = $dashboard['bidang_chart_data'] ?? collect($bidangBreakdown);
        $weeklyProgress = $metrics['weekly_progress'];
        $participantsList = ($dashboard['participants'] ?? collect());
    @endphp

    <div class="px-4 py-10 sm:px-6 lg:px-10 bg-gray-50 min-h-screen">
        <style>
            .kpi-card {
                background: #fff;
                border-radius: 1rem;
                padding: 1.5rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
                transition: transform 0.2s;
            }
            .kpi-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            }
            .kpi-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
            }
            .simple-bar {
                height: 8px;
                background: #e5e7eb;
                border-radius: 4px;
                overflow: hidden;
                position: relative;
            }
            .simple-bar-fill {
                height: 100%;
                border-radius: 4px;
                transition: width 0.3s ease;
            }
        </style>

        <!-- KPI Cards -->
        <section class="mt-8 mb-10 grid gap-6 lg:grid-cols-4">
            <div class="kpi-card" style="background: linear-gradient(135deg, #e0f2fe 0%, #fff 100%);">
                <div class="flex items-center justify-between mb-4">
                    <div class="kpi-icon" style="background: #bae6fd;">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-slate-600 mb-1">Jumlah Peserta Aktif</p>
                <p class="text-3xl font-bold text-slate-900">{{ number_format($metrics['active_participants']) }}</p>
                <p class="text-xs text-slate-500 mt-2">dari {{ number_format($metrics['total_participants']) }} total peserta</p>
            </div>

            <div class="kpi-card" style="background: linear-gradient(135deg, #dcfce7 0%, #fff 100%);">
                <div class="flex items-center justify-between mb-4">
                    <div class="kpi-icon" style="background: #86efac;">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-slate-600 mb-1">Persentase Kelulusan</p>
                <p class="text-3xl font-bold text-slate-900">{{ number_format($metrics['pass_rate'], 1) }}%</p>
                <p class="text-xs text-slate-500 mt-2">{{ number_format($metrics['passed_participants']) }} lulus dari {{ number_format($metrics['completed_participants'] + $metrics['active_participants']) }} peserta selesai dan aktif</p>
            </div>

            <div class="kpi-card" style="background: linear-gradient(135deg, #f3e8ff 0%, #fff 100%);">
                <div class="flex items-center justify-between mb-4">
                    <div class="kpi-icon" style="background: #c4b5fd;">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-slate-600 mb-1">Persentase Sertifikasi</p>
                <p class="text-3xl font-bold text-slate-900">{{ number_format($metrics['certification_rate'], 1) }}%</p>
                <p class="text-xs text-slate-500 mt-2">{{ number_format($metrics['certified_participants']) }} peserta tersertifikasi</p>
            </div>

            <div class="kpi-card" style="background: linear-gradient(135deg, #fef3c7 0%, #fff 100%);">
                <div class="flex items-center justify-between mb-4">
                    <div class="kpi-icon" style="background: #fde047;">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-slate-600 mb-1">Total Program Diklat</p>
                <p class="text-3xl font-bold text-slate-900">{{ count($programs) }}</p>
                <p class="text-xs text-slate-500 mt-2">{{ count($programs) }} program aktif</p>
            </div>
        </section>

        <!-- Simple Visualization Section -->
        <section class="mb-10 grid gap-6 lg:grid-cols-2">
            <!-- Progress Diklat per Minggu - Simple Bar Chart -->
            <div class="kpi-card">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Progress Diklat per Minggu</h3>
                @if(count($weeklyProgress) > 0)
                    <div class="space-y-3">
                        @foreach(array_slice($weeklyProgress, 0, 8) as $week)
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-slate-600">Minggu {{ $week['week'] }}</span>
                                    <span class="font-semibold text-slate-900">{{ number_format($week['completion'], 1) }}%</span>
                                </div>
                                <div class="simple-bar">
                                    <div class="simple-bar-fill bg-blue-500" style="width: {{ min(100, $week['completion']) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-8">Belum ada data progress mingguan</p>
                @endif
            </div>

            <!-- Status Peserta - Simple Visualization -->
            <div class="kpi-card">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Status Peserta Diklat</h3>
                <div class="text-center mb-6">
                    <p class="text-4xl font-bold text-slate-900">{{ number_format($metrics['pass_rate'], 1) }}%</p>
                    <p class="text-sm text-slate-500">Persentase Kelulusan</p>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-green-500 rounded"></div>
                            <span class="text-sm text-slate-600">Lulus</span>
                        </div>
                        <span class="font-semibold text-slate-900">{{ number_format($metrics['passed_participants']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-red-500 rounded"></div>
                            <span class="text-sm text-slate-600">Tidak Lulus</span>
                        </div>
                        <span class="font-semibold text-slate-900">{{ number_format($metrics['completed_participants'] - $metrics['passed_participants']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-orange-500 rounded"></div>
                            <span class="text-sm text-slate-600">Aktif</span>
                        </div>
                        <span class="font-semibold text-slate-900">{{ number_format($metrics['active_participants']) }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Realisasi per Bidang - Simple Table -->
        <section class="mb-10">
            <div class="kpi-card">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Realisasi Diklat per Bidang</h3>
                @if(count($bidangChartData) > 0)
                    <div class="space-y-4">
                        @foreach($bidangChartData as $bidang)
                            <div>
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-semibold text-slate-900">{{ $bidang['bidang'] }}</span>
                                    <span class="text-slate-600">Total: {{ $bidang['total'] }} | Aktif: {{ $bidang['active'] }} | Lulus: {{ $bidang['passed'] }}</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Total Peserta</div>
                                        <div class="simple-bar">
                                            <div class="simple-bar-fill bg-blue-500" style="width: {{ min(100, ($bidang['total'] / max(1, collect($bidangChartData)->max('total'))) * 100) }}%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Peserta Aktif</div>
                                        <div class="simple-bar">
                                            <div class="simple-bar-fill bg-orange-500" style="width: {{ min(100, ($bidang['active'] / max(1, collect($bidangChartData)->max('active'))) * 100) }}%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Peserta Lulus</div>
                                        <div class="simple-bar">
                                            <div class="simple-bar-fill bg-green-500" style="width: {{ min(100, ($bidang['passed'] / max(1, collect($bidangChartData)->max('passed'))) * 100) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-8">Belum ada data bidang</p>
                @endif
            </div>
        </section>

        <!-- Filter & Daftar Peserta -->
        <section class="mb-10 grid gap-6 lg:grid-cols-2">
            <div class="kpi-card h-[24rem] flex flex-col">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Peserta Diklat</h3>
                <form method="GET" action="{{ route('dashboard') }}" class="grid gap-3 sm:grid-cols-3 mb-4">
                    <div>
                        <label class="text-xs text-slate-600">Program</label>
                        <select name="program_id" class="mt-1 block w-full rounded-lg border border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua</option>
                            @foreach($programOptions ?? [] as $p)
                                <option value="{{ $p->id }}" @selected(($filters['program_id'] ?? '') == $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-slate-600">Bidang</label>
                        <select name="bidang" class="mt-1 block w-full rounded-lg border border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua</option>
                            @foreach($bidangOptions ?? [] as $b)
                                <option value="{{ $b }}" @selected(($filters['bidang'] ?? '') == $b)>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Terapkan</button>
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm">Reset</a>
                    </div>
                </form>
                <div class="overflow-x-auto flex-1">
                    <div class="h-full overflow-y-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-600 border-b border-slate-200">
                                    <th class="py-3 px-4 font-semibold">NAMA PESERTA</th>
                                    <th class="py-3 px-4 font-semibold">BIDANG</th>
                                    <th class="py-3 px-4 font-semibold">NAMA DIKLAT</th>
                                    <th class="py-3 px-4 font-semibold">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($participantsList as $enrollment)
                                    <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50">
                                        <td class="py-3 px-4 font-medium text-slate-900">{{ $enrollment->participant?->name }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $enrollment->program?->bidang ?? '-' }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $enrollment->program?->name ?? '-' }}</td>
                                        <td class="py-3 px-4">
                                            @php $s = $enrollment->computed_status; @endphp
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ $s === 'active' ? 'bg-blue-100 text-blue-700' : '' }}
                                                {{ $s === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                {{ $s === 'registered' ? 'bg-orange-100 text-orange-700' : '' }}
                                                {{ $s === 'dropped' ? 'bg-red-100 text-red-700' : '' }}">
                                                {{ ucfirst($s) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-slate-500">Belum ada peserta.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="kpi-card h-[24rem] flex flex-col">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Filter Program Diklat</h3>
                <form method="GET" action="{{ route('dashboard') }}" class="grid gap-3 sm:grid-cols-3 mb-4">
                    <div class="sm:col-span-3">
                        <label class="text-xs text-slate-600">Nama Program</label>
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="mt-1 block w-full rounded-lg border border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Cari nama program">
                    </div>
                    <div class="sm:col-span-3 flex items-center gap-2">
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Terapkan</button>
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm">Reset</a>
                    </div>
                </form>
                <div class="overflow-x-auto flex-1">
                    <div class="h-full overflow-y-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-600 border-b border-slate-200">
                                    <th class="py-3 px-4 font-semibold">NAMA DIKLAT</th>
                                    <th class="py-3 px-4 font-semibold">BIDANG</th>
                                    <th class="py-3 px-4 font-semibold">TANGGAL MULAI</th>
                                    <th class="py-3 px-4 font-semibold">TANGGAL SELESAI</th>
                                    <th class="py-3 px-4 font-semibold">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($programs as $program)
                                    <tr class="border-b border-slate-100 odd:bg-white even:bg-slate-50">
                                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $program['name'] }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $program['bidang'] }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $program['start_date'] }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $program['end_date'] }}</td>
                                        @php
                                            $today = now()->toDateString();
                                            $status = 'Aktif';
                                            if ($today > $program['end_date']) {
                                                $status = 'Selesai';
                                            } elseif ($today < $program['start_date']) {
                                                $status = 'Mendatang';
                                            }
                                        @endphp
                                        <td class="py-3 px-4 text-slate-600">{{ $status }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-500">Belum ada program diklat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ringkasan Program Diklat -->
        <section class="kpi-card h-[24rem] flex flex-col">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Ringkasan Program Diklat</h3>
            <div class="overflow-x-auto flex-1">
                <div class="h-full overflow-y-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-600 border-b border-slate-200">
                            <th class="py-3 px-4 font-semibold">PROGRAM DIKLAT</th>
                            <th class="py-3 px-4 font-semibold">BIDANG</th>
                            <th class="py-3 px-4 font-semibold">TOTAL PESERTA</th>
                            <th class="py-3 px-4 font-semibold">AKTIF</th>
                            <th class="py-3 px-4 font-semibold">LULUS</th>
                            <th class="py-3 px-4 font-semibold">% KELULUSAN</th>
                            <th class="py-3 px-4 font-semibold">% SERTIFIKASI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($programs as $program)
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="py-3 px-4 font-semibold text-slate-900">{{ $program['name'] }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $program['bidang'] }}</td>
                                <td class="py-3 px-4">{{ $program['total_enrollments'] }}</td>
                                <td class="py-3 px-4">{{ $program['active_enrollments'] }}</td>
                                <td class="py-3 px-4">{{ $program['passed_enrollments'] }}</td>
                                <td class="py-3 px-4">{{ $program['pass_rate'] }}%</td>
                                <td class="py-3 px-4">{{ $program['cert_rate'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500">Belum ada program diklat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
