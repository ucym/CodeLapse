# D5 - PHP 5.2.x Library [![Build Status](https://drone.io/bitbucket.org/4af52202f/d5/status.png)](https://drone.io/bitbucket.org/4af52202f/d5/latest)

## Concept
- Simple  
  include 1 file for using.  

- Mixable  
  D5 are stored to one root directory and using original AutoLoader.  
  Can mix in other library or frameworks. (but that's in some cases hell.)

- Degradable (now planned)  
  Every class files not depend to other files.  
  Related classes and methods will embed to one class.  
  (By method dependency resolver compile)  

# Initialize
```php
<?php
// Load d5 bootstrap file.
require 'd5/bs.php';

## Some codes...
```