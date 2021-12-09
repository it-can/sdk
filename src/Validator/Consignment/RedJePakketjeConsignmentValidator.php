<?php

declare(strict_types=1);

namespace MyParcelNL\Sdk\src\Validator\Consignment;

use MyParcelNL\Sdk\src\Rule\Consignment\DeliveryDateRule;
use MyParcelNL\Sdk\src\Rule\Consignment\DropOffPointRule;
use MyParcelNL\Sdk\src\Rule\Consignment\LocalCountryOnlyRule;
use MyParcelNL\Sdk\src\Rule\Consignment\ShipmentOptionsRule;

class RedJePakketjeConsignmentValidator extends AbstractConsignmentValidator
{
    /**
     * @inheritDoc
     */
    protected function getRules(): array
    {
        return parent::getRules() + [
                new DeliveryDateRule(),
                new DropOffPointRule(),
                new LocalCountryOnlyRule(),
                new ShipmentOptionsRule(),
            ];
    }
}
