<?php

namespace App\Http\Controllers;

use App\Models\FunctionsRequirement;
use App\Models\Process;
use App\Http\Requests\FunctionsRequirement\StoreFunctionsRequirementRequest;
use App\Http\Requests\FunctionsRequirement\UpdateFunctionsRequirementRequest;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Exception;

class FunctionsRequirementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view functions requirements')->only(['index', 'show']);
        $this->middleware('permission:create functions requirements')->only(['create', 'store']);
        $this->middleware('permission:edit functions requirements')->only(['edit', 'update']);
        $this->middleware('permission:delete functions requirements')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $functionsRequirements = QueryBuilder::for(FunctionsRequirement::class)
            ->with(['process.system', 'tasksTracking', 'creator', 'updater'])
            ->allowedFilters([
                'name',
                'requirement',
                AllowedFilter::exact('process_id'),
                AllowedFilter::scope('for_process'),
                AllowedFilter::scope('by_name'),
                AllowedFilter::scope('created_by'),
                AllowedFilter::scope('updated_by'),
                AllowedFilter::scope('upcoming'),
                AllowedFilter::scope('overdue'),
                AllowedFilter::scope('not_completed'),
                AllowedFilter::scope('completed'),
                AllowedFilter::scope('planned_between'),
                AllowedFilter::scope('ending_between'),
                AllowedFilter::scope('with_tasks_tracking'),
                AllowedFilter::scope('without_tasks_tracking'),
            ])
            ->allowedSorts([
                'name',
                'process_id',
                'planned_start_date',
                'planned_end_date',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-created_at')
            ->paginate(10)
            ->withQueryString();

        $processes = Process::with('system')->get();

        return Inertia::render('FunctionsRequirements/Index', [
            'functionsRequirements' => $functionsRequirements,
            'processes' => $processes,
            'filters' => $request->only(['filter', 'sort', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $processes = Process::with('system')->get();

        return Inertia::render('FunctionsRequirements/Create', [
            'processes' => $processes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFunctionsRequirementRequest $request)
    {
        try {
            DB::beginTransaction();

            $functionsRequirement = FunctionsRequirement::create([
                ...$request->validated(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            activity()
                ->performedOn($functionsRequirement)
                ->causedBy(auth()->user())
                ->log('Functions requirement created');

            DB::commit();

            return Redirect::route('functions-requirements.index')
                ->with('success', 'Functions requirement created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with('error', 'Failed to create functions requirement: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FunctionsRequirement $functionsRequirement): Response
    {
        $functionsRequirement->load([
            'process.system',
            'tasksTracking',
            'creator',
            'updater',
            'audits.causer'
        ]);

        return Inertia::render('FunctionsRequirements/Show', [
            'functionsRequirement' => $functionsRequirement,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FunctionsRequirement $functionsRequirement): Response
    {
        $processes = Process::with('system')->get();

        return Inertia::render('FunctionsRequirements/Edit', [
            'functionsRequirement' => $functionsRequirement,
            'processes' => $processes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFunctionsRequirementRequest $request, FunctionsRequirement $functionsRequirement)
    {
        try {
            DB::beginTransaction();

            $functionsRequirement->update([
                ...$request->validated(),
                'updated_by' => auth()->id(),
            ]);

            activity()
                ->performedOn($functionsRequirement)
                ->causedBy(auth()->user())
                ->log('Functions requirement updated');

            DB::commit();

            return Redirect::route('functions-requirements.index')
                ->with('success', 'Functions requirement updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with('error', 'Failed to update functions requirement: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FunctionsRequirement $functionsRequirement)
    {
        try {
            DB::beginTransaction();

            activity()
                ->performedOn($functionsRequirement)
                ->causedBy(auth()->user())
                ->log('Functions requirement deleted');

            $functionsRequirement->update(['deleted_by' => auth()->id()]);
            $functionsRequirement->delete();

            DB::commit();

            return Redirect::route('functions-requirements.index')
                ->with('success', 'Functions requirement deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->with('error', 'Failed to delete functions requirement: ' . $e->getMessage());
        }
    }

    /**
     * Display summary statistics for functions requirements.
     */
    public function summary(): Response
    {
        $summary = [
            'total_functions_requirements' => FunctionsRequirement::count(),
            'upcoming_functions_requirements' => FunctionsRequirement::upcoming()->count(),
            'overdue_functions_requirements' => FunctionsRequirement::overdue()->count(),
            'completed_functions_requirements' => FunctionsRequirement::completed()->count(),
            'not_completed_functions_requirements' => FunctionsRequirement::notCompleted()->count(),
            'functions_requirements_by_process' => FunctionsRequirement::select('process_id', DB::raw('count(*) as count'))
                ->with('process:id,name')
                ->groupBy('process_id')
                ->get(),
            'total_tasks_tracking' => DB::table('tasks_tracking')->count(),
            'functions_requirements_with_tasks' => FunctionsRequirement::withTasksTracking()->count(),
            'functions_requirements_without_tasks' => FunctionsRequirement::withoutTasksTracking()->count(),
        ];

        return Inertia::render('FunctionsRequirements/Summary', [
            'summary' => $summary,
        ]);
    }
}
