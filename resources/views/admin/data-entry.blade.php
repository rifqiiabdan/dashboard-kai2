<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Manajemen Data</p>
            <h2 class="mt-1 text-2xl font-semibold text-slate-900">Data Entry</h2>
            <p class="text-sm text-slate-500">Kelola data program diklat, peserta, dan progress mingguan</p>
        </div>
    </x-slot>

    <div class="px-4 py-10 sm:px-6 lg:px-10">
        <!-- Popup Notification -->
        <div id="notification-popup" class="fixed bottom-0 right-0 m-2 z-50 hidden transform transition-all duration-300 ease-in-out translate-x-full opacity-0">
            <div class="bg-white rounded-lg shadow-xl border border-emerald-200 p-4 min-w-[300px] max-w-md">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-0.5">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p id="notification-message" class="text-sm font-medium text-slate-900"></p>
                    </div>
                    <button onclick="hideNotification()" class="flex-shrink-0 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-6 border-b border-slate-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="#program" onclick="showTab('program'); return false;" 
                   class="tab-link border-b-2 border-transparent py-4 px-1 text-sm font-medium text-slate-500 hover:border-slate-300 hover:text-slate-700" 
                   id="tab-program">
                    Program Diklat
                </a>
                <a href="#peserta" onclick="showTab('peserta'); return false;" 
                   class="tab-link border-b-2 border-transparent py-4 px-1 text-sm font-medium text-slate-500 hover:border-slate-300 hover:text-slate-700" 
                   id="tab-peserta">
                    Peserta
                </a>
                <a href="#progress" onclick="showTab('progress'); return false;" 
                   class="tab-link border-b-2 border-transparent py-4 px-1 text-sm font-medium text-slate-500 hover:border-slate-300 hover:text-slate-700" 
                   id="tab-progress">
                    Progress Mingguan
                </a>
            </nav>
        </div>

        <!-- Tab Content: Program Diklat -->
        <div id="content-program" class="tab-content">
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm h-[24rem] flex flex-col">
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Tambah Program Diklat</h3>
                    <p class="text-sm text-slate-500 mb-4">Isi detail jadwal agar progress dashboard bisa dihitung otomatis.</p>
                    <form method="POST" action="{{ route('admin.programs.store') }}" class="space-y-4" onsubmit="sessionStorage.setItem('activeTab', 'program');">
                        @csrf
                        <div>
                            <x-input-label for="program-name" value="Nama Program" />
                            <x-text-input id="program-name" name="name" class="mt-1 block w-full" value="{{ old('name') }}" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="program-bidang" value="Bidang" />
                            <x-text-input id="program-bidang" name="bidang" class="mt-1 block w-full" value="{{ old('bidang') }}" required />
                            <x-input-error :messages="$errors->get('bidang')" class="mt-1" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="program-start" value="Mulai" />
                                <x-text-input id="program-start" type="date" name="start_date" class="mt-1 block w-full" value="{{ old('start_date') }}" required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="program-end" value="Selesai" />
                                <x-text-input id="program-end" type="date" name="end_date" class="mt-1 block w-full" value="{{ old('end_date') }}" required />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="program-target" value="Target Peserta" />
                            <x-text-input id="program-target" type="number" min="0" name="target_participants" class="mt-1 block w-full" value="{{ old('target_participants', 0) }}" required />
                            <x-input-error :messages="$errors->get('target_participants')" class="mt-1" />
                        </div>
                        <x-primary-button>Tambah Program</x-primary-button>
                    </form>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Daftar Program</h3>
                    <div class="overflow-x-auto overflow-y-auto flex-1">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-600 border-b border-slate-200">
                                    <th class="py-2 px-3 font-semibold">NAMA</th>
                                    <th class="py-2 px-3 font-semibold">BIDANG</th>
                                    <th class="py-2 px-3 font-semibold">PESERTA</th>
                                    <th class="py-2 px-3 font-semibold">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentPrograms as $program)
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2 px-3 font-semibold text-slate-900">{{ $program->name }}</td>
                                        <td class="py-2 px-3 text-slate-600">{{ $program->bidang }}</td>
                                        <td class="py-2 px-3">{{ $program->enrollments_count ?? 0 }}</td>
                                        <td class="py-2 px-3">
                                            <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus program ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-slate-500">Belum ada program.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>

        <!-- Tab Content: Peserta -->
        <div id="content-peserta" class="tab-content hidden">
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Import Peserta (CSV)</h3>
                    <p class="text-sm text-slate-500 mb-4">Unggah file CSV (ekspor dari Excel) dengan header: participant_number, name, email, phone, program, bidang, status, score, certified. Nilai kolom <span class="font-semibold">status</span> boleh: Terdaftar, Aktif, Selesai, Keluar (atau registered, active, completed, dropped).</p>
                    <form method="POST" action="{{ route('admin.participants.import') }}" enctype="multipart/form-data" onsubmit="sessionStorage.setItem('activeTab', 'peserta');" class="space-y-3">
                        @csrf
                        <input type="file" name="file" accept=".csv,text/csv" class="block w-full text-sm border border-slate-300 rounded-lg" required />
                        <x-primary-button>Import CSV</x-primary-button>
                    </form>
                </section>
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Tambah Peserta</h3>
                    <p class="text-sm text-slate-500 mb-4">Nomor peserta harus unik karena dipakai untuk pencarian.</p>
                    <form method="POST" action="{{ route('admin.participants.store') }}" class="space-y-4" onsubmit="sessionStorage.setItem('activeTab', 'peserta');">
                        @csrf
                        <div>
                            <x-input-label for="participant-number" value="Nomor Peserta Diklat" />
                            <x-text-input id="participant-number" name="participant_number" class="mt-1 block w-full" value="{{ old('participant_number') }}" required />
                            <x-input-error :messages="$errors->get('participant_number')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="participant-name" value="Nama Peserta" />
                            <x-text-input id="participant-name" name="name" class="mt-1 block w-full" value="{{ old('name') }}" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="participant-email" value="Email" />
                                <x-text-input id="participant-email" type="email" name="email" class="mt-1 block w-full" value="{{ old('email') }}" />
                                <x-input-error :messages="$errors->get('email')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="participant-phone" value="No. Telepon" />
                                <x-text-input id="participant-phone" name="phone" class="mt-1 block w-full" value="{{ old('phone') }}" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                            </div>
                        </div>
                        
                        <div>
                            <x-input-label for="participant-bidang" value="Bidang (Mengikuti Program Diklat)" />
                            <select id="participant-bidang" name="bidang" class="mt-1 block w-full rounded-lg border border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">Pilih Bidang...</option>
                                @foreach($bidangOptions as $bidang)
                                    <option value="{{ $bidang }}" @selected(old('bidang') == $bidang)>{{ $bidang }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('bidang')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="participant-program" value="Program Diklat" />
                            <select id="participant-program" name="program_id" class="mt-1 block w-full rounded-lg border border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">Pilih Program Diklat...</option>
                                @foreach($programOptions as $program)
                                    <option value="{{ $program->id }}" data-bidang="{{ $program->bidang }}" @selected(old('program_id') == $program->id)>{{ $program->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('program_id')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="participant-status" value="Status" />
                            <select id="participant-status" name="status" class="mt-1 block w-full rounded-lg border border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="registered" @selected(old('status', 'registered') == 'registered')>Terdaftar</option>
                                <option value="active" @selected(old('status') == 'active')>Aktif</option>
                                <option value="completed" @selected(old('status') == 'completed')>Selesai</option>
                                <option value="dropped" @selected(old('status') == 'dropped')>Dropped</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-1" />
                            <p class="mt-1 text-xs text-slate-500">
                                Pilih status peserta diklat secara manual.
                            </p>
                        </div>

                        <div>
                            <x-input-label for="participant-score" value="Nilai Ujian (0-100)" />
                            <x-text-input id="participant-score" type="number" min="0" max="100" name="score" class="mt-1 block w-full" value="{{ old('score') }}" />
                            <x-input-error :messages="$errors->get('score')" class="mt-1" />
                            <p class="mt-1 text-xs text-slate-500">Kosongkan jika belum ada nilai ujian</p>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="certified" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" @checked(old('certified'))>
                                <span class="ml-2 text-sm text-slate-600">Sertifikasi</span>
                            </label>
                            <x-input-error :messages="$errors->get('certified')" class="mt-1" />
                            <p class="mt-1 text-xs text-slate-500">Centang jika peserta sudah tersertifikasi</p>
                        </div>

                        <x-primary-button>Tambah Peserta</x-primary-button>
                    </form>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm h-[24rem] flex flex-col">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Daftar Peserta</h3>
                    <div class="overflow-x-auto overflow-y-auto flex-1">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-slate-600 border-b border-slate-200">
                                    <th class="py-2 px-3 font-semibold">NAMA</th>
                                    <th class="py-2 px-3 font-semibold">BIDANG</th>
                                    <th class="py-2 px-3 font-semibold">STATUS</th>
                                    <th class="py-2 px-3 font-semibold">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentParticipants as $participant)
                                    @foreach($participant->enrollments as $enrollment)
                                        @php
                                            $status = $enrollment->status ?? 'registered';
                                            $statusLabels = [
                                                'registered' => 'Terdaftar',
                                                'active' => 'Aktif',
                                                'completed' => 'Selesai',
                                                'dropped' => 'Dropped',
                                            ];
                                            $statusColors = [
                                                'registered' => 'bg-blue-100 text-blue-700',
                                                'active' => 'bg-green-100 text-green-700',
                                                'completed' => 'bg-emerald-100 text-emerald-700',
                                                'dropped' => 'bg-red-100 text-red-700',
                                            ];
                                            $statusColor = $statusColors[$status] ?? 'bg-slate-100 text-slate-700';
                                            $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                                        @endphp
                                        <tr class="border-b border-slate-100">
                                            <td class="py-2 px-3 font-semibold text-slate-900">
                                                {{ $participant->participant_number }} - {{ $participant->name }}
                                            </td>
                                            <td class="py-2 px-3 text-slate-600">
                                                {{ $enrollment->program?->bidang ?? '-' }}
                                            </td>
                                            <td class="py-2 px-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-1 text-xs rounded-full {{ $statusColor }}">
                                                        {{ $statusLabel }}
                                                    </span>
                                                    <span class="text-xs text-amber-600" title="Status diatur manual">
                                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="py-2 px-3">
                                                <div class="flex items-center gap-3">
                                                    <button 
                                                        type="button" 
                                                        onclick="openEditStatusModal({{ $enrollment->id }}, {{ json_encode([
                                                            'participant_number' => $participant->participant_number,
                                                            'name' => $participant->name,
                                                            'email' => $participant->email ?? '',
                                                            'phone' => $participant->phone ?? '',
                                                            'bidang' => $enrollment->program?->bidang ?? '',
                                                            'program_id' => $enrollment->program_id,
                                                            'status' => $enrollment->status ?? '',
                                                            'score' => $enrollment->assessment?->score ?? '',
                                                            'certified' => !empty($enrollment->assessment?->certificate_number)
                                                        ]) }})"
                                                        class="text-blue-600 hover:text-blue-800 text-xs cursor-pointer"
                                                        title="Edit Data Peserta">
                                                        Edit
                                                    </button>
                                                    <span class="text-slate-300">|</span>
                                                    <form method="POST" action="{{ route('admin.participants.destroy', $participant) }}" class="inline" onsubmit="sessionStorage.setItem('activeTab', 'peserta'); return confirm('Yakin ingin menghapus peserta ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-slate-500">Belum ada peserta.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>

        <!-- Tab Content: Progress Mingguan -->
        <div id="content-progress" class="tab-content hidden">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Progress Mingguan (Otomatis)</h3>
                <p class="text-sm text-slate-500 mb-6">Data progress mingguan dihitung otomatis dari sesi diklat yang telah direalisasikan.</p>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-600 border-b border-slate-200">
                                <th class="py-3 px-4 font-semibold">MINGGU</th>
                                <th class="py-3 px-4 font-semibold">PESERTA BARU</th>
                                <th class="py-3 px-4 font-semibold">SELESAI</th>
                                <th class="py-3 px-4 font-semibold">LULUS</th>
                                <th class="py-3 px-4 font-semibold">REALISASI</th>
                                <th class="py-3 px-4 font-semibold">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($weeklyData as $week)
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 px-4 font-semibold">Minggu {{ $week['week_number'] }}</td>
                                    <td class="py-3 px-4">{{ $week['new_participants'] }}</td>
                                    <td class="py-3 px-4">{{ $week['completed'] }}</td>
                                    <td class="py-3 px-4">{{ $week['passed'] }}</td>
                                    <td class="py-3 px-4">{{ $week['realization'] }}%</td>
                                    <td class="py-3 px-4">
                                        <a href="#" class="text-blue-600 hover:text-blue-800 text-xs">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-slate-500">Belum ada data progress mingguan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Peserta -->
    <div id="edit-status-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeEditStatusModal()"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
                <form method="POST" id="edit-status-form" onsubmit="sessionStorage.setItem('activeTab', 'peserta');" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Edit Data Peserta</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="edit-participant-number" value="Nomor Peserta Diklat" />
                                <x-text-input id="edit-participant-number" name="participant_number" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('participant_number')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="edit-name" value="Nama Peserta" />
                                <x-text-input id="edit-name" name="name" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-1" />
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="edit-email" value="Email" />
                                    <x-text-input id="edit-email" type="email" name="email" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="edit-phone" value="No. Telepon" />
                                    <x-text-input id="edit-phone" name="phone" class="mt-1 block w-full" />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="edit-bidang" value="Bidang" />
                                <select id="edit-bidang" name="bidang" class="mt-1 block w-full rounded-lg border border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">Pilih Bidang...</option>
                                    @foreach($bidangOptions as $bidang)
                                        <option value="{{ $bidang }}">{{ $bidang }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('bidang')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="edit-program" value="Program Diklat" />
                                <select id="edit-program" name="program_id" class="mt-1 block w-full rounded-lg border border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="">Pilih Program Diklat...</option>
                                    @foreach($programOptions as $program)
                                        <option value="{{ $program->id }}" data-bidang="{{ $program->bidang }}">{{ $program->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('program_id')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="edit-status" value="Status" />
                                <select id="edit-status" name="status" class="mt-1 block w-full rounded-lg border border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value="registered">Terdaftar</option>
                                    <option value="active">Aktif</option>
                                    <option value="completed">Selesai</option>
                                    <option value="dropped">Dropped</option>
                                </select>
                                <p class="mt-1 text-xs text-slate-500">
                                    Pilih status peserta diklat secara manual.
                                </p>
                            </div>

                            <div>
                                <x-input-label for="edit-score" value="Nilai Ujian (0-100)" />
                                <x-text-input id="edit-score" type="number" min="0" max="100" name="score" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('score')" class="mt-1" />
                                <p class="mt-1 text-xs text-slate-500">Kosongkan jika belum ada nilai ujian</p>
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" id="edit-certified" name="certified" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-slate-600">Sertifikasi</span>
                                </label>
                                <p class="mt-1 text-xs text-slate-500">Centang jika peserta sudah tersertifikasi</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:grid sm:grid-cols-2 sm:gap-3">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                            Simpan
                        </button>
                        <button type="button" onclick="closeEditStatusModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active state from all tabs
            document.querySelectorAll('.tab-link').forEach(link => {
                link.classList.remove('border-blue-500', 'text-blue-600');
                link.classList.add('border-transparent', 'text-slate-500');
            });

            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');

            // Add active state to selected tab
            const activeTab = document.getElementById('tab-' + tabName);
            activeTab.classList.remove('border-transparent', 'text-slate-500');
            activeTab.classList.add('border-blue-500', 'text-blue-600');
            
            // Save active tab to sessionStorage
            sessionStorage.setItem('activeTab', tabName);
        }

        // Notification Popup Functions
        let notificationTimeout = null;
        
        function showNotification(message) {
            const popup = document.getElementById('notification-popup');
            const messageEl = document.getElementById('notification-message');
            
            if (popup && messageEl) {
                // Clear any existing timeout
                if (notificationTimeout) {
                    clearTimeout(notificationTimeout);
                }
                
                messageEl.textContent = message;
                
                // Remove hidden class first, then trigger animation
                popup.classList.remove('hidden');
                
                // Use requestAnimationFrame to ensure the element is visible before animation
                requestAnimationFrame(() => {
                    popup.classList.remove('translate-x-full', 'opacity-0');
                    popup.classList.add('translate-x-0', 'opacity-100');
                });
                
                // Auto hide after 4 seconds
                notificationTimeout = setTimeout(() => {
                    hideNotification();
                }, 4000);
            }
        }

        function hideNotification() {
            const popup = document.getElementById('notification-popup');
            if (popup) {
                // Clear timeout if exists
                if (notificationTimeout) {
                    clearTimeout(notificationTimeout);
                    notificationTimeout = null;
                }
                
                popup.classList.remove('translate-x-0', 'opacity-100');
                popup.classList.add('translate-x-full', 'opacity-0');
                
                setTimeout(() => {
                    popup.classList.add('hidden');
                }, 300);
            }
        }

        // Edit Status Modal Functions (Global)
        function openEditStatusModal(enrollmentId, data) {
            const modal = document.getElementById('edit-status-modal');
            const form = document.getElementById('edit-status-form');
            
            if (!modal || !form) {
                console.error('Modal elements not found');
                return;
            }
            
            // Set form action
            form.action = `/admin/enrollments/${enrollmentId}`;
            
            // Populate all fields
            document.getElementById('edit-participant-number').value = data.participant_number || '';
            document.getElementById('edit-name').value = data.name || '';
            document.getElementById('edit-email').value = data.email || '';
            document.getElementById('edit-phone').value = data.phone || '';
            document.getElementById('edit-bidang').value = data.bidang || '';
            updateEditProgramOptions(data.bidang);
            document.getElementById('edit-program').value = (data.program_id != null ? String(data.program_id) : '');
            
            // Handle status - set to registered as default if empty
            const statusSelect = document.getElementById('edit-status');
            if (data.status && data.status !== '' && data.status !== null) {
                statusSelect.value = data.status;
            } else {
                statusSelect.value = 'registered'; // Default to registered
            }
            
            // Handle score - set to empty string if no score
            const scoreInput = document.getElementById('edit-score');
            if (data.score && data.score !== '') {
                scoreInput.value = data.score;
            } else {
                scoreInput.value = '';
            }
            
            // Handle certified checkbox (true only if certificate exists)
            document.getElementById('edit-certified').checked = data.certified === true;
            
            // Update program options based on bidang
            updateEditProgramOptions(data.bidang);
            
            // Show modal
            modal.classList.remove('hidden');
        }

        function updateEditProgramOptions(selectedBidang) {
            const programSelect = document.getElementById('edit-program');
            if (!programSelect) return;
            
            // Store all program options if not already stored
            if (!programSelect._allOptions) {
                programSelect._allOptions = [];
                Array.from(programSelect.options).forEach(option => {
                    if (option.value !== '') {
                        programSelect._allOptions.push({
                            value: option.value,
                            text: option.text,
                            bidang: option.dataset.bidang
                        });
                    }
                });
            }
            
            // Get current selected value before clearing
            const currentValue = programSelect.value;
            
            // Clear and rebuild options
            programSelect.innerHTML = '<option value="">Pilih Program Diklat...</option>';
            
            programSelect._allOptions.forEach(program => {
                if (!selectedBidang || program.bidang === selectedBidang) {
                    const option = document.createElement('option');
                    option.value = program.value;
                    option.textContent = program.text;
                    option.dataset.bidang = program.bidang;
                    programSelect.appendChild(option);
                }
            });
            
            // Restore selected value if it still exists
            if (currentValue) {
                programSelect.value = currentValue;
            }
        }

        function closeEditStatusModal() {
            const modal = document.getElementById('edit-status-modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditStatusModal();
            }
        });

        // Dynamic dropdown: Program berdasarkan Bidang
        document.addEventListener('DOMContentLoaded', function() {
            // Restore active tab from sessionStorage or default to 'program'
            const savedTab = sessionStorage.getItem('activeTab') || 'program';
            showTab(savedTab);
            
            // Show notification if there's a flash message
            @if (session('status'))
                showNotification('{{ session('status') }}');
            @endif
            
            const bidangSelect = document.getElementById('participant-bidang');
            const programSelect = document.getElementById('participant-program');
            
            if (bidangSelect && programSelect) {
                // Store all program options
                const allProgramOptions = [];
                Array.from(programSelect.options).slice(1).forEach(option => {
                    allProgramOptions.push({
                        value: option.value,
                        text: option.text,
                        bidang: option.dataset.bidang
                    });
                });
                
                function updateProgramOptions() {
                    const selectedBidang = bidangSelect.value;
                    
                    // Clear program options
                    programSelect.innerHTML = '<option value="">Pilih Program Diklat...</option>';
                    
                    // Filter and add programs
                    allProgramOptions.forEach(program => {
                        if (!selectedBidang || program.bidang === selectedBidang) {
                            const option = document.createElement('option');
                            option.value = program.value;
                            option.textContent = program.text;
                            option.dataset.bidang = program.bidang;
                            programSelect.appendChild(option);
                        }
                    });
                }
                
                bidangSelect.addEventListener('change', updateProgramOptions);
                
                // Trigger on load if bidang is already selected
                if (bidangSelect.value) {
                    updateProgramOptions();
                }
            }

            // Edit modal bidang change handler
            const editBidangSelect = document.getElementById('edit-bidang');
            const editProgramSelect = document.getElementById('edit-program');
            
            if (editBidangSelect && editProgramSelect) {
                editBidangSelect.addEventListener('change', function() {
                    updateEditProgramOptions(this.value);
                    // Reset program selection when bidang changes
                    editProgramSelect.value = '';
                });
            }
        });
    </script>
</x-app-layout>
