<?php
declare(strict_types=1);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/TimeSlot.php';
require_once __DIR__ . '/models/Appointment.php';
function respond(array $data, int $status = 200): never { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); exit; }
function fail(string $message, int $status = 400): never { respond(['ok'=>false,'error'=>$message], $status); }
function requireLogin(): array { global $pdo; $id=(int)($_SESSION['user_id']??0); if(!$id)fail('Please sign in first.',401); $s=$pdo->prepare('SELECT id,name,email,role,is_active FROM users WHERE id=?');$s->execute([$id]);$u=$s->fetch();if(!$u || !(int)$u['is_active']){session_unset();session_destroy();fail('Session expired or account inactive.',401);}return $u; }
function requireRole(string $role): array {$u=requireLogin();if($u['role']!==$role)fail('Access denied.',403);return $u;}
function publicUser(array $u): array {return ['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role'],'active'=>(bool)$u['is_active'],'department'=>''];}
function data(): array { $raw=file_get_contents('php://input');$d=json_decode($raw?:'{}',true);if(!is_array($d))fail('Invalid request body.');return $d; }
function postOnly(): void {if($_SERVER['REQUEST_METHOD']!=='POST')fail('POST required.',405);if(!hash_equals($_SESSION['csrf']??'',(string)($_SERVER['HTTP_X_CSRF_TOKEN']??'')))fail('Security token expired. Refresh the page.',403);}
try {
 $pdo=(new Database())->connect();
 $action=(string)($_GET['action']??'bootstrap');
 if($action==='bootstrap'){
   $u=null; if(!empty($_SESSION['user_id'])){$s=$pdo->prepare('SELECT id,name,email,role,is_active FROM users WHERE id=?');$s->execute([(int)$_SESSION['user_id']]);$row=$s->fetch();if($row && (int)$row['is_active'])$u=publicUser($row);else {session_unset();}}
   $_SESSION['csrf']??=bin2hex(random_bytes(32));
   $users=[];$appointments=[];$slots=[];
   if($u){
     $people=$pdo->query("SELECT id,name,email,role,is_active FROM users WHERE role='advisor' AND is_active=1 ORDER BY name")->fetchAll();
     if($u['role']==='admin')$people=$pdo->query('SELECT id,name,email,role,is_active FROM users ORDER BY id')->fetchAll();
     $seen=[];foreach($people as $person){$seen[(int)$person['id']]=publicUser($person);}
     $seen[$u['id']]=$u;
     $query="SELECT a.id,a.student_id,a.slot_id,a.note,a.status,a.created_at,ts.advisor_id FROM appointments a JOIN time_slots ts ON a.slot_id=ts.id";
     $args=[];
     if($u['role']==='student'){$query.=' WHERE a.student_id=?';$args=[$u['id']];}
     if($u['role']==='advisor'){$query.=' WHERE ts.advisor_id=?';$args=[$u['id']];}
     $query.=' ORDER BY a.created_at DESC, a.id DESC';$st=$pdo->prepare($query);$st->execute($args);
     foreach($st->fetchAll() as $a){$appointments[]=['id'=>(int)$a['id'],'userId'=>(int)$a['student_id'],'slotId'=>(int)$a['slot_id'],'note'=>$a['note']??'','status'=>$a['status'],'createdAt'=>$a['created_at']];}
     if($u['role']==='advisor'){
       $st=$pdo->prepare('SELECT DISTINCT u.id,u.name,u.email,u.role,u.is_active FROM users u JOIN appointments a ON a.student_id=u.id JOIN time_slots ts ON ts.id=a.slot_id WHERE ts.advisor_id=?');$st->execute([$u['id']]);foreach($st->fetchAll() as $p)$seen[(int)$p['id']]=publicUser($p);
     }
     $users=array_values($seen);
     $sql="SELECT ts.id,ts.advisor_id,ts.slot_date,ts.start_time,ts.end_time,ts.capacity,COUNT(a.id) booked FROM time_slots ts LEFT JOIN appointments a ON a.slot_id=ts.id AND a.status IN ('pending','confirmed')";
     if($u['role']==='advisor'){$sql.=' WHERE ts.advisor_id='.(int)$u['id'];}
     $sql.=' GROUP BY ts.id,ts.advisor_id,ts.slot_date,ts.start_time,ts.end_time,ts.capacity ORDER BY ts.slot_date,ts.start_time';
     foreach($pdo->query($sql)->fetchAll() as $s){$slots[]=['id'=>(int)$s['id'],'advisorId'=>(int)$s['advisor_id'],'date'=>$s['slot_date'],'start'=>substr($s['start_time'],0,5),'end'=>substr($s['end_time'],0,5),'capacity'=>(int)$s['capacity'],'booked'=>(int)$s['booked']];}
   }
   respond(['ok'=>true,'csrf'=>$_SESSION['csrf'],'current'=>$u,'db'=>['users'=>$users,'slots'=>$slots,'appointments'=>$appointments]]);
 }
 postOnly();$d=data();
 if($action==='login'){
   $email=trim((string)($d['email']??''));$pass=(string)($d['password']??'');$user=(new User($pdo))->findByEmail($email);
   if(!$user||!password_verify($pass,$user['password'])||!(int)$user['is_active'])fail('Invalid email or password, or account inactive.',401);
   session_regenerate_id(true);$_SESSION['user_id']=(int)$user['id'];$_SESSION['role']=$user['role'];$_SESSION['csrf']=bin2hex(random_bytes(32));respond(['ok'=>true,'role'=>$user['role']]);
 }
 if($action==='register'){
   $name=trim((string)($d['name']??''));$email=trim((string)($d['email']??''));$pass=(string)($d['password']??'');
   if(mb_strlen($name)<2||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<8)fail('Enter a valid name, email and password (8+ characters).');
   $check=$pdo->prepare('SELECT id FROM users WHERE email=?');$check->execute([$email]);if($check->fetch())fail('Email already registered.');
   (new User($pdo))->register($name,$email,$pass);session_regenerate_id(true);$_SESSION['user_id']=(int)$pdo->lastInsertId();$_SESSION['role']='student';$_SESSION['csrf']=bin2hex(random_bytes(32));respond(['ok'=>true,'role'=>'student']);
 }
 if($action==='logout'){$_SESSION=[];session_destroy();respond(['ok'=>true]);}
 $u=requireLogin();$uid=(int)$u['id'];$id=(int)($d['id']??0);
 if($action==='book'){
   requireRole('student');$slotId=(int)($d['slotId']??0);$note=trim((string)($d['note']??''));if($slotId<=0||mb_strlen($note)>500)fail('Invalid appointment details.');
   if(!(new Appointment($pdo))->bookAppointment($uid,$slotId,$note))fail('Unable to book: slot full, past, or already booked.');respond(['ok'=>true]);
 }
 if($action==='cancel'){
   requireRole('student');if(!(new Appointment($pdo))->cancelAppointment($uid,$id))fail('Cannot cancel this appointment.');respond(['ok'=>true]);
 }
 if($action==='status'){
   requireRole('admin');$status=(string)($d['status']??'');$model=new Appointment($pdo);
   $ok=$status==='confirmed'?$model->confirmAppointment($id):($status==='rejected'?$model->rejectAppointment($id):false);
   if(!$ok)fail('Only pending future appointments can be confirmed or rejected.');respond(['ok'=>true]);
 }
 if($action==='toggle'){
   requireRole('admin');if($id===$uid)fail('Cannot change your own account.');$s=$pdo->prepare("SELECT is_active FROM users WHERE id=? AND role!='admin'");$s->execute([$id]);$active=$s->fetchColumn();if($active===false)fail('User not found or protected.');if(!(new User($pdo))->updateUserStatus($id,!(bool)$active))fail('Could not update user.');respond(['ok'=>true]);
 }
 if($action==='addSlot'){
   requireRole('admin');$advisor=(int)($d['advisorId']??0);$date=(string)($d['date']??'');$start=(string)($d['start']??'');$end=(string)($d['end']??'');$capacity=(int)($d['capacity']??0);
   $startAt=DateTimeImmutable::createFromFormat('!Y-m-d H:i',"$date $start");$endAt=DateTimeImmutable::createFromFormat('!Y-m-d H:i',"$date $end");
   if(!$startAt||!$endAt||$startAt->format('Y-m-d H:i')!=="$date $start"||$startAt<=new DateTimeImmutable()||$endAt<=$startAt||$capacity<1||$capacity>100)fail('Choose a future date, valid times and capacity 1–100.');
   $s=$pdo->prepare("SELECT id FROM users WHERE id=? AND role='advisor' AND is_active=1");$s->execute([$advisor]);if(!$s->fetch())fail('Invalid advisor.');
   (new TimeSlot($pdo))->createSlot($advisor,$date,$start,$end,$capacity);respond(['ok'=>true]);
 }
 if($action==='deleteSlot'){
   requireRole('admin');$pdo->beginTransaction();$s=$pdo->prepare('SELECT id FROM time_slots WHERE id=? FOR UPDATE');$s->execute([$id]);if(!$s->fetch()){$pdo->rollBack();fail('Slot not found.');}
   $s=$pdo->prepare('SELECT COUNT(*) FROM appointments WHERE slot_id=?');$s->execute([$id]);if((int)$s->fetchColumn()>0){$pdo->rollBack();fail('Cannot delete a slot with appointment history.');}
   $s=$pdo->prepare('DELETE FROM time_slots WHERE id=?');$s->execute([$id]);$pdo->commit();respond(['ok'=>true]);
 }
 fail('Unknown action.',404);
} catch(PDOException $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();if($e->getCode()==='23000')fail('Duplicate or related record prevents this action.');error_log($e->getMessage());fail('Database error. Check configuration and MySQL.',500);
} catch(Throwable $e){error_log($e->getMessage());fail('Server error.',500);}
