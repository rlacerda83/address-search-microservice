<?php

namespace App\Http\Controllers\V1;

use App\Models\Country;
use App\Repositories\Eloquent\CountryRepository;
use App\Transformers\BaseTransformer;
use DB;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use QueryParser\QueryParserException;

class CountriesController extends BaseController
{
    use Helpers;

    /**
     * @var CountryRepository
     */
    private $repository;

    /**
     * @param CountryRepository $repository
     */
    public function __construct(CountryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return mixed
     */
    public function index(Request $request)
    {
        try {
            //            $user = DB::table('countries')->where('countries.code', 'BR')->get();
//            print_R($user); die;
            $paginator = $this->repository->findAllPaginate($request);

            //print_R($paginator); die;
            return $this->response->paginator($paginator, new BaseTransformer());
        } catch (QueryParserException $e) {
            throw new StoreResourceFailedException($e->getMessage(), $e->getFields());
        }
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    public function get($code)
    {
        $country = $this->repository->findBy('code', $code);
        if (!$country) {
            throw new StoreResourceFailedException("Country '{$code}' not found");
        }

        return $this->response->item($country, new BaseTransformer());
    }
}
