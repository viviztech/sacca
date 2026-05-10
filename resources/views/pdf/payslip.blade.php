<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip — {{ $payslip->payrollCycle->monthName() }} {{ $payslip->payrollCycle->year }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #4F46E5; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { color: #4F46E5; margin: 0 0 4px; font-size: 20px; }
        .header p { margin: 0; color: #666; font-size: 11px; }
        .section { margin-bottom: 16px; }
        .section h3 { background: #F3F4F6; padding: 6px 10px; margin: 0 0 8px; font-size: 11px; text-transform: uppercase; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 5px 8px; vertical-align: top; }
        .label { color: #666; width: 40%; }
        .value { font-weight: bold; }
        .salary-table td { border: 1px solid #E5E7EB; }
        .salary-table .total { background: #F0F0FF; font-weight: bold; }
        .footer { margin-top: 30px; border-top: 1px solid #E5E7EB; padding-top: 10px; font-size: 10px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sacca Aviation Training Institute</h1>
        <p>{{ $payslip->payrollCycle->branch->name }} &bull; Payslip for {{ $payslip->payrollCycle->monthName() }} {{ $payslip->payrollCycle->year }}</p>
    </div>

    <div class="section">
        <h3>Employee Details</h3>
        <table>
            <tr><td class="label">Name</td><td class="value">{{ $payslip->user->name }}</td><td class="label">Employee ID</td><td class="value">{{ $payslip->user->employee_id ?? 'N/A' }}</td></tr>
            <tr><td class="label">Role</td><td class="value">{{ $payslip->user->role->label() }}</td><td class="label">Branch</td><td class="value">{{ $payslip->payrollCycle->branch->name }}</td></tr>
            <tr><td class="label">Email</td><td class="value">{{ $payslip->user->email }}</td><td class="label">Pay Period</td><td class="value">{{ $payslip->payrollCycle->monthName() }} {{ $payslip->payrollCycle->year }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Attendance Summary</h3>
        <table>
            <tr><td class="label">Working Days</td><td class="value">{{ $payslip->working_days }}</td><td class="label">Present Days</td><td class="value">{{ $payslip->present_days }}</td></tr>
            <tr><td class="label">Absent Days</td><td class="value">{{ $payslip->absent_days }}</td><td class="label">Attendance %</td><td class="value">{{ $payslip->working_days > 0 ? round(($payslip->present_days / $payslip->working_days) * 100, 1) : 0 }}%</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Salary Breakdown</h3>
        <table class="salary-table">
            <tr><td>Basic Salary</td><td style="text-align:right">₹{{ number_format($payslip->basic_salary, 2) }}</td></tr>
            <tr><td>Gross Salary (Attendance-based)</td><td style="text-align:right">₹{{ number_format($payslip->gross_salary, 2) }}</td></tr>
            @if($payslip->deductions)
                @foreach($payslip->deductions as $label => $amount)
                <tr><td>{{ $label }}</td><td style="text-align:right">- ₹{{ number_format($amount, 2) }}</td></tr>
                @endforeach
            @endif
            <tr class="total"><td><strong>Net Salary</strong></td><td style="text-align:right"><strong>₹{{ number_format($payslip->net_salary, 2) }}</strong></td></tr>
        </table>
    </div>

    <div class="footer">
        This is a computer-generated payslip. No signature required. Generated on {{ now()->format('d M Y') }}.
    </div>
</body>
</html>
