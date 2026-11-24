<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Headers: Content-Type, Authorization'); header('Access-Control-Allow-Methods: GET,POST,OPTIONS');
if($_SERVER['REQUEST_METHOD']==='OPTIONS'){http_response_code(204);exit;}
session_start();
function out($v,$s=200){http_response_code($s);echo json_encode($v);exit;}
function input(){return json_decode(file_get_contents('php://input'),true)??[];}
function db(){static $p=null;if(!$p){$p=new PDO('mysql:host='.(getenv('DB_HOST')?:'127.0.0.1').';dbname='.(getenv('DB_NAME')?:'focus_os').';charset=utf8mb4',getenv('DB_USER')?:'root',getenv('DB_PASS')?:'', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);}return $p;}
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);$script=dirname($_SERVER['SCRIPT_NAME']);if($script!=='.'&&$script!='/')$path=substr($path,strlen($script));$path='/'.trim($path,'/');if(str_starts_with($path,'/api.php'))$path=substr($path,8);$path='/'.trim($path,'/');
try{
if($path==='/health')out(['ok'=>true]);
if($path==='/session/start'&&$_SERVER['REQUEST_METHOD']==='POST'){$b=input();$q=db()->prepare('INSERT INTO sessions(user_id,mode,duration_seconds,started_at) VALUES(1,?,?,NOW())');$q->execute([in_array($b['mode']??'focus',['focus','short','long'])?$b['mode']:'focus',max(60,min(10800,(int)($b['duration']??1500)))]);$_SESSION['sid']=db()->lastInsertId();out(['ok'=>true]);}
if($path==='/session/end'&&$_SERVER['REQUEST_METHOD']==='POST'){if(!empty($_SESSION['sid'])){$q=db()->prepare('UPDATE sessions SET ended_at=NOW(),completed=1 WHERE id=? AND user_id=1');$q->execute([$_SESSION['sid']]);unset($_SESSION['sid']);}out(['ok'=>true]);}
if($path==='/stats'){$q=db()->query("SELECT COUNT(*) sessions,COALESCE(SUM(duration_seconds)/60,0) focus_minutes FROM sessions WHERE user_id=1 AND completed=1 AND mode='focus'");out($q->fetch(PDO::FETCH_ASSOC));}
if($path==='/history'){$q=db()->query('SELECT * FROM sessions WHERE user_id=1 ORDER BY started_at DESC LIMIT 100');out(['history'=>$q->fetchAll(PDO::FETCH_ASSOC)]);}
if($path==='/ai/chat'&&$_SERVER['REQUEST_METHOD']==='POST'){$b=input();$key=getenv('OPENAI_API_KEY');if(!$key)out(['error'=>'OPENAI_API_KEY is not configured on the server'],503);$msg=trim($b['message']??'');if(!$msg)out(['error'=>'Message is required'],422);$url=getenv('OPENAI_BASE_URL')?:'https://api.openai.com/v1/chat/completions';$data=['model'=>getenv('OPENAI_MODEL')?:'gpt-4o-mini','messages'=>[['role'=>'system','content'=>'You are a supportive, concise study focus coach. Give practical study plans and focus advice.'],['role'=>'user','content'=>$msg]]];$c=curl_init($url);curl_setopt_array($c,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$key],CURLOPT_POSTFIELDS=>json_encode($data),CURLOPT_TIMEOUT=>30]);$raw=curl_exec($c);$code=curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);$d=json_decode($raw?:'',true);if($code<200||$code>=300)out(['error'=>$d['error']['message']??'AI provider request failed'],502);out(['reply'=>$d['choices'][0]['message']['content']??'No reply']);}
out(['error'=>'Not found'],404);
}catch(Throwable $e){error_log((string)$e);out(['error'=>'Server/database error. Check PHP logs and configuration.'],500);}
?>