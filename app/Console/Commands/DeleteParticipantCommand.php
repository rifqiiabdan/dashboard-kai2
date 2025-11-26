<?php

namespace App\Console\Commands;

use App\Models\DiklatParticipant;
use Illuminate\Console\Command;

class DeleteParticipantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'participant:delete {number : Nomor peserta yang akan dihapus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus peserta berdasarkan nomor peserta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $participantNumber = $this->argument('number');
        
        $participant = DiklatParticipant::where('participant_number', $participantNumber)->first();
        
        if (!$participant) {
            $this->error("Peserta dengan nomor '{$participantNumber}' tidak ditemukan.");
            return 1;
        }
        
        $this->info("Menemukan peserta: {$participant->name} (Nomor: {$participant->participant_number})");
        
        // Count related data
        $enrollmentsCount = $participant->enrollments()->count();
        $assessmentsCount = 0;
        
        foreach ($participant->enrollments as $enrollment) {
            if ($enrollment->assessment) {
                $assessmentsCount++;
            }
        }
        
        $this->info("Data terkait yang akan dihapus:");
        $this->info("- Enrollments: {$enrollmentsCount}");
        $this->info("- Assessments: {$assessmentsCount}");
        
        if (!$this->confirm('Apakah Anda yakin ingin menghapus peserta ini dan semua data terkait?')) {
            $this->info('Operasi dibatalkan.');
            return 0;
        }
        
        // Delete participant (cascade will handle enrollments and assessments)
        $participant->delete();
        
        $this->info("Peserta dengan nomor '{$participantNumber}' berhasil dihapus beserta semua data terkait.");
        
        return 0;
    }
}




