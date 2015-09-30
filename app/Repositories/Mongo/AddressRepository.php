<?php

namespace App\Repositories\Mongo;

use Elocache\Repositories\Eloquent\AbstractRepository;
use Illuminate\Http\Request;
use QueryParser\ParserRequest;
use Validator;
use Illuminate\Container\Container as App;

class AddressRepository extends AbstractRepository
{
    protected $enableCaching = true;

    public static $rules = [
        'country_code' => 'required|max:3',
        'city' => 'required',
        'state' => 'required',
        'zip' => 'required'
    ];

    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * Specify Model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\Address';
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function validateRequest(Request $request = null, array $fields = [])
    {
        $rules = self::$rules;

        if (! count($fields)) {
            $fields = $request->all();
        }

        $validator = Validator::make($fields, $rules);

        if ($validator->fails()) {
            return $validator->errors()->all();
        }

        return true;
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findByCountryAndPostalCode($countryCode, $postalCode)
    {
        $query = $this->getModel()->newQuery()->where('country_code', '=', $countryCode)->where('zip', $postalCode);

        return $this->cacheQueryBuilder($countryCode.$postalCode, $query, 'first');
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
