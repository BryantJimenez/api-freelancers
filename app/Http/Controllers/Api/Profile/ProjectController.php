<?php

namespace App\Http\Controllers\Api\Profile;

use App\Models\User;
use App\Models\Project;
use App\Models\Payment\Payment;
use App\Models\Payment\Stripe;
use App\Models\Payment\Paypal;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Project\ApiProjectUpdateRequest;
use App\Http\Requests\Api\Payment\ApiProjectPaymentStoreRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\StripeTrait;
use App\Traits\PaypalTrait;
use Auth;
use Arr;

class ProjectController extends ApiController
{
	use StripeTrait, PaypalTrait;

    /**
    *
    * @OA\Get(
    *   path="/api/v1/profile/projects",
    *   tags={"Profile Projects"},
    *   summary="Get projects",
    *   description="Returns all projects",
    *   operationId="indexProfileProject",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Response(
    *       response=200,
    *       description="Show all projects.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   )
    * )
    */
    public function index() {
    	$projects=Project::with(['user', 'employer', 'proposal', 'payment'])->where('user_id', Auth::id())->orWhere('employer_id', Auth::id())->get()->map(function($project) {
    		return $this->dataProject($project);
    	});

    	$page=Paginator::resolveCurrentPage('page');
        $pagination=new LengthAwarePaginator($projects, $total=count($projects), $perPage=15, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']);
        $pagination=Arr::collapse([$pagination->toArray(), ['code' => 200, 'status' => 'success']]);

        return response()->json($pagination, 200);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/profile/projects/{id}",
    *   tags={"Profile Projects"},
    *   summary="Get project",
    *   description="Returns a single project",
    *   operationId="showProfileProject",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID project",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Show project.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=404,
    *       description="No results found."
    *   )
    * )
    */
    public function show(Project $project) {
        if ($project->user_id!=Auth::id() && $project->employer_id!=Auth::id()) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This project does not belong to this user.'], 403);
        }

        $project=$this->dataProject($project);
        return response()->json(['code' => 200, 'status' => 'success', 'data' => $project], 200);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/profile/projects/{id}",
    *   tags={"Profile Projects"},
    *   summary="Update project",
    *   description="Update a single project",
    *   operationId="updateProfileProject",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID project",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="start",
    *       in="query",
    *       description="Date start of project, format Y-m-d",
    *       required=true,
    *       @OA\Schema(
    *           type="string",
    *           format="date"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="end",
    *       in="query",
    *       description="Date end of project, format Y-m-d",
    *       required=false,
    *       @OA\Schema(
    *           type="string",
    *           format="date"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="amount",
    *       in="query",
    *       description="Amount of project",
    *       required=true,
    *       @OA\Schema(
    *           type="number",
    *           format="double"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="content",
    *       in="query",
    *       description="Content of project",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Update project.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=403,
    *       description="Forbidden."
    *   ),
    *   @OA\Response(
    *       response=422,
    *       description="Data not valid."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
    */
    public function update(ApiProjectUpdateRequest $request, Project $project) {
        if ($project->user_id!=Auth::id() && $project->employer_id!=Auth::id()) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This project does not belong to this user.'], 403);
        }

        if ($project->state=='Finalized') {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This project has already been finalized, you cannot edit it.'], 200);
        }

        if ($project->state=='Cancelled') {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This project has already been cancelled, you cannot edit it.'], 200);
        }

        if ($project->pay_state=='Paid') {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This project has already been pay, you cannot edit it.'], 200);
        }

        $data=array('start' => request('start'), 'end' => request('end'), 'content' => request('content'), 'amount' => request('amount'));
        $project->fill($data)->save();
        if ($project) {
            $project=Project::with(['user', 'employer', 'proposal', 'payment'])->where('id', $project->id)->first();
            $project=$this->dataProject($project);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The project has been edited successfully.', 'data' => $project], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/profile/projects/{id}/cancel",
    *   tags={"Profile Projects"},
    *   summary="Cancel project",
    *   description="Cancel a single project",
    *   operationId="cancelProfileProject",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID project",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Cancel project.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=403,
    *       description="Forbidden."
    *   ),
    *   @OA\Response(
    *       response=404,
    *       description="No results found."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
     */
    public function cancel(Request $request, Project $project) {
        if ($project->user_id!=Auth::id() && $project->employer_id!=Auth::id()) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This project does not belong to this user.'], 403);
        }

        if ($project->state=='Finalized') {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This project has already been finalized, you cannot cancel it.'], 200);
        }

        $project->fill(['state' => "0"])->save();
        if ($project) {
            if ($project->pay_state=='Paid') {
                $employer=User::where('id', $project->employer_id)->first();
                if (!is_null($employer)) {
                    $balance=$employer->balance+$project->amount;
                    $employer->fill(['balance' => $balance])->save();
                }
            }

            $project=$this->dataProject($project);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The project has been successfully cancelled.', 'data' => $project], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Put(
    *   path="/api/v1/profile/projects/{id}/finalize",
    *   tags={"Profile Projects"},
    *   summary="Finalize project",
    *   description="Finalize a single project",
    *   operationId="finalizeProfileProject",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID project",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Finalize project.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=403,
    *       description="Forbidden."
    *   ),
    *   @OA\Response(
    *       response=404,
    *       description="No results found."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
     */
    public function finalize(Request $request, Project $project) {
        if ($project->employer_id!=Auth::id()) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This project cannot be finalized by this user.'], 403);
        }

        if ($project->pay_state=='Pending' || $project->pay_state=='Unpaid') {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This project has not been paid, you cannot finish it.'], 403);
        }

        $project->fill(['state' => "1"])->save();
        if ($project) {
            $project=$this->dataProject($project);
            return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The project has been successfully finalized.', 'data' => $project], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Post(
    *   path="/api/v1/profile/projects/{id}/pay",
    *   tags={"Profile Projects"},
    *   summary="Pay project",
    *   description="Pay project",
    *   operationId="payProfileProject",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID project",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="method",
    *       in="query",
    *       description="Method of payment (1=Stripe, 2=PayPal, 3=Wallet)",
    *       required=true,
    *       @OA\Schema(
    *           type="string",
    *           enum={"1", "2", "3"}
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="stripe_token",
    *       in="query",
    *       description="Stripe token of payment (It is required if paying with Stripe)",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="url_success",
    *       in="query",
    *       description="Url success of PayPal",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="url_cancel",
    *       in="query",
    *       description="Url cancel of PayPal",
    *       required=false,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Pay project.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=403,
    *       description="Forbidden."
    *   ),
    *   @OA\Response(
    *       response=422,
    *       description="Data not valid."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
    */
    public function pay(ApiProjectPaymentStoreRequest $request, Project $project) {
        $user=User::where('id', Auth::id())->first();
        $subject="Payment for a new project: ".$project->name.".";

        if ($project->employer_id!=$user->id) {
            return response()->json(['code' => 403, 'status' => 'error', 'message' => 'This project does not belong to this user, you cannot pay for it.'], 403);
        }

        if (request('method')=='1') {
            $response=$this->payWithStripe($project->amount, 'USD', $subject, request('stripe_token'));
            if ($response['status']=='success') {
                $fee=$this->stripeFee($response['data']['balance_transaction']);
                $balance=$project->amount-$fee;

                $data=array('subject' => $subject, 'total' => $project->amount, 'fee' => $fee, 'balance' => $balance, 'method' => '1', 'type' => '2', 'state' => '1', 'user_id' => Auth::id());
                $payment=Payment::create($data);

                if ($payment) {
                    $data=array('stripe_payment_id' => $response['data']['charge']->id, 'balance_transaction' => $response['data']['charge']->balance_transaction, 'payment_id' => $payment->id);
                    Stripe::create($data);

                    $project->fill(['pay_state' => '1', 'payment_id' => $payment->id])->save();
                    if ($project) {
                        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The project has been successfully pay.'], 200);
                    }
                }
            }
        }

        if (request('method')=='2') {
            $response=$this->payWithPaypal($project->amount, Auth::user()->email, Auth::user()->name, Auth::user()->lastname, request('url_success'), request('url_cancel'));
            if ($response['status']=='success') {
                $data=array('href' => $response['data']['links'][1]['href'], 'method' => $response['data']['links'][1]['method']);
                return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The payment order has been successfully created.', 'data' => $data], 200);
            }
        }

        if (request('method')=='3') {
            if ($user->balance>=$project->amount) {
                $data=array('subject' => $subject, 'total' => $project->amount, 'fee' => 0.00, 'balance' => $project->amount, 'method' => '3', 'type' => '2', 'state' => '1', 'user_id' => Auth::id());
                $payment=Payment::create($data);

                if ($payment) {
                    $project->fill(['pay_state' => '1', 'payment_id' => $payment->id])->save();
                    if ($project) {
                        $balance=$user->balance-$project->amount;
                        $user->fill(['balance' => $balance])->save();

                        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The project has been successfully pay.'], 200);
                    }
                }
            } else {
                return response()->json(['code' => 200, 'status' => 'error', 'message' => 'The money in your wallet is not enough to make the payment.'], 200);
            }
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }

    /**
    *
    * @OA\Get(
    *   path="/api/v1/profile/projects/{id}/success",
    *   tags={"Profile Projects"},
    *   summary="Complete pay",
    *   description="Complete pay of project with PayPal",
    *   operationId="successProfileProject",
    *   security={
    *       {"bearerAuth": {}}
    *   },
    *   @OA\Parameter(
    *       name="id",
    *       in="path",
    *       description="Search for ID project",
    *       required=true,
    *       @OA\Schema(
    *           type="integer"
    *       )
    *   ),
    *   @OA\Parameter(
    *       name="token",
    *       in="query",
    *       description="Token of order of PayPal",
    *       required=true,
    *       @OA\Schema(
    *           type="string"
    *       )
    *   ),
    *   @OA\Response(
    *       response=200,
    *       description="Pay with PayPal complete.",
    *       @OA\MediaType(
    *           mediaType="application/json"
    *       )
    *   ),
    *   @OA\Response(
    *       response=401,
    *       description="Not authenticated."
    *   ),
    *   @OA\Response(
    *       response=422,
    *       description="Data not valid."
    *   ),
    *   @OA\Response(
    *       response=500,
    *       description="An error occurred during the process."
    *   )
    * )
    */
    public function success(Request $request, Project $project) {
        if (is_null(request('token'))) {
            return response()->json(['code' => 422, 'status' => 'error', 'message' => 'The data sent was not valid.', 'errors' => ['token' => ['The token field is required.']]], 422);
        }

        $exists=Paypal::where([['paypal_payment_id', request('token')], ['paypal_payer_id', '!=', NULL]])->exists();
        if ($exists) {
            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'This payment has already been confirmed previously'], 200);
        }

        $order=$this->orderSuccess(request('token'));

        if ($order['status']=='success') {
            if ($order['data']['status']=='COMPLETED') {
                $subject="Payment for a new project: ".$project->name.".";
                $data=array('subject' => $subject, 'total' => $order['data']['amount']['gross_amount']['value'], 'fee' => $order['data']['amount']['paypal_fee']['value'], 'balance' => $order['data']['amount']['net_amount']['value'], 'method' => '2', 'type' => '2', 'state' => '1', 'user_id' => Auth::id());
                $payment=Payment::create($data);

                if ($payment) {
                    $data=array('paypal_payment_id' => $order['data']['order_id'], 'paypal_payer_id' => $order['data']['payer_id'], 'data' => json_encode([]), 'payment_id' => $payment->id);
                    Paypal::create($data);

                    $project->fill(['pay_state' => '1', 'payment_id' => $payment->id])->save();
                    if ($project) {
                        return response()->json(['code' => 200, 'status' => 'success', 'message' => 'The project has been successfully pay.'], 200);
                    }
                }

                return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
            }

            return response()->json(['code' => 200, 'status' => 'error', 'message' => 'Payment was not completed, please try again.'], 200);
        }

        return response()->json(['code' => 500, 'status' => 'error', 'message' => 'An error occurred during the process, please try again.'], 500);
    }
}
