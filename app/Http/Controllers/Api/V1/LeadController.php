<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LeadResource;
use Illuminate\Http\Request;
use Webkul\Lead\Repositories\LeadRepository;

class LeadController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  LeadRepository  $leadRepository
     * @return void
     */
    public function __construct(protected LeadRepository $leadRepository)
    {
    }

    /**
     * Display a listing of leads.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status');
        $pipelineId = $request->input('pipeline_id');
        $stageId = $request->input('stage_id');
        $userId = $request->input('user_id');

        $query = $this->leadRepository
            ->with([
                'user:id,name,email,image',
                'person:id,name,emails,contact_numbers,job_title,organization_id',
                'person.organization:id,name',
                'source:id,name',
                'type:id,name',
                'pipeline:id,name,rotten_days',
                'stage:id,name,code,sort_order'
            ])
            ->withCount(['products', 'activities', 'quotes']);

        // Apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($pipelineId) {
            $query->where('lead_pipeline_id', $pipelineId);
        }

        if ($stageId) {
            $query->where('lead_pipeline_stage_id', $stageId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Order by
        $query->orderBy('created_at', 'desc');

        $leads = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => LeadResource::collection($leads),
            'meta' => [
                'current_page' => $leads->currentPage(),
                'from' => $leads->firstItem(),
                'last_page' => $leads->lastPage(),
                'per_page' => $leads->perPage(),
                'to' => $leads->lastItem(),
                'total' => $leads->total(),
            ],
        ], 200);
    }

    /**
     * Display the specified lead.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $lead = $this->leadRepository->with([
            'user',
            'person.organization',
            'source',
            'type',
            'pipeline',
            'stage',
            'tags',
            'products',
            'activities',
            'quotes',
        ])->find($id);

        if (! $lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new LeadResource($lead),
        ], 200);
    }

    /**
     * Store a newly created lead.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lead_value' => 'nullable|numeric',
            'status' => 'nullable|in:new,contacted,qualified,negotiation,won,lost',
            'expected_close_date' => 'nullable|date',
            'person_id' => 'nullable|exists:persons,id',
            'user_id' => 'required|exists:users,id',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'lead_type_id' => 'nullable|exists:lead_types,id',
            'lead_pipeline_id' => 'required|exists:lead_pipelines,id',
            'lead_pipeline_stage_id' => 'required|exists:lead_pipeline_stages,id',
        ]);

        // Use direct model creation to avoid custom attribute issues for mobile API
        $lead = \Webkul\Lead\Models\Lead::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully',
            'data' => new LeadResource($lead->load(['user', 'person', 'source', 'type', 'pipeline', 'stage'])),
        ], 201);
    }

    /**
     * Update the specified lead.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $lead = $this->leadRepository->find($id);

        if (! $lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'lead_value' => 'nullable|numeric',
            'status' => 'nullable|in:new,contacted,qualified,negotiation,won,lost',
            'expected_close_date' => 'nullable|date',
            'person_id' => 'nullable|exists:persons,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'lead_type_id' => 'nullable|exists:lead_types,id',
            'lead_pipeline_id' => 'sometimes|required|exists:lead_pipelines,id',
            'lead_pipeline_stage_id' => 'sometimes|required|exists:lead_pipeline_stages,id',
        ]);

        // Use direct model update to avoid custom attribute issues for mobile API
        $lead->update($validated);
        $lead->refresh();
        $lead->load(['user', 'person', 'source', 'type', 'pipeline', 'stage']);

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully',
            'data' => new LeadResource($lead),
        ], 200);
    }

    /**
     * Remove the specified lead.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $lead = $this->leadRepository->find($id);

        if (! $lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        $this->leadRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Lead deleted successfully',
        ], 200);
    }

    /**
     * Update lead stage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStage(Request $request, $id)
    {
        $lead = $this->leadRepository->find($id);

        if (! $lead) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found',
            ], 404);
        }

        $validated = $request->validate([
            'lead_pipeline_stage_id' => 'required|exists:lead_pipeline_stages,id',
        ]);

        // Use direct model update to avoid custom attribute issues for mobile API
        $lead->update($validated);
        $lead->refresh();
        $lead->load(['stage']);

        return response()->json([
            'success' => true,
            'message' => 'Lead stage updated successfully',
            'data' => new LeadResource($lead),
        ], 200);
    }
}

