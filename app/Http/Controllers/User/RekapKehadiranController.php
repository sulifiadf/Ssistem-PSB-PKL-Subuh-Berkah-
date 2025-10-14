<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\kehadiran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RekapKehadiranController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $currentUserId = $user->user_id;
        
        // Default filter values
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $filterType = $request->get('filter_type', 'range'); // range, month, year
        $month = $request->get('month', now()->format('Y-m'));
        $year = $request->get('year', now()->format('Y'));

        // Build query based on filter type
        $query = kehadiran::where('user_id', $currentUserId);

        switch ($filterType) {
            case 'month':
                $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->format('Y-m-d');
                $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->format('Y-m-d');
                break;
            case 'year':
                $startDate = Carbon::createFromFormat('Y', $year)->startOfYear()->format('Y-m-d');
                $endDate = Carbon::createFromFormat('Y', $year)->endOfYear()->format('Y-m-d');
                break;
            case 'range':
            default:
                // Use provided start and end dates
                break;
        }

        // Apply date filter
        $kehadiranData = $query->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Calculate statistics
        $totalMasuk = $kehadiranData->where('status', 'masuk')->count();
        $totalLibur = $kehadiranData->where('status', 'libur')->count();
        $totalHari = $kehadiranData->count();
        $persentaseKehadiran = $totalHari > 0 ? round(($totalMasuk / $totalHari) * 100, 1) : 0;

        // Group by month for chart data
        $dataPerBulan = $kehadiranData->groupBy(function($item) {
            return Carbon::parse($item->tanggal)->format('Y-m');
        })->map(function($items, $month) {
            return [
                'bulan' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'masuk' => $items->where('status', 'masuk')->count(),
                'libur' => $items->where('status', 'libur')->count(),
                'total' => $items->count()
            ];
        })->values();

        return view('user.rekapKehadiran', compact(
            'kehadiranData',
            'totalMasuk',
            'totalLibur',
            'totalHari',
            'persentaseKehadiran',
            'dataPerBulan',
            'startDate',
            'endDate',
            'filterType',
            'month',
            'year'
        ));
    }
}
