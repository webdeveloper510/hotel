<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\hotel;
use App\Models\Room;
use App\Models\Destination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;
use stripe;
use Exception;
use Stripe\StripeClient;

class UserController extends Controller
{
    public $successStatus = 200;
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // print_r($request);die;
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('app')->accessToken;
            $success['userId'] = $user->id;
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('app')->accessToken;
        $success['name'] =  $user->name;
        return response()->json([
            'success' => $success
        ], $this->successStatus);
    }

    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function userDetails()
    {
        $user = Auth::user();
        return response()->json([
            'success' => $user
        ], $this->successStatus);
    }

    public function getUsers()
    {
        $user = User::all();
        return response()->json([
            'users' => $user,
        ]);
    }

    public function logout()
    {
        $user = Auth::user()->token();
        $user->revoke();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function add_hotel(Request $request)
    {

        $request->validate([
            'tag' => 'required',
            'title' => 'required',
            'location' => 'required',
            'price' => 'required',
            'delayAnimation' => 'required',
            'slideImg' => 'required',
            'img' => 'required',
        ]);

        $hotel = new hotel;

        // Store multiple slideImg files
        if ($request->hasFile('slideImg')) {
            $slideImages = [];
            foreach ($request->file('slideImg') as $slideFile) {
                if ($slideFile) {
                    $extension = $slideFile->getClientOriginalExtension();
                    $filename = time() . '_' . uniqid() . '.' . $extension;
                    $slideFile->move(public_path('images'), $filename);
                    $slideImages[] = $filename;
                }
            }
            $hotel->slideImg = serialize($slideImages);
        }

        // Store the main img file
        if ($request->hasFile('img')) {
            $extension = $request->file('img')->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $request->file('img')->move(public_path('images'), $filename);
            $hotel->img = $filename;
        }

        // Set other attributes
        $hotel->tag = $request->tag;
        $hotel->title = $request->title;
        $hotel->location = $request->location;
        $hotel->price = $request->price;
        $hotel->delayAnimation = $request->delayAnimation;
        $hotel->description = $request->description;
        $hotel->save();

        return response()->json([
            'message' => 'Hotel Saved Successfully!',
            'Hotel' => $hotel,
        ]);
    }

    public function get_hotels()
    {
        $hotels = hotel::with('rooms')->get();

        // Deserialize the slideImg field for each hotel
        foreach ($hotels as $hotel) {
            $hotel->slideImg = @unserialize($hotel->slideImg) ?: [];
        }

        return response()->json([
            'Hotels' => $hotels,
        ]);
    }

    public function delete_hotel($id)
    {

        $hotel = hotel::find($id);
        $hotel->delete();
        return response()->json([
            'Hotels' => $hotel,
        ]);
    }


    public function update_hotel(Request $request, $id)
    {

        $request->validate([
            'slideImg' => 'nullable|image',
            'img' => 'nullable|image',
            'tag' => 'required',
            'title' => 'required',
            'price' => 'required',
            'delayAnimation' => 'required',
        ]);

        $data = array(
            'tag' => $request->tag,
            'title' => $request->title,
            'price' => $request->price,
            'delayAnimation' => $request->delayAnimation,
        );

        if ($request->hasFile('slideImg')) {
            $file = $request->file('slideImg');
            $slideImg = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $slideImg);
            $data['slideImg'] = $slideImg;
        }

        if ($request->hasFile('img')) {
            $file = $request->file('img');
            $img = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $img);
            $data['img'] = $img;
        }

        $updatedRows = hotel::where('id', $id)->update($data);

        return response()->json([
            'Message' => 'Hotel data updated Successfully !!',
            'Updated Rows' => $updatedRows,
        ]);
    }

    public function hotel_location(Request $request)
    {
        $request->validate([
            'country_img' => 'required',
            'country' => 'required',
            'hotel' => 'required',
            'cars' => 'required',
            'tours' => 'required',
            'activity' => 'required',
        ]);

        $hotel_loc = new Destination;

        if ($request->hasFile('country_img')) {
            $extension = $request->file('country_img')->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $request->file('country_img')->move(public_path('country_images'), $filename);
            $hotel_loc->country_img = $filename;
        }

        $hotel_loc->country = $request->country;
        $hotel_loc->hotel = $request->hotel;
        $hotel_loc->cars = $request->cars;
        $hotel_loc->tours = $request->tours;
        $hotel_loc->activity = $request->activity;
        $hotel_loc->save();

        return response()->json([
            'Message' => 'Hotel Location Saved Successfully !!',
            'data' => $hotel_loc,
        ]);
    }

    public function get_hotel_location()
    {
        $hotel_loc = Destination::all();

        return response()->json([
            'data' => $hotel_loc,
        ]);
    }



    public function stripePost(Request $request)
    {
        try {
            $stripe = new \Stripe\StripeClient([
                'api_key' => env('STRIPE_SECRET'),
            ]);

            $token = $stripe->tokens->create([
                'card' => [
                    'number' => $request->number,
                    'exp_month' => $request->exp_month,
                    'exp_year' => $request->exp_year,
                    'cvc' => $request->cvc,
                ],
            ]);

            $response = $stripe->charges->create([
                'amount' => $request->amount,
                'currency' => 'usd',
                'source' => $token->id,
                'description' => $request->description,
            ]);

            return response()->json([$response->status], 201);
        } catch (Exception $ex) {

            return response()->json(['response' => 'Error'], 500);
        }
    }

    public function add_room(Request $request)
    {
        $request->validate([
            'room_name' => 'required',
            'bed_type' => 'required',
            'room_floor' => 'required',
            'facility' => 'required',
        ]);

        $rooms  = new Room;

        $rooms['room_name'] = $request['room_name'];
        $rooms['bed_type'] = $request['bed_type'];
        $rooms['room_floor'] = $request['room_floor'];
        $rooms['facility'] = $request['facility'];
        $rooms['hotel_id'] = $request['hotel_id'];
        $rooms->save();

        return response()->json([
            'Message' => 'Rooms Save Successfully !!',
            'Room' => $rooms,
        ]);
    }

    public function get_rooms()
    {
        $room = Room::all();
        return response()->json([
            'All Rooms' => $room,
        ]);
    }

    public function update_rooms(Request $request, $id)
    {

        $data = array(
            'room_name' => $request->room_name,
            'bed_type' => $request->bed_type,
            'room_floor' => $request->room_floor,
            'facility' => $request->facility,
        );

        $updatedRooms = Room::where('id', $id)->update($data);

        return response()->json([
            'Message' => 'Rooms Updated Successfully !!',
            'Updated Rooms' => $updatedRooms,
        ]);
    }

    public function delete_rooms($id)
    {
        $rooms = Room::find($id);
        $rooms->delete();
        return response()->json([
            'Hotels' => $rooms,
        ]);
    }
}
