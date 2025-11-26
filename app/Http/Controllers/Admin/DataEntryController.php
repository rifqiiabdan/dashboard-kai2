<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiklatAssessment;
use App\Models\DiklatEnrollment;
use App\Models\DiklatParticipant;
use App\Models\DiklatProgram;
use App\Models\DiklatSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DataEntryController extends Controller
{
    public function index(): View
    {
        $weeklyData = DiklatSession::selectRaw('week_number, week_start_date, week_end_date, SUM(planned_hours) as total_planned, SUM(realized_hours) as total_realized, COUNT(*) as session_count')
            ->groupBy('week_number', 'week_start_date', 'week_end_date')
            ->orderBy('week_number')
            ->get()
            ->map(function ($week) {
                $newParticipants = DiklatEnrollment::whereDate('enrolled_at', '>=', $week->week_start_date ?? now()->startOfWeek())
                    ->whereDate('enrolled_at', '<=', $week->week_end_date ?? now()->endOfWeek())
                    ->count();
                $completed = DiklatEnrollment::whereDate('completed_at', '>=', $week->week_start_date ?? now()->startOfWeek())
                    ->whereDate('completed_at', '<=', $week->week_end_date ?? now()->endOfWeek())
                    ->count();
                $passed = DiklatEnrollment::whereDate('completed_at', '>=', $week->week_start_date ?? now()->startOfWeek())
                    ->whereDate('completed_at', '<=', $week->week_end_date ?? now()->endOfWeek())
                    ->whereHas('assessment', fn($q) => $q->where('passed', true))
                    ->count();
                $realization = $week->total_planned > 0 ? round(($week->total_realized / $week->total_planned) * 100, 1) : 0;

                return [
                    'week_number' => $week->week_number,
                    'week_start_date' => $week->week_start_date,
                    'week_end_date' => $week->week_end_date,
                    'new_participants' => $newParticipants,
                    'completed' => $completed,
                    'passed' => $passed,
                    'realization' => $realization,
                    'total_planned' => $week->total_planned,
                    'total_realized' => $week->total_realized,
                ];
            });

        $bidangOptions = DiklatProgram::distinct()->pluck('bidang')->sort()->values();
        $programsByBidang = DiklatProgram::orderBy('name')->get()->groupBy('bidang');

        return view('admin.data-entry', [
            'programOptions' => DiklatProgram::orderBy('name')->get(),
            'participantOptions' => DiklatParticipant::orderBy('name')->get(),
            'enrollmentOptions' => DiklatEnrollment::with(['participant', 'program'])
                ->orderByDesc('created_at')
                ->get(),
            'recentPrograms' => DiklatProgram::withCount('enrollments')->latest()->get(),
            'recentParticipants' => DiklatParticipant::with(['enrollments.program'])->latest()->get(),
            'recentEnrollments' => DiklatEnrollment::with(['participant', 'program'])
                ->latest()
                ->get(),
            'recentAssessments' => DiklatAssessment::with(['enrollment.participant', 'enrollment.program'])
                ->latest()
                ->get(),
            'weeklyData' => $weeklyData,
            'bidangOptions' => $bidangOptions,
            'programsByBidang' => $programsByBidang,
        ]);
    }

    public function storeProgram(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'bidang' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'target_participants' => ['required', 'integer', 'min:0'],
        ]);

        DiklatProgram::create($data);

        return back()->with('status', 'Program diklat berhasil ditambahkan.');
    }

    public function storeParticipant(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'participant_number' => ['required', 'string', 'max:50', 'unique:diklat_participants,participant_number'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'bidang' => ['required', 'string', 'max:255'],
            'program_id' => ['required', 'exists:diklat_programs,id'],
            'status' => ['required', Rule::in(['registered', 'active', 'completed', 'dropped'])],
            'score' => ['nullable', 'integer', 'between:0,100'],
            'certified' => ['nullable', 'boolean'],
        ]);

        // Create participant
        $participant = DiklatParticipant::create([
            'participant_number' => $data['participant_number'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        // Create enrollment with manual status
        $enrollment = DiklatEnrollment::create([
            'participant_id' => $participant->id,
            'program_id' => $data['program_id'],
            'status' => $data['status'], // Manual status: registered, active, completed, or dropped
            'enrolled_at' => now(),
            'completed_at' => null,
        ]);

        // Create assessment if score is provided
        if (isset($data['score']) && $data['score'] !== null && $data['score'] !== '') {
            $passed = $data['score'] >= 80;
            // Only create certificate if certified checkbox is checked AND passed
            $certificateNumber = (($data['certified'] ?? false) && $passed) 
                ? 'CERT-' . str_pad((string) $enrollment->id, 5, '0', STR_PAD_LEFT) 
                : null;

            DiklatAssessment::create([
                'enrollment_id' => $enrollment->id,
                'exam_date' => now(),
                'score' => $data['score'],
                'passed' => $passed,
                'certificate_number' => $certificateNumber,
                'certificate_date' => $certificateNumber ? now() : null,
            ]);
        }

        return back()->with('status', 'Peserta berhasil didaftarkan dan terdaftar dalam program diklat.');
    }

    public function storeEnrollment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'participant_id' => ['required', 'exists:diklat_participants,id'],
            'program_id' => ['required', 'exists:diklat_programs,id'],
            'status' => ['required', Rule::in(['registered', 'active', 'completed', 'dropped'])],
            'enrolled_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:enrolled_at'],
        ]);

        DiklatEnrollment::create($data);

        return back()->with('status', 'Enrolment peserta berhasil dicatat.');
    }

    public function storeAssessment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enrollment_id' => ['required', 'exists:diklat_enrollments,id'],
            'exam_date' => ['nullable', 'date'],
            'score' => ['nullable', 'integer', 'between:0,100'],
            'passed' => ['nullable', 'boolean'],
            'certificate_number' => ['nullable', 'string', 'max:255'],
            'certificate_date' => ['nullable', 'date'],
        ]);

        $passed = $data['passed'] ?? ($data['score'] !== null ? $data['score'] >= 80 : false);

        DiklatAssessment::updateOrCreate(
            ['enrollment_id' => $data['enrollment_id']],
            [
                'exam_date' => $data['exam_date'] ?? null,
                'score' => $data['score'] ?? null,
                'passed' => $passed,
                'certificate_number' => $data['certificate_number'] ?? null,
                'certificate_date' => $data['certificate_date'] ?? null,
            ]
        );

        return back()->with('status', 'Hasil ujian/sertifikasi berhasil diperbarui.');
    }

    public function importParticipants(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if (! $handle) {
            return back()->with('status', 'Gagal membuka file CSV.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return back()->with('status', 'File CSV kosong atau header tidak ditemukan.');
        }

        $header = array_map(function ($h) { return Str::of($h)->lower()->trim()->toString(); }, $header);

        $idx = fn($key) => array_search($key, $header, true);

        $colMap = [
            'participant_number' => $idx('participant_number'),
            'name' => $idx('name'),
            'email' => $idx('email'),
            'phone' => $idx('phone'),
            'program' => $idx('program') !== false ? $idx('program') : $idx('program_name'),
            'bidang' => $idx('bidang'),
            'status' => $idx('status'),
            'score' => $idx('score'),
            'certified' => $idx('certified'),
        ];

        if ($colMap['participant_number'] === false || $colMap['name'] === false || $colMap['program'] === false || $colMap['status'] === false) {
            fclose($handle);
            return back()->with('status', 'Header wajib: participant_number, name, program, status.');
        }

        $created = 0; $updated = 0; $enrolled = 0; $assessed = 0; $errors = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) {
                    continue;
                }

                $participantNumber = trim((string) ($row[$colMap['participant_number']] ?? ''));
                $name = trim((string) ($row[$colMap['name']] ?? ''));
                $email = trim((string) ($row[$colMap['email']] ?? ''));
                $phone = trim((string) ($row[$colMap['phone']] ?? ''));
                $programName = trim((string) ($row[$colMap['program']] ?? ''));
                $bidang = trim((string) ($row[$colMap['bidang']] ?? ''));
                $statusRaw = Str::lower(trim((string) ($row[$colMap['status']] ?? '')));
                $status = match ($statusRaw) {
                    'terdaftar', 'daftar', 'registered', 'register' => 'registered',
                    'aktif', 'active' => 'active',
                    'selesai', 'completed', 'complete', 'lulus' => 'completed',
                    'drop', 'dropped', 'keluar', 'berhenti' => 'dropped',
                    default => 'registered',
                };
                $score = ($colMap['score'] !== false) ? trim((string) ($row[$colMap['score']] ?? '')) : null;
                $certifiedFlag = ($colMap['certified'] !== false) ? trim((string) ($row[$colMap['certified']] ?? '')) : null;

                if ($participantNumber === '' || $name === '' || $programName === '') {
                    $errors++; continue;
                }

                $participant = DiklatParticipant::where('participant_number', $participantNumber)->first();
                if (! $participant) {
                    $participant = DiklatParticipant::create([
                        'participant_number' => $participantNumber,
                        'name' => $name,
                        'email' => $email ?: null,
                        'phone' => $phone ?: null,
                    ]);
                    $created++;
                } else {
                    $participant->update([
                        'name' => $name,
                        'email' => $email ?: null,
                        'phone' => $phone ?: null,
                    ]);
                    $updated++;
                }

                $program = DiklatProgram::where('name', $programName)->first();
                if (! $program) {
                    // If program not found but bidang provided, create minimal program
                    $program = DiklatProgram::create([
                        'name' => $programName,
                        'bidang' => $bidang !== '' ? $bidang : 'Umum',
                        'start_date' => now()->toDateString(),
                        'end_date' => now()->toDateString(),
                        'target_participants' => 0,
                    ]);
                }

                // $status already normalized above

                $enrollment = DiklatEnrollment::firstOrCreate(
                    ['participant_id' => $participant->id, 'program_id' => $program->id],
                    ['status' => $status, 'enrolled_at' => now()]
                );
                if (! $enrollment->wasRecentlyCreated) {
                    $enrollment->update(['status' => $status]);
                }
                $enrolled++;

                if ($score !== null && $score !== '') {
                    $scoreInt = (int) $score;
                    $passed = $scoreInt >= 80;
                    $certified = in_array(Str::lower($certifiedFlag), ['1','true','yes','ya'], true) && $passed;
                    $certificateNumber = $certified ? ('CERT-' . str_pad((string) $enrollment->id, 5, '0', STR_PAD_LEFT)) : null;

                    DiklatAssessment::updateOrCreate(
                        ['enrollment_id' => $enrollment->id],
                        [
                            'exam_date' => now(),
                            'score' => $scoreInt,
                            'passed' => $passed,
                            'certificate_number' => $certificateNumber,
                            'certificate_date' => $certificateNumber ? now() : null,
                        ]
                    );
                    $assessed++;
                }
            }
            fclose($handle);
            DB::commit();
        } catch (\Throwable $e) {
            fclose($handle);
            DB::rollBack();
            return back()->with('status', 'Gagal import: ' . $e->getMessage());
        }

        return back()->with('status', "Import selesai: peserta baru {$created}, diperbarui {$updated}, enrolment {$enrolled}, assessment {$assessed}, error {$errors}.");
    }

    public function destroyProgram(DiklatProgram $program): RedirectResponse
    {
        if ($program->enrollments()->exists()) {
            return back()->with('status', 'Program diklat tidak dapat dihapus karena memiliki peserta terdaftar.');
        }

        $program->delete();
        return back()->with('status', 'Program diklat berhasil dihapus.');
    }

    public function destroyParticipant(DiklatParticipant $participant): RedirectResponse
    {
        $participant->delete();
        return back()->with('status', 'Peserta berhasil dihapus.');
    }

    public function updateParticipant(Request $request, DiklatEnrollment $enrollment): RedirectResponse
    {
        $participant = $enrollment->participant;
        
        // Prepare validation rules
        $rules = [
            'participant_number' => ['required', 'string', 'max:50', Rule::unique('diklat_participants', 'participant_number')->ignore($participant->id)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'bidang' => ['required', 'string', 'max:255'],
            'program_id' => ['required', 'exists:diklat_programs,id'],
            'status' => ['required', Rule::in(['registered', 'active', 'completed', 'dropped'])],
            'certified' => ['nullable', 'boolean'],
        ];
        
        // Only validate score if it's not empty
        if ($request->has('score') && $request->input('score') !== '' && $request->input('score') !== null) {
            $rules['score'] = ['required', 'integer', 'between:0,100'];
        }
        
        $data = $request->validate($rules);

        // Verify that program bidang matches selected bidang
        $program = DiklatProgram::find($data['program_id']);
        if ($program && $program->bidang !== $data['bidang']) {
            return back()->withErrors(['bidang' => 'Bidang yang dipilih tidak sesuai dengan program diklat.'])->withInput();
        }

        // Update participant
        $participant->update([
            'participant_number' => $data['participant_number'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        // Update enrollment with manual status
        $enrollment->update([
            'program_id' => $data['program_id'],
            'status' => $data['status'], // Manual status: registered, active, completed, or dropped
        ]);

        // Handle assessment
        $score = $request->input('score');
        if ($score !== null && $score !== '') {
            // Score provided - update or create assessment
            $scoreValue = (int) $score;
            $passed = $scoreValue >= 80;
            
            // Only create/update certificate if certified checkbox is checked AND passed
            $certified = $request->has('certified') && $request->input('certified') == '1';
            $certificateNumber = ($certified && $passed) 
                ? ($enrollment->assessment?->certificate_number ?? 'CERT-' . str_pad((string) $enrollment->id, 5, '0', STR_PAD_LEFT))
                : null;

            DiklatAssessment::updateOrCreate(
                ['enrollment_id' => $enrollment->id],
                [
                    'exam_date' => $enrollment->assessment?->exam_date ?? now(),
                    'score' => $scoreValue,
                    'passed' => $passed,
                    'certificate_number' => $certificateNumber,
                    'certificate_date' => $certificateNumber ? ($enrollment->assessment?->certificate_date ?? now()) : null,
                ]
            );
        } else {
            // Score is empty - remove assessment if exists
            if ($enrollment->assessment) {
                $enrollment->assessment->delete();
            }
        }

        return back()->with('status', 'Data peserta berhasil diperbarui.');
    }
}
