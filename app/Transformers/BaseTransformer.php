<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use MongoDate;

class BaseTransformer extends TransformerAbstract
{
    /**
     * @param $model
     *
     * @return mixed
     */
    public function transform($model)
    {
        $model->_id = (string) $model->_id;
        if ($model->created_at instanceof MongoDate) {
            $model->created_at = date('Y-m-d H:i:s', $model->created_at->sec);
        }

        if ($model->updated_at instanceof MongoDate) {
            $model->updated_at = date('Y-m-d H:i:s', $model->updated_at->sec);
        }

        if ($model instanceof \stdClass) {
            return json_decode(json_encode($model), true);
        }

        return $model->toArray();
    }
}
