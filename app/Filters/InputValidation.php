<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class InputValidation implements \CodeIgniter\Filters\FilterInterface
{
	public function before(RequestInterface $request, $arguments = null)
	{
		if ($request->getMethod() != 'cli')
		{
			$post = $request->getVar();
			foreach ($post as $field => &$value)
			{
				if (!is_array($value))
				{
					$value = htmlspecialchars_decode($value, ENT_QUOTES | ENT_SUBSTITUTE);
					$value = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
				}
			}
			$request->setGlobal('request', $post);
		}
	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		// TODO: Implement after() method.
	}
}
