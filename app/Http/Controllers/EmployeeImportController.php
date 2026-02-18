<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Account;
use App\Models\Department;
use App\Models\Site;
use App\Models\PayrollGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EmployeeImportController extends Controller
{
    /**
     * Download the CSV template for bulk upload.
     */
    public function downloadTemplate()
    {
        $headers = [
            'EmployeeID', 'FirstName', 'MiddleName', 'LastName', 'Extension',
            'Email', 'TemporaryPassword', 'Role', 'CampaignAccount',
            'Department', 'Position', 'DateHired', 'SiteLocation', 'PayrollCycle'
        ];

        return response()->streamDownload(function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            // Sample data row
            fputcsv($file, [
                'EMP' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT),
                'Juan', 'Protacio', 'Rizal', '',
                'juan.rizal@example.com', 'Welcome' . date('Y'), 'employee', 'Campaign Name',
                'Operations', 'Agent', date('Y-m-d'), 'Main Site', 'Weekly'
            ]);
            
            fclose($file);
        }, 'employee_bulk_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Process the uploaded CSV file.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            $path = $request->file('csv_file')->getRealPath();
            $data = array_map('str_getcsv', file($path));
            
            if (count($data) < 2) {
                return back()->with('error', 'The uploaded file is empty or missing data rows.');
            }

            $header = array_shift($data);
            $mapping = array_flip($header);
            
            // Expected columns: check at least some key ones
            $required = ['EmployeeID', 'Email', 'FirstName', 'LastName', 'CampaignAccount'];
            foreach ($required as $col) {
                if (!isset($mapping[$col])) {
                    return back()->with('error', "Missing required column: $col");
                }
            }

            $successCount = 0;
            $errors = [];

            foreach ($data as $index => $row) {
                if (count($row) < count($header)) continue; // Skip malformed rows
                
                $rowNum = $index + 2;
                $empId = trim($row[$mapping['EmployeeID']]);
                $email = trim($row[$mapping['Email']]);

                // Basic duplicates check
                if (User::where('employee_id', $empId)->orWhere('email', $email)->exists()) {
                    $errors[] = "Row $rowNum: Employee ID ($empId) or Email ($email) already exists.";
                    continue;
                }

                // Look up related IDs
                $accountName = trim($row[$mapping['CampaignAccount']]);
                $account = Account::where('name', $accountName)->where('type', 'campaign')->first();
                
                if (!$account) {
                    $errors[] = "Row $rowNum: Campaign '$accountName' not found.";
                    continue;
                }

                $deptName = trim($row[$mapping['Department']] ?? '');
                $dept = Department::where('name', $deptName)->first();

                $siteName = trim($row[$mapping['SiteLocation']] ?? '');
                $site = Site::where('name', $siteName)->first();

                $payrollName = trim($row[$mapping['PayrollCycle']] ?? '');
                $payrollGroup = PayrollGroup::where('name', $payrollName)->first();

                try {
                    User::create([
                        'employee_id' => $empId,
                        'name' => trim($row[$mapping['FirstName']] . ' ' . $row[$mapping['LastName']]),
                        'first_name' => $row[$mapping['FirstName']],
                        'middle_name' => $row[$mapping['MiddleName']] ?? '',
                        'last_name' => $row[$mapping['LastName']],
                        'name_extension' => $row[$mapping['Extension']] ?? '',
                        'email' => $email,
                        'password' => Hash::make($row[$mapping['TemporaryPassword']] ?? 'Welcome@123'),
                        'role' => strtolower(trim($row[$mapping['Role']] ?? 'employee')),
                        'account_id' => $account->id,
                        'department_id' => $dept?->id,
                        'department' => $dept?->name,
                        'position' => $row[$mapping['Position']] ?? 'Agent',
                        'date_hired' => $row[$mapping['DateHired']] ?? Carbon::now()->format('Y-m-d'),
                        'site_id' => $site?->id,
                        'payroll_group_id' => $payrollGroup?->id,
                        'is_active' => true,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row $rowNum: " . $e->getMessage();
                }
            }

            $result = "Bulk Upload complete. $successCount records imported.";
            if (count($errors) > 0) {
                return back()->with('success', $result)->with('import_errors', $errors);
            }

            return back()->with('success', $result);

        } catch (\Exception $e) {
            Log::error('Bulk Upload Error: ' . $e->getMessage());
            return back()->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }
}
