<?php
include_once(dirname(__FILE__).'/DatumboxAPI.php');
include_once(dirname(__FILE__).'/facebook-php-sdk/src/facebook.php');
class FacebookSentimentAnalysis {
    
    protected $datumbox_api_key; //Your Datumbox API Key. Get it from http://www.datumbox.com/apikeys/view/
    
    protected $app_id; //Your Facebook APP Id. Get it from https://developers.facebook.com/ 
    protected $app_secret; //Your Facebook APP Id. Get it from https://developers.facebook.com/
    
    /**
    * The constructor of the class
    * 
    * @param string $datumbox_api_key   Your Datumbox API Key
    * @param string $app_id             Your Facebook App Id
    * @param string $app_secret         Your Facebook App Secret
    * 
    * @return FacebookSentimentAnalysis  
    */
    public function __construct($datumbox_api_key, $app_id, $app_secret){
        $this->datumbox_api_key=$datumbox_api_key;
        
        $this->app_id=$app_id;
        $this->app_secret=$app_secret;
    }
    
    /**
    * This function fetches the fb posts list and evaluates their sentiment
    * 
    * @param array $facebookSearchParams The Facebook Search Parameters that are passed to Facebook API. Read more here https://developers.facebook.com/docs/reference/api/search/
    * 
    * @return array
    */
    public function sentimentAnalysis($facebookSearchParams) {
        $posts=$this->getPosts($facebookSearchParams);
        
        return $this->findSentiment($posts);
    }
    
    /**
    * Calls the Open Graph Search method of the Facebook API for particular Graph API Search Parameters and returns the list of posts that match the search criteria.
    * 
    * @param mixed $facebookSearchParams The Facebook Search Parameters that are passed to Facebook API. Read more here https://developers.facebook.com/docs/reference/api/search/
    * 
    * @return array $posts
    */
    protected function getPosts($facebookSearchParams) {
        //Use the Facebook SDK Client
        $Client = new Facebook(array(
          'appId'  => $this->app_id,
          'secret' => $this->app_secret,
        ));

        // Get User ID
        $user = $Client->getUser();

        //if Use is not set, redirect to login page
        if(!$user) {
            header('Location: '.$Client->getLoginUrl());
            die();
        }
        
        $posts = $Client->api('/search', 'GET', $facebookSearchParams); //call the service and get the list of posts
        
        unset($Client);
        
        return $posts;
    }
    
    /**
    * Finds the Sentiment for a list of Facebook posts.
    * 
    * @param array $posts List of posts coming from Facebook's API
    * 
    * @param array $posts
    */
    protected function findSentiment($posts) {
        $DatumboxAPI = new DatumboxAPI($this->datumbox_api_key); //initialize the DatumboxAPI client
        
        $results=array();
        if(!isset($posts['data'])) {
            return $results;
        }
        
        foreach($posts['data'] as $post) { //foreach of the posts that we received
            $message=isset($post['message'])?$post['message']:'';
            
            if(isset($post['caption'])) {
                $message.=("\n\n".$post['caption']);
            }
            if(isset($post['description'])) {
                $message.=("\n\n".$post['description']);
            }
            if(isset($post['link'])) {
                $message.=("\n\n".$post['link']);
            }
            
            $message=trim($message);
            if($message!='') {
                $sentiment=$DatumboxAPI->SentimentAnalysis(strip_tags($message)); //call Datumbox service to get the sentiment
                
                if($sentiment!=false) { //if the sentiment is not false, the API call was successful.
                    $tmp = explode('_',$post['id']);
                    if(!isset($tmp[1])) {
                        $tmp[1]='';
                    }
                    $results[]=array( //add the post message in the results
                        'id'=>$post['id'],
                        'user'=>$post['from']['name'],
                        'text'=>$message,
                        'url'=>'https://www.facebook.com/'.$tmp[0].'/posts/'.$tmp[1],
                        'sentiment'=>$sentiment,
                    );
                }
            }
        }
        
        unset($posts);
        unset($DatumboxAPI);
        
        return $results;
    }
}

  
