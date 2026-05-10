<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PayslipController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payslips = Payslip::with('payrollCycle')
            ->where('user_id', $request->user()->id)
            ->whereNotNull('published_at')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payslips->map(fn ($p) => [
                'id' => $p->id,
                'month' => $p->payrollCycle->month,
                'year' => $p->payrollCycle->year,
                'month_name' => $p->payrollCycle->monthName(),
                'net_salary' => $p->net_salary,
                'present_days' => $p->present_days,
                'working_days' => $p->working_days,
                'has_pdf' => ! is_null($p->pdf_path),
            ]),
        ]);
    }

    public function download(Request $request, int $id): JsonResponse
    {
        $payslip = Payslip::where('user_id', $request->user()->id)
            ->whereNotNull('published_at')
            ->findOrFail($id);

        if (! $payslip->pdf_path) {
            return response()->json(['success' => false, 'message' => 'PDF not yet generated.'], 404);
        }

        $url = Storage::disk('public')->url($payslip->pdf_path);

        return response()->json(['success' => true, 'data' => ['url' => $url]]);
    }
}
