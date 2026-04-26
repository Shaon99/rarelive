<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait RecycleBinTrait
{
    /**
     * Automatically move the record to the recycle bin when it's being deleted.
     *
     * @return void
     */
    protected static function bootRecycleBinTrait()
    {
        static::deleting(function ($model) {
            $model->moveToRecycleBin();
        });
    }

    /**
     * Move the record to the recycle bin.
     *
     * @return void
     */
    public function moveToRecycleBin()
    {
        $data = $this->toArray();

        // Add related data if defined
        if (property_exists($this, 'recycleRelations')) {
            foreach ($this->recycleRelations as $relation) {
                if ($this->relationLoaded($relation)) {
                    $data[$relation] = $this->$relation->toArray();
                } else {
                    $data[$relation] = $this->$relation()->get()->toArray();
                }
            }
        }

        DB::table('recycle_bins')->insert([
            'model' => get_class($this),
            'data' => json_encode($data),
            'deleted_at' => now(),
        ]);
    }
}
