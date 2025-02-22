<?php

declare(strict_types=1);

namespace MyParcelNL\Sdk\src\Collection\Fulfilment;

use DateTime;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Concerns\HasApiKey;
use MyParcelNL\Sdk\src\Concerns\HasCountry;
use MyParcelNL\Sdk\src\Concerns\HasUserAgent;
use MyParcelNL\Sdk\src\Model\Fulfilment\AbstractOrder;
use MyParcelNL\Sdk\src\Model\Fulfilment\Order;
use MyParcelNL\Sdk\src\Model\MyParcelRequest;
use MyParcelNL\Sdk\src\Model\RequestBody;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Collection;

/**
 * @property \MyParcelNL\Sdk\src\Model\Fulfilment\Order[] $items
 */
class OrderCollection extends Collection
{
    use HasUserAgent;
    use HasApiKey;
    use HasCountry;

    /**
     * @param  string $apiKey
     * @param  array  $parameters
     *
     * @return self
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \Exception
     */
    public static function query(string $apiKey, array $parameters = []): self
    {
        $request = (new MyParcelRequest())
            ->setRequestParameters($apiKey)
            ->setQuery($parameters)
            ->sendRequest(
                'GET',
                MyParcelRequest::REQUEST_TYPE_ORDERS
            );

        return self::createCollectionFromResponse($request);
    }

    /**
     * @return self
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \Exception
     */
    public function save(): self
    {
        $requestBody = new RequestBody('orders', $this->createRequestBody());
        $request     = (new MyParcelRequest())
            ->setUserAgents($this->getUserAgent())
            ->setRequestParameters(
            $this->ensureHasApiKey(),
            $requestBody
            )
            ->sendRequest('POST', MyParcelRequest::REQUEST_TYPE_ORDERS);

        return self::createCollectionFromResponse($request);
    }

    /**
     * @return array[]
     */
    private function createRequestBody(): array
    {
        return $this->map(
            function (Order $order) {
                $deliveryOptions = $order->getDeliveryOptions();

                return [
                    'external_identifier'           => $order->getExternalIdentifier(),
                    'fulfilment_partner_identifier' => $order->getFulfilmentPartnerIdentifier(),
                    'order_date'                    => $order->getOrderDateString(AbstractOrder::DATE_FORMAT_FULL),
                    'invoice_address'               => $order->getInvoiceAddress()->toArrayWithoutNull(),
                    'order_lines'                   => $order->getOrderLines()->toArrayWithoutNull(),
                    'shipment'                      => [
                        'carrier'             => $deliveryOptions->getCarrierId(),
                        'recipient'           => $order->getRecipient()->toArrayWithoutNull(),
                        'options'             => $this->getShipmentOptions($deliveryOptions),
                        'pickup'              => $order->getPickupLocation() ? $order->getPickupLocation()->toArrayWithoutNull() : null,
                        'customs_declaration' => $order->getCustomsDeclaration(),
                    ],
                ];
            }
        )->toArrayWithoutNull();
    }

    /**
     * @param \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $deliveryOptions
     *
     * @return array
     * @throws \Exception
     */
    private function getShipmentOptions(AbstractDeliveryOptionsAdapter $deliveryOptions): array
    {
        $dateTime     = new DateTime($deliveryOptions->getDate());
        $deliveryDate = $deliveryOptions->getDate()
            ? $dateTime->format(AbstractOrder::DATE_FORMAT_FULL)
            : null;

        $shipmentOptions = $deliveryOptions->getShipmentOptions();

        $options = [
            'package_type'      => $deliveryOptions->getPackageTypeId(),
            'delivery_date'     => $deliveryDate,
            'signature'         => (int) $shipmentOptions->hasSignature(),
            'only_recipient'    => (int) $shipmentOptions->hasOnlyRecipient(),
            'age_check'         => (int) $shipmentOptions->hasAgeCheck(),
            'large_format'      => (int) $shipmentOptions->hasLargeFormat(),
            'return'            => (int) $shipmentOptions->isReturn(),
            'insurance'         => [
                'amount'    => $shipmentOptions->getInsurance(),
                'currency'  => 'EUR'
            ],
            'label_description' => (string) $shipmentOptions->getLabelDescription(),
        ];

        return $options;
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Model\MyParcelRequest $request
     *
     * @return self
     */
    private static function createCollectionFromResponse(MyParcelRequest $request): self
    {
        $orders     = Arr::get($request->getResult(), 'data.orders');
        $collection = new self($orders);

        return $collection->mapInto(Order::class);
    }
}
