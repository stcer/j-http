# 客户端使用
*   服务端地址前缀 http://192.168.0.182:8060/cgi
*   友好显示结果参数 ?pretty=1

###   <s>订阅 Customer</s>
*   列表订阅者 /customer/list
*   删除订阅者 /Customer/del?id=<int>
*   编辑订阅者 /Customer/save?id=<int>&events=<array>&url=<string>&all=<boolean>&tryTimes=<int>

###   <s>消息 Message</s>
 *  推送消息 /message/push?broker=<string event_name>&msg=<string message_content>

###   <s>管理 Manager </s>
*   服务器状态 /manager/status, 各类统计信息
*   服务器重启 /manager/reload
*   服务器关闭 /manager/close

## client
*   push
*   get_history(event, date_start, date_stop)

# server(todo)
*   <s>config(redis server info, mysql server info)</s>
*   <s>Customer 结构定义, 存储(redis), 唯一性</s>
*   <s>日志的处理(将redis的数据迁移到mysql或mongodb，以减少内存)， 或者直接删除过期的数据</s>
*   <s>Error process, 定时在某一work id扫描错误队列并发送</s>
*   定制处理Customer过期

# bug
*   如果消息丢失，通过模块接口同步数据
*   <s>不能中止阻塞的task进程结束</s>


# upgrade
## server config
*   <s>redis直接设置Config静态变量</s>
*   dispatcher（发送消息）
*   <s>发送进程数量</s>

# todo
*   客户端
*   消息丢失的可能检查
*   客户端文档的完善
*   客户端订阅列表ui优化（增加分类，过滤，排序）




