<?php
/**
 * Created by PhpStorm.
 * User: eremenko_aa
 * Date: 13.11.2017
 * Time: 14:53
 */
namespace App\Scopes\PatientDocuments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class DocumentsForAllScope implements Scope {

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model) {
        $builder->where('only_for_admin', false);
    }
}