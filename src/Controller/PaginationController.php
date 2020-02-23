<?php

namespace App\Controller;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class PaginationController extends AbstractController
{

    public $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @param Request $request
     * @param array $array
     * @return PaginationInterface
     */
    public function setPagination(Request $request, Array $array)
    {
        return $pagination = $this->paginator->paginate($array, $request->query->getInt('page', 1), 10);
    }

}
