<?php

namespace App\ICarryShipping;

use Illuminate\Support\Facades\Log;

class ICarryConfig {
	const GET_TOKEN_URL = 'api-frontend/Authenticate/GetTokenForCustomerApi';
	const GET_RATES_URL = 'api-frontend/SmartwareShipment/EstimateRatesByCOD';
	const CREATE_ORDER_URL = 'api-frontend/SmartwareShipment/CreateOrder';

}
