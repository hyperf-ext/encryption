# Hyperf 加密组件

组件当前仅实现了 AES 加密（OpenSSL 所提供的 AES-256 和 AES-128 加密）

所有组件加密之后的结果都会使用消息认证码（MAC）签名，使其底层值不能在加密后再次修改。

> 移植自 [illuminate/encryption](https://github.com/illuminate/encryption )。

## 安装

```shell script
composer require hyperf-ext/encryption
```

## 发布配置

```shell script
php bin/hyperf.php vendor:publish hyperf-ext/encryption
```

> 配置文件位于 `config/autoload/encryption.php`。

## 设置

在使用之前，你必须先设置配置文件中的 `key` 选项。你应当使用 `php bin/hyperf.php gen:key` 命令来生成密钥，这条命令会使用 PHP 的安全随机字节生成器来构建密钥。如果这个 `key` 值没有被正确设置，则无法进行加密。

## 使用

### 加密

你可以使用 `\HyperfExt\Encryption\Crypt` 类来加密一个值。所有加密过的值都会使用消息认证码 (MAC) 来签名，以检测加密字符串是否被篡改过：

```php
<?php

declare(strict_types=1);

namespace App\Http\Controller;

use Hyperf\HttpServer\Request;
use HyperfExt\Encryption\Crypt;

class UpdatePasswordController
{
    public function update(Request $request)
    {
        // ……

        $user->fill([
            'secret' => Crypt::encrypt($request->input('secret'))
        ])->save();
    }
}
```

加密的时候，对象和数组加密过的值都需要通过 serialize 序列化后传递。因此，非 PHP 客户端接收加密值，需要对数据进行 unserialize 反序列化。如果想要在不序列化的情况下加密或解密值，你可以将 `Crypt::encrypt` 方法的第二个参数设置为 `false`：

```php
use HyperfExt\Encryption\Crypt;

$encrypted = Crypt::encrypt('Hello world.', false);
$decrypted = Crypt::decrypt($encrypted);
```

### 解密

你可以使用 `Crypt::decrypt` 方法来进行解密。如果该值不能被正确解密，例如 MAC 无效时，会抛出异常 `HyperfExt\Encryption\Exception\DecryptException`：

```php
use HyperfExt\Encryption\Crypt;
use HyperfExt\Encryption\Exception\DecryptException;

try {
    $decrypted = Crypt::decrypt($encryptedValue);
} catch (DecryptException $e) {
    // 
}
```

### 使用指定驱动

```php
use HyperfExt\Encryption\Crypt;

$hasher = Crypt::getDriver('rsa'); // RSA 尚未实现
$hasher->encrypt('Hello world.', false);
```

### 使用自定义加密类

实现 `\HyperfExt\Hashing\Encryption\SymmetricDriverInterface` 或 `\HyperfExt\Hashing\Encryption\AsymmetricDriverInterface` 接口，并参照配置文件中的其他算法进行配置。
