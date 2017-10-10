<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Validator;
use session;

class Api extends Model
{
	 
	 public function add($table,$data){
			//$fields=$this->getField($table);
			DB::table($table)
			->insert($data);
			return true;		
	 }



	 // add 
	 public function readData($table){
			$data=DB::table($table)
			->get();
			return $data;
	 }

	 // Read data with pagination

	 // add 
	 public function readWithPagin($table,$limit){

	 		//print_r($table);
	 		//exit();
			$data=DB::table($table)
			->paginate($limit);
			return $data;
	 }


	 public function readEdit($table,$id){
			$data = DB::table($table)
			->where('id',$id)
			->get();
			return $data;
	 }

	 public function remove($table,$id){
			DB::table($table)
			->where('id',$id)
			->delete();
			return true;
	 }

	 public function updateData($table,$id,$data){
			DB::table($table)
			->where('id',$id)
			->update($data);
			return true;

	 }


	 // function encrypt
	 public static function encrypt_tb($table){
	 	$tb_en=strrev($table);
	 	$tb_en=str_split($tb_en);
	 	$v='';
	 	foreach ($tb_en as $value) {
	 		$v.=chr(ord($value)-10+ord('@')-ord('B'));
	 	}
	 	$tb_en=$v;
	 	return $tb_en;
	 }

	 public static function decrypt_tb($table_en){
	 	$tb_en=str_split($table_en);
	 	$v='';
	 	foreach ($tb_en as $value) {
	 	
	 			$v.=chr(ord($value)+10-ord('@')+ord('B'));
	 	}
	 	//$tb_en=;
	 	return strrev($v);
	 }

	 public function getPrice($option,$price){
	 	$total=0;
	 	$v=explode(',', $option);
                    if(count($v)>0){
                        foreach ($v as $vs) {
                            foreach ($price as $key => $value) {
                            	if(trim($vs)===$key){
                                	$total+=$price[$key];
	                            }
                            }
                        }
        }
        return $total;
	 }

	 public function getRules($data){
	 	//print_r($data);
		 try{
		 	 //$data=$this->decrypt_tb($data);
			 $r = explode(",",$data);
			 $v=array();
			 $p=array();
			 for($i=0;$i<=count($r)-1;$i++){
					if($i<=0){
						array_push($p, array($r[$i]=>$r[$i+1]));
					}else if($i>0 && $i%2===0){
						array_push($p, array($r[$i]=>$r[$i+1]));
					}
			 }
			 foreach ($p as $value) {
					$v +=$value;
			 }  
			 //print_r($v["name"]);
			 return $v;
		 }catch (Exception $e) {
				echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
		
	 }


	// function check login
	function islog($email, $password) {
		$id =  DB::table('user')->where([
			    ['email', '=', $email],
			    ['password', '=', $password],
			])->value('id');
		if($id>0){
			return $id;
		}else{
			return 0;
		}

		
	}
	// End function check login


	// Function valid token

	function tokenValid($token){
		$users=DB::table('user')->where([
			    ['_token', '=', $token]
			])->get();
		if(count($users)>0){
			return true;
		}else{
			return false;
		}
	}
	// Functio check logged in session
	public static function isLogged(){
		  $id = session('id');
		  if(!empty($id) && isset($id)):
		  	return true;
		  else:
		  	return false;
		  endif;
	}


	function insert($data){
    	$table = $data['table'];    	
    	array_shift($data); // remove _token field
    	array_shift($data); // remove table field
    	return DB::table($table)->insert($data);
    }
	



} // End model


