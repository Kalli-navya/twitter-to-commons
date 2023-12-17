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
use App\NewAccountRequest;

use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Client;

use \CloudConvert\Laravel\Facades\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;
    
class TwitterController extends Controller
{
    // show all recent tweets with details so that user can choose whether to upload or not.
    public function twitter(Request $request)
    {
        if($request->ajax()){
            $handle = $request->handle;

            // ********************************* //
            // ********************************* //

            // for native twitter api v1
            
            // $bearerToken = $request->session()->get('bearer_token');
            // if (!$bearerToken) {
            //     $this->twitterToken($request);
            // }
            // $bearerToken = $request->session()->get('bearer_token');

            // $twitterClient = new GuzzleClient(['http_errors' => false]);
            // if ($request->tweet_id != 'false') {
            //     $showOldTweet = '&max_id=' . $request->tweet_id; 
            // } else {
            //     $showOldTweet = '';
            // }
            // $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json?count=100&screen_name=' . $handle . '&tweet_mode=extended&exclude_replies=1&include_rts=1' . $showOldTweet;
            // try {
            //     $tweetRequest = $twitterClient->get($url, [
            //         'headers' => ['Authorization' => 'Bearer '. $bearerToken],
            //     ]);
            // } catch(GuzzleException $e) {
            //     $response = $e->getResponse();
            // }
            // $tweets = json_decode($tweetRequest->getBody());

            // take 100 latest uplaoded or canceled tweets
            // $uploadMediaIds = Upload::where('status', '>', 0)->take(1000)->pluck('media_id')->toArray();

            // $tweetData = array();

            // foreach ($tweets as $tweet) {
            //     if (isset($tweet->entities->media)) {
            //         foreach ($tweet->extended_entities->media as $media) {
            //             // checking if the tweet is uploaded before
            //             if (!in_array ($media->id_str , $uploadMediaIds)) {
            //                 $tweetData[$media->id_str]['img_url'] = $media->media_url_https;
            //                 $tweetData[$media->id_str]['image_id'] = $media->id_str;
            //                 $tweetData[$media->id_str]['tweet_id'] = $tweet->id_str;
            //                 $tweetData[$media->id_str]['tweet_text'] = $tweet->full_text;
            //                 $tweetData[$media->id_str]['tweet_time'] = $tweet->created_at;
            //                 $tweetData[$media->id_str]['media_type'] = $media->type;
            //                 foreach ($tweet->entities->hashtags as $hashtag) {
            //                     $tweetData[$media->id_str]['hashtags'][] = $hashtag->text;
            //                 }
            //             }
            //             if ($media->type == 'video') {
            //                 $bitrate = 0;
            //                 foreach ($media->video_info->variants as $video) {
            //                     if (isset($video->bitrate)) {
            //                         if ($video->bitrate > $bitrate) {
            //                             $bitrate = $video->bitrate;
            //                             $tweetData[$media->id_str]['video_url'] = $video->url;
            //                         }
            //                     }
            //                 }
            //             }
            //         }
            //     }
            // }
            // return $tweetData;

            // ********************************* //




            // *********************************
            // *********************************

            // https://rapidapi.com/omarmhaimdat/api/twitter154

            // $request->session()->forget('tweets');

            // new code for rapi api
            // $client = new \GuzzleHttp\Client();

            // $response = $client->request('GET', 'https://twitter154.p.rapidapi.com/user/tweets?username='. $handle. '&limit=40&include_replies=false&include_pinned=false', [
            //     'headers' => [
            //         'X-RapidAPI-Host' => 'twitter154.p.rapidapi.com',
            //         'X-RapidAPI-Key' => '410db0d52emshb7f38e2c48fafd1p1e62d8jsn3440a6ea4088',
            //     ],
            // ]);

            // $tweets = json_decode($response->getBody());

            // take 100 latest uplaoded or canceled tweets
            // $uploadMediaIds = Upload::where('status', '>', 0)->take(1000)->pluck('media_id')->toArray();

            // $tweetData = array();

            // $tweetData = array();
            // $request->session()->put('tweets', $tweets);
            // foreach ($tweets as $tweetObject) {
            //     foreach($tweetObject as $tweet) {
            //         // echo $tweet->tweet_id;
            //         if (isset($tweet->extended_entities->media)) {
            //             foreach ($tweet->extended_entities->media as $media) {
            //                 // checking if the tweet is uploaded before
            //                 if (!in_array ($media->id_str , $uploadMediaIds)) {
            //                     $tweetData[$media->id_str]['img_url'] = $media->media_url_https;
            //                     $tweetData[$media->id_str]['image_id'] = $media->id_str;
            //                     $tweetData[$media->id_str]['tweet_id'] = $tweet->tweet_id;
            //                     $tweetData[$media->id_str]['tweet_text'] = $tweet->text;
            //                     $tweetData[$media->id_str]['tweet_time'] = $tweet->creation_date;
            //                     $tweetData[$media->id_str]['media_type'] = $media->type;
            //                     // foreach ($tweet->entities->hashtags as $hashtag) {
            //                     //     $tweetData[$media->id_str]['hashtags'][] = $hashtag->text;
            //                     // }
            //                 }
            //                 if ($media->type == 'video') {
            //                     $bitrate = 0;
            //                     foreach ($media->video_info->variants as $video) {
            //                         if (isset($video->bitrate)) {
            //                             if ($video->bitrate > $bitrate) {
            //                                 $bitrate = $video->bitrate;
            //                                 $tweetData[$media->id_str]['video_url'] = $video->url;
            //                             }
            //                         }
            //                     }
            //                 }
            //             }
            //         }
            //     }
            //     break;
            // }
            // return $tweetData;

            // *********************************

            // *********************************
            // *********************************

            // https://rapidapi.com/omarmhaimdat/api/twitter-v24
            
            // $tweets = $request->session()->get('tweets');

            $request->session()->forget('tweets');
            
            $client = new \GuzzleHttp\Client();

            $response = $client->request('GET', 'https://twitter-v24.p.rapidapi.com/user/tweets?username='. $handle .'&limit=40', [
                'headers' => [
                    'X-RapidAPI-Host' => urlencode(env('RAPIDAPI_HOST')),
                    'X-RapidAPI-Key' => urlencode(env('RAPIDAPI_KEY')),
                ],
            ]);

            $tweetsObject = json_decode($response->getBody());

            $request->session()->put('tweets', $tweetsObject->data->user->result->timeline_v2->timeline->instructions[1]->entries);


            $tweets = $tweetsObject->data->user->result->timeline_v2->timeline->instructions[1]->entries;

            // take 100 latest uplaoded or canceled tweets
            $uploadMediaIds = Upload::where('status', '>', 0)->take(1000)->pluck('media_id')->toArray();

            $tweetData = array();

            foreach($tweets as $tweet) {
                if ($tweet->content->__typename == "TimelineTimelineModule") {
                    foreach($tweet->content->items as $data) {
                        $tweetThred = $data->item->itemContent->tweet_results->result->legacy;
                        if(isset($data->item->itemContent->tweet_results->result->legacy->extended_entities)) {
                           foreach ($data->item->itemContent->tweet_results->result->legacy->extended_entities->media as $media) {
                                if (!in_array ($media->id_str , $uploadMediaIds)) {
                                    $tweetData[$media->id_str]['img_url'] = $media->media_url_https;
                                    $tweetData[$media->id_str]['image_id'] = $media->id_str;
                                    $tweetData[$media->id_str]['tweet_id'] = $tweetThred->id_str;
                                    $tweetData[$media->id_str]['tweet_text'] = $tweetThred->full_text;
                                    $tweetData[$media->id_str]['tweet_time'] = $tweetThred->created_at;
                                    $tweetData[$media->id_str]['media_type'] = $media->type;
                                    foreach ($tweetThred->entities->hashtags as $hashtag) {
                                        $tweetData[$media->id_str]['hashtags'][] = $hashtag->text;
                                    }
                                }
                                if ($media->type == 'video') {
                                    $bitrate = 0;
                                    foreach ($media->video_info->variants as $video) {
                                        if (isset($video->bitrate)) {
                                            if ($video->bitrate > $bitrate) {
                                                $bitrate = $video->bitrate;
                                                $tweetData[$media->id_str]['video_url'] = $video->url;
                                            }
                                        }
                                    }
                                }
                            } 
                        }

                    }
                } else {
                    if(isset($tweet->content->itemContent->tweet_results->result->legacy->extended_entities)) {
                        $tweetThred = $tweet->content->itemContent->tweet_results->result->legacy;

                        foreach ($tweet->content->itemContent->tweet_results->result->legacy->extended_entities->media as $media) {
                            if (!in_array ($media->id_str , $uploadMediaIds)) {
                                $tweetData[$media->id_str]['img_url'] = $media->media_url_https;
                                $tweetData[$media->id_str]['image_id'] = $media->id_str;
                                $tweetData[$media->id_str]['tweet_id'] = $tweetThred->id_str;
                                $tweetData[$media->id_str]['tweet_text'] = $tweetThred->full_text;
                                $tweetData[$media->id_str]['tweet_time'] = $tweetThred->created_at;
                                $tweetData[$media->id_str]['media_type'] = $media->type;
                                foreach ($tweetThred->entities->hashtags as $hashtag) {
                                    $tweetData[$media->id_str]['hashtags'][] = $hashtag->text;
                                }
                            }
                            if ($media->type == 'video') {
                                $bitrate = 0;
                                foreach ($media->video_info->variants as $video) {
                                    if (isset($video->bitrate)) {
                                        if ($video->bitrate > $bitrate) {
                                            $bitrate = $video->bitrate;
                                            $tweetData[$media->id_str]['video_url'] = $video->url;
                                        }
                                    }
                                }
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
        // $aprovedRequest = NewAccountRequest::where('is_approved', 1)->get();
        // $twitterAccounts = $twitters->concat($aprovedRequest);

        if (!$user) {
            return view('wiki.twitter.index')->withTwitters($twitters->sortBy('name'));
        } else {
            return view('wiki.twitter.index')->withuser($user)->withTwitters($twitters->sortBy('name'));
        }
        
    }
    // get single twitter details
    public function getTweet(Request $request)
    {
        if($request->ajax()){
            $tweetIdLink = $request->tweet_id;

            $tweetId = trim(explode('/', trim(substr($tweetIdLink, strpos($tweetIdLink, 'twitter.com') + 12)))[2]);

            // $bearerToken = $request->session()->get('bearer_token');
            // if (!$bearerToken) {
            //     $this->twitterToken($request);
            // }
            // $bearerToken = $request->session()->get('bearer_token');

            // $twitterClient = new GuzzleClient(['http_errors' => false]);
            
            // $url = 'https://api.twitter.com/1.1/statuses/show.json?tweet_mode=extended&id=' .$tweetId;
            // try {
            //     $tweetRequest = $twitterClient->get($url, [
            //         'headers' => ['Authorization' => 'Bearer '. $bearerToken],
            //     ]);
            // } catch(GuzzleException $e) {
            //     $response = $e->getResponse();
            // }
            // $tweet = json_decode($tweetRequest->getBody());

            // $tweetData = array();

            // $client = new \GuzzleHttp\Client();

            // $response = $client->request('GET', 'https://twitter241.p.rapidapi.com/tweet?pid='.$tweetId, [
            //     'headers' => [
            //         'X-RapidAPI-Host' => 'twitter241.p.rapidapi.com',
            //         'X-RapidAPI-Key' => '410db0d52emshb7f38e2c48fafd1p1e62d8jsn3440a6ea4088',
            //     ],
            // ]);

            $tweet = $request->session()->get('tweet');

            $uploadMediaIds = Upload::where('status', '>', 0)->take(1000)->pluck('media_id')->toArray();

            $tweetData = array();
            // $tweet = json_decode($response->getBody());
            ##return $tweet->tweet->extended_entities;
            // $request->session()->put('tweet', $tweet);
            if(isset($tweet->tweet->extended_entities)) {
                foreach ($tweet->tweet->extended_entities->media as $media) {
                    if (!in_array ($media->id_str , $uploadMediaIds)) {
                            // return $tweet;
                            $tweetData[$media->id_str]['img_url'] = $media->media_url_https;
                            $tweetData[$media->id_str]['image_id'] = $media->id_str;
                            $tweetData[$media->id_str]['tweet_id'] = $tweet->tweet->id_str;
                            $tweetData[$media->id_str]['tweet_text'] = $tweet->tweet->full_text;
                            $tweetData[$media->id_str]['tweet_time'] = $tweet->tweet->created_at;
                            $tweetData[$media->id_str]['media_type'] = $media->type;
                            // foreach ($tweet->entities->hashtags as $hashtag) {
                            //     $tweetData[$media->id_str]['hashtags'][] = $hashtag->text;
                            // }
                        }
                    if ($media->type == 'video') {
                        $bitrate = 0;
                        foreach ($media->video_info->variants as $video) {
                            if (isset($video->bitrate)) {
                                if ($video->bitrate > $bitrate) {
                                    $bitrate = $video->bitrate;
                                        $tweetData[$media->id_str]['video_url'] = $video->url;
                                }
                            }
                        }
                    }
                }
            } 
            return $tweetData;
            // if (isset($tweet->entities->media)) {
            //     foreach ($tweet->extended_entities->media as $media) {
            //         $tweetData[$media->id_str]['img_url'] = $media->media_url_https;
            //         $tweetData[$media->id_str]['image_id'] = $media->id_str;
            //         $tweetData[$media->id_str]['tweet_id'] = $tweet->id_str;
            //         $tweetData[$media->id_str]['tweet_text'] = $tweet->full_text;
            //         $tweetData[$media->id_str]['tweet_time'] = $tweet->created_at;
            //         $tweetData[$media->id_str]['media_type'] = $media->type;
            //         foreach ($tweet->entities->hashtags as $hashtag) {
            //             $tweetData[$media->id_str]['hashtags'][] = $hashtag->text;
            //         }
            //         if ($media->type == 'video') {
            //                 $bitrate = 0;
            //                 foreach ($media->video_info->variants as $video) {
            //                     if (isset($video->bitrate)) {
            //                         if ($video->bitrate > $bitrate) {
            //                             $bitrate = $video->bitrate;
            //                             $tweetData[$media->id_str]['video_url'] = $video->url;
            //                         }
            //                     }
            //                 }
            //             }
            //     }
            //     $tweetData[$media->id_str]['handle'] = $tweet->user->screen_name;
            // }
            // return $tweetData;
        }
    }

    //get twitter token
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

        $request->session()->get('tweets');

        $tweets = $request->session()->get('tweets');

        foreach ($tweets as $tweet) {
            if ($tweet->content->__typename == "TimelineTimelineModule") {
                foreach($tweet->content->items as $data) {
                    $tweetThred = $data->item->itemContent->tweet_results->result->legacy;
                    $screenName = $data->item->itemContent->tweet_results->result->core->user_results->result->legacy->screen_name;
                    if(isset($data->item->itemContent->tweet_results->result->legacy->extended_entities)) {

                        if ($tweetThred->id_str ==  $tweetId) {
                            foreach ($data->item->itemContent->tweet_results->result->legacy->extended_entities->media as $media) {
                                $categories = Twitter::where('handle',$screenName);
                                if($categories->count() > 0) {
                                    $category = Twitter::where('handle',$screenName)->first()->category;
                                    $show_permission = 0;
                                } else {
                                    $category = '';
                                    $show_permission = 1;
                                }
                                if ($media->id_str == $mediaId) {
                                    $responseData['status'] = 'success';
                                    $responseData['media_id'] = $media->id_str;
                                    $responseData['handle'] = $screenName;
                                    //renmove link from the tweet text
                                    $regex = "@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@";
                                    $tweetText = preg_replace($regex, ' ', $tweetThred->full_text);
                                    $responseData['tweet_text'] = $tweetText;
                                    $responseData['tweet_id'] = $tweetId;
                                    $responseData['media_id'] = $mediaId;
                                    $responseData['static_category'] = $category;
                                    $responseData['show_permission'] = $show_permission;

                                    return $responseData;
                                }   
                            }
                        }
                    }
                }
            } else {
                if(isset($tweet->content->itemContent->tweet_results->result->legacy->extended_entities)) {
                    $tweetThred = $tweet->content->itemContent->tweet_results->result->legacy;
                    $screenName = $tweet->content->itemContent->tweet_results->result->core->user_results->result->legacy->screen_name;
                    if ($tweetThred->id_str ==  $tweetId) {
                        foreach ($tweet->content->itemContent->tweet_results->result->legacy->extended_entities->media as $media) {
                            $categories = Twitter::where('handle',$screenName);
                            if($categories->count() > 0) {
                                $category = Twitter::where('handle',$screenName)->first()->category;
                                $show_permission = 0;
                            } else {
                                $category = '';
                                $show_permission = 1;
                            }
                            if ($media->id_str == $mediaId) {
                                $responseData['status'] = 'success';
                                $responseData['media_id'] = $media->id_str;
                                $responseData['handle'] = $screenName;
                                //renmove link from the tweet text
                                $regex = "@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@";
                                $tweetText = preg_replace($regex, ' ', $tweetThred->full_text);
                                $responseData['tweet_text'] = $tweetText;
                                $responseData['tweet_id'] = $tweetId;
                                $responseData['media_id'] = $mediaId;
                                $responseData['static_category'] = $category;
                                $responseData['show_permission'] = $show_permission;

                                return $responseData;
                            }   
                        }
                    }
                }
            }
        }



        // old code for calling twitter api

        // $bearerToken = $request->session()->get('bearer_token');

        // $twitterClient = new GuzzleClient(['http_errors' => false]);

        // $url = 'https://api.twitter.com/1.1/statuses/show/' . $tweetId . '.json?entities=1&tweet_mode=extended';
        
        // try {
        //     $tweetRequest = $twitterClient->get($url, [
        //         'headers' => ['Authorization' => 'Bearer '. $bearerToken],
        //     ]);
        // } catch(GuzzleException $e) {
        //     $response = $e->getResponse();
        //     return $response;
        // }

        // $tweet = json_decode($tweetRequest->getBody()); //object

        // foreach ($tweet['extended_entities']['media'] as $media) {
        //     $categories = Twitter::where('handle',$tweet['user']['screen_name']);
        //     if($categories->count() > 0) {
        //         $category = Twitter::where('handle',$tweet['user']['screen_name'])->first()->category;
        //         $show_permission = 0;
        //     } else {
        //         $category = '';
        //         $show_permission = 1;
        //     }
        //     if ($media['id_str'] == $mediaId) {
        //         $responseData['status'] = 'success';
        //         $responseData['media_id'] = $media['id_str'];
        //         $responseData['handle'] = $tweet['user']['screen_name'];
        //         //renmove link from the tweet text
        //         $regex = "@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@";
        //         $tweetText = preg_replace($regex, ' ', $tweet['full_text']);
        //         $responseData['tweet_text'] = $tweetText;
        //         $responseData['tweet_id'] = $tweetId;
        //         $responseData['media_id'] = $mediaId;
        //         $responseData['static_category'] = $category;
        //         $responseData['show_permission'] = $show_permission;

        //         return $responseData;
        //     }
        // }

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

        // $bearerToken = $request->session()->get('bearer_token');

        // $twitterClient = new GuzzleClient(['http_errors' => false]);

        // $url = 'https://api.twitter.com/1.1/statuses/show/' . $tweetId . '.json?entities=1&tweet_mode=extended';
        
        // try {
        //     $tweetRequest = $twitterClient->get($url, [
        //         'headers' => ['Authorization' => 'Bearer '. $bearerToken],
        //     ]);
        // } catch(GuzzleException $e) {
        //     $response = $e->getResponse();
        //     return $response;
        // }

        // // $tweet = json_decode($tweetRequest->getBody()); //object
        // $tweet = json_decode($tweetRequest->getBody()->getContents(), true);

        $tweets = $request->session()->get('tweets');

        $categories = json_decode($request->categories, true);
        $categoryString = '';
        foreach ($categories as $category) {
            if ($category != '') {
                $categoryString = $categoryString . '[[Category:'. $category .']]' . "\r\n";
            }
        }

        $client = $wiki->client;
        $accessToken = $request->session()->get('accessToken');
        $editToken = $request->session()->get('editToken');

        $editToken = json_decode( $client->makeOAuthCall(
            $accessToken,
            env('WIKI_URL') . '/w/api.php?action=query&meta=tokens&format=json'
        ) )->query->tokens->csrftoken;

        foreach ($tweets as $tweet) {
            if ($tweet->content->__typename == "TimelineTimelineModule") {
                foreach($tweet->content->items as $data) {

                    $tweetThred = $data->item->itemContent->tweet_results->result->legacy;
                    $screenName = $data->item->itemContent->tweet_results->result->core->user_results->result->legacy->screen_name;
                    if(isset($data->item->itemContent->tweet_results->result->legacy->extended_entities)) {

                        if($tweetThred->id_str ==  $tweetId) {
                            foreach ($data->item->itemContent->tweet_results->result->legacy->extended_entities->media as $mediaData) {
                                if ($mediaData->id_str == $mediaId) {
                                    $media = $mediaData;
                                }    
                            }
                        }
                    }
                }
            } else {
                if(isset($tweet->content->itemContent->tweet_results->result->legacy->extended_entities)) {
                    $tweetThred = $tweet->content->itemContent->tweet_results->result->legacy;
                    $screenName = $tweet->content->itemContent->tweet_results->result->core->user_results->result->legacy->screen_name;
                    if ($tweetThred->id_str ==  $tweetId) {
                        foreach ($tweet->content->itemContent->tweet_results->result->legacy->extended_entities->media as $mediaData) {
                            if ($mediaData->id_str == $mediaId) {
                                $media = $mediaData;
                            }   
                        }
                    }
                }
            }
        }

        $date = Carbon::create($tweetThred->created_at);

        if ($media->type == 'video') {
            $bitrate = 0;
            foreach ($media->video_info->variants as $video) {
                if (isset($video->bitrate)) {
                    if ($video->bitrate > $bitrate) {
                        $bitrate = $video->bitrate;
                        $url = strtok($video->url, '?');
                        $ext = 'webm';
                        // copy($source, $path);
                    }
                }
            }
            $source = $this->convertFile($url, $mediaId);
            $path = public_path(). '/file/temp/temp-' . $mediaId . '.' . $ext;
        } else {
            $source = $media->media_url_https;
            $ext = pathinfo($source, PATHINFO_EXTENSION);
            $path = public_path(). '/file/temp/temp-' . $mediaId . '.' . $ext;
            copy($source, $path);
        }
        
        // $file = file_get_contents($path);
        // $file = File::get($path); 

        $twitterAccount = Twitter::where('handle',$screenName)->first();
        if ($twitterAccount) {
            $license = $twitterAccount->template;
            $author = $twitterAccount->author;
            $permission = $request->permission;
        } else {
            $license = '{{cc-by-sa-4.0}}';
            $author = '[https://twitter.com/' . $screenName . " " . $screenName . ']';
            $permission = $request->permission;
        }

        $text = '=={{int:filedesc}}==
{{Information
|description={{en|1='. $request->description .'}}
|date='. $date->toDateTimeString() .'
|source=[' . $media->expanded_url .' From Twitter account of '. $screenName .']
|author=' . $author . '
|permission=' . $permission . '
|other versions=
}}

=={{int:license-header}}==

' . $license . '

'. $categoryString . "\r\n" . '[[Category:Media uploaded using twitter to commons]]';
        
        // $ext = pathinfo($media['media_url_https'], PATHINFO_EXTENSION);
        $fileName = str_ireplace(".".$ext,"",$request->name) . '.'. $ext;;
        $apiParams = array(
            'filename' => $fileName,
            'action' => 'upload',
            'file' => new \CurlFile( $path ),
            'comment'=> 'Uploaded new image using twitter to commons',
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
        if ($user->is_banned == 1) {
            $responseData['status'] = 'error';
            $responseData['message'] = 'You are banned from using this tool';
            return $responseData;
        }
        $upload = new Upload;
        $upload->user_id = $user->id;
        $upload->tweet_id = $tweetThred->id_str;
        $upload->media_id = $media->id_str;
        $upload->image_url_local = $path;
        $upload->image_url_twitter = $media->media_url_https;
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

        $uploaders = Upload::select('user_id')->selectRaw('COUNT(*) AS count')
            ->where('status', 1)
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->paginate(10);

    return view('wiki.twitter.statistics')->withUploads($uploads)->withUploaders($uploaders);

    }
    public function administration(Request $request) {
        $users = User::all();
        $accountRequests = NewAccountRequest::all();
        $twitterAccounts = Twitter::all();
        return view('wiki.twitter.administration')->withUsers($users)->withAccountRequests($accountRequests)->withTwitterAccounts($twitterAccounts);

        // $wiki = new Wiki;
        // $wikiUser = $wiki->checkAuth($request);

        // if (!$wikiUser) {
        //     return redirect('/');
        // }
        // $user = User::where('wiki_username', $wikiUser->username)->first();
        // if (!$user or $user->count() == 0) {
        //     return redirect('/');
        // }
        // if ($user->is_admin == 1) {
        //     $users = User::all();
        //     return view('wiki.twitter.administration')->withUsers($users);
        // }
        // return redirect('/');
    }
    public function ban(Request $request) {
        $wiki = new Wiki;
        $wikiUser = $wiki->checkAuth($request);

        if (!$wikiUser) {
            return back()->with('error','Please login.');

        }
        $user = User::where('wiki_username', $wikiUser->username)->first();
        if (!$user or $user->count() == 0) {
            return back()->with('error','Only admins can perform this task.');
        }
        if ($user->is_admin == 1) {
            $banUser = User::find($request->user_id);
            $banUser->is_banned = 1;
            $banUser->save();
            return back()->with('success','The user is banned.');
        }
        return back()->with('error','Only admins can perform this task.');

    }
    public function requestAccount(Request $request) {
        if ($request->method() == 'POST') {
            NewAccountRequest::create($request->all());
            return back()->with('success','The request has been accepted.');
        }
        return view('wiki.twitter.request_new_account');
    }
    public function approve(Request $request, $id) {
        $wiki = new Wiki;
        $wikiUser = $wiki->checkAuth($request);

        if (!$wikiUser) {
            return back()->with('error','Please login.');

        }
        $user = User::where('wiki_username', $wikiUser->username)->first();
        if (!$user or $user->count() == 0) {
            return back()->with('error','Only admins can perform this task.');
        }
        if ($user->is_admin == 1) {
            $accountRequest = NewAccountRequest::find($id);
            $accountRequest->is_approved = 1;
            $accountRequest->approved_by = $user->id;
            $accountRequest->save();

            $twitter = new Twitter;
            $twitter->handle = $accountRequest->handle;
            $twitter->name = $accountRequest->name;
            $twitter->template = $accountRequest->template;
            $twitter->category = $accountRequest->category;
            $twitter->author = $accountRequest->author;
            $twitter->save();
            return back()->with('success','The account has been approved.');
        }
        return back()->with('error','Only admins can perform this task.');

    }
    public function reject(Request $request, $id) {
        $wiki = new Wiki;
        $wikiUser = $wiki->checkAuth($request);

        if (!$wikiUser) {
            return back()->with('error','Please login.');

        }
        $user = User::where('wiki_username', $wikiUser->username)->first();
        if (!$user or $user->count() == 0) {
            return back()->with('error','Only admins can perform this task.');
        }
        if ($user->is_admin == 1) {
            $accountRequest = NewAccountRequest::find($id);
            $accountRequest->is_approved = 2;
            $accountRequest->approved_by = $user->id;
            $accountRequest->save();
            return back()->with('success','The account has been rejected.');
        }
        return back()->with('error','Only admins can perform this task.');

    }
    public function delete(Request $request, $id) {
        $wiki = new Wiki;
        $wikiUser = $wiki->checkAuth($request);

        if (!$wikiUser) {
            return back()->with('error','Please login.');
        }
        $user = User::where('wiki_username', $wikiUser->username)->first();
        if (!$user or $user->count() == 0) {
            return back()->with('error','Only admins can perform this task.');
        }
        if ($user->is_admin == 1) {
            $upload = Upload::find($id);
            $upload->delete();
            return back()->with('success','The tweet has been deleted.');
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
                env('WIKI_URL') . '/w/api.php?action=query&meta=tokens&format=json'
            ) )->query->tokens->csrftoken;

            $request->session()->put('editToken', $editToken);

            if ($request->session()->has('url')) {
                $url = '/' . $request->session()->get('url');
                $request->session()->forget('url');
            } else {
                $url = '/';
            }
            return redirect('/');
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

    public function searchTwitterUser(Request $request) {
        $query = $request->q;

        $twitterClient = new GuzzleClient(['http_errors' => false]);

        $bearerToken = $request->session()->get('bearer_token');

        $twitterClient = new GuzzleClient(['http_errors' => false]);

        $url = 'https://api.twitter.com/1.1/users/lookup.json?screen_name=' . $query;

        try {
            $twitterUserRequest = $twitterClient->get($url, [
                'headers' => ['Authorization' => 'Bearer '. $bearerToken],
            ]);
        } catch(GuzzleException $e) {
            $response = $e->getResponse();
            return $response;
        }

        $users = json_decode($twitterUserRequest->getBody()->getContents(), true);
        var_dump($users);
    }

    public function convertFile($sourceUrl, $mediaId) {
        if(strpos($sourceUrl, 'ext_tw_video') !== false){
            $uri = $sourceUrl;
            $heightWidth = explode('/', $uri);
            $heightWidthUri = explode('x', $heightWidth[7]);
            $width = $heightWidthUri[0];
            $height = $heightWidthUri[1];
        } else{
            $uri = $sourceUrl;
            $heightWidth = explode('/', $uri);
            $heightWidthUri = explode('x', $heightWidth[6]);
            $width = $heightWidthUri[0];
            $height = $heightWidthUri[1];
        }
        
        $job = CloudConvert::jobs()->create(
            (new Job())
            ->setTag($mediaId)
            ->addTask(
                (new Task('import/url', 'import-my-file'))
                    ->set('url', $sourceUrl)
            )
            ->addTask(
                (new Task('convert', 'convert-my-file'))
                    ->set('input', 'import-my-file')
                    ->set('output_format', 'webm')
                    ->set('height', $height)
                    ->set('width', $width)
            )
            ->addTask(
                (new Task('export/url', 'export-my-file'))
                    ->set('input', 'convert-my-file')
            )
        );

        CloudConvert::jobs()->wait($job);

        foreach ($job->getExportUrls() as $file) {
            $path = public_path(). '/file/temp/temp-' . $mediaId . '.webm';
            copy($file->url, $path);
            return $file->url;
        }
    }

}
