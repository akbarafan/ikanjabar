<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm = $request->get('search');

        $branches = Branch::withCount(['ponds', 'users'])
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('location', 'like', '%' . $searchTerm . '%')
                    ->orWhere('pic_name', 'like', '%' . $searchTerm . '%');
            })
            ->paginate(10);

        // Add statistics for each branch
        $branches->getCollection()->transform(function ($branch) {
            $branch->statistics = [
                'total_active_batches' => $branch->ponds()
                    ->with(['fishBatches' => function ($query) {
                        $query->where('status', 'active');
                    }])
                    ->get()
                    ->pluck('fishBatches')
                    ->flatten()
                    ->count()
            ];
            return $branch;
        });

        if ($request->ajax()) {
            return $this->handleAjaxRequest($branches, $searchTerm);
        }

        return view('admin.branches.index', compact('branches', 'searchTerm'));
    }

    public function search(Request $request)
    {
        $searchTerm = $request->get('search');

        $branches = Branch::withCount(['ponds', 'users'])
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('location', 'like', '%' . $searchTerm . '%')
                    ->orWhere('pic_name', 'like', '%' . $searchTerm . '%');
            })
            ->paginate(10);

        // Add statistics for each branch
        $branches->getCollection()->transform(function ($branch) {
            $branch->statistics = [
                'total_active_batches' => $branch->ponds()
                    ->with(['fishBatches' => function ($query) {
                        $query->where('status', 'active');
                    }])
                    ->get()
                    ->pluck('fishBatches')
                    ->flatten()
                    ->count()
            ];
            return $branch;
        });

        return $this->handleAjaxRequest($branches, $searchTerm);
    }

    private function handleAjaxRequest($branches, $searchTerm)
    {
        $html = view('admin.branches.partials.table', compact('branches', 'searchTerm'))->render();
        $searchInfo = view('admin.branches.partials.search-info', [
            'searchTerm' => $searchTerm,
            'total' => $branches->total(),
            'hasResults' => $branches->count() > 0
        ])->render();

        $pagination = '';
        if ($branches->hasPages()) {
            $pagination = $branches->appends(['search' => $searchTerm])->links()->render();
        }

        return response()->json([
            'success' => true,
            'html' => $html,
            'search_info' => $searchInfo,
            'pagination' => $pagination
        ]);
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'required|string',
            'contact_person' => 'required|string|max:100',
            'pic_name' => 'required|string|max:100',
        ]);

        Branch::create($request->all());

        return redirect()->route('admin.branches.index')
            ->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function show(Branch $branch)
    {
        // Redirect to the detailed view
        return redirect()->route('admin.branches.detail', $branch);
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'required|string',
            'contact_person' => 'required|string|max:100',
            'pic_name' => 'required|string|max:100',
        ]);

        $branch->update($request->all());

        return redirect()->route('admin.branches.index')
            ->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        try {
            $branch->delete();
            return redirect()->route('admin.branches.index')
                ->with('success', 'Cabang berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Gagal menghapus cabang. Pastikan tidak ada data terkait.');
        }
    }

    public function apiIndex(Request $request)
    {
        $branches = Branch::withCount(['ponds', 'users'])
            ->when($request->get('search'), function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%');
            })
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $branches
        ]);
    }
}
