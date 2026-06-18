<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Book;
use App\Models\ReadingPlan;

class ReadingPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $currentStatus = $request->status;

        $readingPlans = ReadingPlan::with('book')
            ->where('user_id', auth()->id()) 
            ->filter(['currentStatus' => $currentStatus])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('reading-plans.index', compact('currentStatus', 'readingPlans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create():View
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(): View
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました');
    }
}
