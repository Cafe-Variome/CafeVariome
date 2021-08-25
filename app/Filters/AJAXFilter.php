<?php namespace App\Filters;

use Config\Services;

class AJAXFilter implements \CodeIgniter\Filters\FilterInterface
{

    /**
     * @inheritDoc
     */
    public function before(\CodeIgniter\HTTP\RequestInterface $request, $arguments = null)
    {
		if (!$request->isAJAX()) {
			return Services::response()->setStatusCode(400);
		}
    }

    /**
     * @inheritDoc
     */
    public function after(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, $arguments = null)
    {
		// TODO: Implement after() method.
    }
}
