<?php

namespace App\Console\Commands;

use App\Models\Tool;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Signature('tools:sync-images {--force : Timpa gambar yang sudah terpasang} {--dry-run : Tinjau hasil pencocokan tanpa menyimpan ke database}')]
#[Description('Mencocokkan dan menyinkronkan file foto di folder storage/app/public/tools dengan master alat secara massal.')]
class SyncToolImages extends Command
{
    /**
     * Ekstensi berkas gambar yang didukung.
     *
     * @var array<int, string>
     */
    private const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== SINKRONISASI MASSAL FOTO ALAT LABORATORIUM ===');

        // Pastikan symlink public/storage aktif
        $this->ensureStorageLink();

        $toolsDirectory = storage_path('app/public/tools');

        if (! File::isDirectory($toolsDirectory)) {
            File::makeDirectory($toolsDirectory, 0755, true);
            $this->warn("Folder {$toolsDirectory} belum ada, folder baru telah dibuat otomatis.");
            $this->line('Silakan letakkan berkas foto alat ke dalam folder tersebut dan jalankan perintah ini kembali.');

            return self::SUCCESS;
        }

        $allFiles = File::files($toolsDirectory);
        $imageFiles = array_filter(
            $allFiles,
            fn (\SplFileInfo $file): bool => in_array(strtolower($file->getExtension()), self::SUPPORTED_EXTENSIONS, true)
        );

        if (empty($imageFiles)) {
            $this->warn("Tidak ditemukan berkas gambar (.jpg, .jpeg, .png, .webp) di dalam folder: {$toolsDirectory}");
            $this->line('Petunjuk: Ekstrak foto alat dari Google Drive ke dalam folder tersebut lalu jalankan kembali perintah ini.');

            return self::SUCCESS;
        }

        $this->info('Ditemukan '.count($imageFiles).' berkas gambar di folder tools.');

        $isDryRun = (bool) $this->option('dry-run');
        $isForce = (bool) $this->option('force');

        if ($isDryRun) {
            $this->warn('[DRY-RUN AKTIF]: Perubahan hanya ditampilkan sebagai simulasi dan TIDAK disimpan ke database.');
        }

        $tools = Tool::query()->orderBy('code')->get();
        $matchedCount = 0;
        $skippedCount = 0;
        $unmatchedCount = 0;

        $tableRows = [];

        foreach ($tools as $tool) {
            // Jika sudah ada gambar dan tidak menggunakan flag --force, lewati
            if ($tool->image_path && ! $isForce && File::exists(storage_path('app/public/'.$tool->image_path))) {
                $tableRows[] = [
                    $tool->code,
                    Str::limit($tool->name, 35),
                    $tool->image_path,
                    '<fg=cyan>Sudah Ada (Dilewati)</>',
                ];
                $skippedCount++;

                continue;
            }

            $matchedFile = $this->findBestMatchingImage($tool, $imageFiles);

            if ($matchedFile) {
                $relativePath = 'tools/'.$matchedFile->getFilename();

                if (! $isDryRun) {
                    $this->optimizeImageFile($matchedFile->getRealPath());
                    $tool->update(['image_path' => $relativePath]);
                }

                $tableRows[] = [
                    $tool->code,
                    Str::limit($tool->name, 35),
                    $matchedFile->getFilename(),
                    $isDryRun ? '<fg=yellow>Cocok (Simulasi)</>' : '<fg=green>Berhasil Terpasang</>',
                ];
                $matchedCount++;
            } else {
                $tableRows[] = [
                    $tool->code,
                    Str::limit($tool->name, 35),
                    '-',
                    '<fg=red>Belum Ada Foto</>',
                ];
                $unmatchedCount++;
            }
        }

        $this->table(['Kode Alat', 'Nama Alat', 'Berkas Foto', 'Status'], $tableRows);

        $this->newLine();
        $this->info("Ringkasan: {$matchedCount} alat berhasil dicocokkan, {$skippedCount} dilewati, {$unmatchedCount} belum memiliki foto.");

        if ($isDryRun) {
            $this->line('Jalankan tanpa flag --dry-run untuk menerapkan perubahan secara permanen: <info>php artisan tools:sync-images</info>');
        }

        return self::SUCCESS;
    }

    /**
     * Cari berkas gambar yang paling cocok untuk alat yang diberikan.
     *
     * @param  array<\SplFileInfo>  $imageFiles
     */
    private function findBestMatchingImage(Tool $tool, array $imageFiles): ?\SplFileInfo
    {
        $toolCodeClean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $tool->code));
        $toolSlug = strtolower((string) $tool->slug);
        $toolNameSlug = Str::slug($tool->name);

        // Ekstrak kata-kata kunci penting dari nama alat (panjang >= 3 huruf)
        // Hindari kata-kata umum yang bisa menimbulkan false positive
        $genericWords = [
            'alat', 'mesin', 'dan', 'digital', 'uji', 'set', 'true', 'rms',
            'sensor', 'tube', 'bed', 'box', 'universal', 'manual', 'standar',
            'otomatis', 'mini', 'besar', 'tipe', 'type', 'luar', 'dalam',
        ];

        $keywords = array_filter(
            explode('-', $toolNameSlug),
            fn (string $word): bool => strlen($word) >= 3 && ! in_array($word, $genericWords, true)
        );

        $bestMatch = null;
        $highestScore = 0;

        foreach ($imageFiles as $file) {
            $filenameWithoutExt = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $cleanFilename = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $filenameWithoutExt));
            $fileSlug = Str::slug($filenameWithoutExt);

            // 1. Prioritas Tertinggi: Kode alat sama persis (misal: LABTM16-EL-1026-001.jpg)
            if ($cleanFilename === $toolCodeClean || str_contains($cleanFilename, $toolCodeClean)) {
                return $file;
            }

            // 2. Prioritas Kedua: Slug nama alat sama persis (misal: jangka-sorong-digital.jpg)
            if ($fileSlug === $toolSlug || $fileSlug === $toolNameSlug) {
                return $file;
            }

            // 3. Prioritas Ketiga: Pencocokan kata per kata (word boundary)
            $fileWords = array_filter(explode('-', $fileSlug));
            $score = 0;

            if ($fileSlug === $toolSlug || $fileSlug === $toolNameSlug) {
                $score += 100;
            } elseif (str_contains($toolSlug, $fileSlug) || str_contains($fileSlug, $toolSlug)) {
                $score += 40;
            }

            foreach ($keywords as $keyword) {
                // Cocokkan kata utuh (bukan sekadar substring, misal: 'las' tidak boleh cocok dengan 'plastik')
                if (in_array($keyword, $fileWords, true)) {
                    $score += 30;
                }
            }

            if ($score > $highestScore && $score >= 30) {
                $highestScore = $score;
                $bestMatch = $file;
            }
        }

        return $bestMatch;
    }

    /**
     * Optimasi ukuran gambar jika file melebihi 300 KB menggunakan GD.
     */
    private function optimizeImageFile(string $filePath): void
    {
        if (! extension_loaded('gd') || ! file_exists($filePath) || filesize($filePath) <= 300 * 1024) {
            return;
        }

        $imageInfo = @getimagesize($filePath);

        if (! $imageInfo) {
            return;
        }

        $mime = $imageInfo['mime'];
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($filePath),
            'image/png' => @imagecreatefrompng($filePath),
            'image/webp' => @imagecreatefromwebp($filePath),
            default => null,
        };

        if (! $src) {
            return;
        }

        $origW = imagesx($src);
        $origH = imagesy($src);
        $maxDim = 1000;

        if ($origW > $maxDim || $origH > $maxDim) {
            if ($origW > $origH) {
                $newW = $maxDim;
                $newH = (int) round(($origH / $origW) * $maxDim);
            } else {
                $newH = $maxDim;
                $newW = (int) round(($origW / $origH) * $maxDim);
            }

            $dst = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($src);
            $src = $dst;
        }

        match ($mime) {
            'image/jpeg' => imagejpeg($src, $filePath, 82),
            'image/png' => imagepng($src, $filePath, 8),
            'image/webp' => imagewebp($src, $filePath, 82),
            default => null,
        };

        imagedestroy($src);
    }

    /**
     * Memastikan symlink public/storage sudah terhubung.
     */
    private function ensureStorageLink(): void
    {
        if (! File::exists(public_path('storage'))) {
            $this->line('Symlink public/storage belum ditemukan, menjalankan storage:link...');
            Artisan::call('storage:link');
            $this->info('Symlink public/storage berhasil dibuat.');
        }
    }
}
