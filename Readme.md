# 说明
*   静态文件的解析不存在错误或异常出现
*   动态的解析过程，可能存在相关action有Exception, 
    在DynamicParser::execute()中被捕获， 
    返回客户端500状态码，且记录错误日志
