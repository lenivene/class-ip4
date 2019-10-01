# GET REAL IP ADDRESS

Add class `(required)`
```PHP
require_once __DIR__ . '/class-ip4.php';
```

With usage examples

```PHP
// First Example
$ip4 = new IP4();

$ip = $ip4->get();

var_dump( $ip );

```

```PHP
// Second Example
$ip4 = IP4();
$ip = $ip4->get();

var_dump( $ip );
```

```PHP
// Third Example
global $IP4;

$ip = $IP4->get();

var_dump( $ip );
```