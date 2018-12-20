<?php

namespace backend\modules\niche\models;

use common\models\Administrator;
use common\models\BusinessSubject;
use common\models\Channel;
use common\models\CrmContacts;
use common\models\CrmCustomer;
use common\models\CrmCustomerApi;
use common\models\CrmDepartment;
use common\models\Niche;
use common\models\Source;
use common\models\User;
use yii\base\Model;
use yii\db\Query;


/**
 * 移除商机
 * @SWG\Definition(required={"niche_id"}, @SWG\Xml(name="NichePublicDetailForm"))
 */
class NichePublicDetailForm extends Model
{
    /**
     * 商机团队id
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $niche_id;

    public function rules()
    {
        return [
            [['niche_id'],'required'],
            [['niche_id'],'integer']
        ];
    }

    public function getDetail()
    {
        $niche = Niche::find()->select('crm_contacts.customer_id')->leftJoin(['crm_contacts'=>CrmContacts::tableName()],'niche.contacts_id = crm_contacts.id')->where(['niche.id'=>$this->niche_id])->one();
        if(!empty($niche)){
            $data = ['id'=>$niche->customer_id,'customer_public_id'=>2];
            return $this->getCustomerDetail($data);
        }
        return [];
    }


    //查看个人客户关联信息详情 （公海 我的客户共用）

    public function getCustomerDetail($data)
    {
        $query = new Query();
        $query->select('u.name as user_name,u.phone as user_phone,u.email as user_email,u.address as user_address,b.legal_person_name,b.industry_name,a.title,c.administrator_name,d.name as department_name,c.level,c.id as customer_id,c.administrator_id,cc.id as contact_id,cc.name as contacts_name,cc.gender,cc.birthday,cc.customer_hobby,cc.phone as contact_phone,cc.wechat,cc.qq,cc.caller,cc.tel,cc.email,cs.id as source_id,cs.name as source_name,ch.name as channel_name,cc.province_id,cc.province_name,cc.district_id,cc.district_name,cc.city_id,cc.city_name,cc.street,cc.remark,cc.customer_id as customer_contacts_id,cc.department,cc.position,c.customer_public_id,b.official_website,b.company_name,c.is_protect,cc.native_place')
            ->from(['c' => CrmCustomer::tableName()])
            ->leftJoin(['cc' => CrmContacts::tableName()], 'c.id=cc.customer_id')
            ->leftJoin(['b' => BusinessSubject::tableName()], 'c.id = b.customer_id')
            ->leftJoin(['a' => Administrator::tableName()], 'c.administrator_id = a.id')
            ->leftJoin(['u' => User::tableName()], 'c.user_id = u.id')
            ->leftJoin(['cs' => Source::tableName()], 'cc.source = cs.id')
            ->leftJoin(['ch' => Channel::tableName()], 'cc.channel_id = ch.id')
            ->leftJoin(['d' => CrmDepartment::tableName()], 'c.department_id = d.id')
            ->where(['c.id' => $data['id']]);

        $rs = $query->one();

        if ($data['customer_public_id'] == 2) {
            $result = $this->customerReplace($rs);
        } else {
            $result = $rs;
        }

        return $result;
    }


    //替换个人客户关联信息详情结果
    public function customerReplace($rs)
    {
        $data['user_name'] = $rs['user_name'] !='' ? $this->str_replace_val(strlen($rs['user_name'])):"";
        $data['user_phone'] = $rs['user_phone'] != '' ? substr_replace($rs['user_phone'], '*********', 3, 11) :'';
        $data['user_email'] = $rs['user_email'] != '' ?  $this->str_replace_val(strlen($rs['user_email'])):"";
        $data['user_address'] = $rs['user_address'] != '' ? $this->str_replace_val(strlen($rs['user_address'])):"";
        $data['legal_person_name'] = $rs['legal_person_name'];
        $data['industry_name'] = $rs['industry_name'];
        $data['title'] = $rs['title'];
        $data['administrator_name'] = $rs['administrator_name'];
        $data['department_name'] = $rs['department_name'];
        $data['level'] = $rs['level'];
        $data['customer_id'] = $rs['customer_id'];
        $data['administrator_id'] = $rs['administrator_id'];
        $data['contact_id'] = $rs['contact_id'];
        $data['contacts_name'] = $rs['contacts_name'] !='' ? $this->dealName($rs['contacts_name'],$rs['gender']):"";;
        $data['gender'] = $rs['gender'];
        $data['birthday'] = "**年**月**日";
        $data['customer_hobby'] = $this->str_replace_val(strlen($rs['customer_hobby']));
        $data['contact_phone'] = $rs['contact_phone'] != '' ? substr_replace($rs['contact_phone'], '*********', 3, 11) : '';
        $data['wechat'] = $this->str_replace_val(strlen($rs['wechat']));
        $data['qq'] = $this->str_replace_val(strlen($rs['qq']));
        $data['caller'] = $rs['caller'] != '' ? $this->str_replace_val(strlen($rs['caller'])) : '';
        $data['tel'] = empty($rs['tel'])?"":$this->str_replace_val(strlen($rs['caller']));
        $data['email'] = empty($rs['email'])? "":$this->str_replace_val(strlen($rs['email']));
        $data['source_name'] = $rs['source_name'];
        $data['channel_name'] = $rs['channel_name'];
        $data['province_id'] = $rs['province_id'];
        $data['province_name'] = $rs['province_name'];
        $data['district_id'] = $rs['district_id'];
        $data['district_name'] = mb_substr($rs['district_name'], 0, 1) . $this->str_replace_val(mb_strlen($rs['district_name']));
        $data['city_id'] = $rs['city_id'];
        $data['city_name'] = $rs['city_name'];
        $data['street'] = $this->str_replace_val(mb_strlen($rs['street']));
        $data['remark'] = mb_substr($rs['remark'], 0, 2) . $this->str_replace_val(mb_strlen($rs['remark']));
        $data['customer_contacts_id'] = $rs['customer_contacts_id'];
        $data['department'] = $rs['department'];
        $data['position'] = $rs['position'];
        $data['customer_public_id'] = $rs['customer_public_id'];
        $data['company_name'] = $rs['company_name'];
        $data['official_website'] = $rs['official_website'];
        $data['native_place'] = $rs['native_place'];
        $data['contact_address'] = $rs['province_name'].'******';
        return $data;
    }


    //计算星号个数
    public function str_replace_val($num)
    {
        $str = '';
        for ($i = 0; $i < $num; $i++) {
            $str .= '*';
        }
        return $str;
    }

    public function dealName($name,$gender){
        if($gender == 0){
            $names = mb_substr($name,0,1)."***（先生）";
        }else{
            $names = mb_substr($name,0,1)."***（女士）";
        }

        return $names;
    }


}