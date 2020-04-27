<?php

namespace JA\Lego\Widget\Concern;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Request;

trait HasPagination
{
    protected $paginator;
    protected $paginatorEnabled = false;
    protected $paginatorPerPage = 100;
    protected $paginatorPageName = 'page';
    protected $paginatorLengthAware = true;

    public function paginate(int $perPage, $pageName = 'page')
    {
        $this->paginatorEnabled = true;
        $this->paginatorPerPage = $perPage;
        $this->paginatorPageName = $pageName;
        $this->paginatorLengthAware = true;

        return $this;
    }

    public function simplePaginate(int $perPage, $pageName = 'page')
    {
        $this->paginatorEnabled = true;
        $this->paginatorPerPage = $perPage;
        $this->paginatorPageName = $pageName;
        $this->paginatorLengthAware = false;

        return $this;
    }

    public function paginator()
    {
        if (!$this->paginator) {
            $this->paginator = $this->makePaginator();
        }

        return $this->paginator;
    }

    public function getPaginatorCurrentPage()
    {
        return Request::query($this->paginatorPageName, 1);
    }

    public function getPaginatorPerPage()
    {
        return $this->paginatorPerPage;
    }

    public function makePaginator()
    {
        $paginator = $this->query->paginate(
            $this->paginatorPerPage,
            null,
            $this->paginatorPageName,
            null,
            $this->paginatorLengthAware
        );
        $paginator->appends(Request::input());
        $paginator->setPath(Paginator::resolveCurrentPath());

        return $paginator;
    }
}