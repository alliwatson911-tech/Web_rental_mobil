<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\UpdateProfileRequest;
use App\Models\CustomerDocument;
use App\Models\DocumentAccessLog;
use App\Models\MemberProfile;
use App\Models\MembershipTier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AccountController extends Controller
{
    /** Dasbor Akun Saya (Ringkasan Keanggotaan, Tier, Poin, & Riwayat Pesanan). */
    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = $user->ensureMemberProfile()->load(['automaticTier', 'manualTier']);
        $tier = $profile->effectiveTier();

        // Cari tier berikutnya untuk perhitungan progress
        $nextTier = MembershipTier::active()
            ->where('min_completed_rentals', '>', $profile->total_completed_rentals)
            ->orderBy('min_completed_rentals')
            ->first();

        $rentalsNeeded = $nextTier ? max(0, $nextTier->min_completed_rentals - $profile->total_completed_rentals) : 0;
        $progressPercent = 100;
        if ($nextTier) {
            $prevMin = $tier->min_completed_rentals;
            $span = max(1, $nextTier->min_completed_rentals - $prevMin);
            $currentAdv = max(0, $profile->total_completed_rentals - $prevMin);
            $progressPercent = min(100, (int) round(($currentAdv / $span) * 100));
        }

        $bookings = $user->memberBookings()
            ->with(['car', 'pickupLocation'])
            ->latest()
            ->take(5)
            ->get();

        $ktpDoc = $user->documents()->where('type', CustomerDocument::TYPE_KTP)->first();
        $simDoc = $user->documents()->where('type', CustomerDocument::TYPE_SIM)->first();

        return view('member.account.index', [
            'user' => $user,
            'profile' => $profile,
            'tier' => $tier,
            'nextTier' => $nextTier,
            'rentalsNeeded' => $rentalsNeeded,
            'progressPercent' => $progressPercent,
            'bookings' => $bookings,
            'ktpDoc' => $ktpDoc,
            'simDoc' => $simDoc,
        ]);
    }

    /** Halaman Edit Profil Member. */
    public function showProfile(Request $request): View
    {
        $user = $request->user();
        $profile = $user->ensureMemberProfile();

        return view('member.account.profile', compact('user', 'profile'));
    }

    /** Simpan Perubahan Profil Member. */
    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->name = $data['name'];
        $user->email = strtolower($data['email']);
        $user->phone = preg_replace('/\s+/', '', $data['phone']);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        $profile = $user->ensureMemberProfile();
        $profile->update([
            'address' => $data['address'] ?? $profile->address,
            'nik' => $data['nik'] ?? $profile->nik,
            'sim_number' => $data['sim_number'] ?? $profile->sim_number,
        ]);

        return back()->with('success', 'Profil keanggotaan berhasil diperbarui.');
    }

    /** Halaman Pengelolaan Dokumen e-KYC (KTP & SIM A). */
    public function showDocuments(Request $request): View
    {
        $user = $request->user();
        $profile = $user->ensureMemberProfile();
        $ktpDoc = $user->documents()->where('type', CustomerDocument::TYPE_KTP)->first();
        $simDoc = $user->documents()->where('type', CustomerDocument::TYPE_SIM)->first();

        return view('member.account.documents', compact('user', 'profile', 'ktpDoc', 'simDoc'));
    }

    /** Unggah Dokumen Identitas ke Storage Privat. */
    public function uploadDocument(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->validate([
            'type' => ['required', 'string', 'in:ktp,sim'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'document.required' => 'Pilih berkas foto/scan dokumen terlebih dahulu.',
            'document.mimes' => 'Format berkas harus JPG, PNG, WEBP, atau PDF.',
            'document.max' => 'Ukuran berkas maksimal 5 MB.',
        ]);

        $type = $request->input('type');
        $path = $request->file('document')->store('member_documents', 'local');

        $doc = $user->documents()->where('type', $type)->first();
        if ($doc) {
            if (Storage::disk('local')->exists($doc->path)) {
                Storage::disk('local')->delete($doc->path);
            }
            $doc->update([
                'path' => $path,
                'status' => CustomerDocument::STATUS_PENDING,
                'notes' => null,
                'verified_by' => null,
                'verified_at' => null,
            ]);
        } else {
            $doc = $user->documents()->create([
                'type' => $type,
                'path' => $path,
                'status' => CustomerDocument::STATUS_PENDING,
            ]);
        }

        // Catat ke log akses
        DocumentAccessLog::record($user, $type, $doc->id, 'upload', $request);

        // Perbarui status KYC di profil member
        $profile = $user->ensureMemberProfile();
        $profile->update([
            'kyc_status' => MemberProfile::KYC_PENDING,
            'kyc_notes' => 'Menunggu verifikasi admin.',
        ]);

        $typeLabel = $type === 'ktp' ? 'e-KTP' : 'SIM A';

        return back()->with('success', "Dokumen {$typeLabel} berhasil diunggah secara aman. Admin akan memverifikasi identitas Anda.");
    }

    /** Melihat Berkas Dokumen secara Privat & Terotorisasi dengan Pencatatan Audit Log. */
    public function viewDocument(Request $request, CustomerDocument $document)
    {
        $user = $request->user();

        // Otorisasi: hanya pemilik dokumen atau admin yang berhak membuka
        abort_unless($user && ($user->id === $document->user_id || $user->isAdmin()), 403);

        // Catat ke log audit akses dokumen
        DocumentAccessLog::record($user, $document->type, $document->id, 'view', $request);

        if (Storage::disk('local')->exists($document->path)) {
            return Storage::disk('local')->response($document->path);
        }

        if (Storage::disk('public')->exists($document->path)) {
            return Storage::disk('public')->response($document->path);
        }

        abort(404, 'Berkas dokumen tidak ditemukan di penyimpanan server.');
    }

    /** Riwayat Pesanan Lengkap Member. */
    public function bookings(Request $request): View
    {
        $user = $request->user();
        $bookings = $user->memberBookings()
            ->with(['car.primaryPhoto', 'pickupLocation'])
            ->latest()
            ->paginate(10);

        return view('member.account.bookings', compact('user', 'bookings'));
    }
}

