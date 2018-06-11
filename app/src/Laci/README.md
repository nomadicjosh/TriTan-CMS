
LaciDB - Flat File JSON DBMS
======================================

[![Build Status](https://img.shields.io/travis/emsifa/laci-db.svg?style=flat-square)](https://travis-ci.org/emsifa/laci-db)
[![License](http://img.shields.io/:license-mit-blue.svg?style=flat-square)](http://doge.mit-license.org)

## Overview

LaciDB adalah flat file DBMS dengan format penyimpanan berupa JSON. Karena format JSON, LaciDB bersifat *schemaless* seperti hanya NoSQL lainnya. Sebuah record dapat memiliki kolom yang berbeda-beda.

Dalam LaciDB tidak ada istilah table, yang ada adalah collection. Collection pada LaciDB mewakili sebuah file yang menyimpan banyak records (dalam format JSON).

Nama 'Laci' sendiri diambil karena fungsi dan prosesnya seperti laci pada meja/lemari. Laci pada meja/lemari umumnya tidak membutuhkan kunci (autentikasi), cukup buka > ambil sesuatu dan|atau taruh sesuatu > tutup. Pada LaciDB pun seperti itu, setiap query akan membuka file > eksekusi query (select|insert|update|delete) > file ditutup. Laci juga seperti yang kita ketahui adalah tempat untuk menaruh barang-barang kecil. Bukan barang-barang besar seperti gudang atau lemari.

Untuk itu LaciDB bukan untuk:

* Menyimpan database dengan ukuran yang besar.
* Menyimpan database yang membutuhkan keamanan tingkat tinggi.

LaciDB dibuat untuk:

* Menangani data-data yang kecil seperti pengaturan, atau data-data kecil lain.
* Untuk kalian yang menginginkan database portable yang mudah untuk diimport/export dan backup.
* Untuk kalian yang menginginkan database yang mudah diedit sendiri tanpa menggunakan software khusus. Notepad pun bisa.

## Cara Kerja

Cara kerja LaciDB pada dasarnya hanyalah mengalirkan array hasil `json_decode` kedalam 'pipa-pipa' yang berfungsi sebagai *filtering*, *mapping*, *sorting*, *limiting* sampai akhirnya hasilnya akan di eksekusi untuk diambil nilainya, diubah nilainya atau dibuang (baca: dihapus).

Berikut penjelasan terkait prosesnya:

### Filtering

Untuk melakukan filtering kamu dapat menggunakan method `where` dan `orWhere`. Ke2 method tersebut dapat menerima parameter `Closure` atau beberapa parameter `key, operator, value`.

### Mapping

Mapping digunakan untuk membentuk nilai yang baru pada setiap record yang telah difilter.

Berikut beberapa method untuk mapping record:

#### `map(Closure $mapper)`

Untuk mapping records pada collection yang telah difilter.

#### `select(array $columns)`

Mapping records untuk mengambil kolom-kolom tertentu saja.

#### `withOne(Collection|Query $relation, $key, $otherKey, $operator, $thisKey)`

Untuk mengambil relasi 1:1.

#### `withMany(Collection|Query $relation, $key, $otherKey, $operator, $thisKey)`

Untuk mengambil relasi 1:n.

### Sorting

Sorting digunakan untuk mengurutkan data yang telah difilter dan dimapping. Untuk melakukan sorting kamu dapat menggunakan method `sortBy($key, $ascending)`. Parameter `$key` dapat berupa string key/kolom yang ingin diurutkan atau `Closure` jika ingin mengurutkan berdasarkan nilai yang dikomputasi terlebih dahulu.

### Limiting/Taking

Setelah data selesai difilter, dimapping, dan disorting, kamu dapat memotong dan mengambil sebagian data dengan method `skip($offset)` atau `take($limit, $offset)`.

### Executing

Setelah difilter, dimapping, disorting, dan disisihkan, langkah selanjutnya adalah ekseskusi hasilnya.

Berikut beberapa method untuk executing:

#### `get(array $columns = null)`

Mengambil kumpulan records pada collection. Jika ingin mengambil kolom tertentu definisikan kolom kedalam array `$columns`.

#### `first(array $columns = null)`

Mengambil (sebuah) record pada collection. Jika ingin mengambil kolom tertentu definisikan kolom kedalam array `$columns`.

#### `count()` 

Mengambil banyak data dari collection.

#### `sum($key)` 

Mengambil total key tertentu pada collection.

#### `avg($key)` 

Mengambil rata-rata key tertentu pada collection.

#### `min($key)` 

Mengambil nilai terendah dari key tertentu pada collection.

#### `max($key)` 

Mengambil nilai tertinggi dari key tertentu pada collection.

#### `lists($key, $resultKey = null)` 

Mengumpulkan dan mengambil key tertentu kedalam array pada collection.

#### `insert(array $data)` 

Insert data baru kedalam collection.

#### `inserts(array $listData)` 

Insert beberapa data baru sekaligus kedalam collection. Note: `insert` dan `inserts` tidak dapat dilakukan setelah query di filter atau di mapping.

#### `update(array $newData)` 

Mengupdate data pada records didalam collection yang difilter dan dimapping.

#### `save()` 

Sama seperti update. Hanya saja `save` akan menyimpan record berdasarkan hasil mapping, bukan berdasarkan `$newData` seperti pada update.

#### `delete()` 

Menghapus data pada collection yang difilter dan dimapping.

#### `truncate()` 

Menghapus seluruh data. Tidak membutuhkan filtering dan mapping terlebih dahulu.

## Contoh

#### Inisialisasi

```php
use Emsifa\Laci\Collection;

require 'vendor/autoload.php';

$collection = new Collection(__DIR__.'/users.json');
```

#### Insert Data

```php
$user = $collection->insert([
    'name' => 'John Doe',
    'email' => 'johndoe@mail.com',
    'password' => password_hash('password', PASSWORD_BCRYPT)
]);
```

`$user` akan berupa array seperti ini:

```php
[
    '_id' => '58745c13ad585',
    'name' => 'John Doe',
    'email' => 'johndoe@mail.com',
    'password' => '$2y$10$eMF03850wE6uII7UeujyjOU5Q2XLWz0QEZ1A9yiKPjbo3sA4qYh1m'
]
```

> '_id' adalah `uniqid()`

#### Find Single Record By ID

```php
$user = $collection->find('58745c13ad585');
```

#### Find One

```php
$user = $collection->where('email', 'johndoe@mail.com')->first();
```

#### Select All

```php
$data = $collection->all();
```

#### Update

```php
$collection->where('email', 'johndoe@mail.com')->update([
    'name' => 'John',
    'sex' => 'male'
]);
```

> Return value is count affected records

#### Delete

```php
$collection->where('email', 'johndoe@mail.com')->delete();
```

> Return value is count affected records

#### Multiple Inserts

```php
$bookCollection = new Collection('db/books.json');

$bookCollection->inserts([
    [
        'title' => 'Foobar',
        'published_at' => '2016-02-23',
        'author' => [
            'name' => 'John Doe',
            'email' => 'johndoe@mail.com'
        ],
        'star' => 3,
        'views' => 100
    ],
    [
        'title' => 'Bazqux',
        'published_at' => '2014-01-10',
        'author' => [
            'name' => 'Jane Doe',
            'email' => 'janedoe@mail.com'
        ],
        'star' => 5,
        'views' => 56
    ],
    [
        'title' => 'Lorem Ipsum',
        'published_at' => '2013-05-12',
        'author' => [
            'name' => 'Jane Doe',
            'email' => 'janedoe@mail.com'
        ],
        'star' => 4,
        'views' => 96
    ],
]);

```

#### Find Where

```php
// select * from books.json where author[name] = 'Jane Doe'
$bookCollection->where('author.name', 'Jane Doe')->get();

// select * from books.json where star > 3
$bookCollection->where('star', '>', 3)->get();

// select * from books.json where star > 3 AND author[name] = 'Jane Doe'
$bookCollection->where('star', '>', 3)->where('author.name', 'Jane Doe')->get();

// select * from books.json where star > 3 OR author[name] = 'Jane Doe'
$bookCollection->where('star', '>', 3)->orWhere('author.name', 'Jane Doe')->get();

// select * from books.json where (star > 3 OR author[name] = 'Jane Doe')
$bookCollection->where(function($book) {
    return $book['star'] > 3 OR $book['author.name'] == 'Jane Doe';
})->get();
```

> Operator can be '=', '<', '<=', '>', '>=', 'in', 'not in', 'between', 'match'.

#### Mengambil Kolom/Key Tertentu

```php
// select author, title from books.json where star > 3
$bookCollection->where('star', '>', 3)->get(['author.name', 'title']);
```

#### Alias Kolom/Key

```php
// select author[name] as author_name, title from books.json where star > 3
$bookCollection->where('star', '>', 3)->get(['author.name:author_name', 'title']);
```

#### Mapping

```php
$bookCollection->map(function($row) {
    $row['score'] = $row['star'] + $row['views'];
    return $row;
})
->sortBy('score', 'desc')
->get();
```

#### Sorting

```php
// select * from books.json order by star asc
$bookCollection->sortBy('star')->get();

// select * from books.json order by star desc
$bookCollection->sortBy('star', 'desc')->get();

// sorting calculated value
$bookCollection->sortBy(function($row) {
    return $row['star'] + $row['views'];
}, 'desc')->get();
```

#### Limit & Offset

```php
// select * from books.json offset 4
$bookCollection->skip(4)->get();

// select * from books.json limit 10 offset 4
$bookCollection->take(10, 4)->get();
```

#### Join

```php
$userCollection = new Collection('db/users.json');
$bookCollection = new Collection('db/books.json');

// get user with 'books'
$userCollection->withMany($bookCollection, 'books', 'author.email', '=', 'email')->get();

// get books with 'user'
$bookCollection->withOne($userCollection, 'user', 'email', '=', 'author.email')->get();
```

#### Map & Save

```php
$bookCollection->where('star', '>', 3)->map(function($row) {
    $row['star'] = $row['star'] += 2;
    return $row;
})->save();
```

#### Transaction

```php
$bookCollection->begin();

try {

    // insert, update, delete, etc 
    // will stored into variable (memory)

    $bookCollection->commit(); // until this

} catch(Exception $e) {

    $bookCollection->rollback();

}
```

#### Macro Query

Macro query memungkinkan kita menambahkan method baru kedalam instance `Emsifa\Laci\Collection` sehingga dapat kita gunakan berulang-ulang secara lebih fluent.

Sebagai contoh kita ingin mengambil data user yang aktif, jika dengan cara biasa kita dapat melakukan query seperti ini:

```php
$users->where('active', 1)->get();
```

Cara seperti diatas jika digunakan berulang-ulang, terkadang kita lupa mengenali user aktif itu yang nilai `active`-nya `1`, atau `true`, atau `'yes'`, atau `'YES'`, atau `'yes'`, atau `'y'`, atau `'Y'`, atau `'Ya'`, atau `'ya'`, dsb?

Jadi untuk mempermudahnya, kita dapat menggunakan macro sebagai berikut:

```php
$users->macro('active', function ($query) {
    return $query->where('active', 1);
});
```

Sehingga kita dapat mengambil user aktif dengan cara seperti ini:

```php
$users->active()->get();
```

Tampak lebih praktis bukan?