<?php
namespace common\models;

use yii\helpers\Json;

class CheckName
{
    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $message;

    /**
     * @var CheckName[]
     */
    public $result = [];

    /**
     * @param string $jsonString
     */
    public function loadData($jsonString)
    {
        $data = Json::decode($jsonString);
        if($data['Status'] == 200)
        {
            if (!empty($data)) {
                $this->status = $data['Status'];
                $this->message = $data['Message'];
                foreach ($data['Result'] as $result) {
                    $checkNameData = new SearchName();
                    $checkNameData->load($result);
                    $this->result[] = $checkNameData;
                }
            }
        }

    }
}
