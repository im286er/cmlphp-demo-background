# cmlphp-demo-background
基于cmlphp和adminlte开发的一个权限管理后台示例。包含完整的用户管理、权限管理、操作日志

#### 注意事项
##### 下载后请修改 
 * `proj3e9xooelsxooeeewsb/Config/common.php`中的 *auth_key*的值
 * 如果不能将站点根目录配置到public下。安全起见请修改目录`proj3e9xooelsxooeeewsb`为其它目录名。同时修改public/index.php入口文件中的相应的`proj3e9xooelsxooeeewsb`为新的目录名 
 
##### 相关的数据库文件为 根目录下的`db.sql`

##### 框架使用请参考[相关手册](http://cmlphp.com/) 

##### 初始用户名密码  admin 123456

##### 框架只要php5.4+版本即可运行，`public/index.php` 入口文件中使用了php5.5的语法`::class`用来获取类名。如果php版本< 5.5直接把`xxx::class`改成相应的字符串即可
> 如：\Cml\ErrorOrException::class直接改成'\Cml\ErrorOrException'

#### 以下为截图
![](http://o7v4k1oiv.bkt.clouddn.com/background-login.jpg)
![](http://o7v4k1oiv.bkt.clouddn.com/background-index.jpg)
![](http://o7v4k1oiv.bkt.clouddn.com/background-log.jpg)
![](http://o7v4k1oiv.bkt.clouddn.com/background-menu.jpg)
