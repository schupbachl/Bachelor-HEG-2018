<?php
/**
 * Created by PhpStorm.
 * User: loics
 * Date: 08.08.2018
 * Time: 11:18
 */

namespace App\Http\Controllers;

use App\DailyAvailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Availability;
use Illuminate\Support\Facades\DB;

class AvailabilityController
{
    private $messages = [
        'start.required' => "Une date de début est requise",
        'start.numeric' => 'La date de début doit être un entier au format timestamp',
        'end.required' => "Une date de fin est requise",
        'end.numeric' => 'La date de fin doit être un entier au format timestamp',
        'daily.required' => "Le paramètre \" journalier\" est requis",
        'daily.boolean' => "Le paramètre \"jounalier\" doit être un booléen",
        'carParkId.required' => "Un identifiant d'une place de parking est requise",
        'carParkId.numeric' => "L'identifiant de la place de parking doit être un entier positif",
    ];



    public function get($id)
    {
        $availabilities = DB::table('availabilities')->where('car_park_id', $id)->where('end', '>=', Carbon::now()->subMonth(1)->toDateTimeString())->get();
        $dailyAvailabilities = DB::table('daily_availabilities')->where('car_park_id', $id)->where('end', '>=', Carbon::now()->subMonth(1)->toDateTimeString())->get();

        return response()->json([
            'availabilities' => $availabilities,
            'daily_availabilities' => $dailyAvailabilities
        ], 200);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
       $request->validate([
           'start' => 'required|numeric',
           'end' => 'required|numeric',
           'daily' => 'boolean',
           'carParkId' => 'required|numeric'
       ], $this->messages);

       $availability = null;
       if($request->daily){
           $availability = new DailyAvailability([
               'start' => Carbon::createFromTimestampMs($request->start),
               'end' =>  Carbon::createFromTimestampMs($request->end),
               'car_park_id' => $request->carParkId
           ]);
       } else {
           $availability = new Availability([
               'start' => Carbon::createFromTimestampMs($request->start),
               'end' =>  Carbon::createFromTimestampMs($request->end),
               'car_park_id' => $request->carParkId
           ]);
       }
       $availability->save();

        return response()->json([
            'availability' => $availability,
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function delete($id, Request $request)
    {
        $request->validate([
            'daily' => 'required|boolean'
        ]);

        if($request->daily){
            $availability = DailyAvailability::find($id);
            if($availability->id == $request->user()->id){
                $availability->delete();
            } else {
                return response('Forbidden', 403);
            }
        } else {
            $availability = Availability::find($id);
            if($availability->id == $request->user()->id){
                $availability->delete();
            } else {
                return response('Forbidden', 403);
            }
        }

        return response()->json('Deleted',200);
    }
}