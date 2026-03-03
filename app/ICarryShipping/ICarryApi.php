<?php
namespace App\ICarryShipping;

use Illuminate\Support\Facades\Log;
use App\Models\Credential;
use App\ICarryShipping\ICarryConfig;

class ICarryApi
{

    private function call( string $url, array $parameters = array(), array $headers = array( 'Content-Type: application/json' ) ) {
		$curl = curl_init();

		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_POST, 1 );

		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );

		curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $parameters ) );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );

		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		$response = curl_exec( $curl );

		$error = curl_error( $curl );
		if ( $error ) {
			Log::debug('------ Start ICarryAPI-Error -------');
			Log::debug($error);
			Log::debug('------ End ICarryAPI-Error -------');
		}

		curl_close( $curl );

		if ( is_array( json_decode( $response, true ) ) ) {
			$response = json_decode( $response, true );
		}

		return $response;
	}


	public function getToken($user_info): array {

        // Log::debug("get token [user_info]=====> ");
        // Log::debug($user_info);
        // Log::debug($user_info);
		$storeUrl = $user_info['store_url'];
		$email = $user_info['email'];
		$password = $user_info['password'];
		$apiUrl = $storeUrl . ICarryConfig::GET_TOKEN_URL;


		// Log::debug('email => ' . $email);
		$response = $this->call(
			$apiUrl,
			array(
				'Email'    => $email,
				'Password' => $password,
			)
		);

		if ( isset( $response['message'] ) && ! empty( $response['message'] ) ) {
			Log::debug('---------- ICarryAPI Get Token - Response Error  -------------');
			Log::debug($response);
			return array(
				'type'    => 'error',
				'message' => $response['message'],
			);
		}

		if ( isset( $response['token'] ) && ! empty( $response['token'] ) ) {
			// Log::debug('---------- ICarryAPI Get Token - Response Success  -------------');
			// Log::debug($response);
			return array(
				'type'    => 'success',
				'message' => $response['token'],
			);
		}

		// Logs::log( 'ICarryAPI-Error-getToken', json_encode( $response ) );
		Log::debug('ICarryAPI-Error-getToken');
		Log::debug($response);

		return array(
			'type'    => 'error',
			'message' => 'iCarry API call error',
		);
	}

	public function getRates( string $iCarryStoreUrl, string $token, array $request ): array {
		$apiUrl = $iCarryStoreUrl . ICarryConfig::GET_RATES_URL;

		$response = $this->call(
			$apiUrl,
			$request,
			array(
				'Content-Type: application/json',
				'Authorization: Bearer ' . $token,
			)
		);

		// Logs::log(
		// 	'getRates-Request',
		// 	json_encode( $request )
		// );

		if ( isset( $response['message'] ) && ! empty( $response['message'] ) ) {
			Log::debug('---------- ICarryAPI Get Rates - Response Error  -------------');
			Log::debug($response);
			return array(
				'type'    => 'error',
				'message' => $response['message'],
			);
		}

		if ( isset( $response[0]['Name'] ) && ! empty( $response[0]['Name'] ) ) {
			// Log::debug('---------- ICarryAPI Get Rates - Response Success  -------------');
			// Log::debug($response);
			return array(
				'type'    => 'success',
				'message' => $response,
			);
		}

		Log::debug('---------- ICarryAPI-Error-getRates  -------------');
		Log::debug($response);

		return array(
			'type'    => 'error',
			'message' => 'iCarry API call error',
		);
	}



	public function createOrder( string $iCarryStoreUrl, string $token, array $request ): array {
		// Log::debug('inside create order - before');
		$apiUrl = $iCarryStoreUrl . ICarryConfig::CREATE_ORDER_URL;

		$response = $this->call(
			$apiUrl,
			$request,
			array(
				'Content-Type: application/json',
				'Authorization: Bearer ' . $token,
			)
		);

		if ( isset( $response['message'] ) && ! empty( $response['message'] ) ) {
			Log::debug('---------- ICarryAPI createOrder - Response Error  -------------');
			Log::debug($response);
			return array(
				'type'    => 'error',
				'message' => $response['message'],
			);
		}

		if ( isset( $response['TrackingNumber'] ) && ! empty( $response['TrackingNumber'] ) ) {
			// Log::debug('---------- ICarryAPI createOrder - Response Success  -------------');
			// Log::debug($response);
			return array(
				'type'    => 'success',
				'message' => $response,
			);
		}

		Log::debug('---------- ICarryAPI-Error-createOrder  -------------');
		Log::debug($response);

		return array(
			'type'    => 'error',
			'message' => 'iCarry API call error',
		);
	}

}
