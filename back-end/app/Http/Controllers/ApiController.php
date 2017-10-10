<?php

namespace App\Http\ApiController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use DB;
use App\Api;
use Validator;
use Excel;
use Redirect;
use session;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyMail;


class ApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    
    }


    public function check(){
        if(Api::isLogged()===false){
                //print_r('/login');
                Redirect::to('/login')->send();
        }
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

/////////////////////////////////////// Call View ///////////////////////////////    
    public function index()
    {
        $this->check();
        return view('home');
    }






///////////////////////// End Call View ////////////////////////////////////////


//////////////////// Api Complete //////////////////////////////////////////


	    

/// get all users

    public function openView($table){
            $this->check();
            $table=Api::decrypt_tb($table);
            if(Schema::hasTable($table)==false){
                die('Invalid URL can\'t access');
                exit();
            }
            $model = new Api;
            $dta =$model->readWithPagin($table,5);
            return view($table.".view",[$table=>$dta]); 

       
    }

    // function get data for ionic
     public function ionicget($token,$table){
            $model = new Api;
            // check session login from client Ionic
            if(empty($token) && $model->tokenValid($token)==false):
                return response()->json("Invalid credentails");
                exit();
            endif;
            // get data from ionic
            $dta =$model->readData($table);
            return response()->json($dta);
    }

    // function get data for ionic
     public function ionicGetId($token,$table,$id){
            $model = new Api;
            // check session login from client Ionic
            if(empty($token) && $model->tokenValid($token)==false):
                return response()->json("Invalid credentails");
                exit();
            endif;
            // get data from ionic
            $data = $model->readEdit($table,$id);
            return response()->json($data[0]);
    }
    
    

    public function openAdd($table){
        $this->check();
        $table=Api::decrypt_tb($table);
        if(Schema::hasTable($table)==false){
                die('Invalid URL can\'t access');
                exit();
            }
        return View($table.".add");
    }
    // store Function 

    // function save data from View
    public function save(Request $request){
        //check session login from client
        $this->check();

        // get Table name from Request
        $table = $request->table;

        // Decrypt Table Name
        $table=Api::decrypt_tb($table);
        $model = new Api;
        $data = $request->all();

        // Prepare rules format
        // And Decrypt
        $rules=$model->getRules(Api::decrypt_tb($request->rules));

        //print_r($rules);
        //exit();
        // Remove data don't sent to database
        unset($data['_token']);
        unset($data['table']);
        unset($data['rules']);

        // Check Validation
        $validator=Validator::make($data,$rules);
        
         // Check and response data or error
        if ($validator->fails()) {
            return back()
                        ->withErrors($validator)
                        ->withInput();
        }else{
            // Excecute insert data
           $model->add($table,$data);
           return redirect('/view'.'/'.Api::encrypt_tb($table));
        }
    }


    // function save data from Ionic
    public function ionicsave(Request $request){
        $model = new Api;
        // check session login from client Ionic
        if(empty($request->_token) && $model->tokenValid($request->_token)==false):
            return response()->json("Invalid credentails");
            exit();
        endif;    
        
        $data = $request->all();

        // get Table name from Request
        $table = $request->table;

        // Remove data don't sent to database
        unset($data['_token']);
        unset($data['table']);
        unset($data['rules']);

        // Prepare rules format
        $rules=$model->getRules($request->rules);

        // Check Validation
        $validator=Validator::make($data,$rules);
        
        // Check and response data or error
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }else{
            // Excecute insert data
            $model->add($table,$data);
            return response()->json(['success'=>true]);
        }
        
    }

     // function save data from Ionic
    public function ionicupdate(Request $request){
        $model = new Api;
        // check session login from client Ionic
        if(empty($request->_token) && $model->tokenValid($request->_token)==false):
            return response()->json("Invalid credentails");
            exit();
        endif;    
        
        $data = $request->all();
        $id=$request->id;
        // get Table name from Request
        $table = $request->table;

        // Remove data don't sent to database
        unset($data['_token']);
        unset($data['table']);
        unset($data['id']);

        
        $model->updateData($table,$id,$data);
        return response()->json(['success'=>true]);
    
        
    }


    // get user edit

    public function openEdit($id,$table){
        $this->check();
        $tb=Api::decrypt_tb($table);
        if(Schema::hasTable($tb)==false){
                die('Invalid URL can\'t access');
                exit();
            }
        $model = new Api;
        $data = $model->readEdit($tb,$id);
        return view($tb.'.edit',[$tb=>$data[0]]);
    }


    /// Update Data Function
    public function update(Request $request){
        $this->check();
        $table = $request->table;
        $table=Api::decrypt_tb($table);
        $id=$request->id;
        $data = $request->all();
        $model = new Api;
        $rules=$model->getRules($request->rules);
        unset($data['_token']);
        unset($data['table']);
        unset($data['id']);
        unset($data['rules']);
        $validator=Validator::make($data,$rules);
        
        if ($validator->fails()) {
            return back()
                        ->withErrors($validator)
                        ->withInput();
        }else{
            $model->updateData($table,$id,$data);
            return redirect()->back()->with('status','Updated successfully...'); 
        }
    }

    /// Destory data

    public function destory($id,$table){
        $this->check();
        $table=Api::decrypt_tb($table);
        if(Schema::hasTable($table)==false){
                die('Invalid URL can\'t access');
                exit();
            }
        $model = new Api;
        $model->remove($table,$id);
        return back();
    }



////////////////////////////////// End Api Complete //////////////////////////


///////////////////////////////// Not Complete ////////////////////////////////////
    

    public function imported(Request $request){
        //var_dump($request->hasFile('file'));
        $prices= ['ai'=>10, 'hd'=>12, 'png' => 9, 'jpg' => 20];
        $model = new Api;
       
        if($request->hasFile('file')){
            //print_r($request->file('file')->getMimeType());
            //exit();
            if($request->file('file')->getClientOriginalExtension()==='csv' && $request->file('file')->getMimeType() ==='text/plain'){
                //echo 'Hello';
                $path = $request->file('file')->getRealPath();

                $data = Excel::load($path, function($reader){},'UTF-8')->get()->toArray();

                 //print_r($data);
                  //  exit();
                
                if(!empty($data) && count($data)){
                    foreach ($data as $key => $value) {
                        
                        $total=$model->getPrice($value['options'],$prices);

                        DB::table('tbl_orders')
                        ->insert($value+['price'=>$total]+['orderdate'=>date('Y-m-d')]);
                    }
                    
                    
                }
                return redirect('/uploads/excel')->with('status', 'Imported successfully!');
            }else{
                return redirect('/uploads/excel')->with('failed', 'Import Failed!');
            }
        }else{
             return redirect('/uploads/excel')->with('failed', 'Import Failed!');
        }
       // \Session::flash('flash_message','successfully saved.');
        
    
    }
    public function import_excel(){
        return View('excel_import.import_excel');
    }









    public function getProducts(){
        $table = "tbl_products";
        $model = new Api;
        $dta =$model->readData($table);
        return response()->json($dta);
    }
    


    


    public function store(Request $request)
    {
        $data = new Api;
        $data->insert($request->all());
        return Redirect::back();

    }

   
    public function deletedata($id){
        $table = "tbl_products";
        $model = new Api;
        $model->remove($table,$id);
    }
    public function updatedata(Request $request){
        $table = "tbl_products";
        $id=$request->id;
        $express=[
         "product_name" => $request->name,
         "price" => $request->price, 
         "qty" =>    $request->qty
        ];
        $model = new Api;
        $model->updateData($table,$id,$express);
        return redirect('/addnew');
    }


    public function editdata($id){
        $table = "tbl_products";
        $model = new Api;
        $data = $model->readEdit($table,$id);
        return response()->json($data[0]);
    }

    /////////////////////////// End Not Complete///////////////////////


    function sendMessage(Request $request){
        echo "Message Send";
        
        $content = array(
            "en" => 'Hello Guy!, How are you today?'
            );
        
        $fields = array(
            'app_id' => "a34225b1-2e90-4f60-b930-c81c1d2bcc5a",
            'include_player_ids' => array('4c816753-cdae-4d5d-97ff-69e82b0cc8af'),
            'data' => array("foo" => "bar"),
            'contents' => $content
        );
        
        $fields = json_encode($fields);
        print("\nJSON sent:\n");
        print($fields);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                                   'Authorization: Basic NGEwMGZmMjItY2NkNy0xMWUzLTk5ZDUtMDAwYzI5NDBlNjJj'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);
        return Redirect::back();
        //return $response;

        
    }

    function addMessage(Request $request){

        DB::table('message')->insert(
            ['user-id' => $userID, 'msg-id' => $messageID]
        );



    }

    function updatemsg(Request $request){
        
        $userid=$request->userid;
        $msgid=$request->msgid;
        DB::table('message')
            ->where('user-id', $userid)
            ->update(['msg-id' => $msgid]);

    }

    /*
    *   function check login
    *   return session user
    */
    public function login(){
        if(Api::isLogged()===true){
                Redirect::to('/')->send();
        }
        return view('forms.login');
    }
    function checklogin(Request $request){
        //return response()->json(['message'=>'successfully !']);
        $validator=Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
            ]);
        $model = new Api;
        $email = $request->email;
        $password = $request->password;
        $id=$model->islog($email, $password);
         if ($validator->fails()) {
            return back()
                        ->withErrors($validator)
                        ->withInput();
        }else{
            if($id!=0 && $id>0){
                session(['id'=>'12']);
                $model->updateData("user",$id,[
                    "_token" => csrf_token()
                    ]);
                $data =  DB::table('user')->where([
                    ['id', '=', $id]
                ])->get();
                return redirect('/');
            }else{
                return redirect('/login')
                ->with('message','Incorrect email or password')
                ->withInput();
            }
        }

    }
    function ioniclogin(Request $request){
        //return response()->json(['message'=>'successfully !']);
        $model = new Api;
        $email = $request->email;
        $password = $request->password;
        $id=$model->islog($email, $password);
        if($id!=0 && $id>0){
            session(['id'=>'12']);
            $model->updateData("user",$id,[
                "_token" => csrf_token()
                ]);
            $data =  DB::table('user')->where([
                ['id', '=', $id]
            ])->get();
            return response()->json(["users"=>$data]);
        }else{
            return response()->json(["users"=> []]);
        }

    }


    /*

        functio logout user
    */
    function logout(){
        session()->forget('id');
        session()->flush();
        return redirect('/login');
    }


    // Reset Password 

    function resetpwd(){
        return view('forms.reset');
    }

    function additems(){
        return view('forms/index');
    }

    function submit(){
        return response()->json(['success'=>"hello world"]);
    }   

    function get(){
        $data = [];
        return $data;
    }	

    function getToken(){
        return response()->json(['_token'=>csrf_token()]);
    }
    public function send(Request $request)
    {
       Mail::send('mail.send', [], function ($message)
         {
            $message->from('advancedmspsr01@gmail.com', 'Chanthou');
            $message->to('hacking0008@gmail.com');
            $message->subject('What do you know?');
        });
       // check for failures
    if (Mail::failures()) {
        print_r('Mail Error');
    }else{
        print_r("Send mail successfully");
    }

    }

         










    
     
    
}
