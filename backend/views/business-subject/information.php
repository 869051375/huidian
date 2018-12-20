<?php
/* @var $this yii\web\View */

/** @var \common\models\BusinessSubject $subject */
?>
<div class="wrapper wrapper-content animated fadeIn">
    <?= $this->render('info', ['subject' => $subject]) ?>
    <div class="row">
        <div class="col-xs-12">
            <div class="tabs-container">
                <?= $this->render('nav-tabs', ['subject' => $subject]) ?>
                <div class="tab-content">
                    <div class="panel-body" style="border-top: none">
                        <div class="ibox-content"  style="border-top: none">
                            <?php if(!$subject->subject_type): ?>
                                <div>
                                    <table class="table">
                                        <tr style="border-bottom: 1px dashed #e7eaec;">
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">公司名称：</span>
                                            		<span class="col-md-10"><?= $subject->company_name; ?></span>
                                            	</div>
                                            </td>
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">信用代码：</span>
                                            		<span class="col-md-10"><?= $subject->credit_code; ?></span>
                                            	</div>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #e7eaec;">
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">登记状态：</span>
                                            		<span class="col-md-10"><?= $subject->register_status; ?></span>
                                            	</div>
                                            </td>
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">公司类型：</span>
                                            		<span class="col-md-10"><?= $subject->enterprise_type; ?></span>
                                            	</div>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #e7eaec;">
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2" style="padding-right: 0;">法定代表人：</span>
                                            		<span class="col-md-10"><?= $subject->legal_person_name; ?></span>
                                            	</div>
                                            </td>
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">注册资金：</span>
                                            		<span class="col-md-10"><?= empty($subject->registered_capital) ? null : $subject->registered_capital.'万元'; ?></span>
                                            	</div>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #e7eaec;">
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">成立日期：</span>
                                            		<span class="col-md-10"><?= empty($subject->operating_period_begin) ? '--' : date('Y-m-d',$subject->operating_period_begin); ?></span>
                                            	</div>
                                            </td>
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">到期日期：</span>
                                            		<span class="col-md-10"><?= empty($subject->operating_period_end) ? '--' : date('Y-m-d',$subject->operating_period_end); ?></span>
                                            	</div>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #e7eaec;">
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">登记机关：</span>
                                            		<span class="col-md-10"><?= $subject->register_unit; ?></span>
                                            	</div>
                                            </td>
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">注册地址：</span>
                                            		<span class="col-md-10"><?= $subject->province_name.$subject->city_name.$subject->district_name.$subject->address; ?></span>
                                            	</div>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #e7eaec;">
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;position:relative">
                                            		<span class="col-md-2">经营范围：</span>
                                            		<span class="col-md-9 drop-content" style="height: 20px;overflow: hidden;"><?= $subject->scope; ?></span>
                                                    <span class="col-md-1 drop-btn" style="margin-left:-10px;">
                                                        <i class="fa fa-angle-up hidden" ></i>
                                                        <i class="fa fa-angle-down "></i>
                                                    </span>

                                            	</div>
                                            </td>
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">行业类型：</span>
                                            		<span class="col-md-10"><?= $subject->industry_name; ?></span>
                                            	</div>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #e7eaec;">
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">税务类型：</span>
                                            		<span class="col-md-10"><?= $subject->getTxtName(); ?></span>
                                            	</div>
                                            </td>
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">官网地址：</span>
                                            		<span class="col-md-10"><?= $subject->official_website; ?></span>
                                            	</div>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #e7eaec;">
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">备案电话：</span>
                                            		<span class="col-md-10"><?= $subject->filing_tel; ?></span>
                                            	</div>
                                            </td>
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">备案邮箱：</span>
                                            		<span class="col-md-10"><?= $subject->filing_email; ?></span>
                                            	</div>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #e7eaec;">
                                            <td class="col-md-6" style="border: none;">
                                            	<div class="row" style="padding: 15px 0;">
                                            		<span class="col-md-2">备注描述：</span>
                                            		<span class="col-md-10"><?= $subject->company_remark; ?></span>
                                            	</div>
                                            </td>
                                            <td class="col-md-6" style="border: none;">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div>
                                    <table class="table">
                                    	<tr style="border-bottom: 1px dashed #e7eaec;">
                                            <td style="border: none;">
                                            	<div class="row" style="padding:15px 0 15px 45px;">
                                            		<span class="col-md-1">姓名：</span>
                                            		<span class="col-md-11"><?= $subject->region ?></span>
                                            	</div>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #e7eaec;">
                                            <td style="border: none;">
                                            	<div class="row" style="padding:15px 0 15px 45px;">
                                            		<span class="col-md-1">身份证：</span>
                                            		<span class="col-md-11"><?= $subject->name; ?></span>
                                            	</div>
                                            </td>
                                        </tr>
                                        <tr style="border-bottom: 1px dashed #e7eaec;">
                                            <td style="border: none;">
                                            	<div class="row" style="padding:15px 0 15px 45px;">
                                            		<span class="col-md-1">户籍地址：</span>
                                            		<span class="col-md-11"><?= $subject->scope; ?></span>
                                            	</div>
                                            </td>
                                        </tr>
                                        
                                    </table>
                                </div>
                                
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
$this->registerJs(<<<JS
        var content=$(".drop-content").html();
        if(content==""){
            $(".drop-btn").hide();
        }else{
            $(".drop-btn").show()
        }
        $(".drop-btn").click(function(){
            var height=$(".drop-content").css("height");
            if(height == "20px"){
                $(".drop-content").css("height","100%");
            }else{
                $(".drop-content").css("height","20px");
            }
            $(this).children('.fa-angle-up').toggleClass('hidden');
            $(this).children('.fa-angle-down').toggleClass('hidden');
        })
JS
);
    ?>
