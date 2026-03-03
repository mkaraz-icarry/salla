<?php
namespace App\ICarryShipping;

use Illuminate\Support\Facades\Log;

class ICarry
{

    public static function orderDataToICarryRequest( $order ) {
		$orderId = $order->reference_id;
        $codAmount = 0;
        $codCurrency = "";
		$weight = 0;
		$length = 0;
		$width = 0;
		$height = 0;
		$carrierSystemName = "";
		$shippingMethodId = "";
		$shippingMethodDescription = "";
		$carrierPrice = 0;
		$isFreeShipping = false;

        // foreach ($order['payment_method'] as $item) {
        //     if ($item === 'zid_cod') {
        //         $codAmount = $order['total'];
        //         $codCurrency = $order['currency_code'];
        //         break;
        //     }
        // }
        if ($order->payment_method == 'cod') {
            $codAmount = $order->amounts->total->amount;
            $codCurrency = $order->currency;
        }
		//order_product_id
		$products = $order->items;


        // Prepare Parcel Details
        $weightUnit = $order->shipments[0]->total_weight->units;
        $actualWeight = 0;
        $parcelValue = 0;
        $parcelDescription = "";
        $parcel_dimensions_list = [];
        $notes = "";
		if ( isset( $products ) and ! empty( $products ) ) {
			foreach ( $products as $product ) {

                //Log::debug("--- Product ---" . json_encode($product));
                $parcel_info = array(
					'quantity' => $product->quantity,
					"weight"   => 1,
					"length"   => 1,
					"width"    => 1,
					"height"   => 1,
					"sku"   => $product->sku,
				);
                if (isset($product->weight)) {
                    $parcel_info['weight'] = ICarryHelper::convertWeight($product->weight, $weightUnit, "kg");
                    $parcel_info['weight'] = $parcel_info['weight'] * $product->quantity;
                }
                $actualWeight += $parcel_info['weight'];
                $parcelValue += $product->amounts->total->amount;

                $productOptions = "";
                if (isset($product->options) and !empty($product->options)) {
                    $productOptions = "(";
                    foreach($product->options as $productAttr) {
                        $productOptions = "{$productAttr->name}:{$productAttr->value->name}";
                    }
                    $productOptions .= ")";
                }

                $parcelDescription .= $product->quantity . " X ". $product->name . " $productOptions" ." \n";
                $notes .= $product->notes ." \n";

                array_push($parcel_dimensions_list, $parcel_info);
            }
        }



        // $this->log->write('products => ');
        // $this->log->write($products);

		// foreach ($products as $product) {
		// 	$product_qty = $product['quantity'];
		// 	/
        //     // $convertedWeight = ICarryHelper::convertWeight($product['weight']['value'], $product['weight']['unit'], "kg");

        //     $weight += round(($convertedWeight * $product_qty), 2);
        //     $length += (1 * $product_qty);
        //     $width += (1 * $product_qty);
        //     $height += (1 * $product_qty);
		// }

        //$weight = $order['weight'] / 1000;//weight in gram to kg
		//Log::debug("{$weight} , {$length}, {$width}, {$height}");

		//$parsed_url = parse_url($order['store_url']);



		$i_carry_request = array(
			'ExternalId' 			=> 'SALLA__' . $orderId,
			'ProcessOrder'           => false,
			'dropOffAddress'         => array(
				'FirstName'     => $order->customer->first_name,
				'LastName'      => $order->customer->last_name,
				'Email'         => $order->customer->email,
				'PhoneNumber'   => $order->customer->mobile_code . $order->customer->mobile,
				'Country'       => $order->shipping->address->country,
				'City'          => $order->shipping->address->city,
				'Address1'      => $order->shipping->address->shipping_address,
				'Address2'      => $order->shipping->address->street_number . $order->shipping->address->block,
				'ZipPostalCode' => $order->shipping->address->postal_code,
			),
			'CODAmount'              => number_format((float)$codAmount, 2),
			'COdCurrency'            => $codCurrency,
			'ActualWeight'           => $actualWeight,
			'PackageType'            => 'Parcel',
			'Length'                 => 0,
			'Width'                  => 0,
			'Height'                 => 0,
			'Notes'                  => $notes,
			'SystemShipmentProvider' => $carrierSystemName,
			'Price'                  => $carrierPrice,
			'MethodName'			 => $shippingMethodId,
			'MethodDescription'      => $shippingMethodDescription,

			"ParcelPackageValue"	 => $parcelValue,
			"ParcelPackageCurrency"  => $order->currency,
			"ParcelDescription"		 => $parcelDescription,
			"ParcelQuantity"		 => 0,
			'ParcelDimensionsList'   => $parcel_dimensions_list
		);


		// Log::debug("--- orderDataToICarryRequest ---" . json_encode($i_carry_request));
		return $i_carry_request;
	}
}
