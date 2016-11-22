#   消息定义

## 消息类型
1.  login [c.s]
1.  loginResponse[s.c]
1.  userOnline[s.c]
1.  userOffline[s.c]
1.  managerOnline[s.c]
1.  managerOffline[s.c]
1.  loadHistoryMessage[c.s]
1.  historyMessageReceive[s.c]
1.  sendMessage[c.s]
1.  receiveMessage[s.c]
1.  loadOnlineUserList[c.s]
1.  onlineUserListReceive[s.c]

##  用户登录 login
*   cmd:string = login
*   uid:string [null]
*   username:string [null]
*   type: int 用户类型(1客户 2管理员)
*   token: string 管理员登录单点登录获取 浏览用户通过cookie获取

##  用户登录结果 loginResponse
*   cmd:string = loginResponse
*   uid:string 会员id
*   code: int 登录状态
*   newToken: string 根据需要返回
*   error: 错误信息

##  接收消息


# 模型定义

## 用户
*   uid: string
*   username: string
*   type : int (1客户 2客服 3系统) 
*   phone: string
*   email: string
*   token:  string (可能根据电话号码改变，或是定期改变)

## 在线客户
*   ip: string
*   location: url
*   fd: int
*   sid: int [0] 客服id
*   uid: int [0] 用户id

## 在线客服
*   ip: string
*   fd: int
*   id: int 客服id

## 消息 用户to用户
*   message: string
*   from_uid: string
*   type : int (1用户消息 2客服消息 3系统消息)
*   receive_uid: string 接收方uid 
*   stat: int 发送状态

## 登录日志
*   uid:string
*   type:int
*   ip:string
*   location:url
*   isMath: boolean 是否有匹配
 
# 存储结构
## 永久存储
1.  user
2.  message
3.  messageReady 未读消息
4.  messageReadyCounter 未读消息数量

## 内存存储
1.  客户在线列表
1.  客服在线列表
 
# 流程
## 符号约束
1.  {消息}
1.  <模型>
1.  [存储结构]

## 登录流程

1.  [client] 客户端发 {登录}, 
1.  [server] 验证登录, 
        客服用户
            验证 token，是否已经单点登录, 是获取uid， 获取User
        客户用户
            查看<user>.token, 返回User, 不存在建立User
        获取 <user>, 查找[xxOnline] 是否存在, 
        如果已经登录返回错误
1.  [server] 生成<xx在线> 并更新 [xxOnline]
1.  [server] 如果是 <客户用户>, 
        分配客服, 根据 [serviceOnline] 设置 <xx在线>.sid, 
        发送 {userOnline} 到分配客服
1.  [server] 如果是 <客服用户> 
        更新 [在线客户].sid, 限制数量; 
        发送 {loadOnlineUserList} 消息包; 
        发送 {managerOnline} 给客户端
1.  [server] 发送 {登录成功} 消息包返回客户端
1.  [server] 发送 {历史信息} 消息包返回客户端

## 发送消息流程
1.  [client] 客户端发送 {消息}, 
1.  [server] 进行有效判断
    根据 {消息}.from_uid 查找 [xxxOnline], 得到<xx用户> 
    判断 <xx在线>.fd == client.fd 是否有效,
    有效：存储当前信息, 
    无效: 关闭连接
    
    {消息}.type == 1
    <xx在线>.sid得到 <在线客服>， 向<在线客服>.fd 发送信息
    
    {消息.type} == 2
    {消息}.receive_uid 查找 <在线客户>, 向<在线客户>.fd发送消息
    
    未在线 
    存储消息到 [messageReady] 更新 [messageReadyCounter]
    
## 接收消息流程
1.  [client] 客户接收消息 根据消息类型做相应处理

# 特性
*   用户可以发送留言信息， 客户或客服下次登录可以继续查看
*   只能一处登录
*   显示未读取的信息数量
