# Spring
PHP framework.

 ## nginx setting 
 <pre><code>
 if (!-e $request_filename){
     rewrite ^/([a-zA-Z]+)/?(.*)? /index.php?mod=$1&action=index&$2 last;
     rewrite ^/([a-zA-Z]+)/([a-zA-Z]+)/?(.*)? /index.php?mod=$1&action=$2&$3 last;
 }
 </code></pre>
