<?php
 
namespace App\Http\Controllers;
 
use App\Models\Url;
use Illuminate\Http\Request;
 
class UrlController extends Controller
{   
    public $chars = "abcdefghijklmnopqrstuvwxyz|ABCDEFGHIJKLMNOPQRSTUVWXYZ|0123456789";
    public $checkUrlExists = false;
    public $codeLength = 7;
    
    public function index()
    {   
        // $urls = auth()->user()->urls;

        $urls = Url::where('user_id',auth()->user()->id)
                ->orderBy('created_at','desc')
                ->get();
        
        return response()->json([
            'success' => true,
            'data' => $urls
        ]);
    }
 
    public function show($id)
    {
        $url = auth()->user()->urls()->find($id);
 
        if (!$url) {
            return response()->json([
                'success' => false,
                'message' => 'Url with id ' . $id . ' not found'
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $url->toArray()
        ], 400);
    }
 
    public function store(Request $request)
    {   
        $this->validate($request, [
            'main_url' => 'required',
        ]);
        
        if($this->validateUrlFormat($request->main_url) == false){
            return response()->json([
                'success' => false,
                'message' => 'URL does not have a valid format.'
            ], 500);
        }

        $url = new url();
        $url->main_url = $request->main_url;
        $url->code = $this->createShortCode($request->main_url);
        $url->tiny_url = env('APP_URL').'/u/'.$url->code;
        $url->clicks = 0;
 
        if (auth()->user()->urls()->save($url)){
            return response()->json([
                'success' => true,
                'data' => $url->toArray()
            ]);
        }else{

            return response()->json([
                'success' => false,
                'message' => 'Url could not be added'
            ], 500);
        }
    }
 
    public function update(Request $request, $id)
    {
        $url = auth()->user()->urls()->find($id);

        if (!$url) {
            return response()->json([
                'success' => false,
                'message' => 'Url with id ' . $id . ' not found'
            ], 400);
        }
        
        if($this->validateUrlFormat($request->main_url) == false){
            return response()->json([
                'success' => false,
                'message' => 'URL does not have a valid format.'
            ], 500);
        }
        
        $updated = $url->fill($request->all())->save();
 
        if ($updated)
            return response()->json([
                'success' => true,
                'message' => 'Url updated successfuly'
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Url could not be updated'
            ], 500);
    }
 
    public function destroy($id)
    {
        $url = auth()->user()->urls()->find($id);
 
        if (!$url) {
            return response()->json([
                'success' => false,
                'message' => 'Url with id ' . $id . ' not found'
            ], 400);
        }
 
        if ($url->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Url deleted successfuly'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Url could not be deleted'
            ], 500);
        }
    }

    protected function validateUrlFormat($url){
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    protected function createShortCode($url){
        $shortCode = $this->generateRandomString($this->codeLength);
        return $shortCode;
    }
    protected function generateRandomString($length){
        $sets = explode('|', $this->chars);
        $all = '';
        $randString = '';
        foreach($sets as $set){
            $randString .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++){
            $randString .= $all[array_rand($all)];
        }
        $randString = str_shuffle($randString);
        return $randString;
    }

    public function userClick(Request $request){
        //search url with code
        $url = Url::where('code',$request->code)->first();
        
        //increment click counter 
        $url->clicks++;
        $url->save();

        if ($url!=null)
            return response()->json([
                'success' => true,
                'data' => $url->toArray()
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Url with id ' . $request->code . ' not found'
            ], 404);
    }

}