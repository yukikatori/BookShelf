<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Enums\ReadingPlanStatus;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Http\Requests\StoreReadingPlanRequest;
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
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        ReadingPlan::create([
            'user_id' => auth()->id(),
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::Reading,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を作成しました');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReadingPlan $plan): View
    {
        $this->authorize('update', $plan);

        $readingPlan = $plan;

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $plan): RedirectResponse
    {
        $validated = $request->validated();

        $plan->update([
            'target_date' => $validated['target_date'],
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を編集しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました');
    }

    public function complete(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('complete', $plan);

        $plan->update([
            'completed_at' => now(),
            'status' => ReadingPlanStatus::Completed,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画の状態を「読了」に変更しました');
    }
}
