<?php

class writer {
    public function display ($str1,$str2,$arr){
        echo $str1.'<br>'.$str2.'<br>';
        print_r($arr);
    }
}
class shop {
    protected $writer;
    public function __construct() {
        $this->writer= new writer();
    }
    public function __call($name, $arguments) {
        if (method_exists($this->writer, $name))
            call_user_func_array(array($this->writer,'display'), $arguments);
    }
}
?>
<html>
    <head>
        
    </head>
    <body>
        <?php $myShop = new shop();
        $myShop->display('One','Two',array('one','2','3')); ?>
    </body>
</html>
