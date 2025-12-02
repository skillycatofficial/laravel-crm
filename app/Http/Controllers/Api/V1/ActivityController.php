<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ActivityResource;
use Illuminate\Http\Request;
use Webkul\Activity\Repositories\ActivityRepository;

class ActivityController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  ActivityRepository  $activityRepository
     * @return void
     */
    public function __construct(protected ActivityRepository $activityRepository)
    {
    }

    /**
     * Display a listing of activities.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $type = $request->input('type');
        $isDone = $request->input('is_done');
        $userId = $request->input('user_id');
        $leadId = $request->input('lead_id');
        $personId = $request->input('person_id');
        $upcoming = $request->input('upcoming'); // Get upcoming activities

        $query = $this->activityRepository->with(['user', 'participants', 'leads', 'persons']);

        // Apply filters
        if ($type) {
            $query->where('type', $type);
        }

        if ($isDone !== null) {
            $query->where('is_done', $isDone);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($leadId) {
            $query->whereHas('leads', function($q) use ($leadId) {
                $q->where('leads.id', $leadId);
            });
        }

        if ($personId) {
            $query->whereHas('persons', function($q) use ($personId) {
                $q->where('persons.id', $personId);
            });
        }

        if ($upcoming) {
            $query->where('schedule_from', '>=', now())
                  ->where('is_done', 0);
        }

        // Order by schedule
        $query->orderBy('schedule_from', 'desc');

        $activities = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ActivityResource::collection($activities),
            'meta' => [
                'current_page' => $activities->currentPage(),
                'from' => $activities->firstItem(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'to' => $activities->lastItem(),
                'total' => $activities->total(),
            ],
        ], 200);
    }

    /**
     * Display the specified activity.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $activity = $this->activityRepository->with([
            'user',
            'participants.user',
            'participants.person',
            'files',
            'leads',
            'persons',
        ])->find($id);

        if (! $activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ActivityResource($activity),
        ], 200);
    }

    /**
     * Store a newly created activity.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:call,meeting,lunch,task',
            'location' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'schedule_from' => 'required|date',
            'schedule_to' => 'required|date|after:schedule_from',
            'user_id' => 'required|exists:users,id',
            'is_done' => 'nullable|boolean',
            'lead_ids' => 'nullable|array',
            'lead_ids.*' => 'exists:leads,id',
            'person_ids' => 'nullable|array',
            'person_ids.*' => 'exists:persons,id',
        ]);

        // Extract relationship data
        $leadIds = $validated['lead_ids'] ?? [];
        $personIds = $validated['person_ids'] ?? [];
        unset($validated['lead_ids'], $validated['person_ids']);

        $activity = $this->activityRepository->create($validated);

        // Attach relationships
        if (!empty($leadIds)) {
            $activity->leads()->sync($leadIds);
        }

        if (!empty($personIds)) {
            $activity->persons()->sync($personIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'Activity created successfully',
            'data' => new ActivityResource($activity->load(['user', 'leads', 'persons'])),
        ], 201);
    }

    /**
     * Update the specified activity.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $activity = $this->activityRepository->find($id);

        if (! $activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity not found',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:call,meeting,lunch,task',
            'location' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'schedule_from' => 'sometimes|required|date',
            'schedule_to' => 'sometimes|required|date|after:schedule_from',
            'user_id' => 'sometimes|required|exists:users,id',
            'is_done' => 'nullable|boolean',
            'lead_ids' => 'nullable|array',
            'lead_ids.*' => 'exists:leads,id',
            'person_ids' => 'nullable|array',
            'person_ids.*' => 'exists:persons,id',
        ]);

        // Extract relationship data
        $leadIds = $validated['lead_ids'] ?? null;
        $personIds = $validated['person_ids'] ?? null;
        unset($validated['lead_ids'], $validated['person_ids']);

        $activity = $this->activityRepository->update($validated, $id);

        // Update relationships if provided
        if ($leadIds !== null) {
            $activity->leads()->sync($leadIds);
        }

        if ($personIds !== null) {
            $activity->persons()->sync($personIds);
        }

        return response()->json([
            'success' => true,
            'message' => 'Activity updated successfully',
            'data' => new ActivityResource($activity->load(['user', 'leads', 'persons'])),
        ], 200);
    }

    /**
     * Remove the specified activity.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $activity = $this->activityRepository->find($id);

        if (! $activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity not found',
            ], 404);
        }

        $this->activityRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Activity deleted successfully',
        ], 200);
    }

    /**
     * Mark activity as done/undone.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleDone(Request $request, $id)
    {
        $activity = $this->activityRepository->find($id);

        if (! $activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity not found',
            ], 404);
        }

        $activity = $this->activityRepository->update([
            'is_done' => !$activity->is_done,
        ], $id);

        return response()->json([
            'success' => true,
            'message' => 'Activity status updated successfully',
            'data' => new ActivityResource($activity),
        ], 200);
    }
}

