# Hyperf-Di-Heighten
### 作用
解决`Hyperf`2.1 版本[Inject 或 Value 注解不生效](https://hyperf.wiki/2.1/#/zh-cn/quick-start/questions?id=inject-%e6%88%96-value-%e6%b3%a8%e8%a7%a3%e4%b8%8d%e7%94%9f%e6%95%88)
### 使用
修改`confg/autoload/annotations.php`
```php
<?php
return [
    'scan' => [
        ...
        // scan 增加class_map
        'class_map' => [
            Hyperf\Di\Resolver\ObjectResolver::class => BASE_PATH . '/vendor/assert6/hyperf-di-heighten/classmap/ObjectResolver.php',
            Hyperf\Di\Annotation\Inject::class => BASE_PATH . '/vendor/assert6/hyperf-di-heighten/classmap/Inject.php',
            Hyperf\Di\Annotation\Scanner::class => BASE_PATH . '/vendor/assert6/hyperf-di-heighten/classmap/Scanner.php',
        ],
    ],
];
```