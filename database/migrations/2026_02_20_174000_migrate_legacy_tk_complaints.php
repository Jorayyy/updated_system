<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EmployeeTransaction;
use App\Models\Concern;
use App\Models\ConcernActivity;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all pending timekeeping complaints from the old system
        $oldTransactions = DB::table('employee_transactions')
            ->where('transaction_type', 'timekeeping_complaint')
            ->whereIn('status', ['pending', 'hr_approved'])
            ->get();

        foreach ($oldTransactions as $tx) {
            $details = json_decode($tx->details, true);
            
            // Generate a temporary title
            $title = "Migrated: TK Complaint - " . ($tx->effective_date ?? 'Unknown Date');
            
            // Check if already migrated to prevent duplicates (based on legacy transaction number in description)
            $exists = Concern::where('description', 'like', '%' . $tx->transaction_number . '%')->exists();
            
            if (!$exists) {
                $concern = Concern::create([
                    'ticket_number' => Concern::generateTicketNumber(),
                    'reported_by' => $tx->user_id,
                    'title' => $title,
                    'category' => 'timekeeping',
                    'priority' => 'medium',
                    'description' => "LEGACY TICKET: {$tx->transaction_number}\n\nORIGINAL REASON: " . ($tx->reason ?? 'No reason provided'),
                    'date_affected' => $tx->effective_date,
                    'affected_punch' => $details['affected_punch'] ?? null,
                    'status' => 'open',
                    'created_at' => $tx->created_at,
                    'updated_at' => $tx->updated_at,
                ]);

                // Create activity log
                ConcernActivity::create([
                    'concern_id' => $concern->id,
                    'user_id' => $tx->user_id,
                    'action' => 'created',
                    'description' => 'Migrated from legacy transaction system (' . $tx->transaction_number . ')',
                    'created_at' => $tx->created_at,
                ]);

                // Optional: Update the old transaction status so it doesn't stay pending there
                DB::table('employee_transactions')
                    ->where('id', $tx->id)
                    ->update(['status' => 'cancelled', 'rejection_reason' => 'Migrated to new Concerns system.']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to reverse this without deleting new concerns, best to leave as is.
    }
};
