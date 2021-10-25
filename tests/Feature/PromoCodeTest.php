<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\PromoCode;
use App\Models\User;
use App\Classes\PolylineEncoder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use Carbon\Carbon;


class PromoCodeTest extends TestCase
{
    /**
    * Test successful creation of PromoCodes
    */
    public function testPromoCodesAreCreatedCorrectly()
    {
        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        $event = Event::factory()->create();


        $payload = [
            'amount' => '100.00',
            'expiry' => '2021-05-05 00:00:00',
        ];

        $this->json('POST', '/api/promocodes/' . $event->id, $payload, $headers)
            ->assertStatus(201)
            ->assertJsonStructure([
                    'id',
                    'promo_code',
                    'amount',
                    'expiry',
                    'event_id',
                    'updated_at',
                    'created_at',
                ]);
    }

    /**
    * Test PromoCode details fetched successfully
    */

    public function testPromoCodeDetailsFetchedCorrectly()
    {

        $code1 = PromoCode::factory()->create();

        
        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];


        $response = $this->json('GET', '/api/promocodes/details/' . $code1->id, [], $headers)
            ->assertStatus(200);

        // is returned object same as that one represented by passed object id
        $this->assertEquals($code1->id, $response->decodeResponseJson()['id']);
        $this->assertEquals($code1->promo_code, $response->decodeResponseJson()['promo_code']);
        $this->assertEquals($code1->amount, $response->decodeResponseJson()['amount']);
        $this->assertEquals($code1->event_id, $response->decodeResponseJson()['event_id']);
        $this->assertEquals($code1->expiry, $response->decodeResponseJson()['expiry']);
        $this->assertEquals($code1->created_by, $response->decodeResponseJson()['created_by']);
            
    }
    
    /**
    * Test successful update of PromoCode
    */
    public function testPromoCodesAreUpdatedCorrectly()
    {
        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        $event1 = Event::factory()->create();
        $event2 = Event::factory()->create();

        $promoCode = PromoCode::factory()->create([
            'promo_code' => 'BA456245622',
            'amount' => '150.00',
            'expiry' => '2021-05-05 00:00:00',
            'event_id' => $event1->id,
        ]);

        $payload = [
            'promo_code' => 'BA45624562245',
            'amount' => '180.00',
            'expiry' => '2021-08-05 00:00:00',
            'event_id' => $event2->id,
        ];

        $response = $this->json('PUT', '/api/promocodes/' . $promoCode->id, $payload, $headers)
            ->assertStatus(200)
            ->assertJson([ 'id' => $event1->id, 'promo_code' => 'BA45624562245', 'amount' => '180.00', 'expiry' => '2021-08-05 00:00:00', 'event_id'=> $event2->id ]);
    }

    /**
    * Test successful deletion of PromoCode
    */
    public function testPromoCodesAreDeletedCorrectly()
    {
        $user = User::factory()->create();
        $token = $user->generateToken();
        
        $headers = ['Authorization' => "Bearer $token"];

        // if we dont specify event, automatically PromoCode::factory assigns an event
        $promoCode = PromoCode::factory()->create();

        $response = $this->json('GET', '/api/promocodes/details/' . $promoCode->id, [], $headers)
            ->assertStatus(200);

        $this->json('DELETE', '/api/promocodes/' . $promoCode->id, [], $headers)
            ->assertStatus(204);

        // the event has been deleted, hence a response of 404
        $response = $this->json('GET', '/api/promocodes/details/' . $promoCode->id, [], $headers)
            ->assertStatus(404);
    }

    /**
    * Test List All Promo Codes in the system - including deactivated and expired
    */

    public function testPromoCodesAreListedCorrectly()
    {

        $event = Event::factory()->create();

        PromoCode::factory()->create([
            'promo_code' => 'BA4562456223',
            'amount' => '150.00',
            'event_id' => $event->id,
        ]);

        

        PromoCode::factory()->create([
            'promo_code' => 'BA4562456224',
            'amount' => '160.00',
            'event_id' => $event->id,
        ]);

        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', '/api/promocodes', [], $headers)
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson([
                [ 'promo_code' => 'BA4562456223', 'amount' => '150.00', 'event_id' => $event->id],
                [ 'promo_code' => 'BA4562456224', 'amount' => '160.00', 'event_id' => $event->id],
            ])
            ->assertJsonStructure([
                '*' => ['id', 'promo_code', 'amount', 'is_active', 'expiry', 'event_id', 'created_at', 'updated_at'],
            ]);
    }

    /**
    * Test List All Event Promo Codes in the system
    */

    public function testEventPromoCodesAreListedCorrectly()
    {


        $event1 = Event::factory()->create();

        PromoCode::factory()->create([
            'promo_code' => 'BA4562456223',
            'amount' => '110.00',
            'event_id' => $event1->id,
        ]);

        

        PromoCode::factory()->create([
            'promo_code' => 'BA4562456224',
            'amount' => '120.00',
            'event_id' => $event1->id,
        ]);

        // lets add a code from different event - PromoCode::factory assigns new event and Promo code attributes
        PromoCode::factory()->create();

        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', '/api/promocodes/event/' . $event1->id , [], $headers)
            ->assertStatus(200)
            ->assertJsonCount(2) // 3rd Promo Code was assigned to different event
            ->assertJson([
                [ 'promo_code' => 'BA4562456223', 'amount' => '110.00'],
                [ 'promo_code' => 'BA4562456224', 'amount' => '120.00'],
            ])
            ->assertJsonStructure([
                '*' => ['id', 'promo_code', 'amount', 'is_active', 'expiry', 'event_id', 'created_at', 'updated_at'],
            ]);
    }

    /**
    * Test List All Active Promo Codes in the system
    */

    public function testActivePromoCodesAreListedCorrectly()
    {

        $code1 = PromoCode::factory()->create();

        $code2 = PromoCode::factory()->create();

        $code3 = PromoCode::factory()->create();

        $code4 = PromoCode::factory()->create();
        
        // lets force expire code4 - test if active code will return expired codes
        $code4->expiry = Carbon::now()->subtract(5, 'minute'); # expired 5 minutes ago
        $code4->save();

        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        // lets deactivate code # 2
        $responseDeactivate = $this->json('PUT', '/api/promocodes/deactivate/' . $code2->id, [], $headers);

        $response = $this->json('GET', '/api/promocodes/active', [], $headers)
            ->assertStatus(200)
            ->assertJsonCount(2);
    }


    /**
    * Test List All Active Event Promo Codes in the system
    */
    public function testActiveEventPromoCodesAreListedCorrectly()
    {

        $event = Event::factory()->create();

        $code1 = PromoCode::factory()->create(['event_id' => $event->id]);

        $code2 = PromoCode::factory()->create(['event_id' => $event->id]);

        $code3 = PromoCode::factory()->create(['event_id' => $event->id]);

        $code4 = PromoCode::factory()->create(['event_id' => $event->id]);

        $code5 = PromoCode::factory()->create();// assign code #5 to other event

        // lets force expire code4 - test if active code will return expired codes
        $code4->expiry = Carbon::now()->subtract(5, 'minute'); # expired 5 minutes ago
        $code4->save();

        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        // lets deactivate code # 2
        $responseDeactivate = $this->json('PUT', '/api/promocodes/deactivate/' . $code2->id, [], $headers);

        // code 2 is deactivated and code 4 belongs to different event - hence count = 2

        $response = $this->json('GET', '/api/promocodes/' . $event->id . '/active', [], $headers)
            ->assertStatus(200)
            ->assertJsonCount(2);
            
    }

    /**
    * Test is user can access Promo codes using wrong authentication token
    */

    public function testUserCantAccessPromoCodesWithWrongToken()
    {
        PromoCode::factory()->create();
        $user = User::factory()->create([ 'email' => 'user@safeboda.com' ]);
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        // lets invalidate auth code already set in the Authorization header
        $user->generateToken();

        $this->json('get', '/api/promocodes', [], $headers)->assertStatus(401);
    }

    /**
    * Test is user can access Promo codes without authentication token
    */
    public function testUserCantAccessPromoCodesWithoutToken()
    {
        PromoCode::factory()->create();

        $this->json('get', '/api/promocodes')->assertStatus(401);
    }

    /**
    * Test successful deactivation of PromoCodes
    */
    public function testPromoCodesDeactivateSuccessfully()
    {
        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        $promoCode = PromoCode::factory()->create(); 

        $response = $this->json('PUT', '/api/promocodes/deactivate/' . $promoCode->id, [], $headers)
            ->assertStatus(200)
            ->assertJson([ 'id' => $promoCode->id, 'is_active' => false ]);
    }

    
    /**
    * Test successful validation of PromoCode
    */
    public function testPromoCodesValidateSuccessfully()
    {

        $event = Event::factory()->create([
            'latitude' => "-1.266298",
            'longitude' => "36.763025",
            'location_name' => "Shani Taji School, Musa Gitau Rd, Nairobi City",
        ]);

        $user = User::factory()->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        $promoCode = PromoCode::factory()->create(['event_id' => $event->id]);

        // origin coordinates - CHAK Guest House & Conference Centre, Musa Gitau Rd, Nairobi - within acceptable radius
        $originLat = "-1.2666904";
        $originLng = "36.7628959";

        // destination coordinates - PCEA Kikuyu Hospital
        $destinationLat = "-1.2627461"; 
        $destinationLng = "36.7642235";

        // Assumption is that user will enter code, pick origin and destination coordinates
        // Origin above is very close to event location coordinates
        
        $response = $this->json('GET', '/api/promocodes/validate/' . 
                        $promoCode->promo_code . '/' . 
                        $originLat . '/' . 
                        $originLng . '/' .
                        $destinationLat . '/' .
                        $destinationLng, 
                        [], $headers
                )
            ->assertStatus(200)
            ->assertJsonStructure([
                    'promo_code'=> ["id", "promo_code", "amount", "expiry", "is_active", "event_id", "updated_at", "created_at"],
                    //'polyline2' => ['*' => ['lat', 'lng']],
                    'polyline',
                ]);

        $encodedPolyline = $response->decodeResponseJson()['polyline'];

        $this->assertNotEquals("", $encodedPolyline, "Polyline is null"); 

        // verify encoded polyline against original coordinates

        $polylineEncoder = new PolylineEncoder();

        $polylineEncoder->addPoint($originLat,$originLng);
        $polylineEncoder->addPoint($destinationLat,$destinationLng);

        $tmpEncodedPolyline = $polylineEncoder->encodedString();

        $this->assertEquals($tmpEncodedPolyline, $encodedPolyline);
        
        /** Lets adjust origin coordinates to a location outside the set radius of 300 Metres(0.3 Kms)
        * - TULIA - PETALS GARDEN HOTEL, Kikuyu
        */
        $originLat = "-1.2574974";
        $originLng = "36.6980437";

        $response = $this->json('GET', '/api/promocodes/validate/' . 
                        $promoCode->promo_code . '/' . 
                        $originLat. '/' . 
                        $originLng. '/' .
                        $destinationLat. '/' .
                        $destinationLng, 
                        [], $headers
                )
            ->assertStatus(401);

        // validate non existent promo code
        // origin coordinates - CHAK Guest House & Conference Centre, Musa Gitau Rd, Nairobi - within acceptable radius
        $originLat = "-1.2666904";
        $originLng = "36.7628959";

        $fakePromoCode = "000_NON_EXISTENT";
        $response = $this->json('GET', '/api/promocodes/validate/' . 
                        $fakePromoCode . '/' . 
                        $originLat. '/' . 
                        $originLng. '/' .
                        $destinationLat. '/' .
                        $destinationLng, 
                        [], $headers
                )
            ->assertStatus(401);

        // validate deactivated code
        $promoCode->is_active = false;
        $promoCode->save();

        // origin coordinates - CHAK Guest House & Conference Centre, Musa Gitau Rd, Nairobi - within acceptable radius
        $originLat = "-1.2666904";
        $originLng = "36.7628959";

        $response = $this->json('GET', '/api/promocodes/validate/' . 
                        $promoCode->promo_code . '/' . 
                        $originLat. '/' . 
                        $originLng. '/' .
                        $destinationLat. '/' .
                        $destinationLng, 
                        [], $headers
                )
            ->assertStatus(401);

        $promoCode->is_active = true;
        $promoCode->save();

        // validate expired code
        $promoCode->expiry = Carbon::now()->subtract(5, 'minute'); # expired 5 minutes ago
        $promoCode->save();
        
        // origin coordinates - CHAK Guest House & Conference Centre, Musa Gitau Rd, Nairobi - within acceptable radius
        $originLat = "-1.2666904";
        $originLng = "36.7628959";

        $response = $this->json('GET', '/api/promocodes/validate/' . 
                        $promoCode->promo_code . '/' . 
                        $originLat. '/' . 
                        $originLng. '/' .
                        $destinationLat. '/' .
                        $destinationLng, 
                        [], $headers
                )
            ->assertStatus(401);

    }
}
