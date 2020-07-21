<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\Route;

class AuthController extends Controller
{
    public function login(Request $request){
        // $request->validate([
        //     'email' => 'required|string|email',
        //     'password' => 'required|string',
        //     'remember_me' => 'boolean'
        // ]);
        // $credentials = request(['email', 'password']);
        // if(!Auth::attempt($credentials))
        //     return response()->json([
        //         'message' => 'Unauthorized'
        //     ], 401);

        // $user = $request->user();
        // $tokenResult = $user->createToken('Laravel Password Grant Client');
        // $token = $tokenResult->token;

        // if ($request->remember_me)
        //     $token->expires_at = Carbon::now()->addWeeks(1);
        // $token->save();
        // return response()->json([
        //     'access_token' => $tokenResult->accessToken,
        //     'token_type' => 'Bearer',
        //     'expires_at' => Carbon::parse(
        //         $tokenResult->token->expires_at
        //     )->toDateTimeString()
        // ]);


        $request->request->add([
          'username' => $request->email,
          'password' => $request->password,
            'grant_type' => 'password',
            'client_id' => '2',
            'client_secret' => 'MatR3Tv9YXwVCLwOBOQLNFI4rUYoyd6TolgjMEGh',
            'scope' => ''
        ]);

        $tokenRequest = Request::create(
            url('oauth/token'),
            'post'
        );
        $response = Route::dispatch($tokenRequest);
        return $response;
        // $validator = Validator::make($request->all(), [
        //     'email' => 'required|string|email|max:255',
        //     'password' => 'required|string|min:6|confirmed',
        // ]);

        // if ($validator->fails())
        // {
        //     return response(['errors'=>$validator->errors()->all()], 422);
        // }

        // $user = User::where('email', $request->email)->first();
        // if ($user) {
        //     if (Hash::check($request->password, $user->password)) {
        //         $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        //         $response = ['token' => $token];
        //         return response($response, 200);
        //     } else {
        //         $response = ["message" => "Password mismatch"];
        //         return response($response, 422);
        //     }
        // } else {
        //     $response = ["message" =>'User does not exist'];
        //     return response($response, 422);
        // }
    }

    public function logout(Request $request){
        auth()->user()->tokens->each(function ($token, $key){
            $token->delete();
        });

        return response()->json('Logged out successfully', 200);
    }
        
}