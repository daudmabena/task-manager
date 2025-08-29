<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\System;
use App\Http\Requests\Process\StoreProcessRequest;
use App\Http\Requests\Process\UpdateProcessRequest;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Exception;

class ProcessController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view processes')->only(['index', 'show']);
        $this->middleware('permission:create processes')->only(['create', 'store']);
        $this->middleware('permission:edit processes')->only(['edit', 'update']);
        $this->middleware('permission:delete processes')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $processes = QueryBuilder::for(Process::class)
            ->with(['system', 'functionsRequirements', 'creator', 'updater'])
            ->allowedFilters([
                'name',
                'description',
                AllowedFilter::exact('system_id'),
                AllowedFilter::scope('for_system'),
                AllowedFilter::scope('by_name'),
                AllowedFilter::scope('created_by'),
                AllowedFilter::scope('updated_by'),
                AllowedFilter::scope('created_between'),
                AllowedFilter::scope('updated_between'),
                AllowedFilter::scope('with_functions_requirements'),
                AllowedFilter::scope('without_functions_requirements'),
            ])
            ->allowedSorts([
                'name',
                'system_id',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-created_at')
            ->paginate(10)
            ->withQueryString();

        $systems = System::select('id', 'name')->get();

        return Inertia::render('Processes/Index', [
            'processes' => $processes,
            'systems' => $systems,
            'filters' => $request->only(['filter', 'sort', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $systems = System::select('id', 'name')->get();

        return Inertia::render('Processes/Create', [
            'systems' => $systems,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProcessRequest $request)
    {
        try {
            DB::beginTransaction();

            $process = Process::create([
                ...$request->validated(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            activity()
                ->performedOn($process)
                ->causedBy(auth()->user())
                ->log('Process created');

            DB::commit();

            return Redirect::route('processes.index')
                ->with('success', 'Process created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with('error', 'Failed to create process: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Process $process): Response
    {
        $process->load([
            'system',
            'functionsRequirements.tasksTracking',
            'creator',
            'updater',
            'audits.causer'
        ]);

        return Inertia::render('Processes/Show', [
            'process' => $process,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Process $process): Response
    {
        $systems = System::select('id', 'name')->get();

        return Inertia::render('Processes/Edit', [
            'process' => $process,
            'systems' => $systems,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProcessRequest $request, Process $process)
    {
        try {
            DB::beginTransaction();

            $process->update([
                ...$request->validated(),
                'updated_by' => auth()->id(),
            ]);

            activity()
                ->performedOn($process)
                ->causedBy(auth()->user())
                ->log('Process updated');

            DB::commit();

            return Redirect::route('processes.index')
                ->with('success', 'Process updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with('error', 'Failed to update process: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Process $process)
    {
        try {
            DB::beginTransaction();

            activity()
                ->performedOn($process)
                ->causedBy(auth()->user())
                ->log('Process deleted');

            $process->update(['deleted_by' => auth()->id()]);
            $process->delete();

            DB::commit();

            return Redirect::route('processes.index')
                ->with('success', 'Process deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->with('error', 'Failed to delete process: ' . $e->getMessage());
        }
    }

    /**
     * Display summary statistics for processes.
     */
    public function summary(): Response
    {
        $summary = [
            'total_processes' => Process::count(),
            'processes_by_system' => Process::select('system_id', DB::raw('count(*) as count'))
                ->with('system:id,name')
                ->groupBy('system_id')
                ->get(),
            'recent_processes' => Process::where('created_at', '>=', now()->subDays(30))->count(),
            'processes_with_functions' => Process::withFunctionsRequirements()->count(),
            'processes_without_functions' => Process::withoutFunctionsRequirements()->count(),
            'total_functions_requirements' => DB::table('functions_requirements')->count(),
            'total_tasks_tracking' => DB::table('tasks_tracking')->count(),
        ];

        return Inertia::render('Processes/Summary', [
            'summary' => $summary,
        ]);
    }
}
