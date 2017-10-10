<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
class AuthController extends Controller
{
    public function authToken(Request $request){
    	$credentials=$request->only("email","password");

    	try {
    		if(!$token=JWTAuth::attempt($credentials)){
    			return response()->json(['errors'=>"invalid credentials !"],401);
    		}
    	} catch (JWTException $e) {
    		return response()->json(['errors'=>"could not create token !"]);
    	}
    	return response()->json(["token"=>$token]);
    }
    public function test(){
        return response()->json(['foo'=>'bar']);
    }
}
