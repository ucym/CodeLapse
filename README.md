# D5 - PHP 5.2.x Library [![Build Status](https://drone.io/github.com/4af52202f/d5/status.png)](https://drone.io/github.com/4af52202f/d5/latest)


## How to use
1. copy "d5" directory to your project.
2. write require in d5 using files.
``` php
<?php
// Load d5 bootstrap.
require 'd5/bs.php';

Do anything...
```


## Feature
- Multidimension array utility
  - Arr
- Input wrapper
  - Input
  - Cookie
  - Session
- Smarty wrapper
- Late static binding on PHP 5.2.x
  - LateBinding
- Database Utility
  - DB
  - DB_Resultset (Iterable resultset abstraction.)  
  - DB_CrudModel  
    Active Record like model.(Currently not function as ORM.)
  - DB_Connection  
    Automatic driver switching PDO class or MySQL functions.(Currently only support MySQL.)
