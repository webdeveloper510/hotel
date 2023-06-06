<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\hotel;
use GuzzleHttp\Psr7\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class UserController extends Controller
{
    public $successStatus = 200;
 /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(){
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyLaravelApp')-> accessToken;
            $success['userId'] = $user->id;
            return response()->json(['success' => $success], $this-> successStatus);
        }
        else{
            return response()->json(['error'=>'Unauthorised'], 401);
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
             return response()->json(['error'=>$validator->errors()], 401);
 }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('app')-> accessToken;
        $success['name'] =  $user->name;
        return response()->json([
            'success'=>$success
        ], $this-> successStatus);
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
        ], $this-> successStatus);
    }

    public function getUsers(){
        $user = User::all();
        return response()->json([
            'users' => $user,
        ]);
    }

    public function logout(){
        $user = Auth::user()->token();
        $user->revoke();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function add_hotel(Request $request)
{
    $request->validate([
        'slideImg' => 'required|file',
        'img' => 'required|file',
        'tag' => 'required',
        'title' => 'required',
        'location_id' => 'required',
        'price' => 'required',
        'delayAnimation' => 'required',
    ]);

    $hotel = new hotel;

    if ($request->hasFile('slideImg')) {
        $extension = $request->file('slideImg')->getClientOriginalExtension();
        $filename = time() . '.' . $extension;
        $request->file('slideImg')->move(public_path('images'), $filename);
        $hotel->slideImg = $filename;
    }

    if ($request->hasFile('img')) {
        $extension = $request->file('img')->getClientOriginalExtension();
        $filename = time() . '.' . $extension;
        $request->file('img')->move(public_path('images'), $filename);
        $hotel->img = $filename;
    }

    $hotel->tag = $request->tag;
    $hotel->title = $request->title;
    $hotel->location_id = $request->location_id;
    $hotel->price = $request->price;
    $hotel->delayAnimation = $request->delayAnimation;
    $hotel->save();

    return response()->json([
        'message' => 'Hotel Saved Successfully!',
        'Hotel' => $hotel,
    ]);
}

public function get_hotels(){
    $hotes = hotel::all();
    return response()->json([
        'Hotels' => $hotes,
    ]);
}

public function delete_hotel($id){
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
        'Message' => 'Hotel data updated Successfully',
        'Updated Rows' => $updatedRows,
    ]);
}


}
