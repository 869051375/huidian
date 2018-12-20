<?php

namespace backend\modules\niche\models;

use common\models\BusinessSubject;
use common\models\CrmContacts;
use common\models\CrmCustomer;
use common\models\NicheProduct;
use common\models\NichePublic;
use common\models\NichePublicDepartment;
use common\models\NicheRecord;
use common\models\Product;
use common\models\Tag;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use Yii;


/**
 * @SWG\Definition(required={"type"}, @SWG\Xml(name="PublicListForm"))
 */
class PublicListForm extends Model
{

    /**
     * 类型（0：公海 1：大公海）
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $type;

    /**
     * 公海ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $niche_public_id;


    /**
     * 商机ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $niche_id;

    /**
     * 公海名称
     * @SWG\Property(example = "公海1")
     * @var string
     */
    public $niche_name;

    /**
     * 客户名称名称
     * @SWG\Property(example = "公海1")
     * @var string
     */
    public $customer_name;

    /**
     * 客户ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $customer_id;


    /**
     * 手机号码
     * @SWG\Property(example = "183108224510")
     * @var string
     */
    public $phone;

    /**
     * 联系座机
     * @SWG\Property(example = "183108224510")
     * @var string
     */
    public $tel;

    /**
     * 微信
     * @SWG\Property(example = "183108224510")
     * @var string
     */
    public $wechat;

    /**
     * QQ
     * @SWG\Property(example = "183108224510")
     * @var string
     */
    public $qq;

    /**
     * 来点电话
     * @SWG\Property(example = "183108224510")
     * @var string
     */
    public $caller;

    /**
     * 邮箱
     * @SWG\Property(example = "183108224510")
     * @var string
     */
    public $email;

    /**
     * 标签ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $label_id;

    /**
     * 创建人ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $creator_id;

    /**
     * 最后跟进人ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $last_record_creator_id;

    /**
     * 来源ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $source_id;

    /**
     * 来源渠道ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $channel_id;

    /**
     * 分配人ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $distribution_id;

    /**
     * 一级分类ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $top_category_id;


    /**
     * 二级分类ID
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $category_id;

    /**
     * 商品别名
     * @SWG\Property(example = "你好")
     * @var string
     */
    public $alias;


    /**
     * 创建时间（开始）
     * @SWG\Property(example = "2018-09-10")
     * @var string
     */
    public $created_start;

    /**
     * 创建时间（结束）
     * @SWG\Property(example = "2018-09-10")
     * @var string
     */
    public $created_end;

    /**
     * 最后跟进时间（开始）
     * @SWG\Property(example = "2018-09-10")
     * @var string
     */
    public $last_record_start;

    /**
     * 最后跟进时间（开始）
     * @SWG\Property(example = "2018-09-10")
     * @var string
     */
    public $last_record_end;

    /**
     * 分配时间（开始）
     * @SWG\Property(example = "2018-09-10")
     * @var string
     */
    public $distribution_start;

    /**
     * 分配时间（开始）
     * @SWG\Property(example = "2018-09-10")
     * @var string
     */
    public $distribution_end;

    /**
     * 金额
     * @SWG\Property(example = 100)
     * @var integer
     */
    public $total_amount;

    /**
     * 金额类型
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $compare;

    /**
     * 商机阶段
     * @SWG\Property(example = 20)
     * @var string
     */
    public $progress;

    /**
     * 跟进时间
     * @SWG\Property(example = "2018-09-10")
     * @var string
     */
    public $record_time;

    /**
     * 全部
     * @SWG\Property(example = "")
     * @var string
     */
    public $all;

    /**
     * 我分享的商机
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $is_share;

    /**
     * 我分享的商机
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $follow_today;

    /**
     * 我分享的商机
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $follow_three;

    /**
     * 我分享的商机
     * @SWG\Property(example = 1)
     * @var integer
     */
    public $follow_month;


    /**
     * 每页多少条
     * @SWG\Property(example = 35)
     * @var integer
     */
    public $page_num;

    /**
     * 页码
     * @SWG\Property(example = 35)
     * @var integer
     */
    public $page;




    public function rules()
    {
        return [
            [['type','niche_public_id','niche_name','customer_name','niche_id','customer_id','phone','tel','wechat','qq','caller','email','label_id','creator_id','last_record_creator_id','channel_id','source_id','distribution_id','top_category_id',
                'category_id','alias','created_start','created_end','last_record_start','last_record_end','distribution_start','distribution_end','total_amount', 'compare','progress','record_time','is_share','all','follow_today','follow_three','follow_month','page_num','page'], 'required'],
            [['type','niche_public_id','niche_id','customer_id','label_id','creator_id','last_record_creator_id','channel_id','distribution_id','top_category_id', 'category_id','progress','total_amount','compare','is_share'], 'integer'],
            [['niche_name','customer_name','phone','tel','wechat','qq','caller','email','alias','created_start','created_end','last_record_start','last_record_end','distribution_start','distribution_end','record_time'],'string']
        ];
    }

    public function getList($administrator)
    {
        $query = NichePublicLists::find();
        $query->distinct()->select('niche.*,niche_public.name as public_name,business_subject.company_name as customer_name,business_subject.customer_number');
        $query->leftJoin(['niche_public'=>NichePublic::tableName()],'niche_public.id = niche.niche_public_id');
        $query->leftJoin(['crm_contacts'=>CrmContacts::tableName()],'niche.contacts_id = crm_contacts.id');
        $query->leftJoin(['npd'=>NichePublicDepartment::tableName()],'npd.niche_public_id = niche_public.id');
        $query->leftJoin(['business_subject'=>BusinessSubject::tableName()],'business_subject.id = niche.business_subject_id');
        $query->leftJoin(['niche_product'=>NicheProduct::tableName()],'niche_product.niche_id = niche.id');
        $query->andWhere(['niche_public.type'=>$this->type]);
        if(!empty($this->niche_id)){
            $query->andWhere(['niche.id'=>$this->niche_id]);
        }
        if(!empty($this->niche_name)){
            $query->andWhere(['like','niche.name',$this->niche_name]);
        }
        if(!empty($this->customer_id)){
            $query->andWhere(['crm_contacts.customer_id'=>$this->customer_id]);
        }
        if(!empty($this->customer_name)){
            $query->andWhere(['like','crm_contacts.name',$this->customer_name]);
        }
        if(!empty($this->phone)){
            $query->andWhere(['like','crm_contacts.phone',$this->phone]);
        }
        if(!empty($this->tel)){
            $query->andWhere(['like','crm_contacts.tel',$this->tel]);
        }
        if(!empty($this->wechat)){
            $query->andWhere(['like','crm_contacts.wechat',$this->wechat]);
        }
        if(!empty($this->qq)){
            $query->andWhere(['like','crm_contacts.qq',$this->qq]);
        }
        if(!empty($this->caller)){
            $query->andWhere(['crm_contacts.caller'=>$this->caller]);
        }
        if(!empty($this->email)){
            $query->andWhere(['like','crm_contacts.email',$this->email]);
        }
        if(!empty($this->label_id)){
            $query->andWhere(['like','niche.label_id',$this->label_id]);
        }
        if(!empty($this->creator_id)){
            $query->andWhere(['niche.creator_id'=>$this->creator_id]);
        }
        if(!empty($this->last_record_creator_id)){
            $query->andWhere(['niche.last_record_creator_id'=>$this->last_record_creator_id]);
        }
        if(!empty($this->channel_id)){
            $query->andWhere(['niche.channel_id'=>$this->channel_id]);
        }
        if(!empty($this->source_id)){
            $query->andWhere(['niche.source_id'=>$this->source_id]);
        }
        if(!empty($this->distribution_id)){
            $query->andWhere(['niche.distribution_id'=>$this->distribution_id]);
        }
        if(!empty($this->top_category_id)){
            $query->andWhere(['niche_product.top_category_id'=>$this->top_category_id]);
        }
        if(!empty($this->category_id)){
            $query->andWhere(['niche_product.category_id'=>$this-> category_id]);
        }
        if(!empty($this->alias)){
            $product = Product::find()->where(['like','alias',$this->alias])->asArray()->all();
            $product_id = array_column($product,'id');
            $niche_product = NicheProduct::find()->where(['in','product_id',$product_id])->asArray()->all();
            $niche_id = array_column($niche_product,'niche_id');
            $query->andWhere(['in','niche.id',$niche_id]);
        }
        if(!empty($this->created_start) && empty($this->created_end)){
            $query->andWhere(['>=','niche.created_at',strtotime($this->created_start)]);
        }
        if(empty($this->created_start) && !empty($this->created_end)){
            $query->andWhere(['<=','niche.created_at',strtotime($this->created_end)]);
        }
        if(!empty($this->created_start) && !empty($this->created_end)){
            $query->andWhere(['>=','niche.created_at',strtotime($this->created_start)]);
            $query->andWhere(['<=','niche.created_at',strtotime($this->created_end)]);
        }
        if(!empty($this->last_record_start) && empty($this->last_record_end)){
            $query->andWhere(['>=','niche.last_record',strtotime($this->last_record_start)]);
        }
        if(empty($this->last_record_start) && !empty($this->last_record_end)){
            $query->andWhere(['<=','niche.last_record',strtotime($this->last_record_end)]);
        }
        if(!empty($this->last_record_start) && !empty($this->last_record_end)){
            $query->andWhere(['>=','niche.last_record',strtotime($this->last_record_start)]);
            $query->andWhere(['<=','niche.last_record',strtotime($this->last_record_end)]);
        }
        if(!empty($this->distribution_start) && empty($this->distribution_end)){
            $query->andWhere(['>=','niche.distribution_at',strtotime($this->distribution_start)]);
        }
        if(empty($this->distribution_start) && !empty($this->distribution_end)){
            $query->andWhere(['<=','niche.distribution_at',strtotime($this->distribution_end)]);
        }
        if(!empty($this->distribution_start) && !empty($this->distribution_end)){
            $query->andWhere(['>=','niche.distribution_at',strtotime($this->distribution_start)]);
            $query->andWhere(['<=','niche.distribution_at',strtotime($this->distribution_end)]);
        }
        if(!empty($this->total_amount)){
            if($this->compare ==1){
                $query->andWhere(['>=','niche.total_amount',$this->total_amount]);
            }else{
                $query->andWhere(['<=','niche.total_amount',$this->total_amount]);
            }
        }
        if($this->progress != ''){
            $query->andWhere(['niche.progress'=>$this->progress]);
        }

        $query->orderBy(['niche.recovery_at'=>SORT_DESC]);
        /** @var NichePublic $publics */
        if(empty($this->all) && $this->type !=1 && empty($this->is_share)){
            if(!empty($this->niche_public_id)){
                $query->andWhere(['niche.niche_public_id'=>$this->niche_public_id]);
            }else{
                if($administrator->company_id != 0 && $administrator->department_id != 0){
                    $query->andWhere(['niche_public.company_id'=>$administrator->company_id]);
                    /** @var NichePublicDepartment $public_department */
                    $public_department = NichePublicDepartment::find()->where(['department_id'=>$administrator->department_id])->one();
                    if($public_department){
                        $query->andWhere(['niche.niche_public_id'=>$public_department->niche_public_id]);
                    }else{
                        /** @var \common\models\Administrator $administrator */
                        $administrator = Yii::$app->user->identity;
                        $department_ids = $administrator->getTreeDepartmentId(true);
                        if(empty($department_ids)){
                            $department_ids = [$administrator->department_id];
                        }
                        /** @var NichePublicDepartment $niche_public */
                        $niche_public = NichePublicDepartment::find()->where(['in','department_id',$department_ids])->orderBy(['niche_public_id'=>SORT_DESC])->one();
                        if(!empty($niche_public)){
                            $query->andWhere(['niche.niche_public_id'=>$niche_public->niche_public_id]);
                        }else{
                            $query->andWhere(['<=','niche.id',0]);
                        }
                    }
                }else{
                    $query->where(['niche.niche_public_id'=>1]);
                }
            }
        }

        if(!empty($this->follow_today)){
            $res = $this->getNicheID('today');
            if(!empty($res)){
                $query->andWhere(['in','niche.id',$res]);
            }else{
                $query->andWhere(['niche.id'=>0]);
            }
        }
        if(!empty($this->follow_three)){
            $res = $this->getNicheID('three');
            if(!empty($res)){
                $query->andWhere(['in','niche.id',$res]);
            }else{
                $query->andWhere(['niche.id'=>0]);
            }
        }
        if(!empty($this->follow_month)){
            $res = $this->getNicheID('month');
            if(!empty($res)){
                $query->andWhere(['in','niche.id',$res]);
            }else{
                $query->andWhere(['niche.id'=>0]);
            }
        }
        if(!empty($this->all)){
            /** @var \common\models\Administrator $administrator */
            $administrator = Yii::$app->user->identity;
            if(isset($administrator->company_id) && $administrator->company_id !=0 && isset($administrator->department_id) && $administrator->department_id != 0){
                $department_ids = $administrator->getTreeDepartmentId(true);
                if(empty($department_ids)){
                    $department_ids = [$administrator->department_id];
                }
                $niche_public = NichePublicDepartment::find()->where(['in','department_id',$department_ids])->orderBy(['niche_public_id'=>SORT_DESC])->asArray()->all();
                if(!empty($niche_public)){
                    $niche_public_id = array_column($niche_public,'niche_public_id');
                    $query->andWhere(['in','niche.niche_public_id',$niche_public_id]);
                }else{
                    $query->andWhere(['niche.id'=>0]);
                }
            }
        }
        if(!empty($this->is_share)){
            $query->andWhere(['niche.creator_id'=>$administrator->id]);
            $query->andWhere(['niche.is_cross'=>1]);
            $query->orderBy(['niche.created_at'=>SORT_DESC]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => intval($this->page_num),
                'page' => intval($this->page) - 1,
            ]
        ]);
        return $dataProvider;
    }

    public function exportCustomerCreateTime($niche_id)
    {
        /** @var \common\models\Niche $niche */
        $niche = \common\models\Niche::find()->where(['id'=>$niche_id])->one();
        /** @var CrmCustomer $customer */
        $customer = CrmCustomer::find()->where(['id'=>$niche->customer_id])->one();
        return $customer->created_at;
    }

    public function getLastRecord($niche_id)
    {
        /** @var NicheRecord $record */
        $record = NicheRecord::find()->where(['niche_id'=>$niche_id])->orderBy(['id'=>SORT_DESC])->limit(1)->one();
        if(empty($record)){
            return '';
        }
        return $record->content;
    }


    public function getNicheID($type)
    {
        if($type == 'today'){
            $start = strtotime(date("Y-m-d 00:00:01",time()));
            $end = strtotime(date("Y-m-d 23:59:59",time()));
            $res = NicheRecord::find()->select('niche_id')->distinct('niche_id')->where(['between','created_at',$start,$end])->asArray()->all();
        }elseif($type == 'three'){
            $start = strtotime(date("Y-m-d 00:00:01",strtotime("-3day")));
            $end = strtotime(date("Y-m-d 23:59:59",time()));
            $res = NicheRecord::find()->select('niche_id')->distinct('niche_id')->where(['between','created_at',$start,$end])->asArray()->all();
        }else{
            $start = strtotime(date("Y-m-01 00:00:01",time()));
            $end = time();
            $res = NicheRecord::find()->select('niche_id')->distinct('niche_id')->where(['between','created_at',$start,$end])->asArray()->all();
        }
        $niche_id = array_column($res,'niche_id');
        return $niche_id;
    }




}