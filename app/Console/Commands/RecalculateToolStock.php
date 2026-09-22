<?php

namespace App\Console\Commands;

use App\Models\LoanItem;
use App\Models\Tool;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tools:recalculate-stock')]
#[Description('Hitung ulang dan sinkronkan available_stock seluruh alat berdasarkan total_stock dan tiket peminjaman aktif.')]
class RecalculateToolStock extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tools = Tool::all();

        $rows = [];

        foreach ($tools as $tool) {
            $activeBorrowed = (int) LoanItem::query()
                ->where('tool_id', $tool->id)
                ->whereHas('loanRequest', function ($q) {
                    $q->whereIn('status', ['APPROVED', 'PARTIALLY_APPROVED', 'ON_LOAN', 'OVERDUE']);
                })
                ->sum('approved_quantity');

            $logicalAvailable = max(0, $tool->total_stock - $activeBorrowed);

            $tool->update(['available_stock' => $logicalAvailable]);

            $rows[] = [
                $tool->id,
                $tool->code,
                $tool->name,
                $tool->total_stock,
                $activeBorrowed,
                $logicalAvailable,
            ];
        }

        $this->table(
            ['ID', 'Kode', 'Nama Alat', 'Total Stok', 'Unit Dipinjam', 'Stok Tersedia Baru'],
            $rows
        );

        $this->info('Sinkronisasi stok berhasil diperbarui untuk seluruh alat.');

        return self::SUCCESS;
    }
}
