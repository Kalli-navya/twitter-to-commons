<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Wiki;
use GuzzleHttp\Client as GuzzleClient;
use File;
use Carbon\Carbon;
use App\Twitter;
use App\User;
use App\Upload;

use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Client;
    
class TwitterController extends Controller
{
    public function twitter(Request $request)
    {
        if($request->ajax()){
            $handle = $request->handle;

            $bearerToken = $request->session()->get('bearer_token');
            if (!$bearerToken) {
                $this->twitterToken($request);
            }
            $bearerToken = $request->session()->get('bearer_token');

            $twitterClient = new GuzzleClient(['http_errors' => false]);
            if ($request->tweet_id != 'false') {
                $showOldTweet = '&max_id=' . $request->tweet_id; 
            } else {
                $showOldTweet = '';
            }
            $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json?count=100&screen_name=' . $handle . '&tweet_mode=extended&exclude_replies=1&include_rts=1' . $showOldTweet;
            try {
                $tweetRequest = $twitterClient->get($url, [
                    'headers' => ['Authorization' => 'Bearer '. $bearerToken],
                ]);
            } catch(GuzzleException $e) {
                $response = $e->getResponse();
            }
            $tweets = json_decode($tweetRequest->getBody());
            // take 100 latest uplaoded or canceled tweets
            $uploadMediaIds = Upload::where('status', '>', 0)->take(1000)->pluck('media_id')->toArray();

            $tweetData = array();
            foreach ($tweets as $tweet) {
                if (isset($tweet->entities->media)) {
                    foreach ($tweet->extended_entities->media as $media) {
                        // checking if the tweet is uploaded before
                        if (!in_array ($media->id_str , $uploadMediaIds)) {
                            $tweetData[$media->id_str]['img_url'] = $media->media_url_https;
                            $tweetData[$media->id_str]['image_id'] = $media->id_str;
                            $tweetData[$media->id_str]['tweet_id'] = $tweet->id_str;
                            $tweetData[$media->id_str]['tweet_text'] = $tweet->full_text;
                            foreach ($tweet->entities->hashtags as $hashtag) {
                                $tweetData[$media->id_str]['hashtags'][] = $hashtag->text;
                            }
                        }
                    }
                }
            }
            return $tweetData;
        }

        $wiki = new Wiki;
        $user = $wiki->checkAuth($request);
        $twitters = Twitter::all();

        if (!$user) {
            return view('wiki.twitter.index')->withTwitters($twitters);
        } else {
            return view('wiki.twitter.index')->withuser($user)->withTwitters($twitters);
        }
        
    }
    public function twitterToken(Request $request) {
        // $consumerKey = urlencode("jTzuaBtmmNimzvvkyAYZhP0u4");
        // $apisecretKey = urlencode("NsbSZoDY9BMA4tJdq1QQxEcR6ie5xxD4aJJxKAu7DszI1WaoKB");

        $consumerKey = urlencode(env('TWITTER_CONSUMER_KEY'));
        $apisecretKey = urlencode(env('TWITTER_API_SECRET'));

        $bearerCredentials = $consumerKey.":".$apisecretKey;
        $bearerCredentials = base64_encode ( $bearerCredentials );

        $twitterClient = new GuzzleClient(['http_errors' => false]);

        $bearerTokenRequest = $twitterClient->post('https://api.twitter.com/oauth2/token', [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
                          'Content-Length' => '29',
                          'Accept-Encoding'=> 'gzip',
                          'Authorization' => 'Basic  ' . $bearerCredentials],
            'form_params' => ['grant_type' => "client_credentials"]
        ]);

        // dd($bearerTokenRequest->getBody()->getContents());
        $bearerTokenResponse = json_decode($bearerTokenRequest->getBody());
        $bearerToken = $bearerTokenResponse->access_token;
        $request->session()->put('bearer_token', $bearerToken);

        // //invalidate bearer token
        // $bearerTokenRequest = $twitterClient->post('https://api.twitter.com/oauth2/invalidate_token', [
        //     'headers' => ['Content-Type' => 'application/x-www-form-urlencoded',
        //                   'Content-Length' => '119',
        //                   'User-Agent' => 'twittercom-gyana',
        //                   'Host' => 'api.twitter.com',
        //                   // 'Accept-Encoding'=> 'gzip',
        //                   'Accept' => '*/*',
        //                   'Authorization' => 'Basic  ' . $bearerCredentials],
        //     'form_params' => ['r' => $bearerToken]
        // ]);
    }
    public function initializeTweet(Request $request) {
        $wiki = new Wiki;
        $user = $wiki->checkAuth($request);

        if (!$user) {
            $responseData['status'] = 'error';
            $responseData['message'] = 'Invalid User';
            return $responseData;
        }
        $mediaId = $request->media_id;
        $tweetId = $request->tweet_id;

        $bearerToken = $request->session()->get('bearer_token');

        $twitterClient = new GuzzleClient(['http_errors' => false]);

        $url = 'https://api.twitter.com/1.1/statuses/show/' . $tweetId . '.json?entities=1&tweet_mode=extended';
        
        try {
            $tweetRequest = $twitterClient->get($url, [
                'headers' => ['Authorization' => 'Bearer '. $bearerToken],
            ]);
        } catch(GuzzleException $e) {
            $response = $e->getResponse();
            return $response;
        }

        // $tweet = json_decode($tweetRequest->getBody()); //object
        $tweet = json_decode($tweetRequest->getBody()->getContents(), true);
        foreach ($tweet['extended_entities']['media'] as $media) {
            if ($media['id_str'] == $mediaId) {

                $responseData['status'] = 'success';
                $responseData['media_id'] = $media['id_str'];
                $responseData['handle'] = $tweet['user']['screen_name'];
                //renmove link from the tweet text
                $regex = "@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@";
                $tweetText = preg_replace($regex, ' ', $tweet['full_text']);
                $responseData['tweet_text'] = $tweetText;
                $responseData['tweet_id'] = $tweetId;
                $responseData['media_id'] = $mediaId;
                $responseData['static_category'] = Twitter::where('handle',$tweet['user']['screen_name'])->first()->category;

                return $responseData;
            }
        }

    }

    // upload tweeet
    public function uploadTweet(Request $request) {
        $wiki = new Wiki;
        $wikiUser = $wiki->checkAuth($request);

        if (!$wikiUser) {
            $responseData['status'] = 'error';
            $responseData['message'] = 'Invalid User';
            return $responseData;
        }


        $mediaId = $request->upload_media_id;
        $tweetId = $request->upload_tweet_id;

        $bearerToken = $request->session()->get('bearer_token');

        $twitterClient = new GuzzleClient(['http_errors' => false]);

        $url = 'https://api.twitter.com/1.1/statuses/show/' . $tweetId . '.json?entities=1&tweet_mode=extended';
        
        try {
            $tweetRequest = $twitterClient->get($url, [
                'headers' => ['Authorization' => 'Bearer '. $bearerToken],
            ]);
        } catch(GuzzleException $e) {
            $response = $e->getResponse();
            return $response;
        }

        // $tweet = json_decode($tweetRequest->getBody()); //object
        $tweet = json_decode($tweetRequest->getBody()->getContents(), true);
        $date = Carbon::create($tweet['created_at']);
        $categories = json_decode($request->categories, true);
        $categoryString = '';
        foreach ($categories as $category) {
            $categoryString = $categoryString . '[[Category:'. $category .']]' . "\r\n";
        }

        foreach ($tweet['extended_entities']['media'] as $media) {

            if ($media['id_str'] == $mediaId) {

                $client = $wiki->client;
                $accessToken = $request->session()->get('accessToken');
                $editToken = $request->session()->get('editToken');

                $editToken = json_decode( $client->makeOAuthCall(
                    $accessToken,
                    env('WIKI_URL') . '/w/api.php?action=tokens&format=json'
                ) )->tokens->edittoken;


                $source = $media['media_url_https'];
                $path = public_path(). '/img/temp/temp-' . $mediaId . '.jpg';
                copy($source, $path);

                // $file = file_get_contents($path);
                // $file = File::get($path); 


                $text = '=={{int:filedesc}}==
{{Information
|description={{en|1='. $request->description .'}}
|date='. $date->toDateTimeString() .'
|source=[' . $media['expanded_url'] .' From Twitter account of '. $tweet['user']['name'] .']
|author=[[:en:Government of Odisha|Government of Odisha]]
|permission=
|other versions=
}}

=={{int:license-header}}==

{{GoO-donation}}

'. $categoryString . "\r\n" . '[[Category:Media uploaded using twitter to commons]]';
                
                $ext = pathinfo($media['media_url_https'], PATHINFO_EXTENSION);
                $fileName = str_ireplace(".".$ext,"",$request->name) . '.'. $ext;;
                $apiParams = array(
                    'filename' => $fileName,
                    'action' => 'upload',
                    'file' => new \CurlFile( $path ),
                    'comment'=> 'Uploaded image using twitter to commons',
                    // 'tags' => 'Twitter to Commons',
                    'text' => $text,
                    'token' => $editToken,
                    'format' => 'json',
                );
                $user = User::where('wiki_username', $wikiUser->username)->first();
                if (!$user or $user->count() == 0) {
                    $user = new User;
                    $user->name = $wikiUser->username;
                    $user->wiki_username = $wikiUser->username;
                    $user->email = ' ';
                    $user->save();
                }
                $upload = new Upload;
                $upload->user_id = $user->id;
                $upload->tweet_id = $tweet['id_str'];
                $upload->media_id = $media['id_str'];
                $upload->image_url_local = $path;
                $upload->image_url_twitter = $media['media_url_https'];
                $upload->form_data = json_encode($request->all());
                $upload->save();

                // var_dump($apiParams);die();
                $uploadRequest = $client->makeOAuthCall(
                    $accessToken,
                    env('WIKI_URL') . '/w/api.php',
                    // 'https://commons.wikimedia.org/w/api.php',
                    true,
                    $apiParams
                );
                $uploadResponse = json_decode($uploadRequest);

                if ($uploadResponse->upload->result == 'Success') {
                    $upload->success_url = $uploadResponse->upload->imageinfo->descriptionurl;
                    $upload->status = 1;
                    $upload->save();

                    $responseData['status'] = 'success';
                    $responseData['url'] = $uploadResponse->upload->imageinfo->descriptionurl;
                    $responseData['message'] = 'Upload Successfull!';
                    return $responseData;
                } elseif ($uploadResponse->upload->result == 'Warning') {

                    $upload->response_message = key((array)$uploadResponse->upload->warnings);
                    $upload->save();

                    $responseData['status'] = 'error';
                    $responseData['error'] = key((array)$uploadResponse->upload->warnings);
                    if ($responseData['error'] == 'badfilename') {
                        $responseData['message'] = 'File name exists or Invalid';
                    } elseif ($responseData['error'] == 'duplicate') {
                        $responseData['message'] = 'The file has already been uploaded before.';
                    } elseif ($responseData['error'] == 'exists') {
                        $responseData['message'] = 'The name already taken. Change the name.';
                    } elseif ($responseData['error'] == 'was-deleted') {
                        $responseData['message'] = 'A file with this name has deleted on commons.';
                    }  elseif ($responseData['error'] == 'duplicate-archive') {
                        $responseData['message'] = 'This file has already deleted on commons.';
                    }
                    return $responseData;
                } else {

                }

                var_dump($uploadResponse->upload->result);

            }
        }

    }
    public function cancelTweet(Request $request) {
        $wiki = new Wiki;
        $wikiUser = $wiki->checkAuth($request);

        if (!$wikiUser) {
            $responseData['status'] = 'error';
            $responseData['message'] = 'Invalid User';
            return $responseData;
        }

        $mediaId = $request->cancel_media_id;
        $tweetId = $request->cacnel_tweet_id;

        $bearerToken = $request->session()->get('bearer_token');

        $twitterClient = new GuzzleClient(['http_errors' => false]);

        $url = 'https://api.twitter.com/1.1/statuses/show/' . $tweetId . '.json?entities=1&tweet_mode=extended';
        
        try {
            $tweetRequest = $twitterClient->get($url, [
                'headers' => ['Authorization' => 'Bearer '. $bearerToken],
            ]);
        } catch(GuzzleException $e) {
            $response = $e->getResponse();
            return $response;
        }

        $tweet = json_decode($tweetRequest->getBody()->getContents(), true);

        foreach ($tweet['extended_entities']['media'] as $media) {

            if ($media['id_str'] == $mediaId) {
                $user = User::where('wiki_username', $wikiUser->username)->first();
                if (!$user or $user->count() == 0) {
                    $user = new User;
                    $user->name = $wikiUser->username;
                    $user->wiki_username = $wikiUser->username;
                    $user->email = ' ';
                    $user->save();
                }
                $upload = new Upload;
                $upload->user_id = $user->id;
                $upload->tweet_id = $tweet['id_str'];
                $upload->media_id = $media['id_str'];
                $upload->image_url_twitter = $media['media_url_https'];
                $upload->status = 2;
                $upload->form_data = $request->message;;
                $upload->save();

                $response['status'] = 'success';
                return $response;
            }
        }
        $response['status'] = 'error';
        return $response;
    }

    public function uploads(Request $request) {
        $uploads = Upload::where('status', 1)->orderBy('created_at','desc')->paginate(10);
        return view('wiki.twitter.uploads')->withUploads($uploads);


    }
    public function canceled(Request $request) {
        $uploads = Upload::where('status', 2)->orderBy('created_at','desc')->paginate(10);

        $wiki = new Wiki;
        $wikiUser = $wiki->checkAuth($request);

        if ($wikiUser) {
            $user = User::where('wiki_username', $wikiUser->username)->first();
            if ($user) {
                return view('wiki.twitter.canceled')->withUploads($uploads)->withUser($user);

            }
        }
        return view('wiki.twitter.canceled')->withUploads($uploads);

    }
    public function statistics(Request $request) {
        $uploads =  Upload::all();
        $maxUploader = Upload::select('user_id')->selectRaw('COUNT(*) AS count')
            ->where('status', 1)
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
    return view('wiki.twitter.statistics')->withUploads($uploads)->withMax($maxUploader);

    }
    public function administration(Request $request) {
        $wiki = new Wiki;
        $wikiUser = $wiki->checkAuth($request);

        if (!$wikiUser) {
            return redirect('/');
        }
        $user = User::where('wiki_username', $wikiUser->username)->first();
        if (!$user or $user->count() == 0) {
            return redirect('/');
        }
        if ($user->is_admin == 1) {
            $users = User::all();
            return view('wiki.twitter.administration')->withUsers($users);
        }
        return redirect('/');
    }
    public function ban(Request $request) {
        $wiki = new Wiki;
        $wikiUser = $wiki->checkAuth($request);

        if (!$wikiUser) {
            return redirect('/');
        }
        $user = User::where('wiki_username', $wikiUser->username)->first();
        if (!$user or $user->count() == 0) {
            return redirect('/');
        }
        if ($user->is_admin == 1) {
            $banUser = User::find($request->user_id);
            $banUser->is_banned = 1;
            $banUser->save();
            return redirect('administration');
        }
        return redirect('/');
    }
    public function delete(Request $request, $id) {
        $wiki = new Wiki;
        $wikiUser = $wiki->checkAuth($request);

        if (!$wikiUser) {
            return redirect('/');
        }
        $user = User::where('wiki_username', $wikiUser->username)->first();
        if (!$user or $user->count() == 0) {
            return redirect('/');
        }
        if ($user->is_admin == 1) {
            $upload = Upload::find($id);
            $upload->delete();
            return redirect()->back();
        }
        return redirect('/');
    }

    //redirect the app to authorize
    public function authorizeApp(Request $request) 
    {
        $wiki = new Wiki;
        if(isset($request->url)) {
            $request->session()->put('url', $request->url);
        }

        if (isset($request->oauth_token)) {

            $client = $wiki->client;
            // dd($client);

            $token = $request->session()->get('token');
            $verifyCode = $request->oauth_verifier;
            
            $accessToken = $client->complete( $token,  $verifyCode );

            $request->session()->put('accessToken', $accessToken);

            $editToken = json_decode( $client->makeOAuthCall(
                $accessToken,
                env('WIKI_URL') . '/w/api.php?action=tokens&format=json'
            ) )->tokens->edittoken;

            $request->session()->put('editToken', $editToken);

            if ($request->session()->has('url')) {
                $url = '/' . $request->session()->get('url');
                $request->session()->forget('url');
            } else {
                $url = '';
            }
            return redirect($url);
        }

        $client = $wiki->client;
        $client->setCallback('oob');
        // $client->setCallback( url('https://jsahu.me/wiki/authorize'));
        list( $next, $token ) = $client->initiate();
        $request->session()->put('token', $token);
        return redirect($next);
    }

    // logout user from the app
    public function logout(Request $request)
    {
        $wiki = new Wiki;
        $user = $wiki->checkAuth($request);

        if ($user) {
            $request->session()->forget('accessToken');
            $request->session()->forget('editToken');
        }

        return redirect()->back();
    }

    public function search(Request $request) {
        $query = $request->q;
        $twitterClient = new GuzzleClient(['http_errors' => false]);

        $categoryRequest = $twitterClient->get('https://commons.wikimedia.org/w/api.php?action=opensearch&format=json&formatversion=2&search=category:' . $query . '&namespace=0%7C6%7C12%7C14%7C100%7C106&limit=10&suggest=true', [
            'headers' => ['Content-Type' => 'application/json',
                        ],
        ]);
        $categoryResponse = $categoryRequest->getBody()->getContents();
        $categoriesArray =  json_decode($categoryResponse);
        $categories = [];
        foreach($categoriesArray[1] as $key=>$category){
          $categories[$key]=str_replace("Category:","",$category);
        }
        return json_decode(json_encode($categories));
    }
}
