<?php

namespace App\Http\Controllers;

use App\FixedCost;
use App\BusinessLocation;
use Illuminate\Http\Request;

class FixedCostController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->user()->can('expense.access') && ! auth()->user()->can('all_expense.access')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $costs = FixedCost::where('business_id', $business_id)->orderBy('day_of_month')->get();
        return view('fixed_costs.index', compact('costs'));
    }

    public function create()
    {
        if (! auth()->user()->can('expense.add')) {
            abort(403, 'Unauthorized action.');
        }
        return view('fixed_costs.create');
    }

    public function store(Request $request)
    {
        if (! auth()->user()->can('expense.add')) {
            abort(403, 'Unauthorized action.');
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'day_of_month' => 'required|integer|min:1|max:31',
            'active' => 'nullable|boolean',
        ]);
        $data['business_id'] = $request->session()->get('user.business_id');
        $data['active'] = $request->boolean('active', true);
        // Set next_run_date to upcoming selected day
        $today = now();
        $dom = (int) $data['day_of_month'];
        $runDate = (clone $today)->day(min($dom, (int)$today->endOfMonth()->day));
        if ($runDate->lt($today->startOfDay())) {
            $runDate = $today->addMonthNoOverflow()->day(min($dom, (int)$today->endOfMonth()->day));
        }
        $data['next_run_date'] = $runDate->toDateString();
        FixedCost::create($data);
        return redirect()->action([self::class, 'index'])->with('status', ['success' => 1, 'msg' => __('messages.success')]);
    }

    public function edit($id)
    {
        if (! auth()->user()->can('expense.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $cost = FixedCost::where('business_id', $business_id)->findOrFail($id);
        return view('fixed_costs.edit', compact('cost'));
    }

    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('expense.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = $request->session()->get('user.business_id');
        $cost = FixedCost::where('business_id', $business_id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'day_of_month' => 'required|integer|min:1|max:31',
            'active' => 'nullable|boolean',
        ]);
        $data['active'] = $request->boolean('active', true);
        // Recompute next_run_date based on new day
        $today = now();
        $dom = (int) $data['day_of_month'];
        $runDate = (clone $today)->day(min($dom, (int)$today->endOfMonth()->day));
        if ($runDate->lt($today->startOfDay())) {
            $runDate = $today->addMonthNoOverflow()->day(min($dom, (int)$today->endOfMonth()->day));
        }
        $data['next_run_date'] = $runDate->toDateString();
        $cost->update($data);
        return redirect()->action([self::class, 'index'])->with('status', ['success' => 1, 'msg' => __('messages.updated_success')]);
    }

    public function destroy($id)
    {
        if (! auth()->user()->can('expense.delete')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        FixedCost::where('business_id', $business_id)->where('id', $id)->delete();
        return [ 'success' => true ];
    }
}
