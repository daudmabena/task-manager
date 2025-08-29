<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $tasks = QueryBuilder::for(Task::class)
            ->with(['user', 'assignedUser'])
            ->allowedFilters([
                'title',
                'status',
                'priority',
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('assigned_to'),
                AllowedFilter::scope('due_soon'),
            ])
            ->allowedSorts([
                'title',
                'status',
                'priority',
                'due_date',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-created_at')
            ->paginate(10);

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Task::class);
        
        return Inertia::render('Tasks/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Task::class);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task = Task::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->log('Task created');

        return redirect()->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): Response
    {
        $this->authorize('view', $task);
        
        $task->load(['user', 'assignedUser', 'audits']);
        
        return Inertia::render('Tasks/Show', [
            'task' => $task,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task): Response
    {
        $this->authorize('update', $task);
        
        return Inertia::render('Tasks/Edit', [
            'task' => $task,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task->update($validated);

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->log('Task updated');

        return redirect()->route('tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        
        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->log('Task deleted');
            
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}
