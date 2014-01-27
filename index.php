<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Lang" content="en">
<title>Datumbox Facebook Sentiment Analysis Demo</title>
</head>
<body>
<h1>Datumbox Facebook Sentiment Analysis</h1>
<p>Type your keyword below to perform Sentiment Analysis on Facebook Posts:</p>
<form method="GET">
    <label>Keyword: </label> <input type="text" name="q" /> 
    <input type="submit" />
</form>

<?php

if(isset($_GET['q']) && $_GET['q']!='') {
    
    include_once(dirname(__FILE__).'/config.php');
    include_once(dirname(__FILE__).'/lib/FacebookSentimentAnalysis.php');

    
    $FacebookSentimentAnalysis = new FacebookSentimentAnalysis(DATUMBOX_API_KEY,FACEBOOK_APP_ID,FACEBOOK_APP_SECRET);

    //Open Graph Search parameters as described at https://developers.facebook.com/docs/reference/api/search/
    $facebookSearchParams=array(
        'q'=>$_GET['q'],
        'type'=>'post',
        //'limit'=>10, //not supported for posts
    );
    $results=$FacebookSentimentAnalysis->sentimentAnalysis($facebookSearchParams);


    ?>
    <h1>Results for "<?php echo $_GET['q']; ?>"</h1>
    <table border="1">
        <tr>
            <td>Id</td>
            <td>User</td>
            <td>Text</td>
            <td>Facebook Link</td>
            <td>Sentiment</td>
        </tr>
        <?php
        foreach($results as $post) {
            
            $color=NULL;
            if($post['sentiment']=='positive') {
                $color='#00FF00';
            }
            else if($post['sentiment']=='negative') {
                $color='#FF0000';
            }
            else if($post['sentiment']=='neutral') {
                $color='#FFFFFF';
            }
            ?>
            <tr style="background:<?php echo $color; ?>;">
                <td><?php echo $post['id']; ?></td>
                <td><?php echo $post['user']; ?></td>
                <td><?php echo $post['text']; ?></td>
                <td><a href="<?php echo $post['url']; ?>" target="_blank">View</a></td>
                <td><?php echo $post['sentiment']; ?></td>
            </tr>
            <?php
        }
        ?>    
    </table>
    <?php
}

?>
  
</body>
</html>
