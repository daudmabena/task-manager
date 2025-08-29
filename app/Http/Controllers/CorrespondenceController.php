<?php

namespace App\Http\Controllers;

use App\Models\Correspondence;
use App\Models\TasksTracking;
use App\Http\Requests\Correspondence\StoreCorrespondenceRequest;
use App\Http\Requests\Correspondence\UpdateCorrespondenceRequest;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Exception;

class CorrespondenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view correspondences')->only(['index', 'show']);
        $this->middleware('permission:create correspondences')->only(['create', 'store']);
        $this->middleware('permission:edit correspondences')->only(['edit', 'update']);
        $this->middleware('permission:delete correspondences')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $correspondences = QueryBuilder::for(Correspondence::class)
            ->with(['tasksTracking.functionsRequirement.process.system', 'creator', 'updater'])
            ->allowedFilters([
                'type',
                'reference',
                AllowedFilter::exact('task_id'),
                AllowedFilter::scope('for_task'),
                AllowedFilter::scope('by_type'),
                AllowedFilter::scope('email'),
                AllowedFilter::scope('letter'),
                AllowedFilter::scope('phone'),
                AllowedFilter::scope('meeting'),
                AllowedFilter::scope('document'),
                AllowedFilter::scope('created_by'),
                AllowedFilter::scope('updated_by'),
                AllowedFilter::scope('by_reference'),
                AllowedFilter::scope('created_between'),
                AllowedFilter::scope('updated_between'),
                AllowedFilter::scope('recent'),
                AllowedFilter::scope('today'),
                AllowedFilter::scope('this_week'),
                AllowedFilter::scope('this_month'),
                AllowedFilter::scope('this_year'),
            ])
            ->allowedSorts([
                'type',
                'reference',
                'task_id',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-created_at')
            ->paginate(10)
            ->withQueryString();

        $tasksTracking = TasksTracking::with('functionsRequirement.process.system')->get();
        $typeOptions = Correspondence::getTypeOptions();

        return Inertia::render('Correspondences/Index', [
            'correspondences' => $correspondences,
            'tasksTracking' => $tasksTracking,
            'typeOptions' => $typeOptions,
            'filters' => $request->only(['filter', 'sort', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $tasksTracking = TasksTracking::with('functionsRequirement.process.system')->get();
        $typeOptions = Correspondence::getTypeOptions();

        return Inertia::render('Correspondences/Create', [
            'tasksTracking' => $tasksTracking,
            'typeOptions' => $typeOptions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCorrespondenceRequest $request)
    {
        try {
            DB::beginTransaction();

            $correspondence = Correspondence::create([
                ...$request->validated(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            activity()
                ->performedOn($correspondence)
                ->causedBy(auth()->user())
                ->log('Correspondence created');

            DB::commit();

            return Redirect::route('correspondences.index')
                ->with('success', 'Correspondence created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with('error', 'Failed to create correspondence: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Correspondence $correspondence): Response
    {
        $correspondence->load([
            'tasksTracking.functionsRequirement.process.system',
            'creator',
            'updater',
            'audits.causer'
        ]);

        return Inertia::render('Correspondences/Show', [
            'correspondence' => $correspondence,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Correspondence $correspondence): Response
    {
        $tasksTracking = TasksTracking::with('functionsRequirement.process.system')->get();
        $typeOptions = Correspondence::getTypeOptions();

        return Inertia::render('Correspondences/Edit', [
            'correspondence' => $correspondence,
            'tasksTracking' => $tasksTracking,
            'typeOptions' => $typeOptions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCorrespondenceRequest $request, Correspondence $correspondence)
    {
        try {
            DB::beginTransaction();

            $correspondence->update([
                ...$request->validated(),
                'updated_by' => auth()->id(),
            ]);

            activity()
                ->performedOn($correspondence)
                ->causedBy(auth()->user())
                ->log('Correspondence updated');

            DB::commit();

            return Redirect::route('correspondences.index')
                ->with('success', 'Correspondence updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->withInput()
                ->with('error', 'Failed to update correspondence: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Correspondence $correspondence)
    {
        try {
            DB::beginTransaction();

            activity()
                ->performedOn($correspondence)
                ->causedBy(auth()->user())
                ->log('Correspondence deleted');

            $correspondence->update(['deleted_by' => auth()->id()]);
            $correspondence->delete();

            DB::commit();

            return Redirect::route('correspondences.index')
                ->with('success', 'Correspondence deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->with('error', 'Failed to delete correspondence: ' . $e->getMessage());
        }
    }

    /**
     * Display summary statistics for correspondences.
     */
    public function summary(): Response
    {
        $summary = [
            'total_correspondences' => Correspondence::count(),
            'email_correspondences' => Correspondence::email()->count(),
            'letter_correspondences' => Correspondence::letter()->count(),
            'phone_correspondences' => Correspondence::phone()->count(),
            'meeting_correspondences' => Correspondence::meeting()->count(),
            'document_correspondences' => Correspondence::document()->count(),
            'recent_correspondences' => Correspondence::recent()->count(),
            'today_correspondences' => Correspondence::today()->count(),
            'this_week_correspondences' => Correspondence::thisWeek()->count(),
            'this_month_correspondences' => Correspondence::thisMonth()->count(),
            'this_year_correspondences' => Correspondence::thisYear()->count(),
            'correspondences_by_type' => Correspondence::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get(),
            'correspondences_by_task' => Correspondence::select('task_id', DB::raw('count(*) as count'))
                ->with('tasksTracking:id,correspondence')
                ->groupBy('task_id')
                ->get(),
            'correspondences_by_creator' => Correspondence::select('created_by', DB::raw('count(*) as count'))
                ->with('creator:id,name')
                ->groupBy('created_by')
                ->get(),
        ];

        return Inertia::render('Correspondences/Summary', [
            'summary' => $summary,
        ]);
    }
}
