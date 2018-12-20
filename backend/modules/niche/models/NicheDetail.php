<?php
namespace backend\modules\niche\models;
/**
 * Created by PhpStorm.
 * User: yangge
 * Date: 2018/11/8
 * Time: 3:09 PM
 */
use common\models\Administrator;
use common\models\Company;
use common\models\CrmContacts;
use common\models\CrmDepartment;
use common\models\Niche;
use common\models\NichePublicDepartment;
use common\models\NicheTeam;
use common\models\Tag;
use common\models\User;
use Yii;

class NicheDetail extends Niche{

    public $product_name;
    public $public_name;
    public $customer_number;
    public $department_name;
    public $company_name;
    public $is_power;
    public $is_niche_public;
    public $display_give_up;
    public $business_subjects;


    public function fields()
    {
        $fields = parent::fields();
        $fields['customers'] = 'customers';
        $fields['contacts'] = 'contacts';
        $fields['users'] = 'users';
        $fields['labels'] = 'labels';
        $fields['products'] = 'products';
        $fields['department_name'] = 'department_name';
        $fields['company_name'] = 'company_name';
        $fields['is_power'] = 'is_power';
        $fields['is_niche_public'] = 'is_niche_public';
        $fields['display_give_up'] = 'display_give_up';
        $fields['business_subjects'] = 'business_subjects';
        return $fields;
    }


    public function select()
    {
        $products = \common\models\NicheProduct::findAll(['niche_id' => $this->id]);
        foreach ($products as $product)
        {
            $this->products[] = $product->toArray();
        }

        $this->customers = $this->customer;
        $this->business_subjects = $this->businessSubject;
        if (isset($this->administrator))
        {
            $department = CrmDepartment::find()->select('name')->where(['id'=>$this->administrator->department_id])->one();
            $company = Company::find()->select('name')->where(['id'=>$this->administrator->company_id])->one();
        }
        $this->department_name = isset($department->name) ? $department->name : '';
        $this->company_name = isset($company->name) ? $company->name : '';
//        $this->customers = $this->customer->businessSubject;
        $this->contacts = CrmContacts::find()->where(['id'=>$this->contacts_id])->one();
        $this->users = User::find()->select('id,name,phone,email,address')->where(['id'=>$this->user_id])->one();
        $this->labels = Tag::find()->select('id,name,color')->where(['id'=>$this->label_id])->one();
        /** @var Administrator $administrator */
        $administrator = \Yii::$app->user->identity;
        //判断是否有公海
        $niche_public = NichePublicDepartment::find()->where(['department_id'=>isset($this->administrator->department_id) ? $this->administrator->department_id : 0])->one();
        if (empty($niche_public))
        {
            $this->is_niche_public = 0;
        }
        else
        {
            $this->is_niche_public = 1;
        }
        //判断是否是负责人或者团队成员
        $niche = Niche::find()->where(['administrator_id'=>isset($administrator->id) ? $administrator->id : 0])->andWhere(['id'=>isset($this->id) ? $this->id : 0])->one();
        $this->is_power = 0;
        if (empty($niche))
        {
            $niche_team = NicheTeam::find()->where(['administrator_id'=>$administrator->id])->andWhere(['niche_id'=>$this->id])->andWhere(['is_update'=>1])->one();
            if (!empty($niche_team))
            {
                $this->is_power = 1;
            }
        }
        else
        {
            $this->is_power = 1;
        }

        $this->display_give_up = 1;
        if ($administrator->company_id == 0)
        {
            if ($administrator->department_id == 0)
            {
                $this->display_give_up = 0;
            }
            else
            {
                $niche_public = NichePublicDepartment::find()->where(['department_id'=>$administrator->department_id])->one();
                if (empty($niche_public))
                {
                    $this->display_give_up = 0;
                }
            }
        }
        else
        {
            $niche_public = NichePublicDepartment::find()->where(['department_id'=>$administrator->department_id])->one();
            if (empty($niche_public))
            {
                $this->display_give_up = 0;
            }
        }
    }
}