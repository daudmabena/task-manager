<?php

namespace App\Http\Controllers;

use App\Models\TasksTracking;
use App\Models\FunctionsRequirement;
use App\Http\Requests\TasksTracking\StoreTasksTrackingRequest;
use App\Http\Requests\TasksTracking\UpdateTasksTrackingRequest;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Exception;

class TasksTrackingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view tasks tracking')->only(['index', 'show']);
        $this->middleware('permission:create tasks tracking')->only(['create', 'store']);
        $this->middleware('permission:edit tasks tracking')->only(['edit', 'update']);
        $this->middleware('permission:delete tasks tracking')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $tasksTracking = QueryBuilder::for(TasksTracking::class)
            ->with(['functionsRequirement.process.system', 'correspondences', 'creator', 'updater'])
            ->allowedFilters([
                'correspondence',
                'status',
                AllowedFilter::exact('function_id'),
                AllowedFilter::scope('for_function'),
                AllowedFilter::scope('by_status'),
                AllowedFilter::scope('pending'),
                AllowedFilter::scope('in_progress'),
                AllowedFilter::scope('completed'),
                AllowedFilter::scope('cancelled'),
                AllowedFilter::scope('created_by'),
                AllowedFilter::scope('updated_by'),
                AllowedFilter::scope('upcoming'),
                AllowedFilter::scope('started'),
                AllowedFilter::scope('finished'),
                AllowedFilter::scope('not_finished'),
                AllowedFilter::scope('started_between'),
                AllowedFilter::scope('ending_between'),
                AllowedFilter::scope('with_correspondences'),
                AllowedFilter::scope('without_correspondences'),
            ])
            ->allowedSorts([
                'correspondence',
                'status',
                'actual_start_date',
                'actual_end_date',
                'function_id',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-created_at')
            ->paginate(10)
            ->withQueryString();

        $functionsRequirements = FunctionsRequirement::with('process.system')->get();

        return Inertia::render('TasksTracking/Index', [
            'tasksTracking' => $tasksTracking,
            'functionsRequirements' => $functionsRequirements,
            'filters' => $request->only(['filter', 'sort', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $functionsRequirements = FunctionsRequirement::with('process.system')->get();

        return Inertia::render('TasksTracking/Create', [
            'functionsRequirements' => $functionsRequirements,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTasksTrackingRequest $request)
    {
        try {
            DB::beginTransaction();

            $tasksTracking = TasksTracking::create([
                ...$request->validated(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            activity()
                ->performedOn($tasksTracking)
                ->causedBy(auth()->user())
                ->log('Tasks tracking created');

            DB::commit();

            return Redirect::route('tasks-tracking.index')
                ->with('success', 'Tasks tracking created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with('error', 'Failed to create tasks tracking: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TasksTracking $tasksTracking): Response
    {
        $tasksTracking->load([
            'functionsRequirement.process.system',
            'correspondences',
            'creator',
            'updater',
            'audits.causer'
        ]);

        return Inertia::render('TasksTracking/Show', [
            'tasksTracking' => $tasksTracking,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TasksTracking $tasksTracking): Response
    {
        $functionsRequirements = FunctionsRequirement::with('process.system')->get();

        return Inertia::render('TasksTracking/Edit', [
            'tasksTracking' => $tasksTracking,
            'functionsRequirements' => $functionsRequirements,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTasksTrackingRequest $request, TasksTracking $tasksTracking)
    {
        try {
            DB::beginTransaction();

            $tasksTracking->update([
                ...$request->validated(),
                'updated_by' => auth()->id(),
            ]);

            activity()
                ->performedOn($tasksTracking)
                ->causedBy(auth()->user())
                ->log('Tasks tracking updated');

            DB::commit();

            return Redirect::route('tasks-tracking.index')
                ->with('success', 'Tasks tracking updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with('error', 'Failed to update tasks tracking: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TasksTracking $tasksTracking)
    {
        try {
            DB::beginTransaction();

            activity()
                ->performedOn($tasksTracking)
                ->causedBy(auth()->user())
                ->log('Tasks tracking deleted');

            $tasksTracking->update(['deleted_by' => auth()->id()]);
            $tasksTracking->delete();

            DB::commit();

            return Redirect::route('tasks-tracking.index')
                ->with('success', 'Tasks tracking deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->with('error', 'Failed to delete tasks tracking: ' . $e->getMessage());
        }
    }

    /**
     * Display summary statistics for tasks tracking.
     */
    public function summary(): Response
    {
        $summary = [
            'total_tasks_tracking' => TasksTracking::count(),
            'pending_tasks_tracking' => TasksTracking::pending()->count(),
            'in_progress_tasks_tracking' => TasksTracking::inProgress()->count(),
            'completed_tasks_tracking' => TasksTracking::completed()->count(),
            'cancelled_tasks_tracking' => TasksTracking::cancelled()->count(),
            'upcoming_tasks_tracking' => TasksTracking::upcoming()->count(),
            'started_tasks_tracking' => TasksTracking::started()->count(),
            'finished_tasks_tracking' => TasksTracking::finished()->count(),
            'tasks_tracking_by_function' => TasksTracking::select('function_id', DB::raw('count(*) as count'))
                ->with('functionsRequirement:id,name')
                ->groupBy('function_id')
                ->get(),
            'tasks_tracking_by_status' => TasksTracking::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'total_correspondences' => DB::table('correspondences')->count(),
            'tasks_tracking_with_correspondences' => TasksTracking::withCorrespondences()->count(),
            'tasks_tracking_without_correspondences' => TasksTracking::withoutCorrespondences()->count(),
        ];

        return Inertia::render('TasksTracking/Summary', [
            'summary' => $summary,
        ]);
    }
}
