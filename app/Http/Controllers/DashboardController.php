<?php

namespace App\Http\Controllers;

use App\Models\DiklatAssessment;
use App\Models\DiklatEnrollment;
use App\Models\DiklatParticipant;
use App\Models\DiklatProgram;
use App\Models\DiklatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dashboard = $this->buildDashboardData();
        $searchResult = null;
        $participantQuery = $request->string('participant_number')->toString();

        if ($participantQuery !== '') {
            $searchResult = $this->findParticipantByNumber($participantQuery);
        }

        $participants = DiklatEnrollment::with(['participant', 'program', 'assessment'])->orderByDesc('created_at')->get();
        if ($request->filled('program_id')) {
            $participants = $participants->where('program_id', (int) $request->input('program_id'));
        }
        if ($request->filled('bidang')) {
            $participants = $participants->filter(function ($e) use ($request) {
                return optional($e->program)->bidang === $request->input('bidang');
            });
        }

        $programsData = collect($dashboard['programs']);
        if ($request->filled('q')) {
            $q = mb_strtolower($request->string('q')->toString());
            $programsData = $programsData->filter(function ($p) use ($q) {
                return str_contains(mb_strtolower($p['name']), $q);
            });
        }
        

        return view('dashboard', [
            'dashboard' => array_merge($dashboard, [
                'participants' => $participants,
                'programs_filtered' => $programsData->values(),
            ]),
            'searchResult' => $searchResult,
            'participantQuery' => $participantQuery,
            'programOptions' => DiklatProgram::orderBy('name')->get(['id', 'name', 'bidang']),
            'bidangOptions' => DiklatProgram::distinct()->pluck('bidang')->sort()->values(),
            'filters' => [
                'program_id' => $request->input('program_id'),
                'bidang' => $request->input('bidang'),
                'q' => $request->input('q'),
            ],
        ]);
    }

    public function metrics(): JsonResponse
    {
        return response()->json($this->buildDashboardData());
    }

    private function buildDashboardData(): array
    {
        $today = now()->startOfDay();
        
        // Load programs with enrollments
        $programs = DiklatProgram::select('id', 'name', 'bidang', 'start_date', 'end_date', 'target_participants')
            ->with(['enrollments'])
            ->orderBy('start_date')
            ->get();

        // Calculate enrollment stats using computed status
        $enrollments = DiklatEnrollment::with('program')->get();
        $totalParticipants = $enrollments->count();
        $activeParticipants = $enrollments->filter(function ($enrollment) {
            return $enrollment->computed_status === 'active';
        })->count();
        $completedParticipants = $enrollments->filter(function ($enrollment) {
            return $enrollment->computed_status === 'completed';
        })->count();

        $assessmentStats = DiklatAssessment::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed,
            SUM(CASE WHEN certificate_number IS NOT NULL THEN 1 ELSE 0 END) as certified
        ')->first();

        $passedParticipants = (int) ($assessmentStats->passed ?? 0);
        $certifiedParticipants = (int) ($assessmentStats->certified ?? 0);
        $assessmentTotal = (int) ($assessmentStats->total ?? 0);

        $weeklyProgress = $this->weeklyProgress();
        // Persentase kelulusan dihitung dari peserta selesai dan aktif
        $totalForPassRate = $activeParticipants + $completedParticipants;
        $passRate = $this->percentage($passedParticipants, $totalForPassRate);
        $certificationRate = $this->percentage($certifiedParticipants, $assessmentTotal);
        $bidangBreakdown = $this->bidangBreakdown($programs);

        // Calculate program enrollment stats using computed status
        $programEnrollmentStats = collect();
        foreach ($programs as $program) {
            $programEnrollments = $enrollments->where('program_id', $program->id);
            $active = $programEnrollments->filter(fn($e) => $e->computed_status === 'active')->count();
            $completed = $programEnrollments->filter(fn($e) => $e->computed_status === 'completed')->count();
            $total = $programEnrollments->count();
            
            $programEnrollmentStats->put($program->id, (object)[
                'total' => $total,
                'active' => $active,
                'completed' => $completed,
            ]);
        }

        // Pre-load semua program IDs untuk bidang chart
        $programIdsByBidang = $programs->groupBy('bidang')->map(fn($g) => $g->pluck('id'));

        $programAssessmentStats = DiklatEnrollment::whereIn('program_id', $programs->pluck('id'))
            ->whereHas('assessment')
            ->with('assessment')
            ->get()
            ->groupBy('program_id')
            ->map(function ($enrollments) {
                return [
                    'passed' => $enrollments->filter(fn($e) => $e->assessment?->passed)->count(),
                    'certified' => $enrollments->filter(fn($e) => $e->assessment?->certificate_number)->count(),
                ];
            });

        $programsData = $programs->map(function (DiklatProgram $program) use ($programEnrollmentStats, $programAssessmentStats) {
            $stats = $programEnrollmentStats->get($program->id);
            $assessmentStats = $programAssessmentStats->get($program->id, ['passed' => 0, 'certified' => 0]);
            
            $programTotal = ($stats->active ?? 0) + ($stats->completed ?? 0);
            $programPassed = $assessmentStats['passed'] ?? 0;
            $programCertified = $assessmentStats['certified'] ?? 0;
            $programPassRate = $programTotal > 0 ? round(($programPassed / $programTotal) * 100, 1) : 0;
            $programCertRate = $programTotal > 0 ? round(($programCertified / $programTotal) * 100, 1) : 0;

            return [
                'id' => $program->id,
                'name' => $program->name,
                'bidang' => $program->bidang,
                'start_date' => $program->start_date->toDateString(),
                'end_date' => $program->end_date->toDateString(),
                'target_participants' => $program->target_participants,
                'active_enrollments' => $stats->active ?? 0,
                'completed_enrollments' => $stats->completed ?? 0,
                'total_enrollments' => $programTotal,
                'passed_enrollments' => $programPassed,
                'certified_enrollments' => $programCertified,
                'pass_rate' => $programPassRate,
                'cert_rate' => $programCertRate,
            ];
        });

        // Calculate bidang breakdown using computed status
        $bidangBreakdown = $programs->groupBy('bidang')
            ->map(function (Collection $group) use ($enrollments) {
                $programIds = $group->pluck('id');
                $bidangEnrollments = $enrollments->whereIn('program_id', $programIds);
                
                return [
                    'bidang' => $group->first()->bidang,
                    'programs' => $group->count(),
                    'target_participants' => $group->sum('target_participants'),
                    'active_enrollments' => $bidangEnrollments->filter(fn($e) => $e->computed_status === 'active')->count(),
                    'completed_enrollments' => $bidangEnrollments->filter(fn($e) => $e->computed_status === 'completed')->count(),
                ];
            })
            ->values()
            ->toArray();

        // Pre-calculate bidang chart data
        $bidangChartData = collect($bidangBreakdown)->map(function ($bidang) use ($programIdsByBidang, $enrollments) {
            $programIds = $programIdsByBidang->get($bidang['bidang'], collect());
            $total = $bidang['active_enrollments'] + $bidang['completed_enrollments'];
            
            // Count passed per bidang using enrollments with assessment
            $bidangEnrollments = $enrollments->whereIn('program_id', $programIds);
            $passed = $bidangEnrollments->filter(function ($enrollment) {
                return $enrollment->assessment && $enrollment->assessment->passed;
            })->count();

            return [
                'bidang' => $bidang['bidang'],
                'total' => $total,
                'active' => $bidang['active_enrollments'],
                'passed' => $passed,
            ];
        });

        return [
            'metrics' => [
                'active_participants' => $activeParticipants,
                'total_participants' => $totalParticipants,
                'completed_participants' => $completedParticipants,
                'passed_participants' => $passedParticipants,
                'certified_participants' => $certifiedParticipants,
                'weekly_progress' => $weeklyProgress,
                'pass_rate' => $passRate,
                'certification_rate' => $certificationRate,
            ],
            'programs' => $programsData,
            'bidang_breakdown' => $bidangBreakdown,
            'bidang_chart_data' => $bidangChartData,
        ];
    }

    private function weeklyProgress(): array
    {
        return DiklatSession::selectRaw(
            'week_number, ROUND(AVG(realized_hours / NULLIF(planned_hours, 0)) * 100, 1) as completion'
        )
            ->groupBy('week_number')
            ->orderBy('week_number')
            ->limit(12)
            ->get()
            ->map(fn ($row) => [
                'week' => (int) $row->week_number,
                'completion' => (float) $row->completion,
            ])->toArray();
    }

    private function bidangBreakdown(Collection $programs): array
    {
        return $programs->groupBy('bidang')
            ->map(function (Collection $group) {
                return [
                    'bidang' => $group->first()->bidang,
                    'programs' => $group->count(),
                    'target_participants' => $group->sum('target_participants'),
                    'active_enrollments' => $group->sum('active_enrollments'),
                    'completed_enrollments' => $group->sum('completed_enrollments'),
                ];
            })
            ->values()
            ->toArray();
    }

    private function programProgress(DiklatProgram $program): float
    {
        $totalSessions = $program->sessions->count();

        if ($totalSessions === 0) {
            return 0;
        }

        $completedSessions = $program->sessions->where('status', 'completed')->count();

        return round(($completedSessions / $totalSessions) * 100, 1);
    }

    private function percentage(int $numerator, int $denominator): float
    {
        if ($denominator === 0) {
            return 0;
        }

        return round(($numerator / $denominator) * 100, 1);
    }

    private function findParticipantByNumber(string $participantNumber): ?array
    {
        $participant = DiklatParticipant::with([
            'enrollments.program',
            'enrollments.assessment',
        ])->where('participant_number', $participantNumber)->first();

        if (! $participant) {
            return null;
        }

        return [
            'participant_number' => $participant->participant_number,
            'name' => $participant->name,
            'unit' => $participant->unit,
            'position' => $participant->position,
            'contact' => [
                'email' => $participant->email,
                'phone' => $participant->phone,
            ],
            'enrollments' => $participant->enrollments->map(function (DiklatEnrollment $enrollment) {
                return [
                    'program' => $enrollment->program?->name,
                    'status' => $enrollment->computed_status,
                    'is_manual_override' => $enrollment->is_manual_override,
                    'enrolled_at' => optional($enrollment->enrolled_at)->toDateString(),
                    'completed_at' => optional($enrollment->completed_at)->toDateString(),
                    'assessment' => $enrollment->assessment ? [
                        'score' => $enrollment->assessment->score,
                        'passed' => $enrollment->assessment->passed,
                        'certificate_number' => $enrollment->assessment->certificate_number,
                    ] : null,
                ];
            }),
        ];
    }
}
