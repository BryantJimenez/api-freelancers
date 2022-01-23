<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Exception;
use Auth;

trait PaypalTrait {
	private $base_uri;
	private $public;
	private $secret;
	private $access_token;
	private $url_success;
	private $url_cancel;

	public function initPayPal() {
		$setting=Setting::first();
		if (env('PAYPAL_MODE')=='live') {
			$this->base_uri='https://api-m.paypal.com';
		} else {
			$this->base_uri='https://api-m.sandbox.paypal.com';
		}
		if (!is_null($setting) && !is_null($setting->paypal_public) && !is_null($setting->paypal_secret)) {
			$this->public=$setting->paypal_public;
			$this->secret=$setting->paypal_secret;
			$result=$this->getAccessTokenPaypal();
			if ($result['status']=='success') {
				$this->access_token=$result['data']['access_token'];
			}
		}
	}

	private function getAccessTokenPaypal() {
		try {
			$http=new Client(['base_uri' => $this->base_uri]);
			$response=$http->request('POST', '/v1/oauth2/token', [
				'headers' => [
					'Accept' => 'application/json',
					'Content-Type' => 'application/x-www-form-urlencoded'
				],
				'body' => 'grant_type=client_credentials',
				'auth' => [$this->public, $this->secret, 'basic']
			]);

			$data=json_decode($response->getBody(), true);
			$result=array('status' => 'success', 'data' => $data);
		} catch (ClientException $e) {
			Log::error("PayPal Client Exception: ".$e->getMessage());
			$result=array('status' => 'error', 'message' => $e->getMessage());
		} catch (Exception $e) {
			Log::error("PayPal Exception: ".$e->getMessage());
			$result=array('status' => 'error', 'message' => $e->getMessage());
		}

		return $result;
	}

	public function payWithPaypal($total, $email, $name, $lastname, $urlSuccess=NULL, $urlCancel=NULL) {
		$this->initPayPal();

		$this->url_success=(is_null($urlSuccess)) ? env('PAYPAL_SUCCESS') : $urlSuccess;
		$this->url_cancel=(is_null($urlCancel)) ? env('PAYPAL_CANCEL') : $urlCancel;

		try {
			$order=$this->createOrderPaypal($total, $email, $name, $lastname);
		} catch (Exception $e) {
			Log::error("PayPal Exception: ".$e->getMessage());
			return array('status' => 'error', 'message' => $e->getMessage());
		}

		return $order;
	}

	public function createOrderPaypal($price, $email, $name, $lastname) {
		try {
			$http=new Client(['base_uri' => $this->base_uri]);
			$response=$http->request('POST', '/v2/checkout/orders', [
				'headers' => [
					'Accept' => 'application/json',
					'Content-Type' => 'application/json',
					'Authorization' => "Bearer ".$this->access_token
				],
				'json' => [
					"intent" => "CAPTURE",
					"application_context" => [
						"shipping_preference" => "NO_SHIPPING",
						"return_url" => $this->url_success,
						"cancel_url" => $this->url_cancel
					],
					"payer" => [
						"email_address" => $email,
						"name" => [
							"given_name" => $name,
							"surname" => $lastname
						]
					],
					"purchase_units" => [
						[
							"amount" => [
								"currency_code" => "USD",
								"value" => $price
							]
						]
					]
				]
			]);

			$data=json_decode($response->getBody(), true);
			$result=array('status' => 'success', 'data' => $data);
		} catch (ClientException $e) {
			Log::error("PayPal Client Exception: ".$e->getMessage());
			$result=array('status' => 'error', 'message' => $e->getMessage());
		} catch (Exception $e) {
			Log::error("PayPal Exception: ".$e->getMessage());
			$result=array('status' => 'error', 'message' => $e->getMessage());
		}

		return $result;
	}

	public function orderSuccess($orderId) {
		$this->initPayPal();

		$order=$this->getOrder($orderId);

		if ($order['status']=='success') {
			if ($order['data']['status']=='APPROVED') {
				$capture=$this->captureOrder($orderId);
			} elseif ($order['data']['status']=='COMPLETED') {
				$capture=$order;
			}

			if (isset($capture) && $capture['status']=='success') {
				$data=array('status' => $capture['data']['status'], 'order_id' => $capture['data']['id'], 'capture_id' => $capture['data']['purchase_units'][0]['payments']['captures'][0]['id'], 'amount' => $capture['data']['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown'], 'payer_id' => $capture['data']['payer']['payer_id']);
				$result=array('status' => 'success', 'data' => $data);
			} else {
				$result=$capture;
			}
		} else {
			$result=$order;
		}

		return $result;
	}

	public function getOrder($orderId) {
		try {
			$http=new Client(['base_uri' => $this->base_uri]);
			$response=$http->request('GET', '/v2/checkout/orders/'.$orderId, [
				'headers' => [
					'Accept' => 'application/json',
					'Content-Type' => 'application/json',
					'Authorization' => "Bearer ".$this->access_token
				]
			]);

			$data=json_decode($response->getBody(), true);
			$result=array('status' => 'success', 'data' => $data);
		} catch (Exception $e) {
			Log::error("PayPal Exception: ".$e->getMessage());
			$result=array('status' => 'error', 'message' => $e->getMessage());
		}

		return $result;
	}

	public function captureOrder($orderId) {
		try {
			$http=new Client(['base_uri' => $this->base_uri]);
			$response=$http->request('POST', '/v2/checkout/orders/'.$orderId.'/capture', [
				'headers' => [
					'Accept' => 'application/json',
					'Content-Type' => 'application/json',
					'Authorization' => "Bearer ".$this->access_token
				]
			]);

			$data=json_decode($response->getBody(), true);
			$result=array('status' => 'success', 'data' => $data);
		} catch (Exception $e) {
			Log::error("PayPal Exception: ".$e->getMessage());
			$result=array('status' => 'error', 'message' => $e->getMessage());
		}

		return $result;
	}
}