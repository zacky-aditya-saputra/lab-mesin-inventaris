<?php

namespace App\Console\Commands;

use App\Models\LoanRequest;
use App\Services\LoanStatusService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('loans:check-overdue')]
#[Description('Deteksi dan tandai tiket ON_LOAN yang melewati tanggal kembali (end_date) menjadi OVERDUE secara terjadwal.')]
class CheckOverdueLoans extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(LoanStatusService $service): int
    {
        $overdueLoans = LoanRequest::query()
            ->where('status', 'ON_LOAN')
            ->where('end_date', '<', now()->toDateString())
            ->get();

        if ($overdueLoans->isEmpty()) {
            $this->info('Tidak ada tiket peminjaman yang melewati batas waktu.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($overdueLoans as $loan) {
            $service->markOverdue($loan);
            $count++;
        }

        $this->info("Berhasil menandai {$count} tiket peminjaman sebagai OVERDUE.");

        return self::SUCCESS;
    }
}
