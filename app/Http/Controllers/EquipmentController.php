<?php

namespace App\Http\Controllers;

use App\Http\Resources\EquipmentResource;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\Equipment;
use App\Repositories\EquipmentRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;



class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $equipments = Equipment::paginate(5);
        $categories = Category::all();
        //dd($equipments->toArray());EquipmentResource::collection($equipments->paginate(5))
        return view('pages.equipment', ['equipments' => EquipmentResource::collection($equipments), 'categories' => $categories]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, EquipmentRepository $repository)
    {
        //
        $payload = $request->only([
            'name',
            'desc',
            'status',
            'categories_id',
            'users_id'
        ]);

        $validator = Validator::make($payload, [
            'name' => ['required', 'string'],
            'desc' => ['required', 'string'],
            'status' => ['in:available,used']
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            return new Response(["message" => "bad input"], HttpFoundationResponse::HTTP_BAD_REQUEST);
        }
        $category = Category::find($payload['categories_id']);
        $hash = Hash::make($payload['name']);
        $serial = substr($hash, 9, 3) . $category->title . substr($hash, 7, 6);
        while (!ctype_alnum($serial)) {
            $hash = Hash::make($payload['name']);
            $serial = substr($hash, 9, 3) . $category->title . substr($hash, 7, 6);
        }
        $payload['serial_number'] = $serial;
        $created = $repository->create($payload);
        return new EquipmentResource($created);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Equipment  $equipment
     * @return \Illuminate\Http\Response
     */
    public function show(Equipment $equipment)
    {
        //
        return new EquipmentResource($equipment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Equipment  $equipment
     * @return \Illuminate\Http\Response
     */
    public function edit(Equipment $equipment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Equipment  $equipment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Equipment $equipment, EquipmentRepository $repository)
    {
        //
        $payload = $request->only([
            'name',
            'desc',
            'status',
            'users_id'
        ]);

        $validator = Validator::make($payload, [
            'name' => ['required', 'string'],
            'desc' => ['required', 'string'],
            'status' => ['in:available,used']
        ]);
        if ($validator->stopOnFirstFailure()->fails()) {
            return new Response(["message" => "bad input"], HttpFoundationResponse::HTTP_BAD_REQUEST);
        }

        $updated = $repository->update($equipment, $payload);
        return new EquipmentResource($updated);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Equipment  $equipment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Equipment $equipment, EquipmentRepository $repository)
    {
        //
        $deleted = $repository->forceDelete($equipment);
        if (!$deleted)
            return new \Exception("loi r cha");
        return new EquipmentResource($deleted);
    }

    public function disable(Equipment $equipment, EquipmentRepository $repository)
    {

        $deleted = $repository->softDelete($equipment);
        if (!$deleted)
            return new \Exception("loi r cha");
        return new EquipmentResource($deleted);
        //  return redirect('equipments');
    }

    public function getUser(Equipment $equipment)
    {
        $user = $equipment->user;
        return new UserResource($user);
    }
}