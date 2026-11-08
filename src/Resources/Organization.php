<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Resources;

use Szabrob\LaravelBillingo\Data\Organization\OrganizationData;

final class Organization extends Resource
{
    public function get(): OrganizationData
    {
        return OrganizationData::from($this->json($this->connector->get('organization')));
    }
}
