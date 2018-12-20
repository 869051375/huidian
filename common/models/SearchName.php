<?php
namespace common\models;

class SearchName
{
    /**
     * @var string
     */
    public $keyNo;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $operName;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $startDate;

    /**
     * @var string
     */
    public $no;

    public function load($data)
    {
        $this->keyNo = $data['KeyNo'];
        $this->name = $data['Name'];
        $this->operName = $data['OperName'];
        $this->startDate = $data['StartDate'];
        $this->status = $data['Status'];
        $this->no = $data['No'];
    }
}