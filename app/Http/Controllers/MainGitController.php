<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\LikeAndDis;
use DB;
class MainGitController extends Controller
{
    public $client_id = '1267ed87cd4908d427cc';
    public $secret_client = '074acbba58fa27a2a0a121c8f91baceec8bfbf42';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(session()->exists('my_access_token'))
        {
            $access_token = session('my_access_token');
        }
        else
        {
            $access_token = '';
        }
        if($access_token != '')
            return redirect('/store');
        return view('auth.login')->with([
            'access_token' => $access_token,
            'client_id'=> $this->client_id,
            'secret_client'=> $this->secret_client
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $code = $_GET['code'];
        //d725164dbf89b4889a6f
        if($code == '')
        {
            return redirect('/');
        }
        $url = 'https://github.com/login/oauth/access_token';
        $postFields = [
            'client_id'=> $this->client_id,
            'client_secret' => $this->secret_client,
            'code' => $code
        ];
        $connect_repos = curl_init();
        curl_setopt($connect_repos, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($connect_repos, CURLOPT_POST, 1);
        curl_setopt($connect_repos, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($connect_repos, CURLOPT_URL, $url);
        curl_setopt($connect_repos, CURLOPT_POSTFIELDS, $postFields);
        $data = json_decode( curl_exec($connect_repos) ); // получаем и декодируем данные из JSON
        curl_close($connect_repos);
        if(isset($data->access_token) && $data->access_token != ''){

            if($data->access_token != '')
            {
                session()->put('my_access_token', $data->access_token);
                return redirect('/store');
            }
            else{
                dd($data->error_description);
            }
        }
        elseif(isset($data->error) && $data->error != '')
        {
            dd($data->error);
        }

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
//        $this->myMethod();
        $access_token = session('my_access_token');
        $userAgentHeader = 'User-Agent: demo';
        $authHeader = "Authorization: token ".$access_token;
        if($access_token == '')
           {
               dd('Invalid access token');
           }

        $url = 'https://api.github.com/user';
        $connect_repos = curl_init();
        curl_setopt($connect_repos, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($connect_repos, CURLOPT_HTTPHEADER, array('Accept: application/json', $authHeader,$userAgentHeader));
        curl_setopt($connect_repos, CURLOPT_URL, $url);
        $data = json_decode( curl_exec($connect_repos) ); // получаем и декодируем данные из JSON
        curl_close($connect_repos);

        $id_current_user = $data->id;
        $authorized_user = $data->login;

            $url = 'https://api.github.com/user/repos';
            $connect_repos = curl_init();
            curl_setopt($connect_repos, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($connect_repos, CURLOPT_HTTPHEADER, array('Accept: application/json', $authHeader,$userAgentHeader));
            curl_setopt($connect_repos, CURLOPT_URL, $url);
            $repos = json_decode( curl_exec($connect_repos) ); // получаем и декодируем данные из JSON
            curl_close($connect_repos);
            foreach ($repos as $element)
            {
                $is_like = DB::table('like_and_dis')->where(
                    [
                        'id_author'=> $id_current_user,
                        'id_repos' => $element->id,
                        'like'=> 1
                    ]
                )->first();
                $is_deslike = DB::table('like_and_dis')->where(
                    [
                        'id_author'=> $id_current_user,
                        'id_repos' => $element->id,
                        'deslike'=> 1
                    ]
                )->first();
                if($is_like != null)
                $element->like = 1;
                else
                {
                    $element->like = 0;
                }
                if($is_deslike != null)
                  $element->deslike= 1;
                else
                {
                    $element->deslike= 0;
                }

            }
            return view('layouts.repos')->with([
                'repositories' => $repos,
                'current_user' => $id_current_user,
                'authorized_user' => $authorized_user
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function ajaxCall(Request $request)
    {
        $action = $request->action;
        $access_token = session('my_access_token');
        $userAgentHeader = 'User-Agent: demo';
        $authHeader = "Authorization: token ".$access_token;
        switch ($action) {
            case 'like':
                $data = DB::table('like_and_dis')->where(
                    [
                        'id_author'=> $request->id_author,
                        'id_repos' => $request->id,
                        'like'=> 1
                    ]
                )->first();

                if(!$data){
                    $is_deslike = DB::table('like_and_dis')->where(
                        [
                            'id_author'=> $request->id_author,
                            'id_repos' => $request->id,
                            'deslike'=> 1
                        ]
                    )->first();
                    if($is_deslike != null && $is_deslike->id)
                    {
                        $result = LikeAndDis::find($is_deslike->id);
                        $result->id_author = $request->id_author;
                        $result->id_repos = $request->id;
                        $result->like = 1;
                        $result->save();
                    }
                    else{
                        $result = new LikeAndDis();
                        $result->id_author = $request->id_author;
                        $result->id_repos = $request->id;
                        $result->like = 1;
                        $result->save();
                    }

                }
                break;
            case 'dislike':
                $data = DB::table('like_and_dis')->where(
                    [
                        'id_author'=> $request->id_author,
                        'id_repos' => $request->id,
                        'deslike'=> 1
                    ]
                )->first();

                if(!$data){
                    $is_like = DB::table('like_and_dis')->where(
                        [
                            'id_author'=> $request->id_author,
                            'id_repos' => $request->id,
                            'like'=> 1
                        ]
                    )->first();
                    if($is_like != null && $is_like->id)
                    {
                        $result = LikeAndDis::find($is_like->id);
                        $result->id_author = $request->id_author;
                        $result->id_repos = $request->id;
                        $result->deslike = 1;
                        $result->save();
                    }
                    else
                    {
                        $result = new LikeAndDis();
                        $result->id_author = $request->id_author;
                        $result->id_repos = $request->id;
                        $result->deslike = 1;
                        $result->save();
                    }

                }
                break;
            case 'search':

                $username= $request->user_name;
                $search_word = $request->search_word;
                $count = explode(' ', $search_word);
                if(count($count) > 1)
                {
                    $search_word = '';
                    foreach ($count as $element)
                    {
                        if($element == end($count)) {
                            $search_word .= $element;
                        }
                        else
                        {
                            $search_word .=$element.'-';
                        }

                    }
                }
                $url = 'https://api.github.com/search/repositories?q='.$search_word.'+in:name+user:'.$username;
                $connect_repos = curl_init();
                curl_setopt( $connect_repos, CURLOPT_CUSTOMREQUEST, 'GET' );
                curl_setopt($connect_repos, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($connect_repos, CURLOPT_HTTPHEADER, array('Accept: application/json', $authHeader,$userAgentHeader));
                curl_setopt($connect_repos, CURLOPT_URL, $url);
                $data = json_decode( curl_exec($connect_repos) ); // получаем и декодируем данные из JSON
                curl_close($connect_repos);
                die(json_encode($data));
                break;
            case 'show_all':

                $url = 'https://api.github.com/user/repos';
                $id_current_user = $request->cur_user;
                $connect_repos = curl_init();
                curl_setopt($connect_repos, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($connect_repos, CURLOPT_HTTPHEADER, array('Accept: application/json', $authHeader,$userAgentHeader));
                curl_setopt($connect_repos, CURLOPT_URL, $url);
                $data = json_decode( curl_exec($connect_repos) ); // получаем и декодируем данные из JSON
                curl_close($connect_repos);
                foreach ($data as $element)
                {
                    $is_like = DB::table('like_and_dis')->where(
                        [
                            'id_author'=> $id_current_user,
                            'id_repos' => $element->id,
                            'like'=> 1
                        ]
                    )->first();
                    $is_deslike = DB::table('like_and_dis')->where(
                        [
                            'id_author'=> $id_current_user,
                            'id_repos' => $element->id,
                            'deslike'=> 1
                        ]
                    )->first();
                    if($is_like != null)
                        $element->like = 1;
                    else
                    {
                        $element->like = 0;
                    }
                    if($is_deslike != null)
                        $element->deslike= 1;
                    else
                    {
                        $element->deslike= 0;
                    }

                }
                die(json_encode($data));
                break;
            case 'show_one':

                $url = $request->url_value;
                $connect_repos = curl_init();
                curl_setopt($connect_repos, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($connect_repos, CURLOPT_HTTPHEADER, array('Accept: application/json', $authHeader,$userAgentHeader));
                curl_setopt($connect_repos, CURLOPT_URL, $url);
                $data = json_decode( curl_exec($connect_repos) ); // получаем и декодируем данные из JSON
                curl_close($connect_repos);
                $count_like = DB::table('like_and_dis')->where([
                    'like' => 1,
                    'id_repos'=> $data->id
                ])->count();
                $count_deslike = DB::table('like_and_dis')->where([
                    'deslike' => 1,
                    'id_repos'=> $data->id
                ])->count();
                $data->count_like = $count_like;
                $data->count_deslike = $count_deslike;
                die(json_encode($data));
                break;
        }
    }
}
