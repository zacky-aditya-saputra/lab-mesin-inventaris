<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    /**
     * Serve private identity card file (KTM/KTP) with strict PDP authorization.
     */
    public function showIdentityCard(Request $request, User $user): Response
    {
        $viewer = $request->user();

        // Authorization:
        // 1. Owner
        // 2. Kepala Lab
        // 3. Laboran who shares a loan request with this user
        $isOwner = $viewer->is($user);
        $isKepala = $viewer->isKepalaLab();
        $isRelevantLaboran = $viewer->isLaboran() && $user->loanRequests()
            ->where('laboratory_id', $viewer->laboratory_id)
            ->exists();

        if (! ($isOwner || $isKepala || $isRelevantLaboran)) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat dokumen identitas ini.');
        }

        $path = $user->identity_card_path;

        if (blank($path)) {
            abort(404, 'Dokumen identitas tidak ditemukan.');
        }

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        abort(404, 'Berkas fisik tidak ditemukan di server.');
    }

    /**
     * Serve private loan approval letter with strict PDP authorization.
     */
    public function showLoanLetter(Request $request, LoanRequest $loan): Response
    {
        $viewer = $request->user();

        // Authorization:
        // 1. Borrower
        // 2. Kepala Lab
        // 3. Laboran of the loan's laboratory
        $isBorrower = $viewer->id === $loan->user_id;
        $isKepala = $viewer->isKepalaLab();
        $isLaboranOfLab = $viewer->isLaboran() && $viewer->laboratory_id === $loan->laboratory_id;

        if (! ($isBorrower || $isKepala || $isLaboranOfLab)) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat surat peminjaman ini.');
        }

        $path = $loan->loan_letter_path;

        if (blank($path)) {
            abort(404, 'Surat peminjaman tidak ditemukan.');
        }

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        abort(404, 'Berkas fisik tidak ditemukan di server.');
    }
}
