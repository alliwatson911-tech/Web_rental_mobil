<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerDocument;
use App\Models\DocumentAccessLog;
use App\Models\MemberProfile;
use App\Models\MembershipTier;
use App\Models\User;
use App\Services\MembershipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(protected MembershipService $membership) {}

    /** Daftar Seluruh Member dengan Fitur Pencarian dan Filter. */
    public function index(Request $request): View
    {
        $members = User::where('role', User::ROLE_CUSTOMER)
            ->with(['memberProfile.automaticTier', 'memberProfile.manualTier'])
            ->when($request->query('q'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->query('tier'), function ($q, $tierSlug) {
                $q->whereHas('memberProfile', function ($p) use ($tierSlug) {
                    $p->whereHas('automaticTier', fn ($t) => $t->where('slug', $tierSlug))
                      ->orWhereHas('manualTier', fn ($t) => $t->where('slug', $tierSlug));
                });
            })
            ->when($request->query('kyc'), function ($q, $kycStatus) {
                $q->whereHas('memberProfile', fn ($p) => $p->where('kyc_status', $kycStatus));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $tiers = MembershipTier::active()->orderBy('sort_order')->get();

        return view('admin.members.index', compact('members', 'tiers'));
    }

    /** Detail Member, Profil, Dokumen e-KYC, dan Riwayat Transaksi. */
    public function show(User $member): View
    {
        abort_unless($member->isMember(), 404);

        $member->load([
            'memberProfile.automaticTier',
            'memberProfile.manualTier',
            'memberProfile.manualTierUpdater',
            'documents',
            'loyaltyLedgers' => fn ($q) => $q->latest()->take(10),
            'memberBookings' => fn ($q) => $q->with('car')->latest()->take(10),
        ]);

        $tiers = MembershipTier::active()->orderBy('sort_order')->get();
        $profile = $member->ensureMemberProfile();

        return view('admin.members.show', compact('member', 'profile', 'tiers'));
    }

    /** Ubah Tier Member secara Manual dengan Catatan Alasan. */
    public function updateTier(Request $request, User $member): RedirectResponse
    {
        abort_unless($member->isMember(), 404);

        $request->validate([
            'tier_id' => ['required', 'exists:membership_tiers,id'],
            'notes' => ['required', 'string', 'max:500'],
        ], [
            'notes.required' => 'Catatan alasan perubahan tier wajib diisi.',
        ]);

        $tier = MembershipTier::findOrFail($request->input('tier_id'));
        $this->membership->setManualTier($member, $tier, $request->input('notes'), $request->user());

        return back()->with('success', "Tier member {$member->name} berhasil diubah secara manual menjadi {$tier->name}.");
    }

    /** Kembalikan Tier Member ke Perhitungan Otomatis. */
    public function resetTier(Request $request, User $member): RedirectResponse
    {
        abort_unless($member->isMember(), 404);

        $this->membership->resetManualTier($member);

        return back()->with('success', "Tier member {$member->name} berhasil dikembalikan ke kalkulasi otomatis riwayat sewa.");
    }

    /** Aktifkan / Nonaktifkan Akun Member. */
    public function toggleStatus(Request $request, User $member): RedirectResponse
    {
        abort_unless($member->isMember(), 404);

        $profile = $member->ensureMemberProfile();
        $profile->is_active = ! $profile->is_active;
        $profile->save();

        $statusMsg = $profile->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return back()->with('success', "Akun member {$member->name} berhasil {$statusMsg}.");
    }

    /** Verifikasi Dokumen Identitas Member oleh Admin. */
    public function verifyDocument(Request $request, User $member, CustomerDocument $document): RedirectResponse
    {
        abort_unless($document->user_id === $member->id, 404);

        $document->update([
            'status' => CustomerDocument::STATUS_VERIFIED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'notes' => $request->input('notes', 'Dokumen valid dan sesuai identitas.'),
        ]);

        // Cek jika KTP dan SIM sudah valid, otomatis ubah status KYC member menjadi verified
        $hasVerifiedKtp = $member->documents()->where('type', CustomerDocument::TYPE_KTP)->where('status', CustomerDocument::STATUS_VERIFIED)->exists();
        $hasVerifiedSim = $member->documents()->where('type', CustomerDocument::TYPE_SIM)->where('status', CustomerDocument::STATUS_VERIFIED)->exists();

        if ($hasVerifiedKtp && $hasVerifiedSim) {
            $this->membership->updateKycStatus($member, MemberProfile::KYC_VERIFIED, 'KTP dan SIM A telah diverifikasi lengkap.', $request->user());
        }

        return back()->with('success', "Dokumen {$document->typeLabel()} member {$member->name} telah diverifikasi sah.");
    }

    /** Tolak Dokumen Identitas Member oleh Admin. */
    public function rejectDocument(Request $request, User $member, CustomerDocument $document): RedirectResponse
    {
        abort_unless($document->user_id === $member->id, 404);

        $request->validate(['notes' => ['required', 'string', 'max:255']], [
            'notes.required' => 'Tuliskan alasan penolakan dokumen agar member dapat memperbaikinya.',
        ]);

        $document->update([
            'status' => CustomerDocument::STATUS_REJECTED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        $this->membership->updateKycStatus($member, MemberProfile::KYC_REJECTED, $request->input('notes'), $request->user());

        return back()->with('success', "Dokumen {$document->typeLabel()} ditolak.");
    }

    /** Log Akses Dokumen Sensitif (Audit Trail). */
    public function auditLogs(Request $request): View
    {
        $logs = DocumentAccessLog::with('user')
            ->latest('id')
            ->paginate(30);

        return view('admin.members.logs', compact('logs'));
    }
}

