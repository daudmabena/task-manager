<?php

namespace App\Http\Controllers;

use App\Http\Requests\System\StoreSystemRequest;
use App\Http\Requests\System\UpdateSystemRequest;
use App\Models\System;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SystemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = System::with(['creator', 'updater'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->sort_by, function ($query, $sortBy) use ($request) {
                $direction = $request->sort_direction === 'desc' ? 'desc' : 'asc';
                $query->orderBy($sortBy, $direction);
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            });

        $systems = $query->paginate(10)
            ->withQueryString();

        return Inertia::render('Systems/Index', [
            'systems' => $systems,
            'filters' => $request->only(['search', 'sort_by', 'sort_direction']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Systems/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSystemRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            System::create([
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()->route('systems.index')
            ->with('success', 'System created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(System $system): Response
    {
        $system->load(['creator', 'updater', 'deleter']);

        return Inertia::render('Systems/Show', [
            'system' => $system,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(System $system): Response
    {
        return Inertia::render('Systems/Edit', [
            'system' => $system,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSystemRequest $request, System $system): RedirectResponse
    {
        DB::transaction(function () use ($request, $system) {
            $system->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()->route('systems.index')
            ->with('success', 'System updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(System $system): RedirectResponse
    {
        DB::transaction(function () use ($system) {
            $system->update(['deleted_by' => Auth::id()]);
            $system->delete();
        });

        return redirect()->route('systems.index')
            ->with('success', 'System deleted successfully.');
    }
}
