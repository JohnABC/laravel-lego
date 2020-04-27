<?php

namespace JA\Lego\Widget\Grid\Concern;

use JA\Lego\Foundation\Response;
use Maatwebsite\Excel\Facades\Excel;

trait HasExports
{
    protected $exports = [];

    public function export($name, $callback = null)
    {
        $this->exports[$name] = Response::registerPriority(md5(__METHOD__ . ' ' . $name), function () use ($name, $callback) {
            if ($callback) {
                call_user_func($callback, $this);
            }

            $this->exportExcel($name)->download();
        })->urlWithPriority();

        return $this;
    }

    public function exportExcel($filename)
    {
        $data = [];
        foreach ($this->paginator() as $store) {
            $row = [];
            foreach ($this->cells() as $cell) {
                $row[$cell->description()] = $cell->fill($store)->getPlainValue();
            }
            $data[] = $row;
        }

        return Excel::create(
            $filename,
            function (LaravelExcelWriter $excel) use ($data) {
                $excel->sheet(
                    'SheetName',
                    function (\PHPExcel_Worksheet $sheet) use ($data) {
                        $sheet->fromArray($data);
                    }
                );
            }
        );
    }
}