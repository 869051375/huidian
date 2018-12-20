1.标准返回数据格式：
-----------------

```
// 请求成功
{code: 200, data: 多种类型, message: "OK"}

// 因用户请求有误的情况，比如地址表单参数校验失败
{code: 400, data: 多种类型, message: "消息内容"}

// API接口不存在的情况
{code: 404, data: 多种类型, message: "消息内容"}

// 用户没有登录的情况
{code: 401, data: 多种类型, message: "消息内容"}

// 用户没有权限的情况
{code: 403, data: 多种类型, message: "消息内容"}

// 服务器或系统异常情况
{code: 500, data: 多种类型, message: "消息内容"}
```


2.普通数据列表响应格式：
--------------------

```
{code: 200, data: [{id:123, ...}, ...], message: "OK"}
```


3.带分页的数据列表响应格式：
------------------------

```
{code: 200, data: {totalCount: 1000, pageSize: 10, page: 1, pageCount: 100, items: [{...}, ...]}, message: "OK"}```
// totalCount 总数据条数
// pageSize 每页数量条数
// page 当前页号
// pageCount 总页数
// items 当前页的数据数组，items可以用其他名称定义，比如products、orders等
```