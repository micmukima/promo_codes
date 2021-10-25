<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    
    /**
     * Get Fetch all events in the system.
     *
     * @return \App\Models\Event
     */
    public function index() 
    {
        return Event::all();
    }

    /**
     * Fetch single event details.
     *
     * @param  \App\Models\Event
     * @return @return App\Models\Event
     */
    public function show(Event $event)
    {
        return $event;
    }

    /**
     * Create event
     *
     *@param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->validator($request->all())->validate();

        $data = $request->all();
        $data['created_by'] = $request->user()->id;
        $event = Event::create($data);
        return response()->json($event, 201);
    }

    /**
     * Validator for an incoming event request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'event_name' => 'required|unique:events',
        ]);
    }

    /**
     * Update event
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Models\Event
     * @return mixed
     */

    public function update(Request $request, Event $event)
    {
        $event->update($request->all());
        return response()->json($event, 200);
    }

    /** 
    *Soft-delete alternative for delete function
    *
    * We can alternatively implement a soft delete as follows
    *    - Craete an is_deleted field in events table with default value = 0
    *    - in this method, instead update is_deleted = 1
    *    - whenever fetching all events list, ommit events where is_deleted = 1
    * ****best approach - activate laravels inbuilt soft delete functionality
    */

    /**
     * Delete event
     *
     * @return @return \App\Models\Event
     * @return mixed
     */

    public function delete(Event $event)
    {
        $event->delete();
        return response()->json(null, 204);
    }
}
