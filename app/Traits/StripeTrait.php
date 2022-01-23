<?php

namespace App\Traits;

use App\Models\Setting;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;
use Exception;
use Auth;

trait StripeTrait {
	private $public;
	private $secret;
	private $stripe;

	public function initStripe() {
		$setting=Setting::first();
		if (!is_null($setting) && !is_null($setting->stripe_public) && !is_null($setting->stripe_secret)) {
			$this->public=$setting->stripe_public;
			$this->secret=$setting->stripe_secret;
			$this->stripe=new StripeClient(['api_key' => $this->secret]);
		}
	}

	public function payWithStripe($total, $currency, $subject, $token) {
		$this->initStripe();

		try {
			$data=$this->stripeTransaction($total, $currency, $subject, $token);
			$result=array('status' => 'success', 'data' => $data);
		} catch(CardException $e) {
			Log::error('Stripe Card Exception: The state is: '.$e->getHttpStatus().'\n'.'The type is: '.$e->getError()->type.'\n'.'The code is: '.$e->getError()->code.'\n'.'The parameter is: '.$e->getError()->param.'\n'.'The message is: '.$e->getError()->message);
			$result=array('status' => 'error', 'message' => 'The state is: '.$e->getHttpStatus().', The type is: '.$e->getError()->type.', The code is: '.$e->getError()->code.', The parameter is: '.$e->getError()->param.', The message is: '.$e->getError()->message);
		} catch (RateLimitException $e) {
			Log::error("Stripe Rate Limit Exception: ".$e->getMessage());
			$result=array('status' => 'error', 'message' => $e->getMessage());
		} catch (InvalidRequestException $e) {
			Log::error("Stripe Invalid Request Exception: ".$e->getMessage());
			$result=array('status' => 'error', 'message' => $e->getMessage());
		} catch (AuthenticationException $e) {
			Log::error("Stripe Authentication Exception: ".$e->getMessage());
			$result=array('status' => 'error', 'message' => $e->getMessage());
		} catch (ApiConnectionException $e) {
			Log::error("Stripe Api Connection Exception: ".$e->getMessage());
			$result=array('status' => 'error', 'message' => $e->getMessage());
		} catch (ApiErrorException $e) {
			Log::error("Stripe Api Error Exception: ".$e->getMessage());
			$result=array('status' => 'error', 'message' => $e->getMessage());
		} catch (Exception $e) {
			Log::error("Stripe Exception: ".$e->getMessage());
			$result=array('status' => 'error', 'message' => $e->getMessage());
		}

		return $result;
	}

	public function stripeTransaction($total, $currency, $subject, $token) {
		$user=Auth::user();
		if (is_null($user->stripe_id)) {
			$user->createOrGetStripeCustomer();
			$customer=$user->updateStripeCustomer([
				'name' => $user->name." ".$user->lastname,
				'phone' => $user->phone,
				'address' => $user->address,
				'source' => $token
			]);
		}

		$amount=str_replace(".", "", number_format($total, 2, '.', ''));
		$amount=(int)$amount;

		$charge=$this->stripe->charges->create([
			'amount' => $amount,
			'currency' => $currency,
			'description' => $subject,
			'source' => $token
		]);
		$balance_transaction=$this->stripe->balanceTransactions->retrieve($charge->balance_transaction);

		$data=array('charge' => $charge, 'balance_transaction' => $balance_transaction);
		return $data;
	}

	public function stripeFee($balance_transaction) {
		if (strlen($balance_transaction->fee)>=3) {
			$decimals=substr($balance_transaction->fee, -2);
			$integers=substr($balance_transaction->fee, 0, -2);
			$fee=$integers.".".$decimals;
		} elseif (strlen($balance_transaction->fee)==2) {
			$fee="0.".$balance_transaction->fee;
		} elseif (strlen($balance_transaction->fee)==1) {
			$fee="0.0".$balance_transaction->fee;
		} else {
			$fee=0.00;
		}
		return $fee;
	}
}