<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class EventTest extends TestCase
{
    /**
    * Test successful creation of events
    */
    public function testEventsAreCreatedCorrectly()
    {
        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];
        $payload = [
                'event_name' => 'Lorem1',
                'event_description' => 'Ipsum description',
                'latitude' => "-1.2627461",
                'longitude' => "36.7642235",
                'location_name' => "Naivas Supermarket - Westlands, Delta, Waiyaki Way, Nairobi",
            ];

        $this->json('POST', '/api/events', $payload, $headers)
            ->assertCreated()
            ->assertJson([
                    'id' => 1, 'event_name' => 'Lorem1', 
                    'event_description' => 'Ipsum description', 
                    'latitude' => '-1.2627461', 'longitude' => '36.7642235', 
                    'location_name' => 'Naivas Supermarket - Westlands, Delta, Waiyaki Way, Nairobi',
                ])
            ->assertJsonStructure([
                    'id',
                    'event_name',
                    'event_description',
                    'latitude',
                    'longitude',
                    'location_name',
                    'updated_at',
                    'created_at',                    
                ]);
    }


    /**
    * Test Event details fetched successfully
    */

    public function testEventDetailsFetchedCorrectly()
    {

        $event = Event::factory()->create();

        
        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];


        $response = $this->json('GET', '/api/events/details/' . $event->id, [], $headers)
            ->assertStatus(200);

        // is returned object same as that one represented by passed object id
        $this->assertEquals($event->id, $response->decodeResponseJson()['id']);
        $this->assertEquals($event->event_name, $response->decodeResponseJson()['event_name']);
        $this->assertEquals($event->event_description, $response->decodeResponseJson()['event_description']);
        $this->assertEquals($event->latitude, $response->decodeResponseJson()['latitude']);
        $this->assertEquals($event->location_name, $response->decodeResponseJson()['location_name']);
        $this->assertEquals($event->created_by, $response->decodeResponseJson()['created_by']);
            
    }

    /**
    * Test successful update of event
    */
    public function testEventsAreUpdatedCorrectly()
    {
        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        $createdEvent = Event::factory()->create([
            'event_name' => 'Lorem1',
            'event_description' => 'Ipsum description',
            'latitude' => "-1.2627461",
            'longitude' => "36.7642235",
            'location_name' => "Naivas Supermarket - Westlands, Delta, Waiyaki Way, Nairobi",
        ]);

        $updatePayload = [
            'event_name' => 'Lorem Update',
            'event_description' => 'Ipsum description updated',
            'latitude' => "-1.2634926",
            'longitude' => "36.763804",
            'location_name' => "Quick Mart Waiyaki Way, Deloitte Waruku, Waiyaki Way",
        ];

        $response = $this->json('PUT', '/api/events/' . $createdEvent->id, $updatePayload, $headers)
            ->assertStatus(200)
            ->assertJson([ 
                'id' => $createdEvent->id, 
                'event_name' => 'Lorem Update', 
                'event_description' => 'Ipsum description updated',
                'latitude' => "-1.2634926",
                'longitude' => "36.763804",
                'location_name' => "Quick Mart Waiyaki Way, Deloitte Waruku, Waiyaki Way",
            ]);
    }

    /**
    * Test successful deletion of event
    */
    public function testEventsAreDeletedCorrectly()
    {
        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        $event = Event::factory()->create();

        $response = $this->json('GET', '/api/events/details/' . $event->id, [], $headers)
            ->assertStatus(200);

        $this->json('DELETE', '/api/events/' . $event->id, [], $headers)
            ->assertStatus(204);

        // the event has been deleted, hence a response of 404
        $response = $this->json('GET', '/api/events/details/' . $event->id, [], $headers)
            ->assertStatus(404);
    }

    /**
    * Test successful fetch of events list
    */
    public function testEventsAreListedCorrectly()
    {
        $event1 = Event::factory()->create([
            'event_name' => 'Lorem1 a',
            'event_description' => 'Ipsum description 1a',
            'latitude' => "-1.2627461",
            'longitude' => "36.7642235",
            'location_name' => "Naivas Supermarket - Westlands, Delta, Waiyaki Way, Nairobi",
        ]);

        $event2 = Event::factory()->create([
            'event_name' => 'Lorem2b',
            'event_description' => 'Ipsum description 2b',
            'latitude' => "-1.2627461",
            'longitude' => "36.7642235",
            'location_name' => "Naivas Supermarket - Westlands, Delta, Waiyaki Way, Nairobi",
        ]);

        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', '/api/events', [], $headers)
            ->assertStatus(200)
            ->assertJson([
                [ 
                    'event_name' => 'Lorem1 a', 
                    'event_description' => 'Ipsum description 1a',  
                    'latitude' => "-1.2627461",
                    'longitude' => "36.7642235", 
                    'location_name' => "Naivas Supermarket - Westlands, Delta, Waiyaki Way, Nairobi"
                ],
                [ 
                    'event_name' => 'Lorem2b', 
                    'event_description' => 'Ipsum description 2b',
                    'latitude' => "-1.2627461",
                    'longitude' => "36.7642235",
                    'location_name' => "Naivas Supermarket - Westlands, Delta, Waiyaki Way, Nairobi",
                ],
            ])
            ->assertJsonStructure([
                '*' => ['id', 'event_name', 'event_description', 'latitude','longitude', 'location_name', 'created_at', 'updated_at'],
            ]);
    }

    /**
    * Test fetch event without proper authetication token
    */
    public function testUserCantAccessEventsWithWrongToken()
    {
        Event::factory()->create();
        $user = User::factory()->create([ 'email' => 'user@safeboda.com' ]);
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        // generatwe new token invalidating one thats is added in Authorization header
        $user->generateToken();

        $this->json('get', '/api/events', [], $headers)->assertStatus(401);
    }

    /**
    * Test fetch event without authetication token
    */
    public function testUserCantAccessEventsWithoutToken()
    {
        Event::factory()->create();

        $this->json('get', '/api/events')->assertStatus(401);
    }
}
