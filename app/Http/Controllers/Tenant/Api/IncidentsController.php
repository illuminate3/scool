<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Requests\Incidents\DeleteIncident;
use App\Http\Requests\Incidents\ListIncidents;
use App\Http\Requests\Incidents\ShowIncident;
use App\Http\Requests\Incidents\StoreIncident;
use App\Models\Incident;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class IncidentsController.
 *
 * @package App\Http\Controllers\Api
 */
class IncidentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param ListIncidents $request
     * @return Incident[]|\Illuminate\Database\Eloquent\Collection
     */
    public function index(ListIncidents $request)
    {
        return Incident::getIncidents();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreIncident $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIncident $request)
    {
        return Incident::create($request->only('subject','description'))->assignUser($request->user())->map();
    }

    /**
     * Display the specified resource.
     *
     * @param ShowIncident $request
     * @param Incident $incident
     * @return Incident
     */
    public function show(ShowIncident $request, $tenant,Incident $incident)
    {
        return $incident->map();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteIncident $request
     * @param $tenant
     * @param Incident $incident
     * @return Incident
     * @throws \Exception
     */
    public function destroy(DeleteIncident $request, $tenant, Incident $incident)
    {
        $incident->delete();
        return $incident;
    }
}
