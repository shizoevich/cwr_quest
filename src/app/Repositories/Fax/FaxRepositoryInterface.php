<?php

namespace App\Repositories\Fax;

use App\Models\FaxModel\Fax;
use Illuminate\Pagination\LengthAwarePaginator;

interface FaxRepositoryInterface
{
    public function getFaxesForEntity($entity): array;

    public function getFaxesData(LengthAwarePaginator $faxCollection): array;

    public function attachFax(array $data, $entity, Fax $fax): array;

    public function createAndAttachFaxDocument(array $data, $entity, Fax $fax);

    public function detachFax($entity, $fax): array;

    public function deleteFaxCommentsAndDocuments($entity, $fax): bool;
}
