<?php

namespace  App\Http\Controllers;

use  App\Http\Controllers\Controller;
use App\Models\FishBatchTransfer;
use App\Models\FishBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FishBatchTransferController extends Controller
{
    public function index()
    {
        $transfers = FishBatchTransfer::with([
            'sourceBatch.pond.branch',
            'sourceBatch.fishType',
            'targetBatch.pond.branch',
            'targetBatch.fishType',
            'creator'
        ])
            ->when(request('search'), function($query) {
                $query->whereHas('sourceBatch.pond', function($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                })->orWhereHas('targetBatch.pond', function($q) {
                    $q->where('name', 'like', '%' . request('search') . '%');
                })->orWhere('notes', 'like', '%' . request('search') . '%');
            })
            ->when(request('source_batch_id'), function($query) {
                $query->where('source_batch_id', request('source_batch_id'));
            })
            ->when(request('target_batch_id'), function($query) {
                $query->where('target_batch_id', request('target_batch_id'));
            })
            ->when(request('date_from'), function($query) {
                $query->whereDate('date_transfer', '>=', request('date_from'));
            })
            ->when(request('date_to'), function($query) {
                $query->whereDate('date_transfer', '<=', request('date_to'));
            })
            ->latest('date_transfer')
            ->paginate(15);

        // Tambahkan perhitungan transfer data
        foreach ($transfers as $transfer) {
            $transfer->transfer_data = [
                'transferred_count' => $transfer->transferred_count,
                'transfer_percentage' => $transfer->transfer_percentage,
                'transfer_status' => $transfer->transfer_status,
                'source_stock_before' => $transfer->sourceBatch->current_stock + $transfer->transferred_count,
                'source_stock_after' => $transfer->sourceBatch->current_stock,
                'target_stock_before' => $transfer->targetBatch->current_stock - $transfer->transferred_count,
                'target_stock_after' => $transfer->targetBatch->current_stock,
            ];
        }

        $sourceBatches = FishBatch::with(['pond.branch', 'fishType'])
            ->where('current_stock', '>', 0)
            ->get();
        $targetBatches = FishBatch::with(['pond.branch', 'fishType'])->get();

        // Statistik ringkasan
        $statistics = [
            'total_transfers_today' => FishBatchTransfer::whereDate('date_transfer', today())->count(),
            'total_transfers_this_week' => FishBatchTransfer::whereBetween('date_transfer', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'total_transfers_this_month' => FishBatchTransfer::whereMonth('date_transfer', now()->month)->count(),
            'total_fish_transferred_today' => FishBatchTransfer::whereDate('date_transfer', today())->sum('transferred_count'),
        ];

        return view('fish-batch-transfers.index', compact('transfers', 'sourceBatches', 'targetBatches', 'statistics'));
    }

    public function create()
    {
        $sourceBatches = FishBatch::with(['pond.branch', 'fishType'])
            ->where('current_stock', '>', 0)
            ->get();

        $targetBatches = FishBatch::with(['pond.branch', 'fishType'])
            ->get();

        return view('fish-batch-transfers.create', compact('sourceBatches', 'targetBatches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_batch_id' => 'required|exists:fish_batches,id',
            'target_batch_id' => 'required|exists:fish_batches,id|different:source_batch_id',
            'transferred_count' => 'required|integer|min:1',
            'date_transfer' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
        ]);

        // Validasi source dan target batch tidak sama
        if ($validated['source_batch_id'] == $validated['target_batch_id']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Batch sumber dan tujuan tidak boleh sama');
        }

        // Validasi jumlah transfer tidak melebihi stok sumber
        $sourceBatch = FishBatch::find($validated['source_batch_id']);
        $targetBatch = FishBatch::find($validated['target_batch_id']);

        if ($validated['transferred_count'] > $sourceBatch->current_stock) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Jumlah transfer ({$validated['transferred_count']}) melebihi stok sumber saat ini ({$sourceBatch->current_stock})");
        }

        // Validasi jenis ikan sama
        if ($sourceBatch->fish_type_id !== $targetBatch->fish_type_id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Transfer hanya dapat dilakukan antar batch dengan jenis ikan yang sama');
        }

        $validated['created_by'] = Auth::id();

        DB::beginTransaction();
        try {
            $transfer = FishBatchTransfer::create($validated);

            DB::commit();

            // Alert berdasarkan persentase transfer
            $transferPercentage = $transfer->transfer_percentage;
            $alertMessage = 'Transfer batch berhasil dilakukan';
            $alertType = 'success';

            if ($transferPercentage > 50) {
                $alertMessage .= '. PERINGATAN: Transfer dalam jumlah besar (' . $transferPercentage . '%)!';
                $alertType = 'warning';
            }

            return redirect()->route('fish-batch-transfers.index')
                ->with($alertType, $alertMessage);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal melakukan transfer: ' . $e->getMessage());
        }
    }

    public function show(FishBatchTransfer $fishBatchTransfer)
    {
        $fishBatchTransfer->load([
            'sourceBatch.pond.branch',
            'sourceBatch.fishType',
            'targetBatch.pond.branch',
            'targetBatch.fishType',
            'creator'
        ]);

        // Analisis transfer
        $analysis = [
            'transfer_data' => [
                'transferred_count' => $fishBatchTransfer->transferred_count,
                'transfer_percentage' => $fishBatchTransfer->transfer_percentage,
                'transfer_status' => $fishBatchTransfer->transfer_status,
                'date_transfer' => $fishBatchTransfer->date_transfer,
                'created_by' => $fishBatchTransfer->creator->full_name,
            ],
            'source_batch_impact' => [
                'batch_id' => $fishBatchTransfer->sourceBatch->id,
                'pond_name' => $fishBatchTransfer->sourceBatch->pond->name,
                'branch_name' => $fishBatchTransfer->sourceBatch->pond->branch->name,
                'initial_count' => $fishBatchTransfer->sourceBatch->initial_count,
                'current_stock' => $fishBatchTransfer->sourceBatch->current_stock,
                'stock_before_transfer' => $fishBatchTransfer->sourceBatch->current_stock + $fishBatchTransfer->transferred_count,
                'reduction_percentage' => $fishBatchTransfer->transfer_percentage,
            ],
            'target_batch_impact' => [
                'batch_id' => $fishBatchTransfer->targetBatch->id,
                'pond_name' => $fishBatchTransfer->targetBatch->pond->name,
                'branch_name' => $fishBatchTransfer->targetBatch->pond->branch->name,
                'initial_count' => $fishBatchTransfer->targetBatch->initial_count,
                'current_stock' => $fishBatchTransfer->targetBatch->current_stock,
                'stock_before_transfer' => $fishBatchTransfer->targetBatch->current_stock - $fishBatchTransfer->transferred_count,
                'increase_count' => $fishBatchTransfer->transferred_count,
            ]
        ];

        // Riwayat transfer batch sumber
        $sourceTransferHistory = FishBatchTransfer::where('source_batch_id', $fishBatchTransfer->source_batch_id)
            ->with(['targetBatch.pond'])
            ->orderBy('date_transfer', 'desc')
            ->limit(5)
            ->get();

        // Riwayat transfer batch target
        $targetTransferHistory = FishBatchTransfer::where('target_batch_id', $fishBatchTransfer->target_batch_id)
            ->with(['sourceBatch.pond'])
            ->orderBy('date_transfer', 'desc')
            ->limit(5)
            ->get();

        return view('fish-batch-transfers.show', compact(
            'fishBatchTransfer',
            'analysis',
            'sourceTransferHistory',
            'targetTransferHistory'
        ));
    }

    public function edit(FishBatchTransfer $fishBatchTransfer)
    {
        $sourceBatches = FishBatch::with(['pond.branch', 'fishType'])->get();
        $targetBatches = FishBatch::with(['pond.branch', 'fishType'])->get();

        return view('fish-batch-transfers.edit', compact('fishBatchTransfer', 'sourceBatches', 'targetBatches'));
    }

    public function update(Request $request, FishBatchTransfer $fishBatchTransfer)
    {
        $validated = $request->validate([
            'source_batch_id' => 'required|exists:fish_batches,id',
            'target_batch_id' => 'required|exists:fish_batches,id|different:source_batch_id',
            'transferred_count' => 'required|integer|min:1',
            'date_transfer' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
        ]);

        // Validasi source dan target batch tidak sama
        if ($validated['source_batch_id'] == $validated['target_batch_id']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Batch sumber dan tujuan tidak boleh sama');
        }

        DB::beginTransaction();
        try {
            // Update dengan data baru
            $newSourceBatch = FishBatch::find($validated['source_batch_id']);
            $newTargetBatch = FishBatch::find($validated['target_batch_id']);

            // Validasi jenis ikan sama
            if ($newSourceBatch->fish_type_id !== $newTargetBatch->fish_type_id) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Transfer hanya dapat dilakukan antar batch dengan jenis ikan yang sama');
            }

            $fishBatchTransfer->update($validated);

            DB::commit();

            return redirect()->route('fish-batch-transfers.show', $fishBatchTransfer)
                ->with('success', 'Transfer batch berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui transfer: ' . $e->getMessage());
        }
    }

    public function destroy(FishBatchTransfer $fishBatchTransfer)
    {
        DB::beginTransaction();
        try {
            $fishBatchTransfer->delete();
            DB::commit();

            return redirect()->route('fish-batch-transfers.index')
                ->with('success', 'Transfer batch berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Gagal menghapus transfer: ' . $e->getMessage());
        }
    }
}
