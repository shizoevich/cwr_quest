<?php

namespace App\Jobs\Square;

use App\Models\Square\SquareCatalogItem;
use App\Repositories\Square\ApiRepositoryInterface as SquareApiRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Square\Models\CatalogObject;

class GetCatalogItems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var SquareApiRepositoryInterface
     */
    private $squareApi;
    
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->squareApi = app()->make(SquareApiRepositoryInterface::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $catalogObjects = $this->squareApi->getCatalogObjects();
        $this->saveCatalogItems($catalogObjects);
    }
    
    /**
     * @param CatalogObject[] $catalogObjects
     */
    protected function saveCatalogItems($catalogObjects) {
        foreach($catalogObjects as $catalogObject) {
            $catalogItem = $catalogObject->getItemData();
            if (isset($catalogItem)) {
                $variations = $catalogItem->getVariations();
                if (isset($variations) && count($variations)) {
                    SquareCatalogItem::updateOrCreate([
                        'external_id' => $variations[0]->getId(),
                    ], [
                        'name' => $catalogItem->getName(),
                    ]);
                }
            }
        }
    }
}
