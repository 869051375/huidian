
环境依赖
===============================
```
centos 7
php 5.6.30
nginx 1.10.3
mysql 5.7.17
redis 3.2.8
```

数据迁移安装方式
===============================
```
./yii migrate
```

数据迁移更新方式
===============================
```
./yii migrate -p='@app/migrations/update_scheme'
```

短信发送队列处理，在linux中添加 crontab
===============================
```
crontab -e -u nginx
*/1 * * * * path/to/yii sms/send
```

数据统计脚本 crontab
===================
```
crontab -e -u nginx
1 0 * * * path/to/yii statistics/record
```

自动取消已过期未支付的订单 crontab
===================
```
crontab -e -u nginx
30 0 * * * path/to/yii order-cancel/run
```

邮件发送处理，在linux中添加 crontab
===============================
```
crontab -e -u nginx
*/1 * * * * path/to/yii email/send
```

商机的预计成交时间到期处理，在linux中添加 crontab
===============================
```
crontab -e -u nginx
*/1 * * * * path/to/yii message-remind/opportunity-predict-deal-timeout
```

商机的下次跟进时间到期处理，在linux中添加 crontab
===============================
```
crontab -e -u nginx
*/1 * * * * path/to/yii message-remind/opportunity-next-follow-timeout
```

订单实施节点超时报警处理，在linux中添加 crontab
===============================
```
crontab -e -u nginx
*/1 * * * * path/to/yii message-remind/order-timeout
```

预计利润计算，在linux中添加 crontab
===============================
```
crontab -e -u nginx
*/1 * * * * path/to/yii expected-profit-settlement/settlement
```

业绩报表计算，在linux中添加 crontab
===============================
```
crontab -e -u nginx
*/1 * * * * path/to/yii settlement-performance/settlement
```

查询符合放入商机公海的商机，每天凌晨0-3点每小时执行一次，在linux中添加 crontab
===============================
```
crontab -e -u nginx
0 0-3/1 * * * path/to/yii opportunity-check/run
```

查询符合放入线索公海的线索，执行时间待定，在linux中添加 crontab
===============================
```
crontab -e -u nginx
0 0-3/1 * * * path/to/yii clue-public/run
```

查询电商信息满足条件的放入线索公海 目前一分钟执行一次
===============================
```
crontab -e -u nginx
*/1 * * * * path/to/yii clue-data-synchronization/run
```

查询符合放入客户公海的客户，每天凌晨0-3点每小时执行一次，在linux中添加 crontab
===============================
```
crontab -e -u nginx
0 0-3/1 * * * path/to/yii customer-check/run
```

账簿业绩每天凌晨执行,预计利润计算之后计算在linux中添加 crontab
===============================
```
crontab -e -u nginx
1 0 * * * path/to/yii order-calculate-collect/collect
```

账簿提成每天凌晨执行在linux中添加 crontab
===============================
```
crontab -e -u nginx
1 0 * * * path/to/yii order-performance-collect/collect
```

CRM客户管理客户回收执行规则在linux中添加 每天凌晨执行一次 
===============================
```
crontab -e -u nginx
*/1 * * * * path/to/yii customer-recovery/run
```

CRM商机管理商机回收执行规则在linux中添加 每天凌晨执行一次 
===============================
```
crontab -e -u nginx
*/1 * * * * path/to/yii niche/run
```

CRM客户管理 旧数据分享标签补充执行规则在linux中添加 只需上线手动执行一遍即可 
===============================
```
crontab -e -u nginx
path/to/yii niche/run
```

