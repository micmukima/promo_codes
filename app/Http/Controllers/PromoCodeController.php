<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\PromoCode;
use App\Models\Event;
use App\Classes\PolylineEncoder;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    
    /**
     * Fetch all Promotion Codes
     *

     * @return \App\Models\PromoCode
     */
    public function index() 
    {
        return PromoCode::all();
    }

    /**
     * Fetch active Promotion codes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array App\Models\PromoCode
     */
    public function active() 
    {
        return PromoCode::active()->notExpired()->get();
    }

    /**
     * Fetch Promotion codes for a given event
     *
     * @param  \App\Models\Event  $event
     * @return array \App\Models\PromoCode
     */
    public function eventIndex(Event $event) 
    {
        return PromoCode::event($event)->get();;
    }

    /**
     * Fetch active Promotion codes for given event
     *
     * @param  \App\Models\Event $event
     * @return array \App\Models\PromoCode
     */
    public function eventActive(Event $event) 
    {
        return PromoCode::event($event)->active()->notExpired()->get();
    }

    /**
     * Fetch Promotion code details
     *
     * @param  \App\Models\PromoCode $promoCode
     * @return array \App\Models\PromoCode
     */
    public function show(PromoCode $promoCode)
    {
        return $promoCode;
    }

    /**
     * Create Promotion Code for an event
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @return mixed
     */
    public function store(Request $request, Event $event)
    {
        $expiryDate = is_null($request->expiry) ? Carbon::now()->add(env('PROMO_CODE_EXPIRY', 0), 'hour') : $request->expiry;

        $data = $request->all();
        $data['event_id'] = $event->id;
        $data['expiry'] = $expiryDate;
        $data['created_by'] = $request->user()->id;
        $promoCode = PromoCode::create($data);
        $promoCode = $promoCode->generatePromoCode();
        return response()->json($promoCode, 201);
    }

    /**
     * Update Promotion Code
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PromoCode  $promoCode
     * @return mixed
     */
    public function update(Request $request, PromoCode $promoCode)
    {
        $promoCode->update($request->all());
        return response()->json($promoCode, 200);
    }

    /**
     * Deactivate Promotion Code
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PromoCode  $promoCode
     * @return mixed
     */
    public function deactivate(Request $request, PromoCode $promoCode)
    {
        $promoCode->update(['is_active' => false]);
        return response()->json($promoCode, 200);
    }

    /** 
    *Soft-delete alternative for delete function
    *
    * We can alternatively implement a soft delete as follows
    *    - Craete an is_deleted field in promoCode table with default value = 0
    *    - in this method, instead update is_deleted = 1
    *    - whenever fetching all promoCodes list, ommit promoCodes where is_deleted = 1
    * ****best approach - activate laravels inbuilt soft delete functionality
    */

    /**
     * Delete Promotion Code
     *
     * @param  \App\Models\PromoCode  $promoCode
     * @return mixed
     */
    public function delete(PromoCode $promoCode)
    {
        $promoCode->delete();
        return response()->json(null, 204);
    }

    /**
     * Update event
    *  @param  string $lat1
    *  @param  string $lon1
    *  @param  string $lat2
    *  @param  string $lon2
    *  @param  string $unit
     * @return float
     */

    private function calculateDistance($lat1, $lon1, $lat2, $lon2, $unit) {

          $theta = $lon1 - $lon2;
          $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
          $dist = acos($dist);
          $dist = rad2deg($dist);
          $miles = $dist * 60 * 1.1515;
          $unit = strtoupper($unit);

          if ($unit == "K") {
              return ($miles * 1.609344);
          } else if ($unit == "N") {
              return ($miles * 0.8684);
          } else {
              return $miles;
          }
    }

    /**
     * Validate Event promotion code
    *  @param  string $promo_code
    *  @param  string $originLat
    *  @param  string $originLng
    *  @param  string $destinationLat
    *  @param  string $destinationLng
     * @return mixed
     */
    public function validateCode($promo_code, $originLat, $originLng, $destinationLat, $destinationLng)
    {
        $promoCode = PromoCode::where('promo_code', $promo_code)
               ->first();

        if (is_null($promoCode) || !$promoCode->isValid()) {
            $data  = array (
                "message" => "Invalid Promotion Code"
            );
            return response()->json($data, 401);
        }

        // get event associated with PromoCode
        $promoEvent = $promoCode->getEvent();

        $locationsDistance = $this->calculateDistance(
            $promoEvent->getLatitude(), 
            $promoEvent->getLongitude(), 
            $originLat, 
            $originLng, 
            "K" // Kilometres
        );
        
        $validityRadius = env('PROMO_CODE_RADIUS', 0);

        if($locationsDistance <= $validityRadius) {
            // selected origin and event location is within set radius

            $polylineEncoder = new PolylineEncoder();

            $polylineEncoder->addPoint($originLat,$originLng);
            $polylineEncoder->addPoint($destinationLat,$destinationLng);

            $encodedPolyline = $polylineEncoder->encodedString();

            /*$polyline = array(
                array('lat'=> $originLat, 'lng'=> $originLng),
                array('lat'=> $destinationLat, 'lng'=> $destinationLng),
            );*/

            $resultPromoCode = array(
                'id' => $promoCode->id,
                'promo_code' => $promoCode->promo_code,
                'amount' => $promoCode->amount,
                'expiry' => $promoCode->expiry,
                'is_active' => $promoCode->is_active,
                'event_id' => $promoCode->event_id,
                'updated_at' => $promoCode->updated_at,
                'created_at' => $promoCode->created_at,
            );
            //print $encodedPolyline;
            $resultArray = array(
                'promo_code' =>  $resultPromoCode,
                //'polyline2' =>  $polyline,
                'polyline' =>  $encodedPolyline,
            );

            return response()->json($resultArray, 200);

        } else {
            // selected origin and event location is outside set radius
            return response()->json(null, 401);
        }
    }
}
