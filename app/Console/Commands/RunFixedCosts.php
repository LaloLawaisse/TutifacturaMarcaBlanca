<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\FixedCost;
use App\Business;
use App\BusinessLocation;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RunFixedCosts extends Command
{
    protected $signature = 'fixed-costs:run';
    protected $description = 'Generate monthly expense transactions for fixed costs due today';

    public function handle(TransactionUtil $transactionUtil)
    {
        $today = now()->startOfDay();
        $count = 0;
        $costs = FixedCost::where('active', true)
            ->whereNotNull('next_run_date')
            ->whereDate('next_run_date', '<=', $today->toDateString())
            ->get();
        foreach ($costs as $cost) {
            try {
                DB::beginTransaction();
                $business_id = $cost->business_id;
                $owner_id = Business::where('id', $business_id)->value('owner_id');
                $location_id = BusinessLocation::where('business_id', $business_id)->value('id');
                if (!$location_id) { DB::rollBack(); continue; }
                // Ensure expense category exists (Costos Fijos)
                $expense_category_id = DB::table('expense_categories')
                    ->where('business_id', $business_id)
                    ->where('name', 'Costos Fijos')
                    ->value('id');
                if (!$expense_category_id) {
                    $expense_category_id = DB::table('expense_categories')->insertGetId([
                        'name' => 'Costos Fijos',
                        'business_id' => $business_id,
                        'code' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                // Build a fake request to leverage existing util
                $req = new Request([
                    'ref_no' => null,
                    'transaction_date' => $today->toDateString(),
                    'location_id' => $location_id,
                    'final_total' => $cost->amount,
                    'additional_notes' => 'Costo fijo: '.$cost->name,
                    'expense_category_id' => $expense_category_id,
                ]);
                $transaction = $transactionUtil->createExpense($req, $business_id, $owner_id ?: 1);
                // Advance next_run_date to next month same day
                $dom = (int) $cost->day_of_month;
                $next = $today->copy()->addMonthNoOverflow();
                $next = $next->day(min($dom, (int)$next->endOfMonth()->day));
                $cost->next_run_date = $next->toDateString();
                $cost->save();
                DB::commit();
                $count++;
            } catch (\Throwable $e) {
                DB::rollBack();
                \Log::warning('Fixed cost run failed for ID '.$cost->id.': '.$e->getMessage());
            }
        }
        $this->info('Generated '.$count.' fixed cost expenses.');
        return 0;
    }
}
