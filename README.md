# CodeLapse [![Build Status](https://drone.io/github.com/ucym/CodeLapse/status.png)](https://drone.io/github.com/ucym/CodeLapse/latest)
Fast coding for Small-scale PHP projects.

## How to use
1. copy "cl" directory to your project.
2. write require in CodeLapse using files.
``` php
<?php
// Load CodeLapse bootstrap.
require 'cl/bs.php';

Do anything...
```


## Feature
- Multidimension array utility
  - Arr
- Input wrapper
  - Input
  - Cookie
  - Session
- Mail wrapper
    - Support plain text & HTML
    - Support multiple file attach.
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
