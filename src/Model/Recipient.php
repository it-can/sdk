<?php

declare(strict_types=1);

namespace MyParcelNL\Sdk\src\Model;

/**
 * @property string|null $cc
 * @property string|null $city
 * @property string|null $company
 * @property string|null $email
 * @property string|null $person
 * @property string|null $phone
 * @property string|null $postalCode
 * @property string|null $street
 */
class Recipient extends BaseModel
{
    /**
     * @var string[]
     */
    protected $attributes = [
        'cc'         => null,
        'city'       => null,
        'company'    => null,
        'email'      => null,
        'person'     => null,
        'phone'      => null,
        'postalCode' => null,
        'street'     => null,
    ];
}
