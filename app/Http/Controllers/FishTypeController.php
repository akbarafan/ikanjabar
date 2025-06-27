<?php

namespace App\Http\Controllers;

use App\Models\FishType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FishTypeController extends Controller
{
    /**
     * Display a listing of fish types.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = FishType::query();

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            // Include statistics if requested
            if ($request->boolean('include_stats')) {
                $query->withCount('fishBatches');
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $fishTypes = $query->orderBy('name')->paginate($perPage);

            // Add computed attributes if stats are included
            if ($request->boolean('include_stats')) {
                $fishTypes->getCollection()->transform(function ($fishType) {
                    return [
                        'id' => $fishType->id,
                        'name' => $fishType->name,
                        'description' => $fishType->description,
                        'created_at' => $fishType->created_at,
                        'updated_at' => $fishType->updated_at,
                        'total_batches' => $fishType->total_batches,
                        'average_growth_rate' => $fishType->average_growth_rate,
                        'mortality_rate' => $fishType->mortality_rate,
                    ];
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Fish types retrieved successfully',
                'data' => $fishTypes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve fish types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created fish type.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:fish_types,name',
                'description' => 'nullable|string'
            ]);

            $fishType = FishType::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Fish type created successfully',
                'data' => $fishType
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create fish type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified fish type.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $fishType = FishType::findOrFail($id);

            // Include detailed statistics if requested
            if ($request->boolean('include_details')) {
                $fishType->load(['fishBatches.pond.branch', 'fishBatches.fishGrowthLogs', 'fishBatches.mortalities']);

                $data = [
                    'id' => $fishType->id,
                    'name' => $fishType->name,
                    'description' => $fishType->description,
                    'created_at' => $fishType->created_at,
                    'updated_at' => $fishType->updated_at,
                    'statistics' => [
                        'total_batches' => $fishType->total_batches,
                        'average_growth_rate' => $fishType->average_growth_rate,
                        'mortality_rate' => $fishType->mortality_rate,
                    ],
                    'batches' => $fishType->fishBatches->map(function ($batch) {
                        return [
                            'id' => $batch->id,
                            'pond_name' => $batch->pond->name,
                            'branch_name' => $batch->pond->branch->name,
                            'date_start' => $batch->date_start,
                            'initial_count' => $batch->initial_count,
                        ];
                    })
                ];
            } else {
                $data = $fishType;
            }

            return response()->json([
                'success' => true,
                'message' => 'Fish type retrieved successfully',
                'data' => $data
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fish type not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve fish type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified fish type.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $fishType = FishType::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:fish_types,name,' . $id,
                'description' => 'nullable|string'
            ]);

            $fishType->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Fish type updated successfully',
                'data' => $fishType->fresh()
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fish type not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update fish type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified fish type.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $fishType = FishType::findOrFail($id);

            // Check if fish type has associated batches
            if ($fishType->fishBatches()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete fish type that has associated fish batches'
                ], 422);
            }

            $fishType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fish type deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fish type not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete fish type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get fish type statistics.
     */
    public function statistics($id): JsonResponse
    {
        try {
            $fishType = FishType::with(['fishBatches.fishGrowthLogs', 'fishBatches.mortalities', 'fishBatches.sales'])
                                ->findOrFail($id);

            $statistics = [
                'basic_info' => [
                    'id' => $fishType->id,
                    'name' => $fishType->name,
                    'description' => $fishType->description,
                ],
                'batch_statistics' => [
                    'total_batches' => $fishType->total_batches,
                    'total_initial_fish' => $fishType->fishBatches->sum('initial_count'),
                ],
                'growth_statistics' => $fishType->average_growth_rate,
                'mortality_statistics' => [
                    'mortality_rate_percentage' => $fishType->mortality_rate,
                    'total_deaths' => $fishType->fishBatches->sum(function ($batch) {
                        return $batch->mortalities->sum('dead_count');
                    }),
                ],
                'sales_statistics' => [
                    'total_sales_count' => $fishType->fishBatches->sum(function ($batch) {
                        return $batch->sales->count();
                    }),
                    'total_fish_sold' => $fishType->fishBatches->sum(function ($batch) {
                        return $batch->sales->sum('quantity_fish');
                    }),
                    'total_revenue' => $fishType->fishBatches->sum(function ($batch) {
                        return $batch->sales->sum('total_price');
                    }),
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Fish type statistics retrieved successfully',
                'data' => $statistics
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fish type not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve fish type statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all fish types for dropdown/select options.
     */
    public function options(): JsonResponse
    {
        try {
            $fishTypes = FishType::select('id', 'name')
                                ->orderBy('name')
                                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Fish type options retrieved successfully',
                'data' => $fishTypes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve fish type options',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get fish types with their active batches.
     */
    public function withActiveBatches(): JsonResponse
    {
        try {
            $fishTypes = FishType::with(['fishBatches' => function ($query) {
                $query->whereDoesntHave('sales', function ($salesQuery) {
                    $salesQuery->whereRaw('quantity_fish >= (SELECT initial_count FROM fish_batches WHERE fish_batches.id = sales.fish_batch_id)');
                });
            }])->get();

            $data = $fishTypes->map(function ($fishType) {
                return [
                    'id' => $fishType->id,
                    'name' => $fishType->name,
                    'description' => $fishType->description,
                    'active_batches_count' => $fishType->fishBatches->count(),
                    'active_batches' => $fishType->fishBatches->map(function ($batch) {
                        return [
                            'id' => $batch->id,
                            'pond_id' => $batch->pond_id,
                            'date_start' => $batch->date_start,
                            'initial_count' => $batch->initial_count,
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Fish types with active batches retrieved successfully',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve fish types with active batches',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
