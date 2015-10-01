<?php

namespace App\Http\Controllers\V1;

use App\Models\Country;
use App\Repositories\Mongo\CountryRepository;
use App\Transformers\BaseTransformer;
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
            $paginator = $this->repository->findAllPaginate($request);

            return $this->response->paginator($paginator, new BaseTransformer());
        } catch (QueryParserException $e) {
            throw new StoreResourceFailedException($e->getMessage(), $e->getFields());
        }
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function get($id)
    {
        $country = $this->repository->findBy('_id', $id);
        if (!$country) {
            throw new StoreResourceFailedException('Country not found');
        }

        return $this->response->item($country, new BaseTransformer());
    }
}
