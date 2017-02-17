<?php

namespace App\Http\Controllers;

use App\Model\credit;
use App\Model\creditMaster;
use App\Model\debit;
use App\Model\lock;
use App\Model\unlock;
use App\Model\wallet;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Psy\Exception\ErrorException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
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
     *         description="Body data",
     *         required=true,
     *         type="string",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/wallet")
     *         ),
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="successful"
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Network error",
     *     ),
     *      @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */

    public function credit(Request $request)
    {
//        $amount=credit::where('id','=','5')->pluck('credit_amount');
//        return (Crypt::decrypt($amount[0]));
        DB::beginTransaction();
        try {
            $newCredit = new credit();
            $newCredit->P2S_id = $request->p2sID;
//            $credit_type = strtolower($request->creditType);
//            dd(creditMaster::where('credit_type', '=', $credit_type)->pluck('id'));
//            if ((creditMaster::where('credit_type', '=', $credit_type)->first())) {
//               dd($idx);
//                $idx=creditMaster::where('credit_type', '=', $credit_type)->pluck('id');
//                $newCredit->credit_type = $idx[0];
//                dd($newCredit->credit_type);
//            }
//            else {
//                try{
//                    $newCreditMaster = new creditMaster();
//                     $newCreditMaster->credit_type = $request->creditType;
//                    $newCreditMaster->save();
//                    $idx=creditMaster::where('credit_type', '=', $credit_type)->pluck('id');
////                    dd($idx);
//                    $newCredit->credit_type=$idx[0];
//                }
//                catch (QueryException $exception){
//                    DB::rollback();
//                    return $exception;
//                }
//
//            }
            $newCredit->credit_type=$request->creditType;
            $newCredit->credit_amount=Crypt::encrypt($request->creditAmount);
            $newCredit->requested_by=$request->requestedBy;
            $newCredit->order_id=$request->orderID;
            $newCredit->save();
            if((wallet::where('P2S_id','=',$request->p2sID)->first())) {
                $current_balance = wallet::where('P2S_id', '=', $request->p2sID)->pluck('balance');
                $new_balance = Crypt::decrypt($current_balance[0]) + $request->creditAmount;
                $new_balance = Crypt::encrypt($new_balance);
                wallet::where('P2S_id', '=', $request->p2sID)->update(['balance' => $new_balance]);
            }
            else {
                $newWalletEntry = new wallet();
                $newWalletEntry->P2S_id = $request->p2sID;
                $newWalletEntry->balance = Crypt::encrypt($request->creditAmount);
                $newWalletEntry->locked_amount = 0;
                $newWalletEntry->save();
            }
            $status="successful";
            $currentBalance=wallet::where('P2S_id','=',$request->p2sID)->pluck('balance');
            $walletBalance=Crypt::decrypt($currentBalance[0]);
            $creditID=DB::table('credit')->max('id');
            $response=['status'=>$status,'currentBalance'=>$walletBalance,'creditID'=>$creditID];
            $json=\GuzzleHttp\json_encode($response);
            DB::commit();
            return $json;
        }catch (QueryException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }catch (EncryptException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }catch (DecryptException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }catch (\ErrorException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }


    }

    /**
     * @SWG\post(
     *     path="/api/debit",
     *     summary="Request for debit",
     *     tags={"Debit"},
     *     operationId="debit",
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
     *         description="Body data",
     *         required=true,
     *         type="string",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/wallet")
     *         ),
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="successful"
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Network error",
     *     ),
     *      @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */

    public function debit(Request $request)
    {
        DB::beginTransaction();
        try{
            if(wallet::where('P2S_id','=',$request->p2sID)->first()){
                $balance=wallet::where('P2S_id','=',$request->p2sID)->pluck('balance');
                $currentBalance=Crypt::decrypt($balance[0]);
                if($request->debitAmount>$currentBalance){
                    DB::rollback();
                    $response=['status'=>'unsuccessful','reason'=>'current balance is less than debit amount','currentBalance'=>$currentBalance];
                    $json=\GuzzleHttp\json_encode($response);
                    return $json;
                }
                $newDebit=new debit();
                $newDebit->P2S_id=$request->p2sID;
                $newDebit->debit_type=$request->debitType;
                $newDebit->debit_amount=$request->debitAmount;
                $newDebit->requested_by=$request->requestedBy;
                $newDebit->order_id=$request->orderID;
                $newDebit->save();
                $new_balance=Crypt::encrypt($currentBalance-$request->debitAmount);
                wallet::where('P2S_id','=',$request->p2sID)->update(['balance'=>$new_balance]);
                $status="successful";
                $currentBalance=wallet::where('P2S_id','=',$request->p2sID)->pluck('balance');
                $walletBalance=Crypt::decrypt($currentBalance[0]);
                $debitID=DB::table('debit')->max('id');
                $response=['status'=>$status,'currentBalance'=>$walletBalance,'debitID'=>$debitID];
                $json=\GuzzleHttp\json_encode($response);
                DB::commit();
                return $json;
            }
            else{
                DB::rollback();
                $response=['status'=>'unsuccessful','reason'=>'NO P2SID found for the request'];
                $json=\GuzzleHttp\json_encode($response);
                return $json;
            }

        }catch (QueryException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }catch (EncryptException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }catch (DecryptException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }catch (\ErrorException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }

    }
    /**
     * @SWG\post(
     *     path="/api/lock",
     *     summary="Request for lock",
     *     tags={"Lock Request"},
     *     operationId="Locking",
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
     *         description="Body data",
     *         required=true,
     *         type="string",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/wallet")
     *         ),
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="successful"
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Network error",
     *     ),
     *      @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function lock(Request $request)
    {
        DB::beginTransaction();
        try{
            if(wallet::where('P2S_id','=',$request->p2sID)->first()){
                $balance=wallet::where('P2S_id','=',$request->p2sID)->pluck('balance');
                $currentBalance=Crypt::decrypt($balance[0]);
                if($request->lockAmount>$currentBalance){
                    DB::rollback();
                    $response=['status'=>'unsuccessful','reason'=>'current balance is less than the requested locked amount','currentBalance'=>$currentBalance];
                    $json=\GuzzleHttp\json_encode($response);
                    return $json;
                }
                $newEntry=new lock();
                $newEntry->P2S_id=$request->p2sID;
                $newEntry->lock_type=$request->lockType;
                $newEntry->lock_amount=$request->lockAmount;
                $newEntry->requested_by=$request->requestedBy;
                $newEntry->save();
                $new_balance=Crypt::encrypt($currentBalance-$request->lockAmount);
                $lockedAmount=wallet::where('P2S_id','=',$request->p2sID)->pluck('locked_amount');
                if($lockedAmount[0]=='0'){
                    $newLockedAmount=Crypt::encrypt($request->lockAmount);
                }
                else{

                    $currentLockedAmount=Crypt::decrypt($lockedAmount[0]);
                    $newLockedAmount=Crypt::encrypt($currentLockedAmount+$request->lockAmount);
                }
                wallet::where('P2S_id','=',$request->p2sID)->update(['balance'=>$new_balance,'locked_amount'=>$newLockedAmount]);
                $status="successful";
                $currentBalance=wallet::where('P2S_id','=',$request->p2sID)->pluck('balance');
                $currentLockedAmount=wallet::where('P2S_id','=',$request->p2sID)->pluck('locked_amount');
                $walletBalance=Crypt::decrypt($currentBalance[0]);
                $lockedAmount=Crypt::decrypt($currentLockedAmount[0]);
                $lockID=DB::table('lock')->max('id');
                $response=['status'=>$status,'currentBalance'=>$walletBalance,'lockedAmount'=>$lockedAmount,'lockID'=>$lockID];
                $json=\GuzzleHttp\json_encode($response);
                DB::commit();
                return $json;
            }
            else{
                DB::rollback();
                $response=['status'=>'unsuccessful','reason'=>'NO P2SID found for the request'];
                $json=\GuzzleHttp\json_encode($response);
                return $json;
            }

        }catch (QueryException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }catch (EncryptException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }catch (DecryptException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }catch (\ErrorException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }

    }

    /**
     * @SWG\post(
     *     path="/api/unlock",
     *     summary="Request for unlock",
     *     tags={"Unlock Request"},
     *     operationId="Unlocking",
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
     *         description="Body data",
     *         required=true,
     *         type="string",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/wallet")
     *         ),
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="successful"
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Network error",
     *     ),
     *      @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function unlock(Request $request)
    {
        DB::beginTransaction();
        try{
            if(wallet::where('P2S_id','=',$request->p2sID)->first()){
                $balance=wallet::where('P2S_id','=',$request->p2sID)->pluck('locked_amount');
                $currentLockedAmount=Crypt::decrypt($balance[0]);
                if($currentLockedAmount=='0'){
                    DB::rollback();
                    $response=['status'=>'unsuccessful','reason'=>'current locked amount is less than the requested unlock amount','currentLockedAmount'=>0.00];
                    $json=\GuzzleHttp\json_encode($response);
                    return $json;
                }
                if($request->unlockAmount>$currentLockedAmount){
                    DB::rollback();
                    $response=['status'=>'unsuccessful','reason'=>'current locked amount is less than the requested unlock amount','currentLockedAmount'=>$currentLockedAmount];
                    $json=\GuzzleHttp\json_encode($response);
                    return $json;
                }
                $newEntry=new unlock();
                $newEntry->P2S_id=$request->p2sID;
                $newEntry->unlock_type=$request->unlockType;
                $newEntry->unlock_amount=$request->unlockAmount;
                $newEntry->requested_by=$request->requestedBy;
                $newEntry->save();
                $balance=wallet::where('P2S_id','=',$request->p2sID)->pluck('balance');
                $currentBalance=Crypt::decrypt($balance[0]);
                $new_balance=Crypt::encrypt($currentBalance+$request->unlockAmount);
                $lockedAmount=wallet::where('P2S_id','=',$request->p2sID)->pluck('locked_amount');
                $currentLockedAmount=Crypt::decrypt($lockedAmount[0]);
                $newLockedAmount=Crypt::encrypt($currentLockedAmount-$request->unlockAmount);
                wallet::where('P2S_id','=',$request->p2sID)->update(['balance'=>$new_balance,'locked_amount'=>$newLockedAmount]);
                $status="successful";
                $currentBalance=wallet::where('P2S_id','=',$request->p2sID)->pluck('balance');
                $currentLockedAmount=wallet::where('P2S_id','=',$request->p2sID)->pluck('locked_amount');
                $walletBalance=Crypt::decrypt($currentBalance[0]);
                $lockedAmount=Crypt::decrypt($currentLockedAmount[0]);
                $lockID=DB::table('unlock')->max('id');
                $response=['status'=>$status,'currentBalance'=>$walletBalance,'lockedAmount'=>$lockedAmount,'unlockID'=>$lockID];
                $json=\GuzzleHttp\json_encode($response);
                DB::commit();
                return $json;
            }
            else{
                DB::rollback();
                $response=['status'=>'unsuccessful','reason'=>'NO P2SID found for the request'];
                $json=\GuzzleHttp\json_encode($response);
                return $json;
            }

        }catch (QueryException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }catch (EncryptException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }catch (DecryptException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }catch (\ErrorException $exception){
            DB::rollback();
            $response=['status'=>'unsuccessful','reason'=>$exception->getMessage()];
            $json=\GuzzleHttp\json_encode($response);
            return $json;
        }
    }

    /**
     * @SWG\GET(
     *     path="/api/getWalletDetails",
     *     summary="Request for wallet Details",
     *     tags={"Wallet Detail Request"},
     *     operationId="wallet Details",
     *     consumes={"multipart/form-data"},
     *     produces={"multipart/form-data"},
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
     *         name="p2sID",
     *         in="query",
     *         description="P2S ID",
     *         required=true,
     *         type="string",
     *         @SWG\Schema(type="string"),
     *         collectionFormat="multi"
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="successful"
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Network error",
     *     ),
     *      @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */

    public function getWalletDetails(Request $request)
    {
        try {
            if (wallet::where('P2S_id', '=', $request->p2sID)->first()) {
                $details=wallet::where('P2S_id', '=', $request->p2sID)->first();
                $balance=Crypt::decrypt($details->balance);
                $locked_amount=Crypt::decrypt($details->locked_amount);
                $response=['status'=>'successful','p2sID'=>$request->p2sID,'balance'=>$balance,'lockedAmount'=>$locked_amount];
                $json= \GuzzleHttp\json_encode($response);
                return $json;
            }
            else{
                $response=['status'=>'unsuccessful','reason'=>'NO P2SID found for the request'];
                $json=\GuzzleHttp\json_encode($response);
                return $json;
            }
        } catch (QueryException $exception) {

            $response = ['status' => 'unsuccessful', 'reason' => $exception->getMessage()];
            $json = \GuzzleHttp\json_encode($response);
            return $json;
        } catch (EncryptException $exception) {

            $response = ['status' => 'unsuccessful', 'reason' => $exception->getMessage()];
            $json = \GuzzleHttp\json_encode($response);
            return $json;
        } catch (DecryptException $exception) {

            $response = ['status' => 'unsuccessful', 'reason' => $exception->getMessage()];
            $json = \GuzzleHttp\json_encode($response);
            return $json;
        } catch (\ErrorException $exception) {

            $response = ['status' => 'unsuccessful', 'reason' => $exception->getMessage()];
            $json = \GuzzleHttp\json_encode($response);
            return $json;
        }
    }

    /**
     * @SWG\GET(
     *     path="/api/getCreditDetails",
     *     summary="Request for credit Details",
     *     tags={"Credit Details request"},
     *     operationId="get credit details",
     *     consumes={"multipart/form-data"},
     *     produces={"multipart/form-data"},
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
     *         name="p2sID",
     *         in="query",
     *         description="P2S ID",
     *         required=true,
     *         type="string",
     *         @SWG\Schema(type="string"),
     *         collectionFormat="multi"
     *     ),
     *      @SWG\Parameter(
     *         name="level",
     *         in="query",
     *         description="if not provided return last 10 otherwise last N",
     *         required=false,
     *         type="integer",
     *         @SWG\Schema(type="integer"),
     *         collectionFormat="multi"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful"
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Network error",
     *     ),
     *      @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function getCreditDetails(Request $request)
    {
        try{
            if(wallet::where('P2S_id', '=', $request->p2sID)->first()){
                if($request->level){
                    $details=credit::where('P2S_id', '=', $request->p2sID)->orderBy('id','desc')->take($request->level)->get();
                }
                else{
                    $details=credit::where('P2S_id', '=', $request->p2sID)->orderBy('id','desc')->take(10)->get();
                }

                $response=array();
                foreach ($details as $detail){
                    array_push($response,['creditID'=>$detail['id'],'creditType'=>$detail['credit_type'],'creditAmount'=>Crypt::decrypt($detail['credit_amount']),'requestedBy'=>$detail['requested_by'],'orderID'=>$detail->order_id]);
                }
                $json=\GuzzleHttp\json_encode(['status'=>'successful','creditDetails'=>$response]);
                return $json;
            }
            else{
                $response=['status'=>'unsuccessful','reason'=>'NO P2SID found for the request'];
                $json=\GuzzleHttp\json_encode($response);
                return $json;
            }
        }catch (QueryException $exception) {

            $response = ['status' => 'unsuccessful', 'reason' => $exception->getMessage()];
            $json = \GuzzleHttp\json_encode($response);
            return $json;
        } catch (EncryptException $exception) {

            $response = ['status' => 'unsuccessful', 'reason' => $exception->getMessage()];
            $json = \GuzzleHttp\json_encode($response);
            return $json;
        } catch (DecryptException $exception) {

            $response = ['status' => 'unsuccessful', 'reason' => $exception->getMessage()];
            $json = \GuzzleHttp\json_encode($response);
            return $json;
        } catch (\ErrorException $exception) {

            $response = ['status' => 'unsuccessful', 'reason' => $exception->getMessage()];
            $json = \GuzzleHttp\json_encode($response);
            return $json;
        }
    }

    /**
     * @SWG\GET(
     *     path="/api/getDebitDetails",
     *     summary="Request for debit Details",
     *     tags={"debit details Request"},
     *     operationId="get debit details",
     *     consumes={"multipart/form-data"},
     *     produces={"multipart/form-data"},
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
     *         name="p2sID",
     *         in="query",
     *         description="P2S ID",
     *         required=true,
     *         type="string",
     *         @SWG\Schema(type="string"),
     *         collectionFormat="multi"
     *     ),
     *     @SWG\Parameter(
     *         name="level",
     *         in="query",
     *         description="if not provided return last 10 otherwise last N",
     *         required=false,
     *         type="integer",
     *         @SWG\Schema(type="integer"),
     *         collectionFormat="multi"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful"
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Network error",
     *     ),
     *      @SWG\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */

    public function getDebitDetails(Request $request)
    {
        try{
            if(wallet::where('P2S_id', '=', $request->p2sID)->first()){
                if($request->level){
                    $details=debit::where('P2S_id', '=', $request->p2sID)->orderBy('id','desc')->take($request->level)->get();
                }
                else{
                    $details=debit::where('P2S_id', '=', $request->p2sID)->orderBy('id','desc')->take(10)->get();
                }
                $response=array();
                foreach ($details as $detail){
                    array_push($response,['debitID'=>$detail['id'],'debitType'=>$detail['debit_type'],'debitAmount'=>$detail['debit_amount'],'requestedBy'=>$detail['requested_by'],'orderID'=>$detail->order_id]);
                }
                $json=\GuzzleHttp\json_encode(['status'=>'successful','debitDetails'=>$response]);
                return $json;
            }
            else{
                $response=['status'=>'unsuccessful','reason'=>'NO P2SID found for the request'];
                $json=\GuzzleHttp\json_encode($response);
                return $json;
            }
        }catch (QueryException $exception) {

            $response = ['status' => 'unsuccessful', 'reason' => $exception->getMessage()];
            $json = \GuzzleHttp\json_encode($response);
            return $json;
        } catch (EncryptException $exception) {

            $response = ['status' => 'unsuccessful', 'reason' => $exception->getMessage()];
            $json = \GuzzleHttp\json_encode($response);
            return $json;
        } catch (DecryptException $exception) {

            $response = ['status' => 'unsuccessful', 'reason' => $exception->getMessage()];
            $json = \GuzzleHttp\json_encode($response);
            return $json;
        } catch (\ErrorException $exception) {

            $response = ['status' => 'unsuccessful', 'reason' => $exception->getMessage()];
            $json = \GuzzleHttp\json_encode($response);
            return $json;
        }

    }
    //
}
