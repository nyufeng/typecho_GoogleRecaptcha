# typecho_GoogleRecaptcha
为 Typecho Admin 开启Google invisible Recaptcha 验证  

### 开启 Google reCAPTCHA
前往 [Google reCAPTCHA](https://www.google.com/recaptcha/admin#list) 开启 reCAPTCHA 并 获取参数.
1. 选择 Invisible reCAPTCHA ,填写 Domains 进行注册.
![Snipaste_2018-01-09_08-53-56.png](https://i.loli.net/2018/01/09/5a5412c0cfae7.png)
2. 获取 Site key & Secret key
![Snipaste_2018-01-09_08-55-47.png](https://i.loli.net/2018/01/09/5a5413230deab.png)

### 插件配置
将 Site Key & Secret Key 填入对应的配置项

配置加载 JS 使用的地址

配置后端验证 response 使用的地址
