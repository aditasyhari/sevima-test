<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Tagihan\CreateTagihanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagihanRequest;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagihanController extends Controller
{
    public function __construct(
        protected CreateTagihanAction $createTagihanAction
    ) {}

    /**
     * Display a listing of bills for the authenticated admin's institution.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $search = $request->query('search');

        // Scoped automatically to current institution by BelongsToInstitution trait
        $query = Tagihan::with(['mahasiswa', 'riwayatTransaksis'])
            ->latest('created_at');

        if ($status && in_array($status, [Tagihan::STATUS_BELUM_BAYAR, Tagihan::STATUS_LUNAS, Tagihan::STATUS_KEDALUWARSA])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_tagihan', 'ilike', "%{$search}%")
                    ->orWhereHas('mahasiswa', function ($mQuery) use ($search) {
                        $mQuery->where('name', 'ilike', "%{$search}%")
                            ->orWhere('nim', 'ilike', "%{$search}%");
                    });
            });
        }

        $tagihans = $query->paginate(10)->withQueryString();

        // Summary statistics for the current institution
        $totalNominal = Tagihan::sum('nominal');
        $totalLunas = Tagihan::where('status', Tagihan::STATUS_LUNAS)->sum('nominal');
        $totalBelumBayar = Tagihan::where('status', Tagihan::STATUS_BELUM_BAYAR)->sum('nominal');
        $countBelumBayar = Tagihan::where('status', Tagihan::STATUS_BELUM_BAYAR)->count();
        $countLunas = Tagihan::where('status', Tagihan::STATUS_LUNAS)->count();
        $countExpired = Tagihan::where('status', Tagihan::STATUS_KEDALUWARSA)->count();

        // Mahasiswa list for creating new bill - scoped to admin's institution
        $students = User::where('role', 'mahasiswa')
            ->where('institusi_id', auth()->user()->institusi_id)
            ->select('id', 'name', 'nim', 'prodi')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Tagihan/Index', [
            'tagihans' => $tagihans,
            'filters' => [
                'status' => $status ?? 'semua',
                'search' => $search ?? '',
            ],
            'stats' => [
                'total_nominal' => (float) $totalNominal,
                'total_lunas' => (float) $totalLunas,
                'total_belum_bayar' => (float) $totalBelumBayar,
                'count_belum_bayar' => $countBelumBayar,
                'count_lunas' => $countLunas,
                'count_expired' => $countExpired,
            ],
            'students' => $students,
        ]);
    }

    /**
     * Store a newly created bill.
     */
    public function store(StoreTagihanRequest $request): RedirectResponse
    {
        $this->createTagihanAction->execute(
            $request->validated(),
            auth()->user()->institusi_id
        );

        return redirect()->route('admin.tagihan.index')->with('success', 'Tagihan UKT berhasil dibuat.');
    }
}
