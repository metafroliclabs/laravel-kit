<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Core\JsonResponseService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MainController
 * @package App\Http\Controllers
 */
class MainController extends Controller
{
    /**
     * @var \App\Services\JsonResponseService
     */
    protected $response;

    /**
     * number of pagination
     * @var int
     */
    protected $pagination;

    /**
     * MainController constructor.
     */
    public function __construct()
    {
        $this->response = new JsonResponseService;
        // $requestPerPage = request('per_page', config('app.pagination'));
        // $this->pagination = $requestPerPage > 100 ? 100 : $requestPerPage;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke()
    {
        return $this->response->fail([], 400, Response::HTTP_NOT_FOUND);
    }
}
