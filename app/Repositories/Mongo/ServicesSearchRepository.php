<?php

namespace App\Repositories\Mongo;

use App\Models\Country;
use Elocache\Repositories\Eloquent\AbstractRepository;
use Illuminate\Http\Request;
use QueryParser\ParserRequest;
use Validator;
use Illuminate\Container\Container as App;

class ServicesSearchRepository extends AbstractRepository
{
    protected $enableCaching = true;

    public static $rules = [
        'name' => 'required|max:150',
        'model_reference' => 'required',
        'country_code' => 'required'
    ];

    /**
     * Specify Model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\ServiceSearch';
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function validateRequest(Request $request)
    {
        $rules = self::$rules;

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $validator->errors()->all();
        }

        return true;
    }

    /**
     * @param Request $request
     * @param int $itemsPage
     *
     * @return mixed
     */
    public function findAllPaginate(Request $request, $itemsPage = 30)
    {
        $key = md5($itemsPage.$request->getRequestUri());
        $queryParser = new ParserRequest($request, $this->getModel());
        $queryBuilder = $queryParser->parser();

        $paginator = $this->cacheQueryBuilder($key, $queryBuilder, 'paginate', $itemsPage);

        $key = 0;
        foreach ($paginator->items() as &$item) {
            $item = (object) $item;
            $paginator->offsetSet($key, $item);
            $key ++;
        }

        return $paginator;
    }
}
