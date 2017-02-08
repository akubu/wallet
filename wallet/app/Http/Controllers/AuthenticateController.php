<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests;

class AuthenticateController extends Controller
{
    /**
     * @SWG\Swagger(
     *     schemes={"http","https"},
     *     host="localhost:8000",
     *     basePath="/",
     *     @SWG\Info(
     *         version="1.0.0",
     *         title="Wallet API",
     *         description="This API is used by different P2S systems for handling wallet transactions",
     *         termsOfService="",
     *         @SWG\Contact(
     *             email="akshay.singh@power2sme.com"
     *         ),
     *         @SWG\License(
     *             name="power2sme pvt ltd.",
     *             url="power2sme.com"
     *         )
     *     ),
     *     @SWG\ExternalDocumentation(
     *         description="Find out more about my website",
     *         url="https://www.power2sme.com"
     *     )
     * )
     */

    /**
     * @SWG\post(
     *     path="/api/authenticate",
     *     summary="Get JWT token",
     *     tags={"Get JWT token"},
     *     description="Muliple tags can be provided with comma separated strings. Use tag1, tag2, tag3 for testing.",
     *     operationId="authenticate",
     *     consumes={"multipart/form-data"},
     *     produces={"multipart/form-data"},
     *     @SWG\Parameter(
     *         name="email",
     *         in="query",
     *         description="user email",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi"
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="query",
     *         description="user password",
     *         required=true,
     *         type="string",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation"
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="error",
     *     )
     * )
     */

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            // verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // if no errors are encountered we can return a JWT
        return response()->json(compact('token'));
    }
    //
}
