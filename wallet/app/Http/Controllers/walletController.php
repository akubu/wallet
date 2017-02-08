<?php

namespace App\Http\Controllers;

use App\Model\credit;
use App\Model\creditMaster;
use App\Model\wallet;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;

class walletController extends Controller
{
    public function __construct()
    {
        // Apply the jwt.auth middleware to all methods in this controller
        // except for the authenticate method. We don't want to prevent
        // the user from retrieving their token if they don't already have it
        $this->middleware('jwt.auth');
    }



    /**
     * @SWG\post(
     *     path="/api/credit",
     *     summary="Add credit",
     *     tags={"Credit"},
     *     description="Muliple tags can be provided with comma separated strings. Use tag1, tag2, tag3 for testing.",
     *     operationId="credit",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="token",
     *         in="query",
     *         description="JWT Token",
     *         required=true,
     *         type="string",
     *         @SWG\Schema(type="string"),
     *         collectionFormat="multi"
     *     ),
     *     @SWG\Parameter(
     *         name="Body",
     *         in="body",
     *         description="P2s ID",
     *         required=true,
     *         type="string",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/wallet")
     *         ),
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

    public function credit(Request $request)
    {
//        dd($request->creditType);
//        dd($request->creditType);
        DB::beginTransaction();
        try {
            $newCredit = new credit();
            $newCredit->P2S_id = $request->p2sID;
            $credit_type = strtolower($request->creditType);
//            dd(creditMaster::where('credit_type', '=', $credit_type)->pluck('id'));
            if ((creditMaster::where('credit_type', '=', $credit_type)->first())) {
//               dd($idx);
                $idx=creditMaster::where('credit_type', '=', $credit_type)->pluck('id');
                $newCredit->credit_type = $idx[0];
//                dd($newCredit->credit_type);
            }
            else {
                try{
                    $newCreditMaster = new creditMaster();
                    $newCreditMaster->credit_type = $request->creditType;
                    $newCreditMaster->save();
                    $idx=creditMaster::where('credit_type', '=', $credit_type)->pluck('id');
//                    dd($idx);
                    $newCredit->credit_type=$idx[0];
                }
                catch (QueryException $exception){
                    DB::rollback();
                    return $exception;
                }

            }
            $newCredit->credit_amount=$request->creditAmount;
            $newCredit->requested_by=$request->requestedBy;
            $newCredit->order_id=$request->orderID;
            $newCredit->save();
            if((wallet::where('P2S_id','=',$request->p2sID)->first())){
                try{
                    $current_balance=wallet::where('P2S_id','=',$request->p2sID)->pluck('balance');
//                                        dd($current_balance);
                    $new_balance=$current_balance[0]+$request->creditAmount;
                    wallet::where('P2S_id','=',$request->p2sID)->update(['balance'=>$new_balance]);
                }
                catch (QueryException $exception){
                    DB::rollback();
                    return $exception;
                }
            }
            else{
                try{
                    $newWalletEntry=new wallet();
                    $newWalletEntry->P2S_id=$request->p2sID;
                    $newWalletEntry->balance=$request->creditAmount;
                    $newWalletEntry->locked_amount=0;
                    $newWalletEntry->save();
                }
                catch (QueryException $exception){
                    DB::rollback();
                    return $exception;
                }

            }
        }
        catch(QueryException $exception){
            DB::rollback();
            return $exception;

        }
        DB::commit();
        $status="successfull";
        return $status;
    }
    //
}
