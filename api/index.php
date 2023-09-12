<?php
/* start the relfo */
new relfo('9r3i/relfo');

/**
 * 9r3i\release-forwarder
 * ~ github only via vercel
 * authored by 9r3i
 * https://github.com/9r3i/relfo
 * started at september 9th 2023
 **/
class relfo{
  const version='1.2.0';
  private $mime=[];
  private $base=''; // base of assets of releases
  private $code=''; // code assets in zip and tar.gz
  public function __construct(string $repo){
    $iptrn='https://github.com/%s/archive/refs/tags/';
    $ptrn='https://github.com/%s/releases/download/';
    $this->base=sprintf($ptrn,$repo);
    $this->code=sprintf($iptrn,$repo);
    return $this->s();
  }
  /* start */
  private function s(){
    @set_time_limit(false);
    @date_default_timezone_set('Asia/Jakarta');
    $this->h();
    if(isset($_GET['file'])){
      $file=$_GET['file'];
    }else{
      $path=explode('?',$_SERVER['REQUEST_URI'])[0];
      $file=preg_replace('/^\//','',$path);
    }
    $base=preg_match('/^[^\/]+$/',$file)
      ?$this->code:$this->base;
    $url=$base.$file;
    $data=[];
    $cookie=$_GET['cookie']??'';
    $method=$_GET['method']??'GET';
    $header=[];
    $ua=$_GET['ua']??'Mozilla/5.0 (Windows NT 11; x128; rv:93; 9r3i) Gecko/20100101 Firefox/93';
    if($url==''){
      return $this->o('Error: Invalid URL.');
    }
    $c=$this->c($data,$cookie,$method,$header,$ua);
    $o=@fopen($url,'rb',false,$c);
    if(!is_resource($o)){
      return $this->o('Error: Failed to open file.');
    }
    /* get file size */
    $s=$this->m($o);
    if($s){
      header('Content-Length: '.$s);
    }
    header('HTTP/1.1 200 OK');
    /* default: application/octet-stream */
    header('Content-Type: '.$this->mime($file));
    /* read file */
    while(!@feof($o)){
      echo @fread($o,pow(512,0x02));
    }
    /* close file */
    @fclose($o);
    /* return as true */
    return true;
  }
  /* get mime type from file path
   *   $f = string of file path
   */
  private function mime($f=null){
    $r='application/octet-stream';
    if(!is_string($f)){return $r;}
    $t=array(
      'txt'=>'text/plain',
      'log'=>'text/plain',
      'ini'=>'text/plain',
      'html'=>'text/html',
      'css'=>'text/css',
      'php'=>'application/x-httpd-php',
      'js'=>'application/javascript',
      'json'=>'application/json',
      'xml'=>'application/xml',
      'mp4'=>'video/mp4',
      'mp3'=>'audio/mpeg',
      'wav'=>'audio/wav',
      'ogg'=>'audio/ogg',
      'png'=>'image/png',
      'jpe'=>'image/jpeg',
      'jpeg'=>'image/jpeg',
      'jpg'=>'image/jpeg',
      'gif'=>'image/gif',
      'zip'=>'application/zip',
      'rar'=>'application/x-rar-compressed',
      'pdf'=>'application/pdf',
    );
    $t=array_merge($t,$this->mime);
    $a=explode('.',strtolower(basename($f)));
    $e=array_pop($a);
    return array_key_exists($e,$t)?$t[$e]:$r;
  }
  /* output */
  private function o($s=false){
    $s=is_string($s)?$s:'Error: Unknown error.';
    header('HTTP/1.1 200 OK');
    header('Content-Length: '.strlen($s));
    return exit($s);
  }
  /* meta data */
  private function m($o,$x=false){
    if(!is_resource($o)){return false;}
    $h=@stream_get_meta_data($o);
    if(!is_array($h)||!isset($h['wrapper_data'])){return false;}
    if($x){return $h;}
    $r=false;
    foreach($h['wrapper_data'] as $v){
      if(preg_match('/Content-Length:\s?(\d+)/i',$v,$a)){
        $r=$a[1];break;
      }
    }return $r;
  }
  /* context
   * ~ context for fopen
   * @parameter:
   *   $d = array of post data
   *   $c = string of cookie; default: ''
   *   $m = string of method; default: GET
   *   $h = array of header; default: null
   *   $u = string of user agent; default: 9r3i
   * @return: resource of stream context
   * @usage:
   *   $o=fopen('https://domain.com/','rb',false,$this->c($d,$c,$m,$h,$u));
   */
  public function c($d=[],$c='',$m='GET',$h=[],$u=false){
    $ms=['GET','POST','OPTIONS','PUT'];
    $d=is_array($d)?$d:[];
    $o=http_build_query($d);
    $hs='Content-Type: application/x-www-form-urlencoded;charset=utf-8;'."\r\n";
    $hs.='Content-Length: '.strlen($o);
    $u=is_string($u)?'User-Agent: '.$u
      :'User-Agent: Mozilla/5.0 (Windows NT 11; x128; rv:93; 9r3i) Gecko/20100101 Firefox/93';
    $h=is_array($h)?implode("\r\n",$h):'';
    $hs.="\r\n".$u."\r\ncookie:".$c."\r\n".$h."\r\n";
    $m=is_string($m)&&in_array($m,$ms)?$m:'GET';
    return @stream_context_create([
      'http'=>[
        'header'=>$hs,
        'method'=>$m,
        'content'=>$o,
      ],
      'ssl'=>[
        'cafile'=>__DIR__.'/cacert.pem',
        'verify_peer'=>false,
        'verify_peer_name'=>false,
        'crypto_method'=>STREAM_CRYPTO_METHOD_ANY_CLIENT,
      ]
    ]);
  }
  /* header */
  private function h(){
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Request-Method: POST, GET, OPTIONS');
    header('Access-Control-Request-Headers: X-PINGOTHER, Content-Type');
    header('Access-Control-Max-Age: 86400');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: text/plain;charset=utf-8;');
    if(isset($_SERVER['REQUEST_METHOD'])&&strtoupper($_SERVER['REQUEST_METHOD'])=='OPTIONS'){
      header('Content-Language: en-US');
      header('Content-Encoding: gzip');
      header('Content-Length: 0');
      header('Vary: Accept-Encoding, Origin');
      header('HTTP/1.1 200 OK');
      exit;
    }
  }
}


