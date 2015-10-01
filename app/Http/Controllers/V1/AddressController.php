<?php

namespace App\Http\Controllers\V1;

use App\Repositories\Mongo\AddressRepository;
use App\Repositories\Mongo\CountryRepository;
use App\Transformers\BaseTransformer;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use QueryParser\QueryParserException;

class AddressController extends BaseController
{
    use Helpers;

    /**
     * @var AddressRepository
     */
    private $repository;

    /**
     * @param AddressRepository $repository
     */
    public function __construct(AddressRepository $repository, CountryRepository $countryRepository)
    {
        $this->repository = $repository;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @return mixed
     */
    public function index(Request $request)
    {
        try {
            $paginator = $this->repository->findAllPaginate($request, 10);

            return $this->response->paginator($paginator, new BaseTransformer());
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
        $handleRequest = $this->repository->validateRequest($request);
        if (is_array($handleRequest)) {
            throw new StoreResourceFailedException('Invalid params', $handleRequest);
        }

        try {
            $this->validCountry($request->input('country_code', ''));
            $address = $this->repository->create($request->all());

            return $this->response->item($address, new BaseTransformer())->setStatusCode(201);
        } catch (\Exception $e) {
            throw new StoreResourceFailedException($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @throws UpdateResourceFailedException
     *
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        $address = $this->repository->findBy('_id', $id);
        if (!$address) {
            throw new UpdateResourceFailedException('Register not found');
        }

        try {
            $this->validCountry($request->input('country_code', ''));
            $address = $this->repository->update($request->all(), $address);

            return $this->response->item($address, new BaseTransformer());
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
        $address = $this->repository->findBy('_id', $id);
        if (!$address) {
            throw new StoreResourceFailedException('Register not found');
        }

        return $this->response->item($address, new BaseTransformer());
    }

    /**
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($id)
    {
        try {
            $address = $this->repository->findBy('_id', $id);
            if (!$address) {
                throw new DeleteResourceFailedException('Register not found');
            }

            $address->delete();

            return $this->response->noContent();
        } catch (\Exception $e) {
            throw new DeleteResourceFailedException($e->getMessage());
        }
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    protected function validCountry($code)
    {
        $country = $this->countryRepository->findBy('code', $code);
        if (!$country) {
            throw new UpdateResourceFailedException("Country '{$code}' not found");
        }

        return ['code' => $country->code, 'name' => $country->name];
    }
}
