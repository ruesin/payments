# 在线支付
----
## 为什么写？
## 如何使用
## 例子
#### 3X01
将 `examples/composer.json` 的`PSR-4`修改为对应路径：
```
"autoload": {
	"psr-4": {
		"Ruesin\\Payments\\": "/home/sin/project/payments/src"
	}
}
```
执行 `composer dump-autoload` 重载自动加载文件。
#### 3X02
修改各支付方式下 `start.php` 的支付配置，例如：
```
$config = array(
    'notify_url' => 'http://local.payments.com/alipay/notify.php',
    'return_url' => 'http://local.payments.com/alipay/return.php',
    'partner'    => '2088123456789012',
    'input_charset'  => 'utf-8',
    'sign_type'      => 'MD5',
    'md5_key'        => 'abcdefghijklmnopqrstuvwxyz123456',
    'cacert'         => TEST_PATH.'alipay/config/cacert.pem',
);
```
#### 3X03
访问支付方式文件夹下的 `submit.php` 即可请求，可自行修改订单信息：
```
$order = array(
    'out_trade_no' => 'N'.time(),
    'name'         => '支付宝测试',
    'money'        => '0.01',
    'desc'         => '支付宝测试描述'
);
```