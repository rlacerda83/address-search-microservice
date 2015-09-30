<?php

namespace App\Http\Controllers\V1;

use App\Models\ServiceSearch;
use App\Models\Country;
use App\Repositories\Mongo\ServicesSearchRepository;
use App\Repositories\Mongo\CountryRepository;
use App\Repositories\Mongo\AddressRepository;
use App\Services\ServicesSearch\PostOfficeBrazil;
use App\Transformers\BaseTransformer;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Laravel\Lumen\Routing\Controller as BaseController;
use QueryParser\QueryParserException;

class ServicesSearchController extends BaseController
{
    use Helpers;

    /**
     * @var CarrierRepository
     */
    private $repository;

    /**
     * @var CountryRepository
     */
    private $countryRepository;

    /**
     * @var AddressRepository
     */
    private $addressRepository;

    /**
     * @param ServicesSearchRepository $repository
     * @param CountryRepository $countryRepository
     * @param AddressRepository $countryRepository
     */
    public function __construct(ServicesSearchRepository $repository, CountryRepository $countryRepository, AddressRepository $addressRepository)
    {
        $this->repository = $repository;
        $this->countryRepository = $countryRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @return mixed
     */
    public function index(Request $request)
    {
        try {
            $paginator = $this->repository->findAllPaginate($request, 10);

            return $this->response->paginator($paginator, new BaseTransformer);
        } catch (QueryParserException $e) {
            throw new StoreResourceFailedException($e->getMessage(), $e->getFields());
        }
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function create(Request $request)
    {
        $this->validCountry($request->input('country_code', ''));

        $handleRequest = $this->repository->validateRequest($request);
        if (is_array($handleRequest)) {
            throw new StoreResourceFailedException('Invalid params', $handleRequest);
        }

        try {
            $serviceSearch = $this->repository->create($request->all());

            return $this->response->item($serviceSearch, new BaseTransformer)->setStatusCode(201);
        } catch (\Exception $e) {
            throw new StoreResourceFailedException($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return mixed
     * @throws UpdateResourceFailedException
     */
    public function update(Request $request, $id)
    {
        $serviceSearch = $this->repository->findBy('_id', $id);
        if (! $serviceSearch) {
            throw new UpdateResourceFailedException('Register not found');
        }

        try {
            $this->validCountry($request->input('country_code', ''));
            $serviceSearch = $this->repository->update($request->all(), $serviceSearch);

            return $this->response->item($serviceSearch, new BaseTransformer);
        } catch (\Exception $e) {
            throw new StoreResourceFailedException($e->getMessage());
        }
    }

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get($id)
    {
        $serviceSearch = $this->repository->findBy('_id', $id);
        if (! $serviceSearch) {
            throw new StoreResourceFailedException('Register not found');
        }

        return $this->response->item($serviceSearch, new BaseTransformer);
    }

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($id)
    {
        try {
            $serviceSearch = $this->repository->findBy('_id', $id);
            if (! $serviceSearch) {
                throw new DeleteResourceFailedException('Register not found');
            }

            $serviceSearch->delete();

            return $this->response->noContent();
        } catch (\Exception $e) {
            throw new DeleteResourceFailedException($e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $postalCode = $request->input('postal_code', '');

        $countryCode = $request->input('country_code', '');
        $this->validCountry($countryCode);

        if (! strlen($postalCode)) {
            throw new StoreResourceFailedException("Invalid postal code '{$code}'");    
        }

        $model = ServiceSearch::where('country_code', $countryCode)->first();
        if (! $model) {
            throw new StoreResourceFailedException("Service search not found to country '{$countryCode}'");    
        }

        $searchService = new $model->model_reference($model, $this->addressRepository);
        $searchService->setPostalCode($postalCode);
        $result = $searchService->search();

        return response()->json(['data' => $result]);
    }

    /**
     * @param $code
     * @return mixed
     */
    protected function validCountry($code)
    {
        $country = $this->countryRepository->findBy('code', $code);
        if (! $country) {
            throw new UpdateResourceFailedException("Country '{$code}' not found");
        }

        return true;
    }
}
