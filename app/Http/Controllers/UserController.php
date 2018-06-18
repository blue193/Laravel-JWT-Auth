<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use JWTAuth;
use JWTAuthException;
// use Tymon\JWTAuth\Exceptions\JWTException;
// use Tymon\JWTAuth\Exceptions\TokenExpiredException;
// use Tymon\JWTAuth\Exceptions\TokenInvalidException;
// use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    //log in
    public function login(Request $request){
        $body = $request->only('email', 'password');
        $token = null;

        try {
           if (!$token = JWTAuth::attempt($body)) {
            return response()->json(['invalid_email_or_password'], 422);
           }
        } catch (JWTAuthException $e) {
            return response()->json(['failed_to_create_token'], 500);
        }

        if(User::where('email', $body['email'])->get()){
            $response['status'] = 1;
            $response['message'] = 'Successfully sign in';
            $response['data'] = User::where('email', $body['email'])->get();
            $response['token'] = $token;
        }
        else{
            $response['status'] = 0;
            $response['message'] = "Log in faild";
        }
        
        return response()->json($response);
    }

    //logout
    public function logout(Request $request){
        try {
                $body = $request->all();
                $token = $body['token'];
                if (!$token = JWTAuth::invalidate($token)) 
                {
                    return response()->json(['valid state log in'], 422);
                }
            }
            catch (\JWTAuthException $e) {
                return response()->json(['failed_to_log_out'], 500);
            }
            catch (TokenBlacklistedException $e) {

		        return response()->json(['token_expired'], $e->getStatusCode());

		    }catch (TokenExpiredException $e) {

		        return response()->json(['token_expired'], $e->getStatusCode());

		    } catch (TokenInvalidException $e) {

		        return response()->json(['token_invalid'], $e->getStatusCode());

		    } catch (\JWTException $e) {

		        return response()->json(['token_absent'], $e->getStatusCode());

		    }

        return response()->json(['success log out']);
    }

    public function refreshToken(Request $request){

    	try{
            $output['status'] = 1;
            $output['new_token'] = JWTAuth::refresh($token);
        }catch(\JWTAuthException $e){
            $output['status'] = 0;
            $output['message'] = "this token is not using or used now.";
        }
        catch(\Exception $e) {
            // $value = typeof($e);
            $output['status'] = 2;
            $output['message'] = "unknown exception";
            // $output['data'] = $value;
        }
        catch (TokenBlacklistedException $e) {

        	$output['message'] = json(['token_expired'], $e->getStatusCode());

	    }catch (TokenExpiredException $e) {

	        $output['message'] = json(['token_expired'], $e->getStatusCode());

	    } catch (TokenInvalidException $e) {

	        $output['message'] = json(['token_invalid'], $e->getStatusCode());

	    } catch (\JWTException $e) {

	        $output['message'] = json(['token_absent'], $e->getStatusCode());

	    }

        return response()->json($output, 200);
    }
}
