<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PersonResource;
use Illuminate\Http\Request;
use Webkul\Contact\Repositories\PersonRepository;

class PersonController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  PersonRepository  $personRepository
     * @return void
     */
    public function __construct(protected PersonRepository $personRepository)
    {
    }

    /**
     * Display a listing of persons.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $organizationId = $request->input('organization_id');

        $query = $this->personRepository
            ->with([
                'organization:id,name',
                'user:id,name,email'
            ])
            ->withCount(['leads', 'activities']);

        // Apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('job_title', 'like', "%{$search}%");
            });
        }

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        // Order by
        $query->orderBy('created_at', 'desc');

        $persons = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => PersonResource::collection($persons),
            'meta' => [
                'current_page' => $persons->currentPage(),
                'from' => $persons->firstItem(),
                'last_page' => $persons->lastPage(),
                'per_page' => $persons->perPage(),
                'to' => $persons->lastItem(),
                'total' => $persons->total(),
            ],
        ], 200);
    }

    /**
     * Display the specified person.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $person = $this->personRepository->with(['organization', 'user', 'tags', 'leads', 'activities'])->find($id);

        if (! $person) {
            return response()->json([
                'success' => false,
                'message' => 'Person not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PersonResource($person),
        ], 200);
    }

    /**
     * Store a newly created person.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'emails' => 'nullable|array',
            'emails.*' => 'email',
            'contact_numbers' => 'nullable|array',
            'job_title' => 'nullable|string|max:255',
            'organization_id' => 'nullable|exists:organizations,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $person = $this->personRepository->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Person created successfully',
            'data' => new PersonResource($person->load(['organization', 'user'])),
        ], 201);
    }

    /**
     * Update the specified person.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $person = $this->personRepository->find($id);

        if (! $person) {
            return response()->json([
                'success' => false,
                'message' => 'Person not found',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'emails' => 'nullable|array',
            'emails.*' => 'email',
            'contact_numbers' => 'nullable|array',
            'job_title' => 'nullable|string|max:255',
            'organization_id' => 'nullable|exists:organizations,id',
            'user_id' => 'sometimes|required|exists:users,id',
        ]);

        $person = $this->personRepository->update($validated, $id);

        return response()->json([
            'success' => true,
            'message' => 'Person updated successfully',
            'data' => new PersonResource($person->load(['organization', 'user'])),
        ], 200);
    }

    /**
     * Remove the specified person.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $person = $this->personRepository->find($id);

        if (! $person) {
            return response()->json([
                'success' => false,
                'message' => 'Person not found',
            ], 404);
        }

        $this->personRepository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Person deleted successfully',
        ], 200);
    }
}

